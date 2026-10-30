<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FatwaIntegrityAuditLog;
use App\Models\FatwaIntegrityBackup;
use App\Models\QuestionAndAnswer;
use App\Services\FatwaIntegrityRestorationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FatwaCorrectionController extends Controller
{
    protected FatwaIntegrityRestorationService $restorationService;

    public function __construct(FatwaIntegrityRestorationService $restorationService)
    {
        $this->restorationService = $restorationService;
    }

    /**
     * Display the Fatwa Correction & Restoration Dashboard.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $metrics = $this->restorationService->getMetrics();
        $collisionQIds = FatwaIntegrityRestorationService::COLLISION_QIDS;

        $query = QuestionAndAnswer::with(['category', 'madhab', 'assignedMufti']);

        // Filter Type (all, collisions, published_collisions, restored)
        $filterType = $request->query('filter', 'collisions');

        if ($filterType === 'collisions') {
            $query->whereIn('question_id', $collisionQIds);
        } elseif ($filterType === 'published_collisions') {
            $query->whereIn('question_id', $collisionQIds)->where('status', 'published');
        } elseif ($filterType === 'restored') {
            $restoredIds = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('fatwa_integrity_audit_logs')) {
                    $restoredIds = FatwaIntegrityAuditLog::where('action_type', '!=', 'rollback_restoration')
                        ->pluck('record_id');
                }
            } catch (\Throwable $e) {}
            $query->whereIn('id', $restoredIds);
        }

        // Search
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('question_id', 'like', "%{$term}%")
                  ->orWhere('title', 'like', "%{$term}%")
                  ->orWhere('body', 'like', "%{$term}%")
                  ->orWhere('answer', 'like', "%{$term}%");
            });
        }

        // Madhab Filter
        if ($request->filled('madhab_id')) {
            $query->where('madhab_id', $request->madhab_id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fatwas = $query->orderByRaw("CASE WHEN question_id IN ('" . implode("','", $collisionQIds) . "') THEN 0 ELSE 1 END")
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $madhabs = \App\Models\Madhab::all();

        return view('admin.fatwa-correction.index', compact('fatwas', 'metrics', 'filterType', 'madhabs', 'collisionQIds'));
    }

    /**
     * Comparative 3-Way Workspace for a specific record.
     * Accepts either primary key ID or public Question ID (e.g. 53 or 'Q0053').
     */
    public function show($identifier)
    {
        $this->authorizeAdmin();

        $fatwa = QuestionAndAnswer::where('id', $identifier)
            ->orWhere('question_id', $identifier)
            ->firstOrFail();

        $fatwa->load(['category', 'madhab', 'assignedMufti', 'user', 'references']);

        $isCollision = in_array($fatwa->question_id, FatwaIntegrityRestorationService::COLLISION_QIDS);

        // Historical Sources
        $mongoRecord = $this->restorationService->getHistoricalMongoRecord($fatwa->question_id);
        $mysqlInquiry = $this->restorationService->getOriginalMysqlInquiry($fatwa->question_id);

        // Backups & Audits (with schema existence fallback)
        $backups = collect();
        $auditLogs = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('fatwa_integrity_backups')) {
                $backups = FatwaIntegrityBackup::where('record_id', $fatwa->id)
                    ->orderByDesc('snapshot_version')
                    ->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('fatwa_integrity_audit_logs')) {
                $auditLogs = FatwaIntegrityAuditLog::where('record_id', $fatwa->id)
                    ->with('admin', 'backup')
                    ->latest('id')
                    ->get();
            }
        } catch (\Throwable $e) {}

        $latestBackup = $backups->first();
        $isHashValid = $latestBackup ? $latestBackup->verifyHash() : null;

        return view('admin.fatwa-correction.show', compact(
            'fatwa',
            'isCollision',
            'mongoRecord',
            'mysqlInquiry',
            'backups',
            'latestBackup',
            'isHashValid',
            'auditLogs'
        ));
    }

    /**
     * Search Candidate Answers via AJAX.
     */
    public function searchCandidates(Request $request)
    {
        $this->authorizeAdmin();

        $term = $request->query('q', '');
        $excludeId = $request->query('exclude_id');

        $results = $this->restorationService->searchCandidates($term, $excludeId ? (int)$excludeId : null);

        return response()->json([
            'candidates' => $results,
        ]);
    }

    /**
     * Execute Approved Restoration.
     */
    public function restore(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'target_id'                   => ['required', 'integer', 'exists:question_and_answer,id'],
            'action_type'                 => ['required', 'string', 'in:restore_historical_fatwa,select_verified_answer,restore_and_fork'],
            'reason'                      => ['required', 'string', 'min:5', 'max:500'],
            'title'                       => ['nullable', 'string', 'max:500'],
            'body'                        => ['required', 'string'],
            'answer'                      => ['nullable', 'string'],
            'language'                    => ['nullable', 'string'],
            'status'                      => ['required', 'string', 'in:published,answered,assigned,pending'],
            'is_public'                   => ['nullable', 'boolean'],
            'fork_displaced'              => ['nullable', 'boolean'],
            'displaced_question_body'     => ['nullable', 'string'],
            'displaced_question_user_id'  => ['nullable', 'integer'],
            'source_origin'               => ['nullable', 'string'],
            'source_qid'                  => ['nullable', 'string'],
        ]);

        $approvedChanges = [
            'title'     => $validated['title'] ?? null,
            'body'      => $validated['body'],
            'answer'    => $validated['answer'] ?? null,
            'language'  => $validated['language'] ?? 'English',
            'status'    => $validated['status'],
            'is_public' => $request->has('is_public') ? (bool)$validated['is_public'] : true,
        ];

        $forkData = null;
        if (!empty($validated['fork_displaced']) && !empty($validated['displaced_question_body'])) {
            $forkData = [
                'body'    => $validated['displaced_question_body'],
                'user_id' => $validated['displaced_question_user_id'] ?? 1,
            ];
        }

        $sourceInfo = [
            'origin' => $validated['source_origin'] ?? 'manual_review',
            'qid'    => $validated['source_qid'] ?? null,
        ];

        try {
            $result = $this->restorationService->executeRestoration(
                (int)$validated['target_id'],
                $approvedChanges,
                $validated['action_type'],
                $validated['reason'],
                Auth::id(),
                $forkData,
                $sourceInfo
            );

            $msg = "✅ Fatwa #{$result['record']->question_id} successfully restored and verified with immutable snapshot.";
            if (!empty($result['forked_question_id'])) {
                $msg .= " Displaced inquiry safely forked to pending queue as #{$result['forked_question_id']}.";
            }

            return redirect()->route('admin.fatwa-correction.show', $validated['target_id'])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Restoration failed and was rolled back: ' . $e->getMessage());
        }
    }

    /**
     * Execute Rollback of a Previous Restoration.
     */
    public function rollback(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'audit_log_id' => ['required', 'integer', 'exists:fatwa_integrity_audit_logs,id'],
            'reason'       => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $result = $this->restorationService->executeRollback(
                (int)$validated['audit_log_id'],
                Auth::id(),
                $validated['reason']
            );

            return redirect()->route('admin.fatwa-correction.show', $result['record']->id)
                ->with('success', "🔄 Rollback successfully executed for #{$result['record']->question_id}. Pre-correction state restored.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', '❌ Rollback failed: ' . $e->getMessage());
        }
    }

    /**
     * Strict Authorization Check for Role Admin.
     */
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $isAdmin = ($user->role_id === 1)
            || (strtolower($user->role?->name ?? '') === 'admin')
            || (method_exists($user, 'hasRole') && $user->hasRole('admin'));

        if (!$isAdmin) {
            abort(403, 'Unauthorized access: Fatwa Data Integrity & Correction portal requires Administrator privileges.');
        }
    }
}

