<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCoursePatchFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Course, 1: Category, 2: Category}
     */
    private function makeCourseWithTwoCategories(): array
    {
        $cat1 = Category::query()->create([
            'name' => 'Alpha',
            'slug' => 'alpha',
            'status' => 'draft',
        ]);
        $cat2 = Category::query()->create([
            'name' => 'Beta',
            'slug' => 'beta',
            'status' => 'published',
        ]);
        $course = Course::query()->create([
            'title' => 'Patch Kurs',
            'slug' => 'patch-kurs',
            'short_description' => null,
            'long_description' => null,
            'duration_hours' => null,
            'language_code' => 'de',
            'status' => CourseStatus::Draft,
            'primary_category_id' => $cat1->id,
            'difficulty_level_id' => null,
            'hero_media_asset_id' => null,
            'published_at' => null,
        ]);
        $course->categories()->sync([$cat1->id]);

        return [$course->fresh(), $cat1, $cat2];
    }

    public function test_guest_cannot_patch_course_fields(): void
    {
        [$course] = $this->makeCourseWithTwoCategories();

        $this->patchJson(route('admin.course-catalog.courses.patch-fields', $course), [
            'category_ids' => [$course->primary_category_id],
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_search_categories_for_options(): void
    {
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Suchebar',
            'slug' => 'suchebar',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.taxonomy.categories.options', ['q' => 'Such']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Suchebar');
    }

    public function test_authenticated_user_can_patch_course_categories(): void
    {
        $user = User::factory()->create();
        [$course, $cat1, $cat2] = $this->makeCourseWithTwoCategories();

        $this->actingAs($user)
            ->patchJson(route('admin.course-catalog.courses.patch-fields', $course), [
                'category_ids' => [$cat1->id, $cat2->id],
                'primary_category_id' => $cat2->id,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $course->refresh();
        $this->assertSame($cat2->id, (int) $course->primary_category_id);
        $this->assertCount(2, $course->categories);
    }
}
