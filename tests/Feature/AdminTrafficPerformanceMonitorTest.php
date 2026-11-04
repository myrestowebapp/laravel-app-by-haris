<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\PortalVisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTrafficPerformanceMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['guard_name' => 'web']);

        $this->admin = User::create([
            'role_id'    => $adminRole->id,
            'name'       => 'Admin Supervisor',
            'despl_name' => 'Admin Supervisor',
            'email'      => 'admin.supervisor@darulifta.test',
            'password'   => bcrypt('password123'),
            'is_active'  => true,
        ]);

        $this->regularUser = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Regular Inquirer',
            'despl_name' => 'Regular Inquirer',
            'email'      => 'regular.inquirer@gmail.com',
            'password'   => bcrypt('password123'),
            'is_active'  => true,
        ]);
    }

    public function test_admin_can_access_traffic_performance_dashboard()
    {
        PortalVisitorLog::create([
            'ip_address'   => '192.168.1.100',
            'country'      => 'India',
            'country_code' => 'IN',
            'user_agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'page'         => '/',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.traffic.performance'));

        $response->assertStatus(200);
        $response->assertSee('Site Traffic &amp; Application Performance Monitor', false);
        $response->assertSee('LIVE APM');
        $response->assertSee('MySQL Query Ping');
        $response->assertSee('Synthetic Benchmark Tester');
    }

    public function test_sidebar_renders_traffic_and_performance_link()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.traffic.performance'));
        $response->assertSee('Traffic &amp; Performance', false);
    }

    public function test_traffic_performance_api_returns_json_metrics()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.traffic.performance.api', ['period' => 'today']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'period',
            'liveVisitorsCount',
            'liveByRole',
            'rpm',
            'totalTrafficPeriod',
            'uniqueIpsPeriod',
            'healthScore',
            'avgResponseTimeMs',
            'p95ResponseTimeMs',
            'dbLatencyMs',
            'dbStatus',
            'phpMemUsageMb',
            'hourlyLabels',
            'hourlyTraffic',
            'hourlyLatency',
            'devices',
            'browsers',
            'topPages',
            'recentRequests',
            'statusDistribution',
        ]);
    }

    public function test_synthetic_ping_benchmark_returns_timing_data()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.traffic.performance.ping'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'timestamp',
            'formatted_time',
            'total_ms',
            'db_ms',
            'db_status',
            'cache_ms',
            'memory_mb',
            'grade',
            'grade_color',
        ]);

        $this->assertIsNumeric($response->json('total_ms'));
        $this->assertIsNumeric($response->json('db_ms'));
        $this->assertIsNumeric($response->json('cache_ms'));
    }

    public function test_unauthenticated_user_cannot_access_traffic_performance()
    {
        $response = $this->get(route('admin.traffic.performance'));

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_traffic_performance()
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.traffic.performance'));

        $this->assertTrue(in_array($response->status(), [403, 302]));
    }
}
