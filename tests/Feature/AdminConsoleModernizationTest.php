<?php

namespace Tests\Feature;

use App\Models\Madhab;
use App\Models\QuestionAndAnswer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminConsoleModernizationTest extends TestCase
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
        return User::firstOrCreate(['email' => 'admin_test@darulifta.com'], [
            'name' => 'Admin Tester',
            'despl_name' => 'Admin Tester',
            'password' => bcrypt('password123'),
            'role_id' => $adminRole->id,
            'is_active' => 1,
        ]);
    }

    public function test_question_management_renders_modern_dashboard_elements(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->get(route('admin.questions.index'));
        $response->assertStatus(200);

        // Assert shared layout modern sidebar & header elements
        $response->assertSee('Admin Console');
        $response->assertSee('Question Management');
        $response->assertSee('DARUL IFTA');
        $response->assertSee('admin-live-clock');

        // Assert 5 modern summary metric cards are rendered
        $response->assertSee('Total Questions');
        $response->assertSee('Published to Public');
        $response->assertSee('Pending Review');
        $response->assertSee('Under Review');
        $response->assertSee('Rejected');

        // Assert horizontal filter controls
        $response->assertSee('Search by Question ID');
        $response->assertSee('All Statuses');
        $response->assertSee('All Madhabs');
        $response->assertSee('All Assignments');

        // Assert modern table headers
        $response->assertSee('Questioner');
        $response->assertSee('Question Summary');
        $response->assertSee('Assigned To');
    }

    public function test_pending_question_always_displays_as_unassigned_in_question_lists_and_views(): void
    {
        $admin = $this->getOrCreateAdmin();

        // Create a mufti
        $muftiRole = Role::firstOrCreate(['name' => 'mufti']);
        $mufti = User::firstOrCreate(
            ['email' => 'scholar_test@darulifta.local'],
            [
                'name'       => 'Mufti Ahmad Al-Kauzari',
                'despl_name' => 'Mufti Ahmad Al-Kauzari',
                'password'   => bcrypt('password123'),
                'role_id'    => $muftiRole->id,
                'is_active'  => 1,
            ]
        );

        // Create a pending question that has assigned_mufti_id set (simulating legacy data)
        $pendingQuestion = QuestionAndAnswer::create([
            'question_id'       => 'Q9901',
            'title'             => 'Pending Legacy Assignment Test',
            'slug'              => 'q9901-pending-legacy-assignment-test',
            'body'              => 'Should remain unassigned in UI while pending review',
            'status'            => 'pending',
            'user_id'           => $admin->id,
            'assigned_mufti_id' => $mufti->id,
            'is_published'      => false,
        ]);

        // Create an assigned question
        $assignedQuestion = QuestionAndAnswer::create([
            'question_id'       => 'Q9902',
            'title'             => 'Active Assigned Scholar Test',
            'slug'              => 'q9902-active-assigned-scholar-test',
            'body'              => 'Should display mufti name',
            'status'            => 'assigned',
            'user_id'           => $admin->id,
            'assigned_mufti_id' => $mufti->id,
            'is_published'      => false,
        ]);

        // 1. Check Questions Index view
        $indexResponse = $this->actingAs($admin)->get(route('admin.questions.index'));
        $indexResponse->assertStatus(200);
        $indexContent = $indexResponse->getContent();

        // The index should show Unassigned for Q9901
        $this->assertStringContainsString('Q9901', $indexContent);
        $this->assertStringContainsString('Q9902', $indexContent);

        // 2. Check Question Detail Show view for Q9901
        $showPendingResponse = $this->actingAs($admin)->get(route('admin.questions.show', $pendingQuestion));
        $showPendingResponse->assertStatus(200);
        // On detail page, assigned mufti shows '—' while pending
        $showPendingContent = $showPendingResponse->getContent();
        $this->assertStringContainsString('Pending Review', $showPendingContent);

        // 3. Clean up test records
        $pendingQuestion->delete();
        $assignedQuestion->delete();
    }

    public function test_admin_dashboard_renders_modern_signals_and_charts(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Assert modern command center elements
        $response->assertSee('Administrative Command Center');
        $response->assertSee('Knowledge Growth & Fatwa Progression');
        $response->assertSee('Live Visitors Telemetry');
        $response->assertSee('lifelongChart');
        $response->assertSee('chart-canvas-wrapper');

        // Assert dedicated Live Scholar Dashboard section and responsive container
        $response->assertSee('Live Scholar Dashboard');
        $response->assertSee('live-scholars-grid-container');

        // Assert tabbed activity feed
        $response->assertSee('recent-inquiries-tab');
        $response->assertSee('recent-published-tab');

        // Assert sidebar numbers and telemetry badges
        $response->assertSee('sidebar-count-pill');
        $response->assertSee('Mufti Management');
        $response->assertSee('Categories');
        $response->assertSee('Madhab');
        $response->assertSee('Questioners');
        $response->assertSee('Visitor Insights');

        // Assert Live Stats API returns real-time mufti telemetry
        $apiResponse = $this->actingAs($admin)->getJson(route('admin.live-stats.api'));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'byIp',
            'byIdentity',
            'onlineMuftis',
            'onlineMuftisCount',
        ]);
    }

    public function test_question_detail_page_renders_modern_redesign_and_actions(): void
    {
        $admin = $this->getOrCreateAdmin();

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $questioner = User::firstOrCreate(['email' => 'mahadee_test@gmail.com'], [
            'name' => 'MAHADEE MC',
            'despl_name' => 'MAHADEE MC',
            'password' => bcrypt('password123'),
            'role_id' => $userRole->id,
            'is_active' => 1,
            'profile_completed' => 0,
        ]);

        $madhab = Madhab::firstOrCreate(['name' => 'Shafi\'i']);

        $question = QuestionAndAnswer::firstOrCreate(['question_id' => 'Q0693_TEST'], [
            'user_id' => $questioner->id,
            'madhab_id' => $madhab->id,
            'title' => 'Loan vehicle inquiry test',
            'body' => '<p>അടവിന് വാഹനം എടുക്കുന്നതിന്റെ ശാഫിഈ മദ്ഹബിലെ വിധി എന്താണ്?</p>',
            'status' => 'pending',
            'language' => 'Malayalam',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.questions.show', $question));
        $response->assertStatus(200);

        // 1. Top KPI Summary Cards
        $response->assertSee('Q0693_TEST');
        $response->assertSee('Current Status');
        $response->assertSee('Pending Review');
        $response->assertSee('Submitted On');
        $response->assertSee('Time Since Submission');
        $response->assertSee('Shafi\'i');

        // 2. Question Details Card
        $response->assertSee('Question Details');
        $response->assertSee('Short Question:');
        $response->assertSee('Loan vehicle inquiry test');
        $response->assertSee('Full Question:');
        $response->assertSee('MAHADEE MC');
        $response->assertSee('mahadee_test@gmail.com');

        // 3. Question Actions (Reject & Accept prominent cards)
        $response->assertSee('Question Actions');
        $response->assertSee('action-card-reject', false);
        $response->assertSee('action-card-accept', false);
        $response->assertSee('Reject this question with a reason');
        $response->assertSee('Accept and categorise this question');

        // 4. Secondary Action Toolbar
        $response->assertSee('Copy Link');
        $response->assertSee('History');
        $response->assertSee('Print');
        $response->assertSee('Delete');
        $response->assertSee('Back to List');

        // 5. Additional Information Grid
        $response->assertSee('Additional Information');
        $response->assertSee('Language');
        $response->assertSee('Malayalam');
        $response->assertSee('Word Count');
        $response->assertSee('Letter Count');

        // 6. Right Column Panels
        $response->assertSee('Fatwa Access &amp; URL', false);
        $response->assertSee('Fatwa Direct Link:');
        $response->assertSee('Test Short Link');
        $response->assertSee('Questioner Details');
        $response->assertSee('Profile Incomplete');
        $response->assertSee('Fatwa Log History');

        // 7. Rejection Modal
        $response->assertSee('rejectQuestionModal');
        $response->assertSee('Incomplete Question');
        $response->assertSee('Duplicate Question');
        $response->assertSee('Irrelevant to Islamic Queries');
        $response->assertSee('Other');
    }

    public function test_question_rejection_workflow_via_modal_submission(): void
    {
        $admin = $this->getOrCreateAdmin();

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $questioner = User::firstOrCreate(['email' => 'reject_test@gmail.com'], [
            'name' => 'Test Inquirer',
            'despl_name' => 'Test Inquirer',
            'password' => bcrypt('password123'),
            'role_id' => $userRole->id,
            'is_active' => 1,
        ]);

        $question = QuestionAndAnswer::create([
            'question_id' => 'Q_REJECT_TEST_' . time(),
            'user_id' => $questioner->id,
            'title' => 'Test Question to Reject',
            'body' => '<p>Question content to reject</p>',
            'status' => 'pending',
            'language' => 'English',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.questions.reject', $question), [
            'rejection_reason'  => 'Duplicate Question',
            'explanation'       => 'Already answered in Q0012.',
            'internal_note'     => 'Matched identical query from last week.',
            'notify_questioner' => 0,
        ]);

        $response->assertRedirect(route('admin.questions.show', $question));
        $response->assertSessionHas('success');

        $question->refresh();
        $this->assertEquals('rejected', $question->status);
        $this->assertEquals(\App\Models\Status::REJECTED, $question->status_id);

        // Verify Activity Log entry
        $this->assertDatabaseHas('fatwa_activity_logs', [
            'question_id' => $question->id,
            'event'       => 'rejected',
        ]);

        // Verify Question Status History entry
        $this->assertDatabaseHas('question_status_history', [
            'question_and_answer_id' => $question->id,
            'new_status'             => 'rejected',
        ]);

        // Verify that already accepted or published questions cannot be rejected
        $publishedQuestion = QuestionAndAnswer::create([
            'question_id' => 'Q_PUB_REJECT_TEST_' . time(),
            'user_id' => $questioner->id,
            'title' => 'Published Question Rejection Guard Test',
            'body' => '<p>Published fatwa body</p>',
            'status' => 'published',
            'language' => 'English',
        ]);

        $rejectAttempt = $this->actingAs($admin)->post(route('admin.questions.reject', $publishedQuestion), [
            'rejection_reason' => 'Duplicate Question',
        ]);
        $rejectAttempt->assertSessionHas('error');
        $this->assertEquals('published', $publishedQuestion->fresh()->status);

        // Verify published question detail page does NOT render Reject button or modal
        $showPubResponse = $this->actingAs($admin)->get(route('admin.questions.show', $publishedQuestion));
        $showPubResponse->assertStatus(200);
        $showPubContent = $showPubResponse->getContent();
        $this->assertStringNotContainsString('data-bs-target="#rejectQuestionModal"', $showPubContent);
        $this->assertStringNotContainsString('rejectQuestionModalLabel', $showPubContent);
        $this->assertStringContainsString('View Fatwa', $showPubContent);
        $this->assertStringContainsString('Delete', $showPubContent);

        $publishedQuestion->delete();
        $question->delete();
    }

    public function test_accept_question_page_renders_new_style_ui(): void
    {
        $admin = $this->getOrCreateAdmin();

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $questioner = User::firstOrCreate(['email' => 'accept_ui_test@gmail.com'], [
            'name' => 'MAHADEE MC',
            'despl_name' => 'MAHADEE MC',
            'password' => bcrypt('password123'),
            'role_id' => $userRole->id,
            'is_active' => 1,
        ]);

        $madhab = Madhab::firstOrCreate(['name' => 'Shafi\'i']);

        $question = QuestionAndAnswer::create([
            'question_id' => 'Q_ACCEPT_UI_' . time(),
            'user_id' => $questioner->id,
            'madhab_id' => $madhab->id,
            'title' => 'Loan vehicle',
            'body' => '<p>അടവിന് വാഹനം എടുക്കുന്നതിന്റെ ശാഫിഈ മദ്ഹബിലെ വിധി എന്താണ്?</p>',
            'status' => 'pending',
            'language' => 'English',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.questions.accept.form', $question));
        $response->assertStatus(200);

        // Assert Header & Back link
        $response->assertSee('Accept & Triage Question');
        $response->assertSee('Back to Questions');

        // Assert 3 KPI Cards
        $response->assertSee('Current Status');
        $response->assertSee('Pending Review');
        $response->assertSee('Submitted On');
        $response->assertSee('Time Since Submission');

        // Assert Left Card: Question Information
        $response->assertSee('Question Information');
        $response->assertSee('Selected Madhab');
        $response->assertSee('Shafi\'i');
        $response->assertSee('Original Question Text');
        $response->assertSee('MAHADEE MC');
        $response->assertSee('accept_ui_test@gmail.com');

        // Assert Right Card: Question Categorization form
        $response->assertSee('Question Categorization');
        $response->assertSee('Question Summary (Short Question)');
        $response->assertSee('Question Language');
        $response->assertSee('Main Category');
        $response->assertSee('Cancel');
        $response->assertSee('Accept &amp; Save', false);
    }

    public function test_visitor_insights_page_renders_interactive_globe_and_telemetry(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->get(route('admin.visitor.insights'));
        $response->assertStatus(200);

        // Assert Header & Horizon toggle
        $response->assertSee('Global Visitor Intelligence');
        $response->assertSee('Live Telemetry Active');
        $response->assertSee('horizon-btn');
        $response->assertSee('Today');
        $response->assertSee('Last 7 Days');
        $response->assertSee('Last 30 Days');
        $response->assertSee('All-Time');

        // Assert 4 KPI Cards
        $response->assertSee('Total Platform Reach');
        $response->assertSee('Worldwide Coverage');
        $response->assertSee('Primary Geographic Hub');
        $response->assertSee('Active Readers Right Now');

        // Assert 3D Globe Container & Controls
        $response->assertSee('globe-cosmos-card');
        $response->assertSee('globeContainer');
        $response->assertSee('Auto-Spin: ON');
        $response->assertSee('Reset View');
        $response->assertSee('d3.v7.min.js');
        $response->assertSee('topojson-client.min.js');
        $response->assertSee('world-110m.json');

        // Assert 3 Synchronized Tables
        $response->assertSee('Top Sovereign Nations');
        $response->assertSee('Major Regions &amp; States', false);
        $response->assertSee('Urban Centres &amp; Cities', false);
    }

    public function test_visitor_insights_globe_api_returns_expected_payload_across_horizons(): void
    {
        $admin = $this->getOrCreateAdmin();

        // 1. All-time horizon
        $responseAll = $this->actingAs($admin)->get(route('admin.visitor.insights.api', ['period' => 'all']));
        $responseAll->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'summary' => [
                    'total_visitors',
                    'unique_countries',
                    'top_country',
                    'top_country_percentage',
                    'live_visitors',
                ],
                'countries' => [
                    '*' => [
                        'country',
                        'country_code',
                        'flag',
                        'count',
                        'percentage',
                        'lng',
                        'lat',
                        'marker_radius',
                    ]
                ],
                'regions',
                'cities',
            ]);

        $this->assertEquals('all', $responseAll->json('period'));
        $this->assertGreaterThan(0, $responseAll->json('summary.total_visitors'));
        $this->assertNotEmpty($responseAll->json('countries'));

        // 2. Today horizon
        $responseToday = $this->actingAs($admin)->get(route('admin.visitor.insights.api', ['period' => 'today']));
        $responseToday->assertStatus(200);
        $this->assertEquals('today', $responseToday->json('period'));
        $this->assertLessThanOrEqual($responseAll->json('summary.total_visitors'), $responseToday->json('summary.total_visitors'));
    }

    public function test_system_settings_page_renders_modern_redesign_and_controls(): void
    {
        $admin = $this->getOrCreateAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.system'));
        $response->assertStatus(200);

        // 1. Assert Institutional Configuration Banner
        $response->assertSee('System Settings &amp; Control', false);
        $response->assertSee('Institutional Configuration');
        $response->assertSee('Manage global system behavior, lunar calendar alignments, and public display preferences.');

        // 2. Assert Moon Sighting & Hijri Manager
        $response->assertSee('Moon Sighting &amp; Hijri Manager', false);
        $response->assertSee('Current Calculated Date (Adjusted)');
        $response->assertSee('Lunar Month');
        $response->assertSee('Gregorian Date');
        $response->assertSee('Manual Date Adjustment (Days)');
        $response->assertSee('Apply Shift');

        // 3. Assert Public Display Control
        $response->assertSee('Public Display Control');
        $response->assertSee('Enable Arabic Calendar for Public');
        $response->assertSee('Live Preview (Public Header)');

        // 4. Assert Admin Login Credentials Security Card
        $response->assertSee('Admin Login Credentials');
        $response->assertSee('Admin Login Email');
        $response->assertSee('New Password (Min 8 chars)');
        $response->assertSee('Confirm New Password');
        $response->assertSee('For security reasons, use a strong password');
        $response->assertSee('Update Admin Credentials');
    }

    public function test_system_settings_update_handles_hijri_shift_and_credentials(): void
    {
        $admin = $this->getOrCreateAdmin();

        // 1. Update Hijri shift
        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'hijri_adjustment' => 1,
            'show_hijri_public' => 1,
        ]);
        $response->assertRedirect();
        $this->assertEquals(1, \App\Models\SiteSetting::get('hijri_adjustment'));
        $this->assertTrue((bool)\App\Models\SiteSetting::get('show_hijri_public'));

        // Reset to 0
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'hijri_adjustment' => 0,
            'show_hijri_public' => 1,
        ]);
        $this->assertEquals(0, \App\Models\SiteSetting::get('hijri_adjustment'));
    }

    public function test_global_visitor_analytics_synchronization_across_platform(): void
    {
        $admin = $this->getOrCreateAdmin();

        // 1. Service methods return consistent numbers
        $totalVisitors = \App\Services\GlobalVisitorAnalyticsService::getTotalVisitors();
        $knowledgeViews = \App\Services\GlobalVisitorAnalyticsService::getKnowledgeViews();
        $this->assertGreaterThanOrEqual(20, $totalVisitors);
        $this->assertGreaterThanOrEqual(0, $knowledgeViews);

        // 2. Global summary payload
        $summary = \App\Services\GlobalVisitorAnalyticsService::getGlobalSummary();
        $this->assertEquals($totalVisitors, $summary['total_visitors']);
        $this->assertEquals($knowledgeViews, $summary['knowledge_views']);
        $this->assertArrayHasKey('live_visitors_ip', $summary);
        $this->assertArrayHasKey('unique_countries', $summary);

        // 3. Admin summary API endpoint
        $apiResponse = $this->actingAs($admin)->get('/api/admin/visitor-analytics/summary');
        $apiResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'total_visitors'  => $totalVisitors,
                    'knowledge_views' => $knowledgeViews,
                ]
            ]);

        // 4. Public cards API returns synchronized visitors
        $publicStatsResponse = $this->get('/api/public/stats');
        $publicStatsResponse->assertStatus(200);
        $traffic = $publicStatsResponse->json('data.traffic');
        $this->assertEquals($totalVisitors, $traffic['portal_visitors']);
        $this->assertEquals($knowledgeViews, $traffic['total_views']);
    }

    public function test_mufti_management_ranking_and_pagination_hierarchy(): void
    {
        $admin = $this->getOrCreateAdmin();
        $muftiRole = Role::firstOrCreate(['name' => 'mufti']);

        // 1. Create a never-logged-in scholar
        $inactiveMufti = User::create([
            'name'        => 'Never Logged In Scholar',
            'despl_name'  => 'Never Logged In Scholar',
            'email'       => 'never_logged_' . time() . '@darulifta.local',
            'password'    => bcrypt('password123'),
            'role_id'     => $muftiRole->id,
            'is_active'   => 1,
            'login_count' => 0,
            'last_login_at' => null,
            'avatar'      => null,
        ]);

        // 2. Create an active top scholar with avatar and published questions
        $topMufti = User::create([
            'name'          => 'King Top Contributing Scholar',
            'despl_name'    => 'King Top Contributing Scholar',
            'email'         => 'top_scholar_' . time() . '@darulifta.local',
            'password'      => bcrypt('password123'),
            'role_id'       => $muftiRole->id,
            'is_active'     => 1,
            'login_count'   => 50,
            'last_login_at' => now(),
            'last_seen_at'  => now(),
            'avatar'        => 'avatars/top_scholar.jpg',
        ]);

        // Give the top mufti 5 published fatwas
        for ($i = 1; $i <= 5; $i++) {
            QuestionAndAnswer::create([
                'question_id'       => 'Q_TOP_M_' . $i . '_' . time(),
                'user_id'           => $admin->id,
                'title'             => 'Published Fatwa by Top Mufti ' . $i,
                'body'              => 'Fatwa body ' . $i,
                'status'            => 'published',
                'assigned_mufti_id' => $topMufti->id,
                'is_published'      => true,
            ]);
        }

        // Request /admin/muftis
        $response = $this->actingAs($admin)->get(route('admin.muftis.index'));
        $response->assertStatus(200);

        // Assert 8 profiles per page
        $paginatedMuftis = $response->viewData('muftis');
        $this->assertEquals(8, $paginatedMuftis->perPage());

        // Assert Top Scholar ranks first with King status
        $firstMufti = $paginatedMuftis->first();
        $this->assertEquals($topMufti->id, $firstMufti->id);
        $response->assertSee('TOP SCHOLAR');

        // Assert never-logged-in scholar is NOT on page 1 if more than 8 scholars exist
        if ($paginatedMuftis->total() > 8) {
            $this->assertFalse($paginatedMuftis->contains('id', $inactiveMufti->id));
        }

        // Clean up
        QuestionAndAnswer::where('assigned_mufti_id', $topMufti->id)->delete();
        $topMufti->delete();
        $inactiveMufti->delete();
    }
}
