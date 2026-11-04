<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $questioner;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        $this->admin = User::create([
            'role_id'    => $adminRole->id,
            'name'       => 'Admin User',
            'despl_name' => 'Admin User',
            'email'      => 'admin@darulifta.test',
            'password'   => bcrypt('password123'),
            'is_active'  => true,
        ]);

        $this->questioner = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Rafeeq Kottakkal',
            'despl_name' => 'Rafeeq Kottakkal',
            'email'      => 'rafeeq.kottakkal@gmail.com',
            'mobile'     => '+919876543210',
            'password'   => bcrypt('password123'),
            'country'    => 'India',
            'is_active'  => true,
        ]);
    }

    public function test_admin_can_view_user_behavior_logs_page()
    {
        UserActivityLog::record(
            action: 'login_success',
            description: 'Signed in successfully from Chrome on Windows',
            status: 'success',
            user: $this->questioner,
            identifier: $this->questioner->email,
            actionCategory: 'auth'
        );

        $response = $this->actingAs($this->admin)->get(route('admin.user-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('User Behavioral Intelligence');
        $response->assertSee('rafeeq.kottakkal@gmail.com');
        $response->assertSee('Total Logs');
        $response->assertSee('Successful Logins');
        $response->assertSee('Login & Auth Issues');
    }

    public function test_user_behavior_logs_sidebar_link_is_present()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('User Behavior Logs');
        $response->assertSee(route('admin.user-logs.index'));
    }

    public function test_failed_login_records_activity_log_with_reason()
    {
        // Attempt login with wrong password
        $response = $this->post(route('login'), [
            'login_id' => 'rafeeq.kottakkal@gmail.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertSessionHasErrors('login_id');

        // Verify log was automatically created
        $log = UserActivityLog::where('identifier', 'rafeeq.kottakkal@gmail.com')->latest('id')->first();
        $this->assertNotNull($log, 'Failed login should automatically generate a UserActivityLog entry.');
        $this->assertEquals('failed', $log->status);
        $this->assertEquals('login_failed', $log->action);
        $this->assertEquals('invalid_password', $log->failure_reason);
        $this->assertEquals('Rafeeq Kottakkal', $log->user_name);
    }

    public function test_non_existent_user_failed_login_records_identifier()
    {
        $unknownEmail = 'nonexistent.unknown@domain.com';

        $response = $this->post(route('login'), [
            'login_id' => $unknownEmail,
            'password' => 'SomePassword123!',
        ]);

        $response->assertSessionHasErrors('login_id');

        $log = UserActivityLog::where('identifier', $unknownEmail)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertEquals('user_not_found', $log->failure_reason);
    }

    public function test_search_user_logs_by_email_finds_matching_records()
    {
        UserActivityLog::record(
            action: 'login_failed',
            description: 'Failed password attempt for user',
            status: 'failed',
            user: $this->questioner,
            identifier: $this->questioner->email,
            failureReason: 'invalid_password'
        );

        UserActivityLog::record(
            action: 'login_success',
            description: 'Other user logged in',
            status: 'success',
            identifier: 'other.person@randomdomain.test'
        );

        $response = $this->actingAs($this->admin)->get(route('admin.user-logs.index', [
            'search' => 'rafeeq.kottakkal'
        ]));

        $response->assertStatus(200);
        $response->assertSee('rafeeq.kottakkal@gmail.com');
        $response->assertDontSee('other.person@randomdomain.test');
    }

    public function test_search_user_logs_by_mobile_finds_matching_records()
    {
        UserActivityLog::record(
            action: 'login_failed',
            description: 'Failed login via mobile',
            status: 'failed',
            user: $this->questioner,
            identifier: $this->questioner->mobile,
            failureReason: 'invalid_password'
        );

        $response = $this->actingAs($this->admin)->get(route('admin.user-logs.index', [
            'search' => '9876543210'
        ]));

        $response->assertStatus(200);
        $response->assertSee('+919876543210');
    }

    public function test_search_user_logs_by_name_finds_matching_records()
    {
        UserActivityLog::record(
            action: 'profile_completed',
            description: 'User completed required details',
            status: 'success',
            user: $this->questioner,
            identifier: $this->questioner->email
        );

        $response = $this->actingAs($this->admin)->get(route('admin.user-logs.index', [
            'search' => 'Rafeeq'
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rafeeq Kottakkal');
    }

    public function test_inspect_user_log_detail_endpoint_returns_json()
    {
        $log = UserActivityLog::record(
            action: 'otp_failed',
            description: 'Expired OTP code entered',
            status: 'failed',
            user: $this->questioner,
            identifier: $this->questioner->email,
            failureReason: 'expired_otp',
            contextData: ['ip' => '127.0.0.1']
        );

        $response = $this->actingAs($this->admin)->get(route('admin.user-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $log->id,
            'user_email' => 'rafeeq.kottakkal@gmail.com',
            'status' => 'failed',
            'failure_raw' => 'expired_otp',
        ]);
    }

    public function test_dashboard_live_stats_includes_latest_event_for_sound_notification()
    {
        $log = UserActivityLog::record(
            action: 'login_failed',
            description: 'User failed password login',
            status: 'failed',
            user: $this->questioner,
            identifier: $this->questioner->email,
            failureReason: 'invalid_password'
        );

        $response = $this->actingAs($this->admin)->get(route('admin.live-stats.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'latestEvent' => [
                'id',
                'action',
                'action_label',
                'user_name',
                'description',
                'status',
                'time',
                'url',
            ]
        ]);
        $response->assertJsonPath('latestEvent.id', $log->id);
    }

    public function test_dashboard_renders_sound_notification_controls_and_toast()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('btn-sound-toggle');
        $response->assertSee('dash-live-notification-toast');
        $response->assertSee('playNotificationSound');
        $response->assertSee('showDashboardToast');
    }
}
