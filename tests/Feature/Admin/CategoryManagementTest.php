<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\CategoryTaxonomySetting;
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
                'sort_order' => 0,
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
                'sort_order' => 0,
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.taxonomy.categories.edit', $root))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_legacy_main_and_sub_routes_redirect_to_index_with_level(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.main'))
            ->assertRedirect(route('admin.taxonomy.categories.index', ['level' => 'root']));

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.sub'))
            ->assertRedirect(route('admin.taxonomy.categories.index', ['level' => 'child']));
    }

    public function test_level_filters_show_root_and_child_categories(): void
    {
        $user = User::factory()->create();
        $root = Category::query()->create([
            'name' => 'Filter Root Only',
            'slug' => 'filter-root-only',
            'status' => 'draft',
        ]);
        Category::query()->create([
            'name' => 'Filter Child Only',
            'slug' => 'filter-child-only',
            'parent_id' => $root->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index', ['level' => 'root']))
            ->assertOk()
            ->assertSee('Filter Root Only')
            ->assertDontSee('Filter Child Only');

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index', ['level' => 'child']))
            ->assertOk()
            ->assertSee('Filter Child Only');
    }

    public function test_index_tree_orders_three_levels_depth_first(): void
    {
        $user = User::factory()->create();

        $root = Category::query()->create([
            'name' => 'Root A',
            'slug' => 'root-a',
            'status' => 'draft',
        ]);
        $child = Category::query()->create([
            'name' => 'Child B',
            'slug' => 'child-b',
            'parent_id' => $root->id,
            'status' => 'draft',
        ]);
        Category::query()->create([
            'name' => 'Grandchild C',
            'slug' => 'grandchild-c',
            'parent_id' => $child->id,
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index', ['level' => 'all', 'sort' => 'name', 'order' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Root A', 'Child B', 'Grandchild C']);
    }

    public function test_index_supports_search_and_status_filter(): void
    {
        $user = User::factory()->create();

        Category::query()->create([
            'name' => 'Azure Cloud',
            'slug' => 'azure-cloud',
            'status' => 'published',
        ]);
        Category::query()->create([
            'name' => 'Legacy Office',
            'slug' => 'legacy-office',
            'status' => 'archived',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index', [
                'search' => 'azure',
                'status' => 'published',
            ]))
            ->assertOk()
            ->assertSee('Azure Cloud')
            ->assertDontSee('Legacy Office');
    }

    public function test_index_supports_sorting_by_id_desc(): void
    {
        $user = User::factory()->create();

        $first = Category::query()->create([
            'name' => 'Alpha',
            'slug' => 'alpha',
            'status' => 'draft',
        ]);
        $second = Category::query()->create([
            'name' => 'Beta',
            'slug' => 'beta',
            'status' => 'draft',
        ]);

        $this->assertTrue($second->id > $first->id);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index', [
                'sort' => 'id',
                'order' => 'desc',
            ]))
            ->assertOk()
            ->assertSeeInOrder([
                (string) $second->id,
                (string) $first->id,
            ]);
    }

    public function test_create_form_can_prefill_parent_for_child_creation(): void
    {
        $user = User::factory()->create();
        $parent = Category::query()->create([
            'name' => 'Parent Root',
            'slug' => 'parent-root',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.create', ['parent_id' => $parent->id]))
            ->assertOk()
            ->assertSee('Unterkategorie erstellen')
            ->assertSee('data-selected="'.$parent->id.'"', false);
    }

    public function test_index_renders_status_labels_for_new_model(): void
    {
        $user = User::factory()->create();

        Category::query()->create([
            'name' => 'Draft Cat',
            'slug' => 'draft-cat',
            'status' => 'draft',
        ]);
        Category::query()->create([
            'name' => 'Published Cat',
            'slug' => 'published-cat',
            'status' => 'published',
        ]);
        Category::query()->create([
            'name' => 'Archived Cat',
            'slug' => 'archived-cat',
            'status' => 'archived',
        ]);

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index'))
            ->assertOk()
            ->assertSee('Entwurf')
            ->assertSee('Veröffentlicht')
            ->assertSee('Archiviert');
    }

    public function test_bulk_update_sets_status_for_selected_categories(): void
    {
        $user = User::factory()->create();
        $first = Category::query()->create([
            'name' => 'Bulk One',
            'slug' => 'bulk-one',
            'status' => 'draft',
        ]);
        $second = Category::query()->create([
            'name' => 'Bulk Two',
            'slug' => 'bulk-two',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->from(route('admin.taxonomy.categories.index', [
                'level' => 'all',
                'search' => 'bulk',
            ]))
            ->post(route('admin.taxonomy.categories.bulk-update'), [
                'level' => 'all',
                'search' => 'bulk',
                'sort' => 'name',
                'order' => 'asc',
                'action' => 'set_status',
                'bulk_status' => 'published',
                'ids' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('admin.taxonomy.categories.index', [
                'level' => 'all',
                'search' => 'bulk',
            ]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('categories', ['id' => $first->id, 'status' => 'published']);
        $this->assertDatabaseHas('categories', ['id' => $second->id, 'status' => 'published']);
    }

    public function test_taxonomy_settings_page_updates_default_new_category_status(): void
    {
        $user = User::factory()->create();

        $this->assertSame('draft', CategoryTaxonomySetting::singleton()->default_new_category_status);

        $this->actingAs($user)
            ->put(route('admin.taxonomy.category-taxonomy-settings.update'), [
                'default_new_category_status' => 'published',
            ])
            ->assertRedirect(route('admin.taxonomy.category-taxonomy-settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame('published', CategoryTaxonomySetting::singleton()->fresh()->default_new_category_status);
    }

    public function test_bulk_update_requires_at_least_one_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.taxonomy.categories.bulk-update'), [
                'action' => 'set_status',
                'bulk_status' => 'published',
                'ids' => [],
            ])
            ->assertSessionHasErrors('ids');
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
