<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use App\Models\User;
use App\Models\PortalVisitorLog;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class UserActivityLogController extends Controller
{
    /**
     * Display comprehensive unified behavioral logs aggregated across all system tables:
     *   1. user_activity_logs         (Auth, profile, question events)
     *   2. user_login_logs            (Historical login sessions with duration & location)
     *   3. portal_visitor_logs        (Public & user web visitor telemetry across all pages)
     *   4. fatwa_activity_logs        (Fatwa reads, assignments, answers, scholar logins)
     *   5. system_health_audit_logs   (OTP events, security events, auth health)
     *   6. system_api_logs            (API requests, routes, latencies and error logs)
     *   7. fatwa_integrity_audit_logs (Integrity backups and restoration audits)
     *
     * All results are UNION-ed into a single normalized stream, server-side paginated (8 rows/page)
     * and server-side searched.
     */
    public function index(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $status    = $request->get('status', 'all');
        $action    = $request->get('action', 'all');
        $sourceFilter = $request->get('source', 'all');
        $role      = $request->get('role', 'all');
        $dateRange = $request->get('date_range', 'all');
        $sort      = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage   = 8; // Strictly 8 lines per page

        $isSqlite  = DB::getDriverName() === 'sqlite';

        // ─── 1. Base date boundary ─────────────────────────────────────────────
        $dateFrom = match ($dateRange) {
            'today' => Carbon::today(),
            '7d'    => now()->subDays(7),
            '30d'   => now()->subDays(30),
            default => null,
        };

        // ─── 2. Build 7 Normalized Subqueries ─────────────────────────────────

        // === SOURCE 1: user_activity_logs =====================================
        $sActivity = DB::table('user_activity_logs')
            ->select([
                DB::raw("'activity' AS _source"),
                'id',
                'user_id',
                'user_name',
                'user_email',
                'user_mobile',
                DB::raw("COALESCE(user_role, 'questioner') AS user_role"),
                'action',
                DB::raw("COALESCE(action_category, 'general') AS action_category"),
                DB::raw("COALESCE(status, 'info') AS status"),
                'description',
                'failure_reason',
                'ip_address',
                'device',
                'platform',
                'browser',
                'location',
                'created_at',
            ]);

        // === SOURCE 2: user_login_logs ========================================
        $descLogin = $isSqlite
            ? DB::raw("'Signed in from ' || COALESCE(user_login_logs.device,'Device') || ' on ' || COALESCE(user_login_logs.platform,'OS') AS description")
            : DB::raw("CONCAT('Signed in from ', COALESCE(user_login_logs.device,'Device'), ' on ', COALESCE(user_login_logs.platform,'OS')) AS description");

        $sLogin = DB::table('user_login_logs')
            ->leftJoin('users', 'user_login_logs.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select([
                DB::raw("'login' AS _source"),
                'user_login_logs.id',
                'user_login_logs.user_id',
                DB::raw("COALESCE(users.name, 'Registered User') AS user_name"),
                DB::raw("COALESCE(users.email, '') AS user_email"),
                DB::raw("COALESCE(users.mobile, '') AS user_mobile"),
                DB::raw("COALESCE(LOWER(roles.name), 'questioner') AS user_role"),
                DB::raw("'login_success' AS action"),
                DB::raw("'auth' AS action_category"),
                DB::raw("'success' AS status"),
                $descLogin,
                DB::raw("NULL AS failure_reason"),
                'user_login_logs.ip_address',
                DB::raw("COALESCE(user_login_logs.device,'Desktop') AS device"),
                DB::raw("COALESCE(user_login_logs.platform,'Unknown OS') AS platform"),
                DB::raw("COALESCE(user_login_logs.browser,'Browser') AS browser"),
                DB::raw("COALESCE(user_login_logs.location,'') AS location"),
                DB::raw("COALESCE(user_login_logs.login_at, user_login_logs.created_at) AS created_at"),
            ]);

        // === SOURCE 3: portal_visitor_logs ====================================
        $visitorNameExpr = $isSqlite
            ? DB::raw("COALESCE(users.name, 'Visitor #' || SUBSTR(REPLACE(portal_visitor_logs.ip_address,'.',''),1,8)) AS user_name")
            : DB::raw("COALESCE(users.name, CONCAT('Visitor #', SUBSTR(REPLACE(portal_visitor_logs.ip_address,'.',''),1,8))) AS user_name");
        $descVisitorExpr = $isSqlite
            ? DB::raw("'Visited: ' || COALESCE(portal_visitor_logs.page, '/') AS description")
            : DB::raw("CONCAT('Visited: ', COALESCE(portal_visitor_logs.page, '/')) AS description");
        $locationVisitorExpr = $isSqlite
            ? DB::raw("CASE WHEN portal_visitor_logs.city IS NOT NULL THEN portal_visitor_logs.city || ', ' || COALESCE(portal_visitor_logs.country,'') ELSE COALESCE(portal_visitor_logs.country,'') END AS location")
            : DB::raw("CASE WHEN portal_visitor_logs.city IS NOT NULL THEN CONCAT(portal_visitor_logs.city,', ',COALESCE(portal_visitor_logs.country,'')) ELSE COALESCE(portal_visitor_logs.country,'') END AS location");

        $sVisitor = DB::table('portal_visitor_logs')
            ->leftJoin('users', 'portal_visitor_logs.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select([
                DB::raw("'visitor' AS _source"),
                'portal_visitor_logs.id',
                'portal_visitor_logs.user_id',
                $visitorNameExpr,
                DB::raw("COALESCE(users.email, '') AS user_email"),
                DB::raw("COALESCE(users.mobile, '') AS user_mobile"),
                DB::raw("CASE WHEN portal_visitor_logs.user_id IS NOT NULL THEN COALESCE(LOWER(roles.name),'questioner') ELSE 'guest' END AS user_role"),
                DB::raw("'page_visit' AS action"),
                DB::raw("'visitor' AS action_category"),
                DB::raw("'info' AS status"),
                $descVisitorExpr,
                DB::raw("NULL AS failure_reason"),
                'portal_visitor_logs.ip_address',
                DB::raw("COALESCE(portal_visitor_logs.device_type,'Desktop') AS device"),
                DB::raw("COALESCE(portal_visitor_logs.operating_system,'Unknown OS') AS platform"),
                DB::raw("COALESCE(portal_visitor_logs.browser,'Browser') AS browser"),
                $locationVisitorExpr,
                'portal_visitor_logs.created_at',
            ]);

        // === SOURCE 4: fatwa_activity_logs ====================================
        $sFatwa = DB::table('fatwa_activity_logs')
            ->leftJoin('users', 'fatwa_activity_logs.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select([
                DB::raw("'fatwa' AS _source"),
                'fatwa_activity_logs.id',
                'fatwa_activity_logs.user_id',
                DB::raw("COALESCE(users.name, 'Public Reader / Scholar') AS user_name"),
                DB::raw("COALESCE(users.email, '') AS user_email"),
                DB::raw("COALESCE(users.mobile, '') AS user_mobile"),
                DB::raw("CASE WHEN users.id IS NOT NULL THEN COALESCE(LOWER(roles.name),'scholar') ELSE 'guest' END AS user_role"),
                'fatwa_activity_logs.event AS action',
                DB::raw("CASE WHEN fatwa_activity_logs.event IN ('scholar_login','scholar_logout','google_login','otp_login','password_reset') THEN 'auth' ELSE 'fatwa' END AS action_category"),
                DB::raw("CASE WHEN fatwa_activity_logs.color = 'danger' THEN 'failed' WHEN fatwa_activity_logs.color = 'warning' THEN 'warning' WHEN fatwa_activity_logs.color = 'success' THEN 'success' ELSE 'info' END AS status"),
                DB::raw("COALESCE(fatwa_activity_logs.notes, fatwa_activity_logs.event_label) AS description"),
                DB::raw("NULL AS failure_reason"),
                DB::raw("NULL AS ip_address"),
                DB::raw("'Web' AS device"),
                DB::raw("'Web' AS platform"),
                DB::raw("'Browser' AS browser"),
                DB::raw("COALESCE(fatwa_activity_logs.event_label, '') AS location"),
                DB::raw("COALESCE(fatwa_activity_logs.event_at, fatwa_activity_logs.created_at) AS created_at"),
            ]);

        // === SOURCE 5: system_health_audit_logs ===============================
        $sHealth = DB::table('system_health_audit_logs')
            ->select([
                DB::raw("'audit' AS _source"),
                'id',
                DB::raw("NULL AS user_id"),
                DB::raw("COALESCE(email, 'System Security') AS user_name"),
                DB::raw("COALESCE(email, '') AS user_email"),
                DB::raw("'' AS user_mobile"),
                DB::raw("'user' AS user_role"),
                'event AS action',
                DB::raw("'auth' AS action_category"),
                DB::raw("CASE WHEN event LIKE '%fail%' OR event LIKE '%expired%' THEN 'failed' WHEN event LIKE '%success%' THEN 'success' ELSE 'info' END AS status"),
                DB::raw("COALESCE(details, event) AS description"),
                DB::raw("CASE WHEN event LIKE '%fail%' OR event LIKE '%expired%' THEN event ELSE NULL END AS failure_reason"),
                'ip_address',
                DB::raw("'Device' AS device"),
                DB::raw("'Platform' AS platform"),
                DB::raw("'Browser' AS browser"),
                DB::raw("'' AS location"),
                'created_at',
            ]);

        // === SOURCE 6: system_api_logs ========================================
        $apiDesc = $isSqlite
            ? DB::raw("method || ' ' || path || ' (' || status_code || ') - ' || duration_ms || 'ms' AS description")
            : DB::raw("CONCAT(method, ' ', path, ' (', status_code, ') - ', duration_ms, 'ms') AS description");

        $sApi = DB::table('system_api_logs')
            ->select([
                DB::raw("'api' AS _source"),
                'id',
                DB::raw("NULL AS user_id"),
                DB::raw("'API Client' AS user_name"),
                DB::raw("'' AS user_email"),
                DB::raw("'' AS user_mobile"),
                DB::raw("'guest' AS user_role"),
                'method AS action',
                DB::raw("'api' AS action_category"),
                DB::raw("CASE WHEN status_code >= 400 THEN 'failed' ELSE 'info' END AS status"),
                $apiDesc,
                'error_category AS failure_reason',
                'ip_address',
                DB::raw("COALESCE(source_category, 'API') AS device"),
                DB::raw("'HTTP' AS platform"),
                DB::raw("'API' AS browser"),
                DB::raw("'' AS location"),
                'created_at',
            ]);

        // === SOURCE 7: fatwa_integrity_audit_logs =============================
        $sIntegrity = DB::table('fatwa_integrity_audit_logs')
            ->leftJoin('users', 'fatwa_integrity_audit_logs.admin_id', '=', 'users.id')
            ->select([
                DB::raw("'security' AS _source"),
                'fatwa_integrity_audit_logs.id',
                'fatwa_integrity_audit_logs.admin_id AS user_id',
                DB::raw("COALESCE(users.name, 'Admin') AS user_name"),
                DB::raw("COALESCE(users.email, '') AS user_email"),
                DB::raw("COALESCE(users.mobile, '') AS user_mobile"),
                DB::raw("'admin' AS user_role"),
                'fatwa_integrity_audit_logs.action_type AS action',
                DB::raw("'security' AS action_category"),
                DB::raw("'warning' AS status"),
                DB::raw("COALESCE(fatwa_integrity_audit_logs.reason, fatwa_integrity_audit_logs.action_type) AS description"),
                DB::raw("NULL AS failure_reason"),
                'fatwa_integrity_audit_logs.ip_address',
                DB::raw("'Desktop' AS device"),
                DB::raw("'Admin Console' AS platform"),
                DB::raw("'Browser' AS browser"),
                DB::raw("'' AS location"),
                'fatwa_integrity_audit_logs.created_at',
            ]);

        // ─── 3. Source Filtering (Optional quick filter) ──────────────────────
        $sources = [$sActivity, $sLogin, $sVisitor, $sFatwa, $sHealth, $sApi, $sIntegrity];

        if ($sourceFilter !== 'all') {
            if ($sourceFilter === 'activity') {
                $sources = [$sActivity];
            } elseif ($sourceFilter === 'login') {
                $sources = [$sLogin];
            } elseif ($sourceFilter === 'visitor') {
                $sources = [$sVisitor];
            } elseif ($sourceFilter === 'fatwa') {
                $sources = [$sFatwa];
            } elseif ($sourceFilter === 'security') {
                $sources = [$sHealth, $sIntegrity];
            } elseif ($sourceFilter === 'api') {
                $sources = [$sApi];
            }
        }

        // ─── 4. Apply Date Filter to all active subqueries ────────────────────
        if ($dateFrom) {
            $sActivity->where('user_activity_logs.created_at', '>=', $dateFrom);
            $sLogin->where('user_login_logs.created_at', '>=', $dateFrom);
            $sVisitor->where('portal_visitor_logs.created_at', '>=', $dateFrom);
            $sFatwa->where('fatwa_activity_logs.created_at', '>=', $dateFrom);
            $sHealth->where('system_health_audit_logs.created_at', '>=', $dateFrom);
            $sApi->where('system_api_logs.created_at', '>=', $dateFrom);
            $sIntegrity->where('fatwa_integrity_audit_logs.created_at', '>=', $dateFrom);
        }

        // ─── 5. Apply Status Filter ───────────────────────────────────────────
        if ($status !== 'all') {
            if ($status === 'failed') {
                $sActivity->where('user_activity_logs.status', 'failed');
                $sLogin->whereRaw('1=0');
                $sVisitor->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.color', 'danger');
                $sHealth->where(function ($q) {
                    $q->where('system_health_audit_logs.event', 'like', '%fail%')
                      ->orWhere('system_health_audit_logs.event', 'like', '%expired%');
                });
                $sApi->where('system_api_logs.status_code', '>=', 400);
                $sIntegrity->whereRaw('1=0');
            } elseif ($status === 'success') {
                $sActivity->where('user_activity_logs.status', 'success');
                // sLogin is all success
                $sVisitor->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.color', 'success');
                $sHealth->where('system_health_audit_logs.event', 'like', '%success%');
                $sApi->whereRaw('1=0');
                $sIntegrity->whereRaw('1=0');
            } elseif ($status === 'warning') {
                $sActivity->where('user_activity_logs.status', 'warning');
                $sLogin->whereRaw('1=0');
                $sVisitor->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.color', 'warning');
                $sHealth->whereRaw('1=0');
                $sApi->whereRaw('1=0');
                // sIntegrity is all warning
            } elseif ($status === 'info') {
                $sActivity->where('user_activity_logs.status', 'info');
                $sLogin->whereRaw('1=0');
                // sVisitor is all info
                $sFatwa->whereNotIn('fatwa_activity_logs.color', ['danger', 'warning', 'success']);
                $sHealth->where(function ($q) {
                    $q->where('system_health_audit_logs.event', 'not like', '%fail%')
                      ->where('system_health_audit_logs.event', 'not like', '%expired%')
                      ->where('system_health_audit_logs.event', 'not like', '%success%');
                });
                $sApi->where('system_api_logs.status_code', '<', 400);
                $sIntegrity->whereRaw('1=0');
            }
        }

        // ─── 6. Apply Action / Event Filter ───────────────────────────────────
        if ($action !== 'all') {
            if ($action === 'login_all') {
                $sActivity->whereIn('action', ['login_success', 'login_failed']);
                $sVisitor->whereRaw('1=0');
                $sFatwa->whereIn('fatwa_activity_logs.event', ['scholar_login', 'google_login', 'otp_login']);
                $sHealth->whereRaw('1=0');
                $sApi->whereRaw('1=0');
                $sIntegrity->whereRaw('1=0');
            } elseif ($action === 'otp_all') {
                $sActivity->whereIn('action', ['otp_requested', 'otp_verified', 'otp_failed']);
                $sLogin->whereRaw('1=0');
                $sVisitor->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.event', 'otp_login');
                $sHealth->where('system_health_audit_logs.event', 'like', '%otp%');
                $sApi->whereRaw('1=0');
                $sIntegrity->whereRaw('1=0');
            } elseif ($action === 'page_visit') {
                $sActivity->whereRaw('1=0');
                $sLogin->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.event', 'country_view');
                $sHealth->whereRaw('1=0');
                $sApi->whereRaw('1=0');
                $sIntegrity->whereRaw('1=0');
            } else {
                $sActivity->where('user_activity_logs.action', $action);
                $sLogin->whereRaw('1=0');
                $sVisitor->whereRaw('1=0');
                $sFatwa->where('fatwa_activity_logs.event', $action);
                $sHealth->where('system_health_audit_logs.event', $action);
                $sApi->whereRaw('1=0');
                $sIntegrity->where('fatwa_integrity_audit_logs.action_type', $action);
            }
        }

        // ─── 7. Apply Multi-Field Search Filter ───────────────────────────────
        if ($search !== '') {
            $like = "%{$search}%";

            $sActivity->where(function ($q) use ($like) {
                $q->where('user_name', 'like', $like)
                  ->orWhere('user_email', 'like', $like)
                  ->orWhere('user_mobile', 'like', $like)
                  ->orWhere('identifier', 'like', $like)
                  ->orWhere('ip_address', 'like', $like)
                  ->orWhere('description', 'like', $like);
            });

            $sLogin->where(function ($q) use ($like) {
                $q->where('users.name', 'like', $like)
                  ->orWhere('users.email', 'like', $like)
                  ->orWhere('users.mobile', 'like', $like)
                  ->orWhere('user_login_logs.ip_address', 'like', $like)
                  ->orWhere('user_login_logs.location', 'like', $like);
            });

            $sVisitor->where(function ($q) use ($like) {
                $q->where('users.name', 'like', $like)
                  ->orWhere('users.email', 'like', $like)
                  ->orWhere('users.mobile', 'like', $like)
                  ->orWhere('portal_visitor_logs.ip_address', 'like', $like)
                  ->orWhere('portal_visitor_logs.page', 'like', $like)
                  ->orWhere('portal_visitor_logs.country', 'like', $like)
                  ->orWhere('portal_visitor_logs.city', 'like', $like);
            });

            $sFatwa->where(function ($q) use ($like) {
                $q->where('users.name', 'like', $like)
                  ->orWhere('fatwa_activity_logs.event', 'like', $like)
                  ->orWhere('fatwa_activity_logs.event_label', 'like', $like)
                  ->orWhere('fatwa_activity_logs.notes', 'like', $like);
            });

            $sHealth->where(function ($q) use ($like) {
                $q->where('system_health_audit_logs.email', 'like', $like)
                  ->orWhere('system_health_audit_logs.event', 'like', $like)
                  ->orWhere('system_health_audit_logs.ip_address', 'like', $like)
                  ->orWhere('system_health_audit_logs.details', 'like', $like);
            });

            $sApi->where(function ($q) use ($like) {
                $q->where('system_api_logs.path', 'like', $like)
                  ->orWhere('system_api_logs.ip_address', 'like', $like)
                  ->orWhere('system_api_logs.method', 'like', $like);
            });

            $sIntegrity->where(function ($q) use ($like) {
                $q->where('users.name', 'like', $like)
                  ->orWhere('fatwa_integrity_audit_logs.reason', 'like', $like)
                  ->orWhere('fatwa_integrity_audit_logs.ip_address', 'like', $like);
            });
        }

        // ─── 8. Construct Dynamic UNION ───────────────────────────────────────
        $firstQuery = array_shift($sources);
        $unionSql = $firstQuery;
        foreach ($sources as $subQuery) {
            $unionSql = $unionSql->unionAll($subQuery);
        }

        $allowedSorts = ['created_at', 'user_name', 'user_role', 'status'];
        $sortColumn = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';

        // Count total matching rows
        $totalCount = DB::table(DB::raw("({$unionSql->toSql()}) AS count_wrap"))
            ->mergeBindings($unionSql)
            ->count();

        // Paginate (8 per page)
        $currentPage = max(1, (int) $request->get('page', 1));
        $offset      = ($currentPage - 1) * $perPage;

        $rawRows = DB::table(DB::raw("({$unionSql->toSql()}) AS unified_logs"))
            ->mergeBindings($unionSql)
            ->orderBy($sortColumn, $direction)
            ->skip($offset)
            ->take($perPage)
            ->get();

        // ─── 9. Enrich Rows with Users ────────────────────────────────────────
        $userIds = $rawRows->pluck('user_id')->filter()->unique()->values();
        $usersMap = User::with('role')
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $logs = $rawRows->map(function ($row) use ($usersMap) {
            $user = isset($row->user_id) ? ($usersMap[$row->user_id] ?? null) : null;
            $row->_user_model  = $user;
            $row->user_name    = $row->user_name    ?: ($user?->name     ?? 'Anonymous');
            $row->user_email   = $row->user_email   ?: ($user?->email    ?? '');
            $row->user_mobile  = $row->user_mobile  ?: ($user?->mobile   ?? '');
            $row->user_role    = $row->user_role    ?: ($user?->role?->name ?? 'guest');
            return $row;
        });

        // ─── 10. LengthAwarePaginator (8 per page) ────────────────────────────
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs,
            $totalCount,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ─── 11. Global Metric Counters ───────────────────────────────────────
        $totalLogsAll = DB::table('user_activity_logs')->count()
                      + DB::table('user_login_logs')->count()
                      + DB::table('portal_visitor_logs')->count()
                      + DB::table('fatwa_activity_logs')->count()
                      + DB::table('system_health_audit_logs')->count()
                      + DB::table('system_api_logs')->count();

        $todayLogs    = DB::table('user_activity_logs')->whereDate('created_at', Carbon::today())->count()
                      + DB::table('user_login_logs')->whereDate('created_at', Carbon::today())->count()
                      + DB::table('portal_visitor_logs')->whereDate('created_at', Carbon::today())->count()
                      + DB::table('fatwa_activity_logs')->whereDate('created_at', Carbon::today())->count();

        $successLogins = DB::table('user_activity_logs')->where('action', 'login_success')->count()
                       + DB::table('user_login_logs')->count()
                       + DB::table('fatwa_activity_logs')->whereIn('event', ['scholar_login', 'google_login', 'otp_login'])->count();

        $failedLogins  = DB::table('user_activity_logs')
            ->where(function ($q) {
                $q->where('action', 'login_failed')
                  ->orWhere('action', 'otp_failed')
                  ->orWhere('status', 'failed');
            })->count()
            + DB::table('system_health_audit_logs')->where(function ($q) {
                $q->where('event', 'like', '%fail%')->orWhere('event', 'like', '%expired%');
            })->count();

        $activeUsersToday = DB::table('user_login_logs')
            ->whereDate('created_at', Carbon::today())
            ->distinct('user_id')
            ->count('user_id');

        $uniqueVisitorsToday = DB::table('portal_visitor_logs')
            ->whereDate('created_at', Carbon::today())
            ->distinct('ip_address')
            ->count('ip_address');

        // AJAX response (Server-side dynamic reload)
        if ($request->ajax() || $request->header('X-Live-Search')) {
            return response()->json([
                'html' => view('admin.user-logs.partials.table-unified', [
                    'logs'     => $paginator,
                    'usersMap' => $usersMap,
                ])->render(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem() ?? 0,
                    'to'           => $paginator->lastItem() ?? 0,
                    'per_page'     => $paginator->perPage(),
                ],
                'stats' => [
                    'total'          => number_format($totalLogsAll),
                    'today'          => number_format($todayLogs),
                    'successLogins'  => number_format($successLogins),
                    'failedLogins'   => number_format($failedLogins),
                    'activeToday'    => number_format($activeUsersToday),
                    'uniqueVisitors' => number_format($uniqueVisitorsToday),
                ],
            ]);
        }

        return view('admin.user-logs.index', compact(
            'paginator',
            'totalLogsAll',
            'todayLogs',
            'successLogins',
            'failedLogins',
            'activeUsersToday',
            'uniqueVisitorsToday',
            'search',
            'status',
            'action',
            'sourceFilter',
            'role',
            'dateRange',
            'sort',
            'direction',
            'perPage'
        ));
    }

    /**
     * Inspect single log entry across any of the 7 sources.
     */
    public function show(Request $request, $id)
    {
        $source = $request->get('source', 'activity');

        // 1. Activity Log
        if ($source === 'activity') {
            $log = UserActivityLog::with('user.role')->find($id);
            if ($log) {
                return response()->json([
                    'id'              => $log->id,
                    'source'          => 'Activity Log',
                    'user_name'       => $log->user_name ?: ($log->user?->name ?: 'Anonymous'),
                    'user_email'      => $log->user_email ?: ($log->user?->email ?: $log->identifier),
                    'user_mobile'     => $log->user_mobile ?: ($log->user?->mobile ?: '—'),
                    'user_role'       => ucfirst($log->user_role ?: 'guest'),
                    'action'          => $log->action,
                    'action_label'    => $log->getActionLabel(),
                    'action_category' => ucfirst($log->action_category),
                    'status'          => $log->status,
                    'description'     => $log->description,
                    'failure_reason'  => $log->getFormattedFailureReason(),
                    'failure_raw'     => $log->failure_reason,
                    'ip_address'      => $log->ip_address,
                    'location'        => $log->location,
                    'device'          => $log->device,
                    'platform'        => $log->platform,
                    'browser'         => $log->browser,
                    'user_agent'      => $log->user_agent,
                    'context_data'    => $log->context_data,
                    'created_at'      => $log->created_at->format('d M Y, h:i:s A'),
                    'time_ago'        => $log->created_at->diffForHumans(),
                    'user_profile'    => $log->user_id ? route('admin.questioners.show', $log->user_id) : null,
                ]);
            }
        }

        // 2. Login Session Log
        if ($source === 'login') {
            $l = UserLoginLog::with('user.role')->find($id);
            if ($l) {
                return response()->json([
                    'id'              => $l->id,
                    'source'          => 'Login Session Log',
                    'user_name'       => $l->user?->name ?? 'Registered User',
                    'user_email'      => $l->user?->email ?? '—',
                    'user_mobile'     => $l->user?->mobile ?? '—',
                    'user_role'       => ucfirst($l->user?->role?->name ?? 'User'),
                    'action'          => 'login_success',
                    'action_label'    => 'Login Session',
                    'action_category' => 'Auth',
                    'status'          => 'success',
                    'description'     => 'Login session from ' . ($l->device ?? 'Device') . ' on ' . ($l->platform ?? 'OS') . ' (Duration: ' . round(($l->duration_seconds ?? 0) / 60, 1) . ' mins)',
                    'failure_reason'  => '—',
                    'failure_raw'     => null,
                    'ip_address'      => $l->ip_address,
                    'location'        => $l->location ?: '—',
                    'device'          => $l->device ?: 'Desktop',
                    'platform'        => $l->platform ?: 'OS',
                    'browser'         => $l->browser ?: 'Browser',
                    'user_agent'      => $l->user_agent,
                    'context_data'    => [
                        'login_at'         => $l->login_at?->toIso8601String(),
                        'logout_at'        => $l->logout_at?->toIso8601String(),
                        'last_activity_at' => $l->last_activity_at?->toIso8601String(),
                        'is_active'        => (bool) $l->is_active,
                        'duration_seconds' => $l->duration_seconds,
                    ],
                    'created_at'      => ($l->login_at ?: $l->created_at)->format('d M Y, h:i:s A'),
                    'time_ago'        => ($l->login_at ?: $l->created_at)->diffForHumans(),
                    'user_profile'    => $l->user_id ? route('admin.questioners.show', $l->user_id) : null,
                ]);
            }
        }

        // 3. Visitor Log
        if ($source === 'visitor') {
            $v = PortalVisitorLog::with('user.role')->find($id);
            if ($v) {
                return response()->json([
                    'id'              => $v->id,
                    'source'          => 'Public Visitor Telemetry',
                    'user_name'       => $v->user?->name ?: ('Visitor #' . substr(str_replace('.', '', $v->ip_address), 0, 8)),
                    'user_email'      => $v->user?->email ?: '—',
                    'user_mobile'     => $v->user?->mobile ?: '—',
                    'user_role'       => ucfirst($v->user?->role?->name ?? 'Guest'),
                    'action'          => 'page_visit',
                    'action_label'    => 'Page Visit',
                    'action_category' => 'Visitor',
                    'status'          => 'info',
                    'description'     => 'Visited URL: ' . $v->page,
                    'failure_reason'  => '—',
                    'failure_raw'     => null,
                    'ip_address'      => $v->ip_address,
                    'location'        => ($v->city ? $v->city . ', ' : '') . ($v->country ?: 'Global Location'),
                    'device'          => $v->device_type ?: 'Desktop',
                    'platform'        => $v->operating_system ?: 'OS',
                    'browser'         => $v->browser ?: 'Browser',
                    'user_agent'      => $v->user_agent,
                    'context_data'    => [
                        'page'         => $v->page,
                        'country_code' => $v->country_code,
                        'region'       => $v->region,
                        'city'         => $v->city,
                    ],
                    'created_at'      => $v->created_at->format('d M Y, h:i:s A'),
                    'time_ago'        => $v->created_at->diffForHumans(),
                    'user_profile'    => $v->user_id ? route('admin.questioners.show', $v->user_id) : null,
                ]);
            }
        }

        // 4. Fatwa Activity Log
        if ($source === 'fatwa') {
            $f = DB::table('fatwa_activity_logs')
                ->leftJoin('users', 'fatwa_activity_logs.user_id', '=', 'users.id')
                ->where('fatwa_activity_logs.id', $id)
                ->select('fatwa_activity_logs.*', 'users.name as u_name', 'users.email as u_email')
                ->first();
            if ($f) {
                return response()->json([
                    'id'              => $f->id,
                    'source'          => 'Fatwa & Scholar Activity',
                    'user_name'       => $f->u_name ?: 'Public Reader / Scholar',
                    'user_email'      => $f->u_email ?: '—',
                    'user_mobile'     => '—',
                    'user_role'       => 'Scholar / User',
                    'action'          => $f->event,
                    'action_label'    => ucwords(str_replace('_', ' ', $f->event)),
                    'action_category' => 'Fatwa',
                    'status'          => $f->color === 'danger' ? 'failed' : ($f->color === 'warning' ? 'warning' : 'info'),
                    'description'     => $f->notes ?: $f->event_label,
                    'failure_reason'  => '—',
                    'failure_raw'     => null,
                    'ip_address'      => '—',
                    'location'        => $f->event_label ?: '—',
                    'device'          => 'Web',
                    'platform'        => 'Web',
                    'browser'         => 'Browser',
                    'user_agent'      => '—',
                    'context_data'    => [
                        'question_id' => $f->question_id,
                        'fatwa_id'    => $f->fatwa_id,
                        'event_label' => $f->event_label,
                        'notes'       => $f->notes,
                    ],
                    'created_at'      => Carbon::parse($f->created_at)->format('d M Y, h:i:s A'),
                    'time_ago'        => Carbon::parse($f->created_at)->diffForHumans(),
                    'user_profile'    => $f->user_id ? route('admin.questioners.show', $f->user_id) : null,
                ]);
            }
        }

        // 5. System Health Audit Log
        if ($source === 'audit') {
            $h = DB::table('system_health_audit_logs')->where('id', $id)->first();
            if ($h) {
                return response()->json([
                    'id'              => $h->id,
                    'source'          => 'System Health & Security Audit',
                    'user_name'       => $h->email ?: 'System',
                    'user_email'      => $h->email ?: '—',
                    'user_mobile'     => '—',
                    'user_role'       => 'User',
                    'action'          => $h->event,
                    'action_label'    => ucwords(str_replace('_', ' ', $h->event)),
                    'action_category' => 'Auth / Security',
                    'status'          => (str_contains($h->event, 'fail') || str_contains($h->event, 'expired')) ? 'failed' : 'success',
                    'description'     => $h->details ?: $h->event,
                    'failure_reason'  => (str_contains($h->event, 'fail') || str_contains($h->event, 'expired')) ? $h->event : '—',
                    'failure_raw'     => $h->event,
                    'ip_address'      => $h->ip_address,
                    'location'        => '—',
                    'device'          => 'Device',
                    'platform'        => 'Platform',
                    'browser'         => 'Browser',
                    'user_agent'      => $h->user_agent,
                    'context_data'    => json_decode($h->details, true) ?: ['details' => $h->details],
                    'created_at'      => Carbon::parse($h->created_at)->format('d M Y, h:i:s A'),
                    'time_ago'        => Carbon::parse($h->created_at)->diffForHumans(),
                    'user_profile'    => null,
                ]);
            }
        }

        // 6. API Logs
        if ($source === 'api') {
            $a = DB::table('system_api_logs')->where('id', $id)->first();
            if ($a) {
                return response()->json([
                    'id'              => $a->id,
                    'source'          => 'System API Telemetry',
                    'user_name'       => 'API Client',
                    'user_email'      => '—',
                    'user_mobile'     => '—',
                    'user_role'       => 'Guest',
                    'action'          => $a->method,
                    'action_label'    => $a->method . ' ' . $a->path,
                    'action_category' => 'API',
                    'status'          => $a->status_code >= 400 ? 'failed' : 'info',
                    'description'     => $a->method . ' ' . $a->path . ' [' . $a->status_code . '] in ' . $a->duration_ms . 'ms',
                    'failure_reason'  => $a->error_category ?: '—',
                    'failure_raw'     => $a->error_category,
                    'ip_address'      => $a->ip_address,
                    'location'        => '—',
                    'device'          => $a->source_category ?: 'API',
                    'platform'        => 'HTTP',
                    'browser'         => 'API Client',
                    'user_agent'      => '—',
                    'context_data'    => (array) $a,
                    'created_at'      => Carbon::parse($a->created_at)->format('d M Y, h:i:s A'),
                    'time_ago'        => Carbon::parse($a->created_at)->diffForHumans(),
                    'user_profile'    => null,
                ]);
            }
        }

        // Fallback search in UserActivityLog
        $log = UserActivityLog::with('user.role')->find($id);
        if ($log) {
            return $this->show($request->merge(['source' => 'activity']), $id);
        }

        return response()->json(['error' => 'Log entry not found'], 404);
    }

    /**
     * Export unified logs as CSV.
     */
    public function export(Request $request)
    {
        $search    = trim((string) $request->get('search', ''));
        $dateRange = $request->get('date_range', 'all');
        $dateFrom  = match ($dateRange) {
            'today' => Carbon::today(),
            '7d'    => now()->subDays(7),
            '30d'   => now()->subDays(30),
            default => null,
        };

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="all_system_activity_logs_' . date('Y-m-d_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($dateFrom, $search) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Source', 'ID', 'Timestamp', 'User', 'Email', 'Role', 'Action', 'Status', 'Description', 'IP Address', 'Location']);

            // Activity Logs
            $act = DB::table('user_activity_logs')
                ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->take(1000)->get();
            foreach ($act as $l) {
                fputcsv($handle, ['Activity', $l->id, $l->created_at, $l->user_name, $l->user_email, $l->user_role, $l->action, $l->status, $l->description, $l->ip_address, $l->location]);
            }

            // Login Logs
            $logins = DB::table('user_login_logs')->leftJoin('users', 'user_login_logs.user_id', '=', 'users.id')
                ->when($dateFrom, fn($q) => $q->where('user_login_logs.created_at', '>=', $dateFrom))
                ->select('user_login_logs.*', 'users.name as u_name', 'users.email as u_email')
                ->latest('user_login_logs.created_at')->take(1000)->get();
            foreach ($logins as $l) {
                fputcsv($handle, ['Login', $l->id, $l->created_at, $l->u_name, $l->u_email, 'user', 'login_success', 'success', 'Session login', $l->ip_address, $l->location]);
            }

            // Visitor Logs
            $visitors = DB::table('portal_visitor_logs')->leftJoin('users', 'portal_visitor_logs.user_id', '=', 'users.id')
                ->when($dateFrom, fn($q) => $q->where('portal_visitor_logs.created_at', '>=', $dateFrom))
                ->select('portal_visitor_logs.*', 'users.name as u_name', 'users.email as u_email')
                ->latest('portal_visitor_logs.created_at')->take(1000)->get();
            foreach ($visitors as $l) {
                fputcsv($handle, ['Visitor', $l->id, $l->created_at, $l->u_name ?: $l->ip_address, $l->u_email, 'guest', 'page_visit', 'info', $l->page, $l->ip_address, $l->country]);
            }

            // Fatwa Activity Logs
            $fatwas = DB::table('fatwa_activity_logs')
                ->when($dateFrom, fn($q) => $q->where('created_at', '>=', $dateFrom))
                ->latest('created_at')->take(1000)->get();
            foreach ($fatwas as $l) {
                fputcsv($handle, ['Fatwa', $l->id, $l->created_at, 'Reader/Scholar', '', 'guest', $l->event, 'info', $l->notes ?: $l->event_label, '', $l->event_label]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
