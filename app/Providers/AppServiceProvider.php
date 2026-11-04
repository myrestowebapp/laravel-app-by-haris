<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\QuestionAndAnswer;
use App\Models\Category;
use App\Models\Madhab;
use App\Models\User;
use App\Models\FatwaFeaturedRule;
use App\Http\Controllers\Admin\DashboardController;
use App\Services\FatwaIntegrityRestorationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Centralized Cache Invalidation closure
        $clearCache = function () {
            Cache::flush();
        };

        // Attach listeners to relevant models
        QuestionAndAnswer::saved($clearCache);
        QuestionAndAnswer::deleted($clearCache);
        Category::saved($clearCache);
        Category::deleted($clearCache);
        Madhab::saved($clearCache);
        Madhab::deleted($clearCache);
        User::saved($clearCache);
        User::deleted($clearCache);

        // Global View Composer for Admin Layout (Sidebar Telemetry Numbers)
        View::composer('layouts.admin', function ($view) {
            $sidebarCounts = Cache::remember('admin_sidebar_counts', 15, function () {
                try {
                    $statusCounts = QuestionAndAnswer::selectRaw('status, count(*) as count')
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray();
                    $totalQuestions = array_sum($statusCounts);

                    $muftiCount = User::whereHas('role', fn($q) => $q->where('name', 'mufti'))->count();
                    $categoryCount = Category::count();
                    $madhabCount = Madhab::count();
                    $questionerCount = User::whereHas('questions')->count();

                    $featuredCount = 0;
                    if (Schema::hasTable('fatwa_featured_rules')) {
                        $featuredCount = FatwaFeaturedRule::count();
                    }

                    $liveVisitors = 1;
                    if (class_exists(DashboardController::class)) {
                        $liveData = DashboardController::getRealtimeLiveVisitorsCount();
                        $liveVisitors = $liveData['byIp'] ?? 1;
                    }

                    $totalAudience = \App\Services\GlobalVisitorAnalyticsService::getTotalVisitors();

                    $collisionCount = 0;
                    if (class_exists(FatwaIntegrityRestorationService::class)) {
                        $collisionCount = QuestionAndAnswer::whereIn('question_id', FatwaIntegrityRestorationService::COLLISION_QIDS)
                            ->where('status', 'published')
                            ->count();
                    }

                    $userLogsCount = 0;
                    if (Schema::hasTable('user_activity_logs')) {
                        $userLogsCount = \App\Models\UserActivityLog::count();
                    }

                    return [
                        'total_questions' => $totalQuestions,
                        'pending'         => $statusCounts['pending'] ?? 0,
                        'accepted'        => $statusCounts['accepted'] ?? 0,
                        'assigned'        => $statusCounts['assigned'] ?? 0,
                        'drafted'         => $statusCounts['drafted'] ?? 0,
                        'answered'        => $statusCounts['answered'] ?? 0,
                        'under_review'    => $statusCounts['under_review'] ?? 0,
                        'published'       => $statusCounts['published'] ?? 0,
                        'rejected'        => $statusCounts['rejected'] ?? 0,
                        'muftis'          => $muftiCount,
                        'categories'      => $categoryCount,
                        'madhabs'         => $madhabCount,
                        'questioners'     => $questionerCount,
                        'featured'        => $featuredCount,
                        'visitors'        => $liveVisitors,
                        'total_visitors'  => $totalAudience,
                        'collisions'      => $collisionCount,
                        'user_logs'       => $userLogsCount,
                    ];
                } catch (\Throwable $e) {
                    return [];
                }
            });

            $view->with('sidebarCounts', $sidebarCounts);
        });

        // Global Blade Directives for Status Management
        \Illuminate\Support\Facades\Blade::directive('statusBadge', function ($expression) {
            return "<?php echo \App\Models\Status::renderBadge($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('statusLabel', function ($expression) {
            return "<?php echo \App\Models\Status::getLabel($expression); ?>";
        });

        // Global Blade Directives for Safe Email Content
        \Illuminate\Support\Facades\Blade::directive('safeEmailHtml', function ($expression) {
            return "<?php echo \App\Services\Mail\EmailContentSanitizer::sanitize($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('emailPlainText', function ($expression) {
            return "<?php echo \App\Services\Mail\EmailContentSanitizer::toPlainText($expression); ?>";
        });

        // Automatically record user logins across web, OTP, and OAuth
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if (isset($event->user) && $event->user instanceof \App\Models\User) {
                try {
                    $event->user->recordLogin();
                    $log = \App\Models\UserLoginLog::logLogin($event->user);
                    
                    // Comprehensive User Behavior Log
                    if (class_exists(\App\Models\UserActivityLog::class) && Schema::hasTable('user_activity_logs')) {
                        \App\Models\UserActivityLog::record(
                            action: 'login_success',
                            description: 'Signed in successfully from ' . ($log->device ?? 'Device') . ' (' . ($log->browser ?: 'Browser') . ' on ' . ($log->platform ?: 'OS') . ')',
                            status: 'success',
                            user: $event->user,
                            identifier: $event->user->email,
                            actionCategory: 'auth',
                            contextData: [
                                'device'   => $log->device ?? 'Desktop',
                                'platform' => $log->platform,
                                'browser'  => $log->browser,
                                'location' => $log->location,
                                'ip'       => $log->ip_address,
                            ]
                        );
                    }

                    if ($event->user->hasRole('mufti') || $event->user->hasRole('admin')) {
                        \App\Models\FatwaActivityLog::create([
                            'question_id' => null,
                            'fatwa_id'    => null,
                            'user_id'     => $event->user->id,
                            'event'       => 'scholar_login',
                            'event_label' => 'Scholar ' . $event->user->name . ' Logged In from ' . ($log->device ?? 'Device'),
                            'icon'        => 'bi-box-arrow-in-right',
                            'color'       => 'success',
                            'notes'       => 'Logged in from ' . ($log->device ?? 'Device') . ' (' . ($log->platform ?: 'OS') . ' / ' . ($log->browser ?: 'Browser') . ') @ ' . now()->format('h:i A : d-m-Y') . ($log->location ? ' from ' . $log->location : ''),
                            'event_at'    => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Error recording login activity: ' . $e->getMessage());
                }
            }
        });

        // Automatically record failed login attempts
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            try {
                if (!class_exists(\App\Models\UserActivityLog::class) || !Schema::hasTable('user_activity_logs')) {
                    return;
                }

                $credentials = $event->credentials ?? [];
                $identifier = $credentials['email'] ?? ($credentials['login_id'] ?? ($credentials['mobile'] ?? null));
                $user = $event->user;

                if (!$user && $identifier) {
                    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
                    $user = \App\Models\User::where($isEmail ? 'email' : 'mobile', $identifier)->first();
                }

                $failureReason = 'invalid_password';
                $reasonText = 'Incorrect password entered';
                if (!$user) {
                    $failureReason = 'user_not_found';
                    $reasonText = 'No user account registered with this identifier';
                } elseif (!$user->is_active) {
                    $failureReason = 'account_inactive';
                    $reasonText = 'User account is deactivated / suspended';
                }

                \App\Models\UserActivityLog::record(
                    action: 'login_failed',
                    description: 'Failed login attempt (' . ($identifier ?: 'Unknown Identifier') . '): ' . $reasonText,
                    status: 'failed',
                    user: $user,
                    identifier: $identifier,
                    failureReason: $failureReason,
                    actionCategory: 'auth',
                    contextData: [
                        'identifier_attempted' => $identifier,
                        'user_exists'          => (bool)$user,
                        'account_active'       => $user ? (bool)$user->is_active : null,
                    ]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error recording failed login activity: ' . $e->getMessage());
            }
        });

        // Automatically record user logout
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if (isset($event->user) && $event->user instanceof \App\Models\User) {
                try {
                    $activeLog = \App\Models\UserLoginLog::where('user_id', $event->user->id)->where('is_active', true)->latest()->first();
                    $device = $activeLog ? $activeLog->device : 'Device';
                    $durationMins = 1;

                    if ($activeLog) {
                        $activeLog->closeSession();
                        $durationMins = max(1, round($activeLog->duration_seconds / 60));
                    }
                    
                    if (class_exists(\App\Models\UserActivityLog::class) && Schema::hasTable('user_activity_logs')) {
                        \App\Models\UserActivityLog::record(
                            action: 'logout',
                            description: 'User logged out. Session active duration: ' . $durationMins . ' mins',
                            status: 'info',
                            user: $event->user,
                            identifier: $event->user->email,
                            actionCategory: 'auth',
                            contextData: ['duration_minutes' => $durationMins]
                        );
                    }

                    if ($event->user->hasRole('mufti') || $event->user->hasRole('admin')) {
                        \App\Models\FatwaActivityLog::create([
                            'question_id' => null,
                            'fatwa_id'    => null,
                            'user_id'     => $event->user->id,
                            'event'       => 'scholar_logout',
                            'event_label' => 'Scholar ' . $event->user->name . ' Logged Out from ' . $device,
                            'icon'        => 'bi-box-arrow-left',
                            'color'       => 'secondary',
                            'notes'       => 'Session ended @ ' . now()->format('h:i A : d-m-Y') . ' (Session active duration: ' . $durationMins . ' mins)',
                            'event_at'    => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Error recording logout activity: ' . $e->getMessage());
                }
            }
        });

        // Record password resets
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\PasswordReset::class, function ($event) {
            try {
                if (isset($event->user) && $event->user instanceof \App\Models\User) {
                    if (class_exists(\App\Models\UserActivityLog::class) && Schema::hasTable('user_activity_logs')) {
                        \App\Models\UserActivityLog::record(
                            action: 'password_reset_success',
                            description: 'Password was successfully reset by user',
                            status: 'success',
                            user: $event->user,
                            identifier: $event->user->email,
                            actionCategory: 'security'
                        );
                    }
                }
            } catch (\Throwable $e) {}
        });
    }
}
