<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_category_landingpage(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index'))
            ->assertOk()
            ->assertSee('Kategorien');
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.taxonomy.categories.store'), [
                'name' => 'Netzwerk',
                'slug' => 'netzwerk',
                'description' => 'Technische Kurse',
                'parent_id' => null,
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.taxonomy.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Netzwerk',
            'slug' => 'netzwerk',
            'status' => 'draft',
        ]);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Security',
            'slug' => 'security',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->from(route('admin.taxonomy.categories.edit', $category))
            ->put(route('admin.taxonomy.categories.update', $category), [
                'name' => 'Security',
                'slug' => 'security',
                'description' => null,
                'parent_id' => $category->id,
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.taxonomy.categories.edit', $category))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_category_cannot_use_descendant_as_parent(): void
    {
        $user = User::factory()->create();
        $root = Category::query()->create([
            'name' => 'Root',
            'slug' => 'root',
            'status' => 'draft',
        ]);
        $child = Category::query()->create([
            'name' => 'Child',
            'slug' => 'child',
            'parent_id' => $root->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->from(route('admin.taxonomy.categories.edit', $root))
            ->put(route('admin.taxonomy.categories.update', $root), [
                'name' => 'Root',
                'slug' => 'root',
                'description' => null,
                'parent_id' => $child->id,
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.taxonomy.categories.edit', $root))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_level_filters_show_root_and_child_categories(): void
    {
        $user = User::factory()->create();
        $root = Category::query()->create([
            'name' => 'Hauptkategorie',
            'slug' => 'hauptkategorie',
            'status' => 'draft',
        ]);
        $child = Category::query()->create([
            'name' => 'Unterkategorie',
            'slug' => 'unterkategorie',
            'parent_id' => $root->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.main'))
            ->assertOk()
            ->assertSee('Hauptkategorie')
            ->assertDontSee('Unterkategorie');

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.sub'))
            ->assertOk()
            ->assertSee('Unterkategorie')
            ->assertDontSee('Hauptkategorie');
    }

    public function test_category_with_dependencies_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $parent = Category::query()->create([
            'name' => 'Parent',
            'slug' => 'parent',
            'status' => 'draft',
        ]);
        Category::query()->create([
            'name' => 'Child',
            'slug' => 'child-delete-check',
            'parent_id' => $parent->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->delete(route('admin.taxonomy.categories.destroy', $parent))
            ->assertRedirect(route('admin.taxonomy.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);

        $courseCategory = Category::query()->create([
            'name' => 'Course Category',
            'slug' => 'course-category',
            'status' => 'draft',
        ]);

        $course = Course::query()->create([
            'title' => 'Testkurs',
            'slug' => 'testkurs',
            'language_code' => 'de',
            'status' => 'draft',
            'primary_category_id' => $courseCategory->id,
        ]);
        $course->categories()->attach($courseCategory->id);

        $this->actingAs($user)
            ->delete(route('admin.taxonomy.categories.destroy', $courseCategory))
            ->assertRedirect(route('admin.taxonomy.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $courseCategory->id]);
    }
}
