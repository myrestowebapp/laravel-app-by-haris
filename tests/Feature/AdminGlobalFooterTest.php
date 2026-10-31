<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGlobalFooterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);

        $this->admin = User::create([
            'role_id'    => $adminRole->id,
            'name'       => 'Super Admin',
            'despl_name' => 'Super Admin',
            'email'      => 'admin_footer@darulifta.test',
            'password'   => bcrypt('secret123'),
        ]);
    }

    public function test_global_fixed_footer_renders_across_all_admin_pages(): void
    {
        $routes = [
            route('admin.dashboard'),
            route('admin.categories.index'),
            route('admin.questions.index'),
            route('admin.questioners.index'),
            route('admin.settings.system'),
            route('admin.reports.index'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get($route);

            $response->assertStatus(200, "Failed asserting route: {$route} returns 200");
            $response->assertSee('admin-global-fixed-footer');
            $response->assertSee('2026 Darul Ifta Kauzariyya. All rights reserved.');
            $response->assertSee('Maked & created by');
            $response->assertSee('Mufti Haris Al Kausari - Etpa');
            $response->assertSee('developer-credit-badge');
        }
    }
}
