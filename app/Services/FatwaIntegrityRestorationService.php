<?php

namespace App\Services;

use App\Models\FatwaIntegrityAuditLog;
use App\Models\FatwaIntegrityBackup;
use App\Models\QuestionAndAnswer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class FatwaIntegrityRestorationService
{
    /**
     * List of 94 known collision QIDs identified in Phase 0 audit.
     */
    public const COLLISION_QIDS = [
        'Q0007', 'Q0008', 'Q0009', 'Q0010', 'Q0011', 'Q0012', 'Q0013', 'Q0014', 'Q0015',
        'Q0016', 'Q0017', 'Q0018', 'Q0019', 'Q0020', 'Q0021', 'Q0022', 'Q0023', 'Q0024',
        'Q0025', 'Q0026', 'Q0027', 'Q0028', 'Q0029', 'Q0030', 'Q0031', 'Q0032', 'Q0033',
        'Q0034', 'Q0035', 'Q0036', 'Q0037', 'Q0038', 'Q0039', 'Q0040', 'Q0041', 'Q0042',
        'Q0043', 'Q0044', 'Q0045', 'Q0046', 'Q0047', 'Q0048', 'Q0049', 'Q0050', 'Q0051',
        'Q0052', 'Q0053', 'Q0054', 'Q0055', 'Q0056', 'Q0057', 'Q0060', 'Q0061', 'Q0062',
        'Q0063', 'Q0064', 'Q0065', 'Q0066', 'Q0067', 'Q0068', 'Q0069', 'Q0070', 'Q0071',
        'Q0072', 'Q0073', 'Q0074', 'Q0075', 'Q0076', 'Q0077', 'Q0078', 'Q0079', 'Q0080',
        'Q0081', 'Q0082', 'Q0083', 'Q0084', 'Q0085', 'Q0086', 'Q0087', 'Q0088', 'Q0089',
        'Q0090', 'Q0091', 'Q0092', 'Q0093', 'Q0094', 'Q0095', 'Q0096', 'Q0097', 'Q0098',
        'Q0099', 'Q0100', 'Q0101', 'Q0102', 'Q0103', 'Q0104', 'Q0105'
    ];

    /**
     * Get live dashboard telemetry metrics.
     */
    public function getMetrics(): array
    {
        $totalInspected = QuestionAndAnswer::count();
        $confirmedCollisions = count(self::COLLISION_QIDS);

        // Published collisions currently in database
        $publishedCollisions = QuestionAndAnswer::whereIn('question_id', self::COLLISION_QIDS)
            ->where('status', 'published')
            ->count();

        // Snapshots captured (with schema existence fallback)
        $totalBackups = 0;
        $restorationsCount = 0;
        $rollbacksCount = 0;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('fatwa_integrity_backups')) {
                $totalBackups = FatwaIntegrityBackup::count();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('fatwa_integrity_audit_logs')) {
                $restorationsCount = FatwaIntegrityAuditLog::where('action_type', '!=', 'rollback_restoration')->count();
                $rollbacksCount = FatwaIntegrityAuditLog::where('action_type', 'rollback_restoration')->count();
            }
        } catch (\Throwable $e) {}

        // Awaiting review
        $awaitingReview = max(0, $confirmedCollisions - $restorationsCount);

        return [
            'total_inspected'      => $totalInspected,
            'confirmed_collisions' => $confirmedCollisions,
            'published_collisions' => $publishedCollisions,
            'awaiting_review'      => $awaitingReview,
            'total_backups'        => $totalBackups,
            'restorations_count'   => $restorationsCount,
            'rollbacks_count'      => $rollbacksCount,
            'unresolved_cases'     => 0,
        ];
    }

    /**
     * Parse and cache the authoritative MongoDB migration dump.
     */
    public function getMongoArchiveIndex(): array
    {
        return Cache::remember('mongo_migration_dump_index', 3600, function () {
            $dumpPath = base_path('database/migrations_data/migrate_from_mongodb_to_mysql.sql');
            if (!file_exists($dumpPath)) {
                return [];
            }

            $archive = [];
            $fp = fopen($dumpPath, 'r');
            while (($line = fgets($fp)) !== false) {
                if (strpos($line, "INSERT INTO `question_and_answer`") !== false) {
                    if (preg_match("/VALUES\s*\(\s*'([^']+)'\s*,\s*(\d+)\s*,\s*(?:NULL|(\d+))\s*,\s*(?:NULL|(\d+))\s*,\s*(?:NULL|(\d+))\s*,\s*(?:NULL|(\d+))\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(?:NULL|'([^']*)')/u", $line, $m)) {
                        $qid = $m[1];
                        $archive[$qid] = [
                            'question_id'         => $qid,
                            'title'               => $m[7],
                            'body'                => $m[8],
                            'language'            => $m[9],
                            'status'              => $m[10],
                            'answer'              => $m[11] ?? '',
                            'category_id'         => !empty($m[3]) ? (int)$m[3] : null,
                            'madhab_id'           => !empty($m[4]) ? (int)$m[4] : null,
                            'assigned_mufti_id'   => !empty($m[5]) ? (int)$m[5] : null,
                            'checked_approved_by' => !empty($m[6]) ? (int)$m[6] : null,
                        ];
                    }
                }
            }
            fclose($fp);
            return $archive;
        });
    }

    /**
     * Get authoritative historical MongoDB record for a specific question_id.
     */
    public function getHistoricalMongoRecord(string $questionId): ?array
    {
        $archive = $this->getMongoArchiveIndex();
        return $archive[$questionId] ?? null;
    }

    /**
     * Parse original 2025 MySQL question data from public/questions.sql.
     */
    public function getOriginalMysqlInquiry(string $questionId): ?array
    {
        return Cache::remember('mysql_questions_sql_index', 3600, function () use ($questionId) {
            $sqlPath = database_path('migrations_data/questions.sql');
            if (!file_exists($sqlPath)) {
                $sqlPath = public_path('questions.sql');
            }
            if (!file_exists($sqlPath)) {
                return [];
            }

            $inquiries = [];
            $lines = file($sqlPath);
            foreach ($lines as $line) {
                if (preg_match("/^\s*\(\s*(\d+)\s*,\s*'([^']+)'\s*,\s*(\d+)\s*,\s*'([^']*)'\s*,\s*(?:NULL|'[^']*')\s*,\s*(?:NULL|'[^']*')\s*,\s*'([^']*)'/u", $line, $m)) {
                    $inquiries[$m[2]] = [
                        'legacy_id'  => (int)$m[1],
                        'question_id'=> $m[2],
                        'user_id'    => (int)$m[3],
                        'madhhab'    => $m[4],
                        'question'   => $m[5],
                    ];
                }
            }
            return $inquiries;
        })[$questionId] ?? null;
    }

    /**
     * Search candidate answers across active DB and historical archive.
     */
    public function searchCandidates(string $searchTerm, ?int $excludeRecordId = null, int $limit = 10): array
    {
        $term = trim($searchTerm);
        if (empty($term)) {
            return [];
        }

        $results = [];

        // 1. Search Active Database
        $dbCandidates = QuestionAndAnswer::with(['category', 'madhab', 'assignedMufti'])
            ->where('status', 'published')
            ->whereNotNull('answer')
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('body', 'like', "%{$term}%")
                  ->orWhere('answer', 'like', "%{$term}%")
                  ->orWhere('question_id', 'like', "%{$term}%");
            });

        if ($excludeRecordId) {
            $dbCandidates->where('id', '!=', $excludeRecordId);
        }

        foreach ($dbCandidates->take($limit)->get() as $fatwa) {
            $results[] = [
                'source'       => 'database',
                'record_id'    => $fatwa->id,
                'question_id'  => $fatwa->question_id,
                'title'        => $fatwa->title,
                'body'         => $fatwa->body,
                'answer'       => $fatwa->answer,
                'status'       => $fatwa->status,
                'madhab_name'  => $fatwa->madhab?->name ?? '—',
                'category_name'=> $fatwa->category?->name ?? '—',
                'published_at' => $fatwa->published_at?->format('d M Y') ?? '—',
            ];
        }

        // 2. Search MongoDB Archive if results fewer than limit
        if (count($results) < $limit) {
            $archive = $this->getMongoArchiveIndex();
            $termLower = mb_strtolower($term);

            foreach ($archive as $item) {
                if (count($results) >= $limit) {
                    break;
                }

                $matchTitle = mb_stripos($item['title'], $termLower) !== false;
                $matchBody  = mb_stripos($item['body'], $termLower) !== false;
                $matchAnswer= mb_stripos($item['answer'], $termLower) !== false;
                $matchQid   = mb_stripos($item['question_id'], $termLower) !== false;

                if ($matchTitle || $matchBody || $matchAnswer || $matchQid) {
                    $results[] = [
                        'source'       => 'mongodb_archive',
                        'record_id'    => null,
                        'question_id'  => $item['question_id'],
                        'title'        => $item['title'],
                        'body'         => $item['body'],
                        'answer'       => $item['answer'],
                        'status'       => $item['status'],
                        'madhab_name'  => 'Shafii / General',
                        'category_name'=> 'Historical Archive',
                        'published_at' => 'Historical (2019–2024)',
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Create an immutable snapshot of the production record before any change.
     */
    public function createImmutableSnapshot(
        QuestionAndAnswer $record,
        int $adminId,
        string $reason,
        string $operationUuid
    ): FatwaIntegrityBackup {
        // Load relationships
        $record->loadMissing('references');

        $rowArray = $record->toArray();
        $refsArray = $record->references ? $record->references->toArray() : [];

        // Determine next snapshot version
        $nextVersion = (int)FatwaIntegrityBackup::where('record_id', $record->id)->max('snapshot_version') + 1;

        // Compute deterministic cryptographic hash
        $contentHash = FatwaIntegrityBackup::computeIntegrityHash($rowArray);

        return FatwaIntegrityBackup::create([
            'operation_uuid'            => $operationUuid,
            'record_id'                 => $record->id,
            'question_id'               => $record->question_id,
            'snapshot_version'          => $nextVersion,
            'original_title'            => $record->title,
            'original_body'             => $record->body,
            'original_answer'           => $record->answer,
            'original_status'           => $record->status,
            'original_is_public'        => (bool)$record->is_public,
            'original_madhab_id'        => $record->madhab_id,
            'original_category_id'      => $record->category_id,
            'original_user_id'          => $record->user_id,
            'original_assigned_mufti_id'=> $record->assigned_mufti_id,
            'original_published_at'     => $record->published_at,
            'original_created_at'       => $record->created_at,
            'original_updated_at'       => $record->updated_at,
            'full_row_snapshot'         => $rowArray,
            'references_snapshot'       => $refsArray,
            'content_sha256'            => $contentHash,
            'created_by_admin_id'       => $adminId,
            'snapshot_reason'           => $reason,
        ]);
    }

    /**
     * Verify snapshot integrity against canonical hash and live target.
     */
    public function verifySnapshotIntegrity(FatwaIntegrityBackup $backup, QuestionAndAnswer $record): bool
    {
        // 1. Verify internal model hash consistency
        if (!$backup->verifyHash()) {
            throw new RuntimeException("Snapshot integrity verification failed: internal SHA-256 hash mismatch.");
        }

        // 2. Verify target identity alignment
        if ((int)$backup->record_id !== (int)$record->id) {
            throw new RuntimeException("Snapshot integrity verification failed: record ID mismatch.");
        }

        // 3. Verify that snapshot matches current pre-correction database state
        $recordCanonicalHash = FatwaIntegrityBackup::computeIntegrityHash($record->toArray());
        if (!hash_equals($backup->content_sha256, $recordCanonicalHash)) {
            throw new RuntimeException("Snapshot integrity verification failed: database record was modified during capture.");
        }

        return true;
    }

    /**
     * Execute an atomic, backup-preserved restoration.
     */
    public function executeRestoration(
        int $targetFatwaId,
        array $approvedChanges,
        string $actionType,
        string $reason,
        int $adminId,
        ?array $forkDisplacedQuestionData = null,
        ?array $sourceRecordInfo = null
    ): array {
        $operationUuid = (string)Str::uuid();

        return DB::transaction(function () use (
            $targetFatwaId,
            $approvedChanges,
            $actionType,
            $reason,
            $adminId,
            $forkDisplacedQuestionData,
            $sourceRecordInfo,
            $operationUuid
        ) {
            // 1. Pessimistic lock on target record
            $target = QuestionAndAnswer::where('id', $targetFatwaId)->lockForUpdate()->firstOrFail();
            $preSnapshot = $target->toArray();

            // 2. Create immutable backup snapshot
            $backup = $this->createImmutableSnapshot($target, $adminId, $reason, $operationUuid);

            // 3. Verify snapshot integrity before writing
            $this->verifySnapshotIntegrity($backup, $target);

            // 4. Sanitize and apply approved changes
            $allowedFields = ['title', 'body', 'answer', 'language', 'category_id', 'madhab_id', 'status', 'is_public', 'published_at'];
            $filteredUpdates = array_intersect_key($approvedChanges, array_flip($allowedFields));

            if (empty($filteredUpdates)) {
                throw new RuntimeException("No valid approved fields provided for restoration.");
            }

            $target->update($filteredUpdates);

            // 5. Handle forking of displaced question (Workflow C)
            $forkedQId = null;
            if ($forkDisplacedQuestionData && !empty($forkDisplacedQuestionData['body'])) {
                $forkedQId = $this->forkDisplacedQuestion($forkDisplacedQuestionData, $target->question_id);
            }

            $postSnapshot = $target->fresh()->toArray();

            // 6. Record immutable audit log
            $auditLog = FatwaIntegrityAuditLog::create([
                'operation_uuid'           => $operationUuid,
                'record_id'                => $target->id,
                'question_id'              => $target->question_id,
                'backup_id'                => $backup->id,
                'admin_id'                 => $adminId,
                'action_type'              => $actionType,
                'changed_fields'           => array_keys($filteredUpdates),
                'pre_restoration_snapshot' => $preSnapshot,
                'post_restoration_snapshot'=> $postSnapshot,
                'source_record_info'       => $sourceRecordInfo,
                'forked_question_id'       => $forkedQId,
                'reason'                   => $reason,
                'ip_address'               => request()?->ip(),
            ]);

            return [
                'success'           => true,
                'operation_uuid'    => $operationUuid,
                'record'            => $target->fresh(),
                'backup'            => $backup,
                'audit_log'         => $auditLog,
                'forked_question_id'=> $forkedQId,
            ];
        });
    }

    /**
     * Fork a displaced question to a clean pending inquiry in the scholar queue.
     */
    private function forkDisplacedQuestion(array $data, string $originQId): string
    {
        $newQId = QuestionAndAnswer::generateQuestionId();

        QuestionAndAnswer::create([
            'question_id'        => $newQId,
            'user_id'            => $data['user_id'] ?? 1,
            'category_id'        => $data['category_id'] ?? null,
            'madhab_id'          => $data['madhab_id'] ?? null,
            'assigned_mufti_id'  => null,
            'title'              => $data['title'] ?? Str::limit(strip_tags($data['body']), 60),
            'body'               => $data['body'],
            'language'           => $data['language'] ?? 'English',
            'status'             => 'pending',
            'is_public'          => false,
            'answer'             => null,
            'draft_answer'       => null,
            'tags'               => ['forked_recovery', 'origin_' . $originQId],
        ]);

        return $newQId;
    }

    /**
     * Rollback a restoration using its pre-correction snapshot.
     */
    public function executeRollback(int $auditLogId, int $adminId, string $reason): array
    {
        $operationUuid = (string)Str::uuid();

        return DB::transaction(function () use ($auditLogId, $adminId, $reason, $operationUuid) {
            $log = FatwaIntegrityAuditLog::with('backup')->findOrFail($auditLogId);
            $backup = $log->backup;

            if (!$backup) {
                throw new RuntimeException("Cannot rollback: associated immutable backup snapshot not found.");
            }

            // Pessimistic lock target record
            $target = QuestionAndAnswer::where('id', $backup->record_id)->lockForUpdate()->firstOrFail();
            $preRollbackSnapshot = $target->toArray();

            // Create a snapshot of the current state before rolling back
            $rollbackBackup = $this->createImmutableSnapshot(
                $target,
                $adminId,
                "Pre-rollback snapshot for audit log #{$auditLogId}: {$reason}",
                $operationUuid
            );

            // Reapply original pre-correction values from backup
            $orig = $backup->full_row_snapshot;
            $restoreFields = [
                'title'        => $orig['title'] ?? null,
                'body'         => $orig['body'],
                'answer'       => $orig['answer'] ?? null,
                'status'       => $orig['status'] ?? 'pending',
                'language'     => $orig['language'] ?? 'English',
                'category_id'  => $orig['category_id'] ?? null,
                'madhab_id'    => $orig['madhab_id'] ?? null,
                'is_public'    => $orig['is_public'] ?? true,
                'published_at' => $orig['published_at'] ?? null,
            ];

            $target->update($restoreFields);

            $postRollbackSnapshot = $target->fresh()->toArray();

            // Log the rollback action
            $rollbackLog = FatwaIntegrityAuditLog::create([
                'operation_uuid'           => $operationUuid,
                'record_id'                => $target->id,
                'question_id'              => $target->question_id,
                'backup_id'                => $rollbackBackup->id,
                'admin_id'                 => $adminId,
                'action_type'              => 'rollback_restoration',
                'changed_fields'           => array_keys($restoreFields),
                'pre_restoration_snapshot' => $preRollbackSnapshot,
                'post_restoration_snapshot'=> $postRollbackSnapshot,
                'source_record_info'       => ['reverted_log_id' => $auditLogId, 'reverted_backup_id' => $backup->id],
                'forked_question_id'       => null,
                'reason'                   => $reason,
                'ip_address'               => request()?->ip(),
            ]);

            return [
                'success'       => true,
                'operation_uuid'=> $operationUuid,
                'record'        => $target->fresh(),
                'rollback_log'  => $rollbackLog,
            ];
        });
    }
}

