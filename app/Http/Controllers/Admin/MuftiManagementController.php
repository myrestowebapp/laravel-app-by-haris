<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HandleAvatars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class MuftiManagementController extends Controller
{
    use HandleAvatars;
    /** List all scholars with the 'mufti' role */
    public function index()
    {
        $query = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->withCount([
                'assignedQuestions as total_assigned',
                'assignedQuestions as pending_count' => fn($q) => $q->where('status', 'assigned'),
                'assignedQuestions as answered_count' => fn($q) => $q->whereIn('status', ['answered', 'approved', 'published']),
                'assignedQuestions as published_count' => fn($q) => $q->where('status', 'published'),
                'assignedQuestions as total_answers' => fn($q) => $q->whereIn('status', ['answered', 'approved', 'published']),
            ])
            ->selectSub(function ($q) {
                $q->from('user_login_logs')
                    ->whereColumn('user_login_logs.user_id', 'users.id')
                    ->selectRaw('COALESCE(SUM(duration_seconds), 0)');
            }, 'total_time_spent')
            // 1. Scholars who never logged in are placed at the very end
            ->orderByRaw('CASE WHEN (users.last_login_at IS NOT NULL OR users.login_count > 0) THEN 0 ELSE 1 END ASC')
            // 2. Profile image priority: scholars with custom profile image get higher priority
            ->orderByRaw('CASE WHEN (users.avatar IS NOT NULL AND users.avatar != "" AND users.avatar != "default.png") THEN 0 ELSE 1 END ASC')
            // 3. Highest published fatwas ("King" status)
            ->orderByDesc('published_count')
            // 4. Platform usage and engagement
            ->orderByDesc('total_time_spent')
            ->orderByDesc('login_count')
            // 5. Recent activity
            ->latest('last_seen_at')
            ->latest('last_login_at')
            ->orderBy('users.id');

        $muftis = $query->paginate(8);

        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $diffSql = $isSqlite ? 'ROUND(AVG(julianday(updated_at) - julianday(created_at)), 1)' : 'AVG(DATEDIFF(updated_at, created_at))';

        $muftis->getCollection()->each(function ($mufti) use ($diffSql) {
            $mufti->avg_days = \App\Models\QuestionAndAnswer::where('assigned_mufti_id', $mufti->id)
                ->whereIn('status', ['answered', 'approved', 'published'])
                ->selectRaw("{$diffSql} as avg")
                ->value('avg');
            $mufti->avg_days = $mufti->avg_days ? round($mufti->avg_days, 1) : null;
        });

        $onlineCount = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->count();

        $totalMuftis = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->count();
        $totalLogins = (int) User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->sum('login_count');

        return view('admin.muftis.index', compact('muftis', 'onlineCount', 'totalMuftis', 'totalLogins'));
    }

    /** Show the form for creating a new scholar */
    public function create()
    {
        $madhabs = \App\Models\Madhab::all();
        return view('admin.muftis.create', compact('madhabs'));
    }

    /** Store a newly created scholar in storage */
    public function store(Request $request)
    {
        $minDob = now()->subYears(18)->format('Y-m-d');

        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'despl_name'       => ['nullable', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'         => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'gender'           => ['nullable', 'string', 'in:Male,Female,Other'],
            'dob'              => ['nullable', 'date', 'before_or_equal:' . $minDob],
            'country_code'     => ['nullable', 'string', 'max:10'],
            'mobile'           => ['nullable', 'string', 'max:20'],
            'country'          => ['nullable', 'string', 'max:100'],
            'state'            => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'address_line_1'   => ['nullable', 'string', 'max:255'],
            'madhab'           => ['nullable', 'array'],
            'mufti_sort_order' => ['nullable', 'integer', 'min:0', 'max:9998'],
        ], [
            'dob.before_or_equal' => 'Scholar must be at least 18 years of age (Date of birth cannot be under 18 years old).',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'despl_name'        => $request->filled('despl_name') ? $request->despl_name : $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'gender'            => $request->gender,
            'dob'               => $request->dob ?: null,
            'country_code'      => $request->input('country_code', '+91'),
            'mobile'            => $request->mobile,
            'country'           => $request->input('country', 'India'),
            'state'             => $request->state,
            'district'          => $request->district,
            'address_line_1'    => $request->address_line_1 ?: $request->district,
            'madhab_id'         => !empty($request->madhab) ? head($request->madhab) : null,
            'is_active'         => $request->has('is_active'),
            'can_check_approve' => $request->has('can_check_approve'),
            'profile_completed' => true,
            'mufti_sort_order'  => (int) $request->input('mufti_sort_order', 0),
        ]);

        $user->assignRole('mufti');

        return redirect()->route('admin.muftis.index')->with('success', 'Mufti profile created successfully.');
    }

    /** Display the specified scholar's dashboard/activity */
    public function show(User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(404);
        }
        return redirect()->route('admin.muftis.activity', $user);
    }

    /** Show the form for editing the specified scholar */
    public function edit(User $user)
    {
        // Ensure we are only editing a mufti
        if (!$user->hasRole('mufti')) {
            abort(403, 'Unauthorized action.');
        }

        $madhabs = \App\Models\Madhab::all();
        return view('admin.muftis.edit', compact('user', 'madhabs'));
    }

    /** Update the specified scholar in storage */
    public function update(Request $request, User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(403, 'Unauthorized action.');
        }

        $minDob = now()->subYears(18)->format('Y-m-d');

        $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'despl_name'       => ['nullable', 'string', 'max:255'],
            'email'            => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password'         => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'gender'           => ['nullable', 'string', 'in:Male,Female,Other'],
            'dob'              => ['nullable', 'date', 'before_or_equal:' . $minDob],
            'country_code'     => ['nullable', 'string', 'max:10'],
            'mobile'           => ['nullable', 'string', 'max:20'],
            'country'          => ['nullable', 'string', 'max:100'],
            'state'            => ['nullable', 'string', 'max:100'],
            'district'         => ['nullable', 'string', 'max:100'],
            'address_line_1'   => ['nullable', 'string', 'max:255'],
            'madhab'           => ['nullable', 'array'],
            'mufti_sort_order' => ['nullable', 'integer', 'min:0', 'max:9998'],
        ], [
            'dob.before_or_equal' => 'Scholar must be at least 18 years of age (Date of birth cannot be under 18 years old).',
        ]);

        $data = [
            'name'              => $request->name,
            'despl_name'        => $request->filled('despl_name') ? $request->despl_name : $request->name,
            'email'             => $request->email,
            'gender'            => $request->gender,
            'dob'               => $request->dob ?: null,
            'country_code'      => $request->input('country_code', '+91'),
            'mobile'            => $request->mobile,
            'country'           => $request->input('country', 'India'),
            'state'             => $request->state,
            'district'          => $request->district,
            'address_line_1'    => $request->address_line_1 ?: $request->district,
            'madhab_id'         => !empty($request->madhab) ? head($request->madhab) : null,
            'is_active'         => $request->has('is_active'),
            'can_check_approve' => $request->has('can_check_approve'),
            'mufti_sort_order'  => (int) $request->input('mufti_sort_order', 0),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle Admin Direct Upload
        if ($request->filled('avatar_data')) {
            $path = $this->processAndStoreAvatar($request->avatar_data, $user->id);
            if ($path) {
                $data['avatar'] = $path;
                // Clear any pending avatar if admin uploads directly
                Storage::disk('public')->delete("avatars/pending_{$user->id}.jpg");
            }
        }

        $user->update($data);

        return redirect()->route('admin.muftis.index')->with('success', 'Mufti profile updated successfully.');
    }

    /** Remove the specified scholar from storage */
    public function destroy(User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if mufti has answers or assigned questions
        if ($user->muftiAnswers()->exists() || $user->assignedQuestions()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete Mufti because they have existing fatwas or assignments. Disable their account instead.');
        }

        $user->delete();

        return redirect()->route('admin.muftis.index')->with('success', 'Mufti profile deleted.');
    }

    /** Toggle active/inactive status */
    public function toggleStatus(User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(403, 'Unauthorized action.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->back()->with('success', 'Mufti status updated.');
    }

    /** Display Academic Impact & Contributions */
    public function activity(User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(403, 'Unauthorized action.');
        }

        // 1. Questions Metrics
        $stats = [
            'assigned'   => $user->assignedQuestions()->count(),
            'answered'   => $user->assignedQuestions()->whereIn('status', ['answered', 'approved', 'published'])->count(),
            'published'  => $user->assignedQuestions()->where('status', 'published')->count(),
            'unassigned' => \App\Models\QuestionAndAnswer::whereNull('assigned_mufti_id')->where('status', 'pending')->count(), 
        ];

        // 2. Topics Covered (Categories)
        $topics_count = \App\Models\Category::whereHas('questions', function($q) use ($user) {
            $q->where('assigned_mufti_id', $user->id)
              ->whereIn('status', ['answered', 'approved', 'published']);
        })->count();

        // 3. Appreciation/Likes & 4. Public Reads
        $engagement = \App\Models\QuestionAndAnswer::where('assigned_mufti_id', $user->id)
            ->whereIn('status', ['answered', 'approved', 'published'])
            ->selectRaw('SUM(likes) as total_likes, SUM(views) as total_views')
            ->first();

        $likes = $engagement->total_likes ?? 0;
        $views = $engagement->total_views ?? 0;

        // 5. Active Track (Currently assigned)
        $active_track = $user->assignedQuestions()->where('status', 'assigned')->count();

        // 6. Ifta Work Score (Efficiency, Speed & Volume Index — Standardized 100-pt Benchmark)
        $driver = \DB::connection()->getDriverName();
        $dateDiffSql = $driver === 'sqlite' 
            ? 'AVG(julianday(updated_at) - julianday(created_at))' 
            : 'AVG(DATEDIFF(updated_at, created_at))';

        $avg_days = \App\Models\QuestionAndAnswer::where('assigned_mufti_id', $user->id)
            ->whereIn('status', ['answered', 'approved', 'published'])
            ->selectRaw("{$dateDiffSql} as avg")
            ->value('avg') ?: 0;
        
        $reportingService = app(\App\Services\ReportingService::class);
        $scoreData = $reportingService->calculatePerformanceScore(
            (int) $stats['assigned'],
            (int) $stats['answered'],
            (int) $stats['answered'],
            (int) $stats['published'],
            (int) ($user->login_count ?? 0),
            max(1, (int) ($user->login_count ?? 1))
        );

        $work_score = $scoreData['score'];
        $score_tier = $scoreData['tier'];
        $score_color = $scoreData['color'];

        $pendingAvatar = Storage::disk('public')->exists("avatars/pending_{$user->id}.jpg") 
            ? asset("storage/avatars/pending_{$user->id}.jpg") 
            : null;

        return view('admin.muftis.activity', compact(
            'user', 'stats', 'topics_count', 'likes', 'views', 
            'active_track', 'work_score', 'score_tier', 'score_color', 
            'scoreData', 'avg_days', 'pendingAvatar'
        ));
    }

    /**
     * Approve a pending avatar submitted by a Mufti
     */
    public function approveAvatar(User $user)
    {
        $pendingFile = "avatars/pending_{$user->id}.jpg";
        $finalFile = "avatars/{$user->id}.jpg";

        if (Storage::disk('public')->exists($pendingFile)) {
            // Also copy to public folder directly for immediate visibility
            if (!file_exists(public_path('storage/avatars'))) {
                mkdir(public_path('storage/avatars'), 0755, true);
            }
            Storage::disk('public')->move($pendingFile, $finalFile);
            @copy(storage_path("app/public/{$finalFile}"), public_path("storage/{$finalFile}"));
            
            $user->update(['avatar' => "/storage/{$finalFile}"]);
            return back()->with('success', 'Profile photo approved successfully.');
        }

        return back()->with('error', 'No pending photo found.');
    }

    /**
     * Reject/Delete a pending avatar
     */
    public function rejectAvatar(User $user)
    {
        Storage::disk('public')->delete("avatars/pending_{$user->id}.jpg");
        return back()->with('success', 'Pending photo removed.');
    }

    /**
     * Display Comprehensive Mufti Work History & Session Timers
     */
    public function history(Request $request, User $user)
    {
        if (!$user->hasRole('mufti')) {
            abort(404);
        }

        $assignedQuestionIds = $user->assignedQuestions()->pluck('id');

        // Total calculated logged time across sessions
        $loggedSeconds = (int) \App\Models\UserLoginLog::where('user_id', $user->id)->sum('duration_seconds');
        
        // If user is currently online, add the live ongoing session seconds
        $isLive = $user->isOnline(5);
        if ($isLive && $user->last_login_at) {
            $loggedSeconds += max(0, now()->diffInSeconds($user->last_login_at));
        }

        // If no login logs recorded yet, provide a sensible baseline from login count
        if ($loggedSeconds === 0 && ($user->login_count ?? 0) > 0) {
            $loggedSeconds = $user->login_count * 1800; // Average 30 mins per login
        }

        // Format total logged time
        $totalHours = floor($loggedSeconds / 3600);
        $totalMinutes = floor(($loggedSeconds % 3600) / 60);
        $totalSeconds = $loggedSeconds % 60;
        $formattedTotalTime = sprintf('%dh %dm', $totalHours, $totalMinutes);

        // Word count statistics
        $allAssigned = $user->assignedQuestions()->get();
        $totalAnswerWords = 0;
        $totalDraftWords = 0;
        foreach ($allAssigned as $q) {
            if (!empty($q->answer)) {
                $totalAnswerWords += str_word_count(strip_tags($q->answer));
            }
            if (!empty($q->draft_answer)) {
                $totalDraftWords += str_word_count(strip_tags($q->draft_answer));
            }
        }
        $totalWordsAuthored = $totalAnswerWords + $totalDraftWords;

        // Fetch activity logs
        $query = \App\Models\FatwaActivityLog::with(['questionAndAnswer.category', 'questionAndAnswer.madhab', 'actor'])
            ->where(function ($q) use ($user, $assignedQuestionIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('question_id', $assignedQuestionIds);
            })
            ->latest('event_at')
            ->latest('id');

        if ($request->filled('event')) {
            $eventFilter = $request->event;
            if ($eventFilter === 'opened') {
                $query->whereIn('event', ['mufti_opened', 'admin_opened']);
            } elseif ($eventFilter === 'drafted') {
                $query->where('event', 'draft_saved');
            } elseif ($eventFilter === 'answered') {
                $query->whereIn('event', ['answer_submitted', 'published']);
            } elseif ($eventFilter === 'review') {
                $query->whereIn('event', ['correction_requested', 'rejected', 'under_review']);
            } elseif ($eventFilter === 'logins') {
                $query->whereIn('event', ['scholar_login', 'scholar_logout', 'google_login', 'otp_login']);
            } elseif ($eventFilter === 'settings') {
                $query->where('event', 'settings_opened');
            } elseif ($eventFilter === 'security') {
                $query->whereIn('event', ['password_reset', 'mobile_updated']);
            } else {
                $query->where('event', $eventFilter);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_label', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('questionAndAnswer', function ($sub) use ($search) {
                      $sub->where('question_id', 'like', "%{$search}%")
                          ->orWhere('title', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->paginate(12)->withQueryString();

        // Recent login session logs
        $loginLogs = \App\Models\UserLoginLog::where('user_id', $user->id)
            ->latest('login_at')
            ->take(15)
            ->get();

        // Summary counts
        $counts = [
            'opened'    => \App\Models\FatwaActivityLog::where('user_id', $user->id)->where('event', 'mufti_opened')->count(),
            'drafted'   => \App\Models\FatwaActivityLog::where('user_id', $user->id)->where('event', 'draft_saved')->count(),
            'answered'  => $user->assignedQuestions()->whereIn('status', ['answered', 'approved', 'published'])->count(),
            'published' => $user->assignedQuestions()->where('status', 'published')->count(),
            'reviews'   => \App\Models\FatwaActivityLog::where('user_id', $user->id)->whereIn('event', ['correction_requested', 'rejected'])->count(),
            'logins'    => \App\Models\FatwaActivityLog::where('user_id', $user->id)->whereIn('event', ['scholar_login', 'scholar_logout', 'google_login', 'otp_login'])->count() ?: ($user->login_count ?? 0),
            'settings'  => \App\Models\FatwaActivityLog::where('user_id', $user->id)->where('event', 'settings_opened')->count(),
            'security'  => \App\Models\FatwaActivityLog::where('user_id', $user->id)->whereIn('event', ['password_reset', 'mobile_updated'])->count(),
        ];

        return view('admin.muftis.history', compact(
            'user', 'activities', 'loginLogs', 'counts', 
            'isLive', 'loggedSeconds', 'formattedTotalTime', 
            'totalWordsAuthored', 'totalAnswerWords', 'totalDraftWords'
        ));
    }
}
