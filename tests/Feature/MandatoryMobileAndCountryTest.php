<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Madhab;
use App\Models\QuestionAndAnswer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MandatoryMobileAndCountryTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWithoutProfile;
    protected Madhab $madhab;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $roleQuestioner = Role::firstOrCreate(['id' => 3], ['name' => 'questioner']);

        $this->userWithoutProfile = User::create([
            'role_id'           => $roleQuestioner->id,
            'name'              => 'Zeynova Test',
            'despl_name'        => 'Zeynova Test',
            'email'             => 'zeynova.test@example.com',
            'password'          => bcrypt('password123'),
            'mobile'            => null,
            'country'           => null,
            'gender'            => null,
            'address_line_1'    => null,
            'profile_completed' => false,
            'is_active'         => true,
        ]);

        $this->madhab = Madhab::firstOrCreate(['id' => 1], ['name' => 'Shafi']);
        $this->category = Category::firstOrCreate(['id' => 1], ['name' => 'General', 'sort_order' => 1]);
    }

    public function test_user_without_mobile_and_country_is_redirected_to_profile_complete(): void
    {
        $response = $this->actingAs($this->userWithoutProfile)->post(route('question.store'), [
            'title'       => 'Is playing dice games allowed in Islam?',
            'body'        => 'I want to ask a question regarding the permissibility of dice-based board games under Islamic jurisprudence.',
            'madhab_id'   => $this->madhab->id,
            'category_id' => $this->category->id,
        ]);

        $response->assertRedirect(route('profile.complete'));
        $this->assertTrue(session()->has('pending_question'));
        $this->assertEquals(0, QuestionAndAnswer::count());
    }

    public function test_skip_profile_details_route_is_strictly_blocked(): void
    {
        session([
            'pending_question' => [
                'title'       => 'Test Question',
                'body'        => 'This is a test question details text exceeding twenty characters.',
                'madhab_id'   => $this->madhab->id,
                'category_id' => $this->category->id,
            ]
        ]);

        $response = $this->actingAs($this->userWithoutProfile)->post(route('profile.complete.skip'));

        $response->assertRedirect(route('profile.complete'));
        $response->assertSessionHas('error');
        // Verify no question was created through the skipped route
        $this->assertEquals(0, QuestionAndAnswer::count());
    }

    public function test_profile_complete_validation_requires_country(): void
    {
        $response = $this->actingAs($this->userWithoutProfile)->post(route('profile.complete.store'), [
            'mobile'         => '919876543210',
            'gender'         => 'Male',
            'madhab_id'      => $this->madhab->id,
            'address_line_1' => 'Main Street 123',
            'state'          => 'Kerala',
            'district'       => 'Malappuram',
            // Missing country
        ]);

        $response->assertSessionHasErrors('country');
    }

    public function test_profile_complete_validation_requires_mobile(): void
    {
        $response = $this->actingAs($this->userWithoutProfile)->post(route('profile.complete.store'), [
            // Missing mobile
            'gender'         => 'Male',
            'madhab_id'      => $this->madhab->id,
            'address_line_1' => 'Main Street 123',
            'country'        => 'India',
            'state'          => 'Kerala',
            'district'       => 'Malappuram',
        ]);

        $response->assertSessionHasErrors('mobile');
    }

    public function test_completing_profile_with_mobile_and_country_posts_pending_question(): void
    {
        session([
            'pending_question' => [
                'title'       => 'Question on Fasting during Travel',
                'body'        => 'What is the ruling on breaking fast while travelling more than the distance of Qasr?',
                'madhab_id'   => $this->madhab->id,
                'category_id' => $this->category->id,
            ]
        ]);

        $response = $this->actingAs($this->userWithoutProfile)->post(route('profile.complete.store'), [
            'mobile'         => '+919876543210',
            'country'        => 'India',
            'gender'         => 'Male',
            'madhab_id'      => $this->madhab->id,
            'address_line_1' => 'P.O. Box 456, Calicut',
            'state'          => 'Kerala',
            'district'       => 'Kozhikode',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertFalse(session()->has('pending_question'));

        $this->userWithoutProfile->refresh();
        $this->assertEquals('+919876543210', $this->userWithoutProfile->mobile);
        $this->assertEquals('India', $this->userWithoutProfile->country);
        $this->assertTrue((bool)$this->userWithoutProfile->profile_completed);

        $this->assertEquals(1, QuestionAndAnswer::count());
        $createdQuestion = QuestionAndAnswer::first();
        $this->assertEquals('Question on Fasting during Travel', $createdQuestion->title);
        $this->assertEquals($this->userWithoutProfile->id, $createdQuestion->user_id);
    }

    public function test_completing_profile_with_country_code_and_local_number_formats_full_international_number(): void
    {
        $response = $this->actingAs($this->userWithoutProfile)->post(route('profile.complete.store'), [
            'country_code'   => '+966',
            'mobile'         => '501234567',
            'country'        => 'Saudi Arabia',
            'gender'         => 'Male',
            'madhab_id'      => $this->madhab->id,
            'address_line_1' => 'Al-Olaya Street, Riyadh',
            'state'          => 'Riyadh Region',
            'district'       => 'Riyadh',
        ]);

        $response->assertRedirect(route('user.dashboard'));

        $this->userWithoutProfile->refresh();
        $this->assertEquals('+966501234567', $this->userWithoutProfile->mobile);
        $this->assertEquals('+966', $this->userWithoutProfile->country_code);
        $this->assertEquals('Saudi Arabia', $this->userWithoutProfile->country);
    }

    public function test_mobile_api_strictly_requires_mobile_and_country(): void
    {
        // 1. Attempt API submission without mobile and country
        $response = $this->actingAs($this->userWithoutProfile, 'sanctum')->postJson('/api/user/questions', [
            'title'       => 'API Question Inquiry',
            'body'        => 'This is an inquiry posted via mobile API with length over twenty chars.',
            'category_id' => $this->category->id,
            'madhab_id'   => $this->madhab->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['status' => 'error']);

        // 2. Submission with mobile and country succeeds
        $successResponse = $this->actingAs($this->userWithoutProfile, 'sanctum')->postJson('/api/user/questions', [
            'title'       => 'API Question Inquiry',
            'body'        => 'This is an inquiry posted via mobile API with length over twenty chars.',
            'category_id' => $this->category->id,
            'madhab_id'   => $this->madhab->id,
            'mobile'      => '+971501234567',
            'country'     => 'United Arab Emirates',
        ]);

        $successResponse->assertStatus(201);
        $successResponse->assertJsonFragment(['status' => 'success']);

        $this->userWithoutProfile->refresh();
        $this->assertEquals('+971501234567', $this->userWithoutProfile->mobile);
        $this->assertEquals('United Arab Emirates', $this->userWithoutProfile->country);
    }
}

