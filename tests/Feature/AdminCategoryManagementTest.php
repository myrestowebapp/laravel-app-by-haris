<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);

        $this->admin = User::create([
            'role_id'    => $adminRole->id,
            'name'       => 'Admin User',
            'despl_name' => 'Admin User',
            'email'      => 'admin_cat@darulifta.test',
            'password'   => bcrypt('secret123'),
        ]);
    }

    public function test_categories_page_renders_two_column_tree_management(): void
    {
        // Seeded category exists from migration (id: 1, Faiths & Beliefs)
        $root = Category::whereNull('parent_id')->first();
        $this->assertNotNull($root);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Category Tree Management');
        $response->assertSee('Add New Category');
        $response->assertSee('Hierarchical Category Tree');
        $response->assertSee('Category Tips');
        $response->assertSee($root->name);
        $response->assertSee('treeSearchInput');
        $response->assertSee('expandAllCategories');
        $response->assertSee('collapseAllCategories');
    }

    public function test_category_creation_with_multilingual_and_active_state(): void
    {
        $root = Category::whereNull('parent_id')->first();

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name'      => 'Contemporary Issues',
            'name_ar'   => 'قضايا معاصرة',
            'name_ur'   => 'جدید مسائل',
            'parent_id' => $root->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name'      => 'Contemporary Issues',
            'name_ar'   => 'قضايا معاصرة',
            'name_ur'   => 'جدید مسائل',
            'parent_id' => $root->id,
            'is_active' => true,
        ]);
    }

    public function test_category_updating_and_parent_reassignment(): void
    {
        $roots = Category::whereNull('parent_id')->take(2)->get();
        $root1 = $roots[0];
        $root2 = $roots[1];

        $child = Category::create([
            'name'      => 'Unique Topic A ' . time(),
            'parent_id' => $root1->id,
            'slug'      => 'unique-topic-a-' . time(),
            'is_active' => true,
        ]);

        // Reassign to root2 and deactivate
        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $child), [
            'name'      => 'Unique Topic A Renamed',
            'name_ar'   => 'موضوع أ',
            'parent_id' => $root2->id,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $child->refresh();
        $this->assertEquals('Unique Topic A Renamed', $child->name);
        $this->assertEquals($root2->id, $child->parent_id);
        $this->assertFalse($child->is_active);
    }

    public function test_category_toggle_status_ajax(): void
    {
        $cat = Category::create([
            'name'      => 'Test Toggle Cat ' . time(),
            'parent_id' => null,
            'slug'      => 'test-toggle-' . time(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patchJson(route('admin.categories.toggle-status', $cat));

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'is_active' => false,
        ]);

        $cat->refresh();
        $this->assertFalse($cat->is_active);
    }

    public function test_category_deletion(): void
    {
        $root = Category::create([
            'name'      => 'To Delete Root ' . time(),
            'parent_id' => null,
            'slug'      => 'to-delete-root-' . time(),
        ]);

        $child = Category::create([
            'name'      => 'Child of Delete ' . time(),
            'parent_id' => $root->id,
            'slug'      => 'child-of-delete-' . time(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $root));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $root->id]);

        $child->refresh();
        $this->assertNull($child->parent_id);
    }
}
