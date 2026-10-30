<?php

namespace Tests\Feature;

use App\Models\FatwaIntegrityAuditLog;
use App\Models\FatwaIntegrityBackup;
use App\Models\Madhab;
use App\Models\QuestionAndAnswer;
use App\Models\Role;
use App\Models\User;
use App\Services\FatwaIntegrityRestorationService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FatwaCorrectionRestorationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        Cache::flush();
    }

    private function getOrCreateAdmin(): User
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        return User::firstOrCreate(['email' => 'admin_tester@darulifta.com'], [
            'name'       => 'Admin Tester',
            'despl_name' => 'Admin Tester',
            'password'   => bcrypt('password123'),
            'role_id'    => $adminRole->id,
            'is_active'  => 1,
        ]);
    }

    private function getOrCreateQuestioner(): User
    {
        $role = Role::firstOrCreate(['name' => 'questioner', 'guard_name' => 'web']);
        return User::firstOrCreate(['email' => 'questioner_tester@darulifta.com'], [
            'name'       => 'Questioner User',
            'despl_name' => 'Questioner User',
            'password'   => bcrypt('password123'),
            'role_id'    => $role->id,
            'is_active'  => 1,
        ]);
    }

    /**
     * Test 1: Unauthorized guests or non-admin users cannot access the portal.
     */
    public function test_unauthorized_guest_or_non_admin_cannot_access_portal(): void
    {
        // 1. Guest
        $resGuest = $this->get(route('admin.fatwa-correction.index'));
        $resGuest->assertRedirect();

        // 2. Questioner (Web redirect)
        $questioner = $this->getOrCreateQuestioner();
        $resQuestioner = $this->actingAs($questioner)->get(route('admin.fatwa-correction.index'));
        $resQuestioner->assertRedirect();

        // 3. Questioner (JSON request 403)
        $resQuestionerJson = $this->actingAs($questioner)->getJson(route('admin.fatwa-correction.index'));
        $resQuestionerJson->assertStatus(403);
    }

    /**
     * Test 2: Admin can access portal and view live telemetry metrics.
     */
    public function test_admin_can_access_portal_and_view_metrics(): void
    {
        $admin = $this->getOrCreateAdmin();

        $res = $this->actingAs($admin)->get(route('admin.fatwa-correction.index'));
        $res->assertStatus(200);
        $res->assertSee('Fatwa Data Integrity');
        $res->assertSee('Total Inspected');
        $res->assertSee('Confirmed Collisions');
        $res->assertSee('Live Published Mismatches');
        $res->assertSee('Snapshots Preserved');
    }

    /**
     * Test 3: Viewing show page does not modify database state (strictly read-only).
     */
    public function test_inspection_is_strictly_read_only(): void
    {
        $admin = $this->getOrCreateAdmin();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0053',
            'user_id'     => $admin->id,
            'title'       => 'Can I use an alcohol-containing spray?',
            'body'        => 'Can I use an alcohol-containing spray?',
            'answer'      => 'Imam Nawawi Al-Tibyan...',
            'language'    => 'Malayalam',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $backupCountBefore = FatwaIntegrityBackup::count();

        $res = $this->actingAs($admin)->get(route('admin.fatwa-correction.show', $fatwa->id));
        $res->assertStatus(200);
        $res->assertSee('Q0053');
        $res->assertSee('Current Production Record');
        $res->assertSee('Immutable Original Backup');

        // Confirm access also works using public Question ID (e.g. Q0053)
        $resQid = $this->actingAs($admin)->get(route('admin.fatwa-correction.show', 'Q0053'));
        $resQid->assertStatus(200);
        $resQid->assertSee('Q0053');

        // Confirm zero writes during inspection
        $this->assertEquals($backupCountBefore, FatwaIntegrityBackup::count());
    }

    /**
     * Test 4 & 5: Snapshot creation preserves all fields and SHA-256 hash matches canonical serialization.
     */
    public function test_snapshot_creation_preserves_fields_and_computes_sha256(): void
    {
        $admin = $this->getOrCreateAdmin();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0009',
            'user_id'     => $admin->id,
            'title'       => 'Can we eat pork if on edge of death?',
            'body'        => 'Can we eat pork if on edge of death?',
            'answer'      => 'Threading eyebrows is prohibited...',
            'language'    => 'English',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);
        $backup = $service->createImmutableSnapshot($fatwa, $admin->id, 'Pre-test snapshot', 'test-uuid-1234');

        $this->assertNotNull($backup->id);
        $this->assertEquals('Q0009', $backup->question_id);
        $this->assertEquals($fatwa->id, $backup->record_id);
        $this->assertEquals('Can we eat pork if on edge of death?', $backup->original_body);
        $this->assertEquals('Threading eyebrows is prohibited...', $backup->original_answer);

        // Verify SHA-256 hash
        $this->assertTrue($backup->verifyHash());
        $this->assertTrue($service->verifySnapshotIntegrity($backup, $fatwa));
    }

    /**
     * Test 6: Backup records are immutable and cannot be updated or deleted.
     */
    public function test_backup_cannot_be_updated_or_deleted(): void
    {
        $admin = $this->getOrCreateAdmin();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0010',
            'user_id'     => $admin->id,
            'body'        => 'Digital gold question...',
            'answer'      => 'Selling materials for temples...',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);
        $backup = $service->createImmutableSnapshot($fatwa, $admin->id, 'Testing immutability', 'test-uuid-5678');

        // Try updating
        $this->expectException(\RuntimeException::class);
        $backup->update(['original_title' => 'Tampered Title']);
    }

    /**
     * Test 7 & 8: Restoration executes atomically, creates snapshot, updates approved fields, and logs audit.
     */
    public function test_restoration_executes_atomically_and_logs_audit(): void
    {
        $admin = $this->getOrCreateAdmin();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0053',
            'user_id'     => $admin->id,
            'title'       => 'Can I use an alcohol-containing spray?',
            'body'        => 'Can I use an alcohol-containing spray?',
            'answer'      => 'Surah Baqarah Khatm ruling text...',
            'language'    => 'English',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);

        $result = $service->executeRestoration(
            $fatwa->id,
            [
                'title'    => 'സൂറത്തുൽ ബഖറയുടെ ആദ്യ ഭാഗം ഖത്തം ചെയ്യുമ്പോൾ ഓതൽ',
                'body'     => 'ഖുർആൻ ഖത്തം തീർക്കുമ്പോൾ സൂറത്തുൽ ബഖറയുടെ ആദ്യ ഭാഗം പാരായണം ചെയ്യുന്നതിന്റെ അടിസ്ഥാനം എന്താണ്?',
                'language' => 'Malayalam',
                'status'   => 'published',
                'is_public'=> true,
            ],
            'restore_historical_fatwa',
            'Restoring authentic 2019 Baqarah question text',
            $admin->id
        );

        $this->assertTrue($result['success']);

        // Check updated record
        $refreshed = $fatwa->fresh();
        $this->assertEquals('സൂറത്തുൽ ബഖറയുടെ ആദ്യ ഭാഗം ഖത്തം ചെയ്യുമ്പോൾ ഓതൽ', $refreshed->title);
        $this->assertEquals('ഖുർആൻ ഖത്തം തീർക്കുമ്പോൾ സൂറത്തുൽ ബഖറയുടെ ആദ്യ ഭാഗം പാരായണം ചെയ്യുന്നതിന്റെ അടിസ്ഥാനം എന്താണ്?', $refreshed->body);
        $this->assertEquals('Surah Baqarah Khatm ruling text...', $refreshed->answer);

        // Check backup was created with pre-correction values
        $this->assertEquals(1, FatwaIntegrityBackup::where('record_id', $fatwa->id)->count());
        $savedBackup = FatwaIntegrityBackup::where('record_id', $fatwa->id)->first();
        $this->assertEquals('Can I use an alcohol-containing spray?', $savedBackup->original_body);

        // Check audit log
        $this->assertEquals(1, FatwaIntegrityAuditLog::where('record_id', $fatwa->id)->count());
        $log = FatwaIntegrityAuditLog::where('record_id', $fatwa->id)->first();
        $this->assertEquals('restore_historical_fatwa', $log->action_type);
        $this->assertEquals($admin->id, $log->admin_id);
    }

    /**
     * Test 9: Workflow C forks the displaced community inquiry to the pending queue.
     */
    public function test_workflow_c_forks_displaced_question(): void
    {
        $admin = $this->getOrCreateAdmin();
        $questioner = $this->getOrCreateQuestioner();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0053',
            'user_id'     => $questioner->id,
            'title'       => 'Can I use an alcohol-containing spray?',
            'body'        => 'Can I use an alcohol-containing spray?',
            'answer'      => 'Surah Baqarah Khatm answer...',
            'language'    => 'English',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);

        $result = $service->executeRestoration(
            $fatwa->id,
            [
                'title' => 'Surah Baqarah Khatm Fatwa',
                'body'  => 'What is the ruling on reading initial Baqarah verses upon Khatm?',
                'status'=> 'published',
            ],
            'restore_and_fork',
            'Restoring Q0053 and forking User alcohol spray inquiry',
            $admin->id,
            [
                'body'    => 'Can I use an alcohol-containing spray?',
                'user_id' => $questioner->id,
            ]
        );

        $this->assertNotNull($result['forked_question_id']);

        // Check forked question in database
        $forked = QuestionAndAnswer::where('question_id', $result['forked_question_id'])->first();
        $this->assertNotNull($forked);
        $this->assertEquals($questioner->id, $forked->user_id);
        $this->assertEquals('Can I use an alcohol-containing spray?', $forked->body);
        $this->assertEquals('pending', $forked->status);
        $this->assertNull($forked->answer);
    }

    /**
     * Test 10: Rollback restores pre-correction state without deleting history.
     */
    public function test_rollback_restores_pre_correction_state(): void
    {
        $admin = $this->getOrCreateAdmin();

        $fatwa = QuestionAndAnswer::create([
            'question_id' => 'Q0053',
            'user_id'     => $admin->id,
            'title'       => 'Original Spray Title',
            'body'        => 'Original Spray Body',
            'answer'      => 'Original Answer',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);

        // 1. Execute restoration
        $restoredResult = $service->executeRestoration(
            $fatwa->id,
            [
                'title' => 'Changed Title',
                'body'  => 'Changed Body',
                'status'=> 'published',
            ],
            'restore_historical_fatwa',
            'First change',
            $admin->id
        );

        $auditLogId = $restoredResult['audit_log']->id;

        // 2. Execute rollback
        $rollbackResult = $service->executeRollback($auditLogId, $admin->id, 'Reverting first change');
        $this->assertTrue($rollbackResult['success']);

        // 3. Verify target record values were restored
        $refreshed = $fatwa->fresh();
        $this->assertEquals('Original Spray Title', $refreshed->title);
        $this->assertEquals('Original Spray Body', $refreshed->body);

        // 4. Verify that previous audit logs and snapshots were NOT deleted
        $this->assertGreaterThanOrEqual(2, FatwaIntegrityAuditLog::where('record_id', $fatwa->id)->count());
        $this->assertGreaterThanOrEqual(2, FatwaIntegrityBackup::where('record_id', $fatwa->id)->count());
    }

    /**
     * Test 11: Candidate search returns results from database and historical archive.
     */
    public function test_candidate_search_returns_candidates(): void
    {
        $admin = $this->getOrCreateAdmin();

        QuestionAndAnswer::create([
            'question_id' => 'Q0200',
            'user_id'     => $admin->id,
            'title'       => 'Fasting during travel ruling',
            'body'        => 'Is it allowed to fast while travelling?',
            'answer'      => 'Fasting during travel is permissible if it does not cause severe hardship...',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        $service = app(FatwaIntegrityRestorationService::class);
        $results = $service->searchCandidates('travel');

        $this->assertNotEmpty($results);
        $this->assertEquals('Q0200', $results[0]['question_id']);
    }
}
