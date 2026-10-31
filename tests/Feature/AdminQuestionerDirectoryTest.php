<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\QuestionAndAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuestionerDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $questioner1;
    protected User $questioner2;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['id' => 2], ['name' => 'User']);

        $this->admin = User::create([
            'role_id'    => $adminRole->id,
            'name'       => 'Admin User',
            'despl_name' => 'Admin User',
            'email'      => 'admin@darulifta.test',
            'password'   => bcrypt('secret123'),
        ]);

        $this->questioner1 = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Ahmad Questioner',
            'despl_name' => 'Ahmad Questioner',
            'email'      => 'ahmad@example.com',
            'country'    => 'India',
            'created_at' => now()->subDays(10),
        ]);

        $this->questioner2 = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Zaid Questioner',
            'despl_name' => 'Zaid Questioner',
            'email'      => 'zaid@example.com',
            'country'    => 'United Kingdom',
            'created_at' => now()->subDays(2),
        ]);

        // Questions for questioner1
        QuestionAndAnswer::create([
            'question_id' => 'Q001',
            'user_id'     => $this->questioner1->id,
            'title'       => 'Question 1 by Ahmad',
            'body'        => 'Is this permissible?',
            'status'      => 'published',
            'is_public'   => true,
        ]);
        QuestionAndAnswer::create([
            'question_id' => 'Q002',
            'user_id'     => $this->questioner1->id,
            'title'       => 'Question 2 by Ahmad',
            'body'        => 'Pending inquiry',
            'status'      => 'pending',
            'is_public'   => false,
        ]);

        // Questions for questioner2
        QuestionAndAnswer::create([
            'question_id' => 'Q003',
            'user_id'     => $this->questioner2->id,
            'title'       => 'Question by Zaid',
            'body'        => 'Another question',
            'status'      => 'pending',
            'is_public'   => false,
        ]);
    }

    public function test_questioner_directory_renders_compact_table_and_summary_kpis(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questioners.index'));

        $response->assertStatus(200);
        $response->assertSee('Questioner Directory');
        $response->assertSee('Total Inquirers');
        $response->assertSee('In Progress Review');
        $response->assertSee('Published Answers');
        $response->assertSee('Ahmad Questioner');
        $response->assertSee('Zaid Questioner');
        $response->assertSee('India');
        $response->assertSee('United Kingdom');
        $response->assertSee('bulkActionBar');
    }

    public function test_questioner_directory_search_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => 'Ahmad']));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Questioner');
        $response->assertDontSee('Zaid Questioner');

        $responseCountry = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => 'Kingdom']));
        $responseCountry->assertStatus(200);
        $responseCountry->assertSee('Zaid Questioner');
        $responseCountry->assertDontSee('Ahmad Questioner');
    }

    public function test_questioner_directory_sorting(): void
    {
        // Sort by name ASC: Ahmad should appear before Zaid
        $responseAsc = $this->actingAs($this->admin)->get(route('admin.questioners.index', [
            'sort'      => 'name',
            'direction' => 'asc',
        ]));
        $responseAsc->assertStatus(200);
        $contentAsc = $responseAsc->getContent();
        $this->assertTrue(strpos($contentAsc, 'Ahmad Questioner') < strpos($contentAsc, 'Zaid Questioner'));

        // Sort by name DESC: Zaid should appear before Ahmad
        $responseDesc = $this->actingAs($this->admin)->get(route('admin.questioners.index', [
            'sort'      => 'name',
            'direction' => 'desc',
        ]));
        $responseDesc->assertStatus(200);
        $contentDesc = $responseDesc->getContent();
        $this->assertTrue(strpos($contentDesc, 'Zaid Questioner') < strpos($contentDesc, 'Ahmad Questioner'));
    }

    public function test_questioner_directory_pagination_density(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['per_page' => 10]));
        $response->assertStatus(200);
        $response->assertSee('Show 10 rows');
    }

    public function test_questioner_bulk_export_selected_csv(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questioners.export-selected'), [
            'selected_ids' => [$this->questioner1->id],
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('Ahmad Questioner', $csvContent);
        $this->assertStringContainsString('ahmad@example.com', $csvContent);
        $this->assertStringNotContainsString('Zaid Questioner', $csvContent);
    }
}
