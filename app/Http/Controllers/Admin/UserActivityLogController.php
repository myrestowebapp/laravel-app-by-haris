<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserActivityLogController extends Controller
{
    /**
     * Display a comprehensive listing of all user behavioral, auth and system logs.
     */
    public function index(Request $request)
    {
        // Safe one-time legacy logs backfill from user_login_logs
        try {
            UserActivityLog::syncFromLegacyLogs(300);
        } catch (\Throwable $e) {}

        $query = UserActivityLog::with('user.role');

        // 1. Search Filter (Name, Email, Mobile, Identifier, IP, Description, Failure Reason)
        if ($search = trim((string)$request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_mobile', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('failure_reason', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Status Filter (success, failed, warning, info)
        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // 3. Action Filter
        if ($action = $request->get('action')) {
            if ($action === 'login_all') {
                $query->whereIn('action', ['login_success', 'login_failed']);
            } elseif ($action === 'otp_all') {
                $query->whereIn('action', ['otp_requested', 'otp_verified', 'otp_failed']);
            } elseif ($action !== 'all') {
                $query->where('action', $action);
            }
        }

        // 4. Action Category Filter (auth, question, profile, security)
        if ($category = $request->get('category')) {
            if ($category !== 'all') {
                $query->where('action_category', $category);
            }
        }

        // 5. Role Filter (questioner, mufti, admin, guest)
        if ($role = $request->get('role')) {
            if ($role !== 'all') {
                $query->where('user_role', strtolower($role));
            }
        }

        // 6. Date Range Filter
        $dateRange = $request->get('date_range', 'all');
        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === '7d') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($dateRange === '30d') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // 7. Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'action', 'status', 'user_name', 'ip_address'];
        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 8. Pagination
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $logs = $query->paginate($perPage)->withQueryString();

        // 9. Overall Summary Statistics for Dashboard Cards
        $totalLogs = UserActivityLog::count();
        $todayLogs = UserActivityLog::whereDate('created_at', Carbon::today())->count();
        
        $successLogins = UserActivityLog::where('action', 'login_success')->count();
        $failedLogins = UserActivityLog::where(function($q) {
            $q->where('action', 'login_failed')
              ->orWhere('action', 'otp_failed')
              ->orWhere('status', 'failed');
        })->count();

        $activeUsersToday = UserActivityLog::whereDate('created_at', Carbon::today())
            ->whereNotNull('identifier')
            ->distinct('identifier')
            ->count('identifier');

        $questionEventsCount = UserActivityLog::where('action', 'question_submitted')->count();

        // If AJAX request (Live Search)
        if ($request->ajax() || $request->header('X-Live-Search')) {
            return response()->json([
                'html' => view('admin.user-logs.partials.table', compact('logs'))->render(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page'    => $logs->lastPage(),
                    'total'        => $logs->total(),
                    'from'         => $logs->firstItem() ?? 0,
                    'to'           => $logs->lastItem() ?? 0,
                    'per_page'     => $logs->perPage(),
                ],
                'stats' => [
                    'total'         => number_format($totalLogs),
                    'today'         => number_format($todayLogs),
                    'successLogins' => number_format($successLogins),
                    'failedLogins'  => number_format($failedLogins),
                    'activeToday'   => number_format($activeUsersToday),
                ]
            ]);
        }

        return view('admin.user-logs.index', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'successLogins',
            'failedLogins',
            'activeUsersToday',
            'questionEventsCount',
            'search',
            'status',
            'action',
            'category',
            'role',
            'dateRange',
            'sort',
            'direction',
            'perPage'
        ));
    }

    /**
     * Get specific log entry details as JSON for modal inspection.
     */
    public function show($id)
    {
        $log = UserActivityLog::with('user.role')->findOrFail($id);

        return response()->json([
            'id'             => $log->id,
            'user_name'      => $log->user_name ?: ($log->user?->name ?: 'Guest / Non-registered'),
            'user_email'     => $log->user_email ?: ($log->user?->email ?: $log->identifier),
            'user_mobile'    => $log->user_mobile ?: ($log->user?->mobile ?: '—'),
            'user_role'      => ucfirst($log->user_role ?: 'guest'),
            'action'         => $log->action,
            'action_label'   => $log->getActionLabel(),
            'action_category'=> ucfirst($log->action_category),
            'status'         => $log->status,
            'description'    => $log->description,
            'failure_reason' => $log->getFormattedFailureReason(),
            'failure_raw'    => $log->failure_reason,
            'ip_address'     => $log->ip_address,
            'location'       => $log->location,
            'device'         => $log->device,
            'platform'       => $log->platform,
            'browser'        => $log->browser,
            'user_agent'     => $log->user_agent,
            'context_data'   => $log->context_data,
            'created_at'     => $log->created_at->format('d M Y, h:i:s A'),
            'time_ago'       => $log->created_at->diffForHumans(),
            'user_profile'   => $log->user_id ? route('admin.questioners.show', $log->user_id) : null,
        ]);
    }

    /**
     * Export filtered logs as CSV.
     */
    public function export(Request $request)
    {
        $query = UserActivityLog::with('user');

        if ($search = trim((string)$request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_mobile', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status !== 'all') $query->where('status', $status);
        }

        $logs = $query->latest('created_at')->take(5000)->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="user_behavior_logs_' . date('Y-m-d_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'Log ID', 'Timestamp', 'User Name', 'Email / Identifier', 'Mobile', 'Role',
                'Action', 'Category', 'Status', 'Failure Reason', 'Description', 'IP Address',
                'Device', 'OS', 'Browser', 'Location'
            ]);

            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l->id,
                    $l->created_at->toIso8601String(),
                    $l->user_name ?: ($l->user?->name ?? 'Guest'),
                    $l->user_email ?: $l->identifier,
                    $l->user_mobile ?: ($l->user?->mobile ?? ''),
                    $l->user_role,
                    $l->getActionLabel(),
                    $l->action_category,
                    $l->status,
                    $l->getFormattedFailureReason(),
                    $l->description,
                    $l->ip_address,
                    $l->device,
                    $l->platform,
                    $l->browser,
                    $l->location,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
