<?php

namespace App\Http\Controllers;

use App\Mail\NewQuestionMail;
use App\Models\Category;
use App\Models\FatwaActivityLog;
use App\Models\QuestionAndAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class QuestionController extends Controller
{
    public function create()
    {
        $madhabs = \App\Models\Madhab::all();
        return view('ask-question', compact('madhabs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check for 5-minute patience (Throttling)
        $lastQuestion = QuestionAndAnswer::where('user_id', Auth::id())
            ->latest('created_at')
            ->first();

        if ($lastQuestion && $lastQuestion->created_at->addMinutes(5)->isFuture()) {
            $diff = $lastQuestion->created_at->addMinutes(5)->diffForHumans();
            return redirect()->back()->withInput()->with('error', "Please wait. You can submit another question in {$diff}. A 5-minute patience is required between submissions.");
        }

        $rules = [
            'body'        => ['required', 'string', 'min:20'],
            'madhab_id'   => ['required', 'exists:madhabs,id'],
            'title'       => ['required', 'string', 'max:200'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];

        $request->validate($rules);

        // Deduplication Guard: If identical question was submitted in the last 2 minutes, don't create duplicate
        $existing = QuestionAndAnswer::where('user_id', Auth::id())
            ->where('title', $request->title)
            ->where('body', $request->body)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->latest('id')
            ->first();

        if ($existing) {
            return redirect()->route('home')->with('success', 'Your question has been submitted successfully! Question ID: ' . $existing->question_id);
        }

        // If mobile or country were submitted directly in request, persist them
        if ($request->filled('mobile') && empty($user->mobile)) {
            $user->mobile = trim($request->mobile);
            $user->save();
        }
        if ($request->filled('country') && (empty($user->country) || strtolower($user->country) === 'not set')) {
            $user->country = trim($request->country);
            $user->save();
        }

        // Check if the user's profile is incomplete (Mobile and Country are strictly mandatory)
        $hasMobile  = !empty(trim((string)$user->mobile));
        $hasCountry = !empty(trim((string)$user->country)) && strtolower(trim((string)$user->country)) !== 'not set';
        $hasGender  = !empty($user->gender) && strtolower($user->gender) !== 'not set';
        $hasMadhab  = !empty($user->madhab_id);
        $hasAddress = !empty($user->address_line_1);

        $isProfileIncomplete = !$hasMobile || !$hasCountry || !$hasGender || !$hasMadhab || !$hasAddress || !$user->profile_completed;

        if ($isProfileIncomplete) {
            // Save question to session
            session([
                'pending_question' => [
                    'title'          => $request->title,
                    'body'           => $request->body,
                    'madhab_id'      => $request->madhab_id,
                    'category_id'    => $request->category_id,
                ]
            ]);

            return redirect()->route('profile.complete')->with('warning', 'Mobile number and Country are strictly mandatory to submit your question. Please complete your profile details.');
        }

        // Profile is complete, post the question directly
        $question = self::saveQuestion(Auth::id(), [
            'title'          => $request->title,
            'body'           => $request->body,
            'madhab_id'      => $request->madhab_id,
            'category_id'    => $request->category_id,
        ]);

        return redirect()->route('home')->with('success', 'Your question has been submitted successfully! Question ID: ' . $question->question_id);
    }

    /**
     * Common helper method to save the question, handle file migration, log activity, and notify admin/user.
     */
    public static function saveQuestion($userId, array $data)
    {
        $user = User::find($userId);
        if ($user) {
            $hasMobile  = !empty(trim((string)$user->mobile));
            $hasCountry = !empty(trim((string)$user->country)) && strtolower(trim((string)$user->country)) !== 'not set';
            if (!$hasMobile || !$hasCountry) {
                throw new \InvalidArgumentException("Mobile number and Country are strictly mandatory before submitting a question.");
            }
        }

        // 1. Strict Idempotency / Deduplication Guard: Check if identical question was created in the last 2 minutes
        $existing = QuestionAndAnswer::where('user_id', $userId)
            ->where('title', $data['title'])
            ->where('body', $data['body'])
            ->where('created_at', '>=', now()->subMinutes(2))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        // 2. Wrap creation in DB transaction
        $question = \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $data) {
            return QuestionAndAnswer::create([
                'question_id' => QuestionAndAnswer::generateQuestionId(),
                'user_id'     => $userId,
                'category_id' => $data['category_id'] ?? null,
                'title'       => $data['title'],
                'body'        => $data['body'],
                'madhab_id'   => $data['madhab_id'],
                'status'      => 'pending',
            ]);
        });

        $madhabName = \App\Models\Madhab::find($data['madhab_id'])?->name ?? 'General';

        // Log the first event in the fatwa timeline
        FatwaActivityLog::log(
            $question->id, 'question_submitted', 'Question Submitted by Questioner',
            'bi-send', 'secondary',
            'Submitted in ' . $madhabName . ' madhab.', $userId,
            null, $question->created_at
        );

        if (class_exists(\App\Models\UserActivityLog::class)) {
            $qUser = \App\Models\User::find($userId);
            \App\Models\UserActivityLog::record(
                action: 'question_submitted',
                description: 'Submitted new inquiry #' . $question->question_id . ' ("' . \Illuminate\Support\Str::limit($question->title, 40) . '")',
                status: 'success',
                user: $qUser,
                identifier: $qUser?->email,
                actionCategory: 'question',
                contextData: [
                    'question_id' => $question->question_id,
                    'category_id' => $question->category_id,
                    'madhab_id'   => $question->madhab_id,
                ]
            );
        }

        // Email admin
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
        if ($admin) {
            try {
                Mail::to($admin->email)->send(new \App\Mail\NewQuestionMail($question));
            } catch (\Exception $e) {
                \Log::error("Failed to send NewQuestionMail to admin: " . $e->getMessage());
            }
        }

        // Email the user
        if ($user) {
            try {
                Mail::to($user->email)->send(new \App\Mail\UserQuestionSubmittedMail($question));
            } catch (\Exception $e) {
                \Log::error("Failed to send UserQuestionSubmittedMail to user: " . $e->getMessage());
            }
        }

        return $question;
    }
}
