<?php

use App\Http\Controllers\Api\PublicStatsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FatwaController;
use App\Http\Controllers\QuestionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Darul Ifta Portal
|--------------------------------------------------------------------------
*/

// Public: Portal Numbers & Statistics Details API (Reusable across web, apps, cards)
Route::get('/stats', [PublicStatsController::class, 'index']);
Route::get('/public/stats', [PublicStatsController::class, 'index']);
Route::get('/public/stats/cards', [PublicStatsController::class, 'cards']);

// Centralized Global Visitor Analytics API
Route::get('/admin/visitor-analytics/summary', function () {
    return response()->json([
        'status'  => 'success',
        'message' => 'Global visitor analytics summary retrieved successfully',
        'data'    => \App\Services\GlobalVisitorAnalyticsService::getGlobalSummary()
    ]);
});

// Public: Search Filters (Madhabs & Popular Categories with Fatwa Count) API
Route::get('/public/search-filters', function () {
    $filters = \Illuminate\Support\Facades\Cache::remember('api_public_search_filters_v3', 1800, function () {
        $madhabs = \App\Models\Madhab::orderBy('id')->get(['id', 'name', 'slug']);
        
        // Fetch top 6-8 popular categories with published fatwas count
        $popularCategories = \App\Models\Category::withCount(['questions' => function ($q) {
                $q->where('status', 'published');
            }])
            ->having('questions_count', '>', 0)
            ->orderByDesc('questions_count')
            ->limit(8)
            ->get(['id', 'name', 'slug']);

        // Backfill with top categories if fewer than 6 have published fatwas
        if ($popularCategories->count() < 6) {
            $existingIds = $popularCategories->pluck('id')->toArray();
            $needed = 8 - $popularCategories->count();
            $fillers = \App\Models\Category::whereNotIn('id', $existingIds)
                ->whereNull('parent_id')
                ->withCount(['questions' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->limit($needed)
                ->get(['id', 'name', 'slug']);
            $popularCategories = $popularCategories->concat($fillers);
        }

        return [
            'madhabs' => $madhabs,
            'popular_categories' => $popularCategories->take(8)->values()
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $filters
    ]);
});

Route::get('/public/madhabs', function () {
    $madhabs = \Illuminate\Support\Facades\Cache::remember('api_public_madhabs', 3600, function () {
        return \App\Models\Madhab::orderBy('id')->get(['id', 'name', 'slug']);
    });
    return response()->json(['status' => 'success', 'data' => $madhabs]);
});

Route::get('/public/popular-categories', function () {
    $categories = \Illuminate\Support\Facades\Cache::remember('api_public_popular_categories_v3', 1800, function () {
        $popular = \App\Models\Category::withCount(['questions' => function ($q) {
                $q->where('status', 'published');
            }])
            ->having('questions_count', '>', 0)
            ->orderByDesc('questions_count')
            ->limit(8)
            ->get(['id', 'name', 'slug']);

        if ($popular->count() < 6) {
            $existingIds = $popular->pluck('id')->toArray();
            $fillers = \App\Models\Category::whereNotIn('id', $existingIds)
                ->whereNull('parent_id')
                ->withCount(['questions' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->orderBy('sort_order')
                ->limit(8 - $popular->count())
                ->get(['id', 'name', 'slug']);
            $popular = $popular->concat($fillers);
        }
        return $popular->take(8)->values();
    });
    return response()->json(['status' => 'success', 'data' => $categories]);
});

// Include Dedicated Mufti Mobile App API Routes
require __DIR__ . '/mufti_mobile_app_api.php';

// Public: List published fatwas with optional search
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

    return response()->json([
        'status' => 'success',
        'data' => $fatwas,
    ]);
});

// Public: Get categories tree
Route::get('/categories', function () {
    $categories = \Illuminate\Support\Facades\Cache::remember('api_categories', 3600, function () {
        return \App\Models\Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
    });
    return response()->json(['status' => 'success', 'data' => $categories]);
});

// Public: Get single fatwa
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

// Auth: Public Login
Route::post('/auth/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json([
        'status' => 'success', 
        'token'  => $token, 
        'user'   => [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'mobile'             => $user->mobile,
            'role'               => $user->role ?? 'mufti',
            'can_check_approve'  => (bool)($user->can_check_approve ?? false),
            'avatar'             => $user->avatar ? asset($user->avatar) : null,
            'country'            => $user->country ?? 'Global Scholar',
            'is_active'          => (bool)($user->is_active ?? true),
        ]
    ]);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
});


/*
|--------------------------------------------------------------------------
| System Health API Endpoints
|--------------------------------------------------------------------------
*/
Route::prefix('system-health')->name('api.system_health.')->group(function () {
    Route::post('/auth/request-otp', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'requestOtp'])->name('request_otp');
    Route::post('/auth/verify-otp', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'verifyOtp'])->name('verify_otp');
    Route::post('/auth/logout', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'logout'])->name('logout');

    Route::middleware('system_health.auth')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'overview'])->name('overview');
        Route::get('/server', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'server'])->name('server');
        Route::get('/php', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'phpRuntime'])->name('php');
        Route::get('/laravel', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'laravel'])->name('laravel');
        Route::get('/database', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'database'])->name('database');
        Route::get('/resources', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'resources'])->name('resources');
        Route::get('/api-metrics', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'apiMetrics'])->name('api_metrics');
        Route::get('/recent-requests', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'recentRequests'])->name('recent_requests');
        Route::get('/errors', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'errors'])->name('errors');
        Route::get('/security', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'security'])->name('security');
        Route::post('/synthetic-ping', [\App\Http\Controllers\SystemHealth\SystemHealthPortalController::class, 'runSyntheticPing'])->name('synthetic_ping');
    });
});
