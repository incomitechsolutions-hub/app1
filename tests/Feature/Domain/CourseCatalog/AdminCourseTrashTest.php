<?php

namespace Tests\Feature\Domain\CourseCatalog;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseTrashTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourseWithCategory(): Course
    {
        $category = Category::query()->create([
            'name' => 'Testkategorie',
            'slug' => 'testkategorie',
            'description' => null,
            'parent_id' => null,
            'status' => 'published',
        ]);

        $course = Course::query()->create([
            'title' => 'Testkurs',
            'slug' => 'testkurs',
            'short_description' => null,
            'long_description' => null,
            'duration_hours' => null,
            'language_code' => 'de',
            'status' => CourseStatus::Draft,
            'primary_category_id' => $category->id,
            'difficulty_level_id' => null,
            'hero_media_asset_id' => null,
            'published_at' => null,
        ]);

        $course->categories()->sync([$category->id]);

        return $course->fresh();
    }

    public function test_guest_cannot_delete_course(): void
    {
        $course = $this->makeCourseWithCategory();

        $response = $this->from(route('admin.course-catalog.courses.show', $course))
            ->delete(route('admin.course-catalog.courses.destroy', $course));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'deleted_at' => null]);
    }

    public function test_authenticated_user_soft_deletes_course_and_can_restore(): void
    {
        $user = User::factory()->create();
        $course = $this->makeCourseWithCategory();

        $this->actingAs($user)
            ->from(route('admin.course-catalog.courses.show', $course))
            ->delete(route('admin.course-catalog.courses.destroy', $course))
            ->assertRedirect(route('admin.course-catalog.courses.index'));

        $this->assertSoftDeleted('courses', ['id' => $course->id]);

        $this->actingAs($user)
            ->get(route('admin.course-catalog.courses.index'))
            ->assertOk()
            ->assertDontSee('testkurs', false);

        $this->actingAs($user)
            ->get(route('admin.course-catalog.courses.index', ['trashed' => true]))
            ->assertOk()
            ->assertSee('Testkurs', false);

        $trashed = Course::onlyTrashed()->findOrFail($course->id);

        $this->actingAs($user)
            ->post(route('admin.course-catalog.courses.restore', $trashed))
            ->assertRedirect(route('admin.course-catalog.courses.show', $course->id));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    }
}
