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

        $adminRole = Role::where('name', 'admin')->first() ?? Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::where('name', 'questioner')->first() ?? Role::firstOrCreate(['name' => 'questioner']);

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

        // Test Rafeeq search with Arabic/Display name and mobile
        $userRole = Role::firstOrCreate(['id' => 2], ['name' => 'User']);
        $rafeeq = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Rafeeq Al-Kausari',
            'despl_name' => 'رفيق الكوثري',
            'email'      => 'rafeeq@example.org',
            'mobile'     => '+919876543210',
            'country'    => 'United Arab Emirates',
        ]);
        QuestionAndAnswer::create([
            'question_id' => 'Q0078',
            'user_id'     => $rafeeq->id,
            'title'       => 'Ruling on ghusl nose cleaning',
            'body'        => 'Is passing water specifically mandatory?',
            'status'      => 'published',
            'is_public'   => true,
        ]);

        // 1. Search lowercase "rafeeq"
        $respLower = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => 'rafeeq']));
        $respLower->assertStatus(200);
        $respLower->assertSee('رفيق الكوثري');
        $respLower->assertSee('rafeeq@example.org');

        // 2. Search Arabic name "رفيق"
        $respArabic = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => 'رفيق']));
        $respArabic->assertStatus(200);
        $respArabic->assertSee('رفيق الكوثري');

        // 3. Search by mobile number
        $respMobile = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => '9876543210']));
        $respMobile->assertStatus(200);
        $respMobile->assertSee('رفيق الكوثري');

        // 4. Search by Question ID linked to questioner
        $respQid = $this->actingAs($this->admin)->get(route('admin.questioners.index', ['search' => 'Q0078']));
        $respQid->assertStatus(200);
        $respQid->assertSee('رفيق الكوثري');
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

    public function test_admin_can_delete_questioner_with_no_questions(): void
    {
        $userRole = Role::where('name', 'questioner')->first() ?? Role::firstOrCreate(['name' => 'questioner']);
        $emptyUser = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Empty Questioner',
            'despl_name' => 'Empty Questioner',
            'email'      => 'empty@example.com',
            'country'    => 'India',
        ]);

        $this->assertDatabaseHas('users', ['id' => $emptyUser->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.questioners.destroy', $emptyUser));
        $response->assertRedirect(route('admin.questioners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $emptyUser->id]);
    }

    public function test_admin_cannot_delete_questioner_with_existing_fatwas(): void
    {
        // questioner1 has questions
        $this->assertDatabaseHas('users', ['id' => $this->questioner1->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.questioners.destroy', $this->questioner1));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->questioner1->id]);
    }

    public function test_admin_cannot_delete_admin_or_mufti_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.questioners.destroy', $this->admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_bulk_delete_questioners_with_no_questions(): void
    {
        $userRole = Role::where('name', 'questioner')->first() ?? Role::firstOrCreate(['name' => 'questioner']);
        $empty1 = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Empty One',
            'despl_name' => 'Empty One',
            'email'      => 'empty1@example.com',
        ]);
        $empty2 = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Empty Two',
            'despl_name' => 'Empty Two',
            'email'      => 'empty2@example.com',
        ]);

        // Bulk delete empty1, empty2, and questioner1 (which has questions)
        $response = $this->actingAs($this->admin)->delete(route('admin.questioners.bulk-delete'), [
            'selected_ids' => [$empty1->id, $empty2->id, $this->questioner1->id],
        ]);

        $response->assertRedirect(route('admin.questioners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $empty1->id]);
        $this->assertDatabaseMissing('users', ['id' => $empty2->id]);
        $this->assertDatabaseHas('users', ['id' => $this->questioner1->id]); // Preserved!
    }

    public function test_questioner_directory_table_shows_delete_button_only_for_empty_profiles(): void
    {
        $userRole = Role::where('name', 'questioner')->first() ?? Role::firstOrCreate(['name' => 'questioner']);
        $emptyUser = User::create([
            'role_id'    => $userRole->id,
            'name'       => 'Empty Questioner Visible',
            'despl_name' => 'Empty Questioner Visible',
            'email'      => 'emptyvis@example.com',
            'country'    => 'India',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.questioners.index'));
        $response->assertStatus(200);

        // Form action targeting emptyUser destroy route should be visible
        $response->assertSee('action="' . route('admin.questioners.destroy', $emptyUser) . '"', false);

        // Form action targeting questioner1 should NOT be present in table
        $response->assertDontSee('action="' . route('admin.questioners.destroy', $this->questioner1) . '"', false);
    }
}
