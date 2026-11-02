<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationOtpMail;

/*
|--------------------------------------------------------------------------
| User Mobile App Routes
|--------------------------------------------------------------------------
*/

// --- PUBLIC ROUTES ---

// List fatwas
Route::get('/fatwas', function (Request $request) {
    $page = $request->get('page', 1);
    $search = $request->get('search', '');
    $cacheKey = "api_fatwas_page_{$page}_search_" . md5($search);

    $fatwas = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($search) {
        $query = \App\Models\Fatwa::with(['question.category', 'question.madhab', 'muftiAnswer.mufti', 'muftiAnswer.supportingReferences'])
                    ->latest('published_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('question', function ($sq) use ($search) {
                    $sq->where('body', 'like', "%{$search}%")
                       ->orWhere('title', 'like', "%{$search}%");
                })
                ->orWhereHas('muftiAnswer', function ($aq) use ($search) {
                    $aq->where('full_answer', 'like', "%{$search}%")
                       ->orWhere('summary', 'like', "%{$search}%");
                });
            });
        }

        return $query->paginate(20);
    });

    return response()->json(['status' => 'success', 'data' => $fatwas]);
});

// Single fatwa (optimized with ID lookup to bypass model binding DB queries)
Route::get('/fatwas/{fatwaId}', function ($fatwaId) {
    $fatwa = \Illuminate\Support\Facades\Cache::remember("api_fatwa_{$fatwaId}", 3600, function () use ($fatwaId) {
        $item = \App\Models\Fatwa::find($fatwaId);
        if ($item) {
            $item->load(['question.category', 'question.madhab', 'muftiAnswer.mufti', 'muftiAnswer.supportingReferences', 'publisher']);
        }
        return $item;
    });

    if (!$fatwa) {
        return response()->json(['status' => 'error', 'message' => 'Fatwa not found'], 404);
    }

    return response()->json(['status' => 'success', 'data' => $fatwa]);
});

// Categories
Route::get('/categories', function () {
    $categories = \Illuminate\Support\Facades\Cache::remember('api_categories', 3600, function () {
        return \App\Models\Category::with('children')
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get();
    });
    return response()->json(['status' => 'success', 'data' => $categories]);
});

// Madhabs
Route::get('/madhabs', function () {
    $madhabs = \Illuminate\Support\Facades\Cache::remember('api_madhabs', 86400, function () {
        return \App\Models\Madhab::all();
    });
    return response()->json(['status' => 'success', 'data' => $madhabs]);
});


// --- AUTHENTICATION ROUTES ---

// Standard Password Login
Route::post('/auth/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['status' => 'success', 'token' => $token, 'user' => $user]);
});

// Send OTP for Login
Route::post('/auth/otp/send', function (Request $request) {
    $request->validate(['email' => 'required|email|exists:users,email']);
    $user = User::where('email', $request->email)->first();
    
    $otp = rand(100000, 999999);
    $user->update([
        'otp' => $otp,
        'otp_expires_at' => now()->addMinutes(15),
    ]);

    try {
        Mail::to($user->email)->send(new RegistrationOtpMail($otp));
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Failed to send OTP email'], 500);
    }

    return response()->json(['status' => 'success', 'message' => 'OTP sent successfully']);
});

// Verify OTP Login
Route::post('/auth/otp/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|numeric'
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user->otp == $request->otp && $user->otp_expires_at > now()) {
        $user->update(['otp' => null, 'otp_expires_at' => null]);
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['status' => 'success', 'token' => $token, 'user' => $user]);
    }

    return response()->json(['status' => 'error', 'message' => 'Invalid or expired OTP'], 401);
});

// 3-Step Registration
Route::post('/auth/register', function (Request $request) {
    $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users',
        'mobile'    => 'required|string',
        'password'  => 'required|min:8',
        'madhab_id' => 'nullable|exists:madhabs,id',
    ]);

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'mobile'    => $request->mobile,
        'password'  => Hash::make($request->password),
        'madhab_id' => $request->madhab_id,
        'profile_completed' => true,
    ]);

    $user->assignRole('questioner');
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['status' => 'success', 'token' => $token, 'user' => $user], 201);
});


// --- PROTECTED ROUTES ---

Route::middleware('auth:sanctum')->group(function () {
    
    // Submit inquiry with file upload support (Mobile number and Country are strictly mandatory)
    Route::post('/questions', function (Request $request) {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'body'        => 'required|min:20',
            'category_id' => 'required|exists:categories,id',
            'madhab_id'   => 'nullable|exists:madhabs,id',
            'mobile'      => 'nullable|string|min:7|max:20',
            'country'     => 'nullable|string|max:100',
            'files.*'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB limit
        ]);

        $user = $request->user();

        // If mobile or country were passed with the inquiry, persist them
        if ($request->filled('mobile') && empty($user->mobile)) {
            $user->mobile = trim($request->input('mobile'));
            $user->save();
        }
        if ($request->filled('country') && (empty($user->country) || strtolower($user->country) === 'not set')) {
            $user->country = trim($request->input('country'));
            $user->save();
        }

        // Strictly verify mandatory mobile and country
        if (empty(trim((string)$user->mobile))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mobile number is strictly required to submit an inquiry. Please complete your profile details or provide mobile number.'
            ], 422);
        }

        if (empty(trim((string)$user->country)) || strtolower(trim((string)$user->country)) === 'not set') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Country is strictly required to submit an inquiry. Please complete your profile details or provide country.'
            ], 422);
        }

        $question = \App\Models\QuestionAndAnswer::create([
            'question_id' => \App\Models\QuestionAndAnswer::generateQuestionId(),
            'user_id'     => $user->id,
            'category_id' => $request->category_id,
            'madhab_id'   => $request->madhab_id,
            'title'       => $request->title,
            'body'        => $request->body,
            'status'      => 'pending',
        ]);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('questions', 'public');
                \App\Models\QuestionFile::create([
                    'question_id'   => $question->id,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path'     => $path,
                    'file_type'     => $file->getClientMimeType(),
                    'file_size'     => $file->getSize(),
                ]);
            }
        }

        return response()->json(['status' => 'success', 'question_id' => $question->question_id], 201);
    });

    // List user's inquiries
    Route::get('/questions', function (Request $request) {
        $questions = \App\Models\QuestionAndAnswer::where('user_id', $request->user()->id)
                    ->with(['category', 'madhab'])
                    ->latest()
                    ->paginate(20);
        return response()->json(['status' => 'success', 'data' => $questions]);
    });

    // User Profile
    Route::get('/profile', function (Request $request) {
        return response()->json(['status' => 'success', 'data' => $request->user()]);
    });
});
