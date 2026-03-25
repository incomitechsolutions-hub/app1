<?php

namespace Tests\Feature\Public;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAndCategoryPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_course_is_visible_and_draft_returns_404(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Öffentlich',
            'slug' => 'oeffentlich',
            'status' => 'published',
        ]);

        $this->actingAs($user)->post(route('admin.course-catalog.courses.store'), [
            'title' => 'Mein Kurs',
            'slug' => 'mein-kurs',
            'short_description' => str_repeat('a', 20),
            'long_description' => null,
            'language_code' => 'de',
            'status' => CourseStatus::Published->value,
            'primary_category_id' => $category->id,
            'tag_ids' => [],
            'audience_ids' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
            'seo' => [
                'seo_title' => 'SEO Titel',
                'meta_description' => 'Beschreibung für SEO',
            ],
        ])->assertRedirect();

        $this->get(route('public.courses.show', ['slug' => 'mein-kurs']))
            ->assertOk()
            ->assertSee('Mein Kurs');

        $this->actingAs($user)->post(route('admin.course-catalog.courses.store'), [
            'title' => 'Entwurf',
            'slug' => 'entwurf',
            'short_description' => str_repeat('b', 20),
            'language_code' => 'de',
            'status' => CourseStatus::Draft->value,
            'primary_category_id' => $category->id,
            'tag_ids' => [],
            'audience_ids' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
        ])->assertRedirect();

        $this->get(route('public.courses.show', ['slug' => 'entwurf']))
            ->assertNotFound();
    }

    public function test_seo_meta_row_is_created_for_course(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Cat',
            'slug' => 'cat',
            'status' => 'published',
        ]);

        $this->actingAs($user)->post(route('admin.course-catalog.courses.store'), [
            'title' => 'SEO Kurs',
            'slug' => 'seo-kurs',
            'short_description' => str_repeat('a', 20),
            'language_code' => 'de',
            'status' => CourseStatus::Published->value,
            'primary_category_id' => $category->id,
            'tag_ids' => [],
            'audience_ids' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
            'seo' => [
                'seo_title' => 'Custom SEO',
                'meta_description' => 'Meta hier',
            ],
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'seo-kurs')->firstOrFail();
        $this->assertDatabaseHas('seo_meta', [
            'owner_type' => Course::class,
            'owner_id' => $course->id,
            'seo_title' => 'Custom SEO',
        ]);
    }

    public function test_published_category_is_visible_and_draft_returns_404(): void
    {
        $user = User::factory()->create();

        Category::query()->create([
            'name' => 'Veröffentlicht',
            'slug' => 'veroeffentlicht',
            'status' => 'published',
        ]);

        $this->get(route('public.categories.show', ['slug' => 'veroeffentlicht']))
            ->assertOk()
            ->assertSee('Veröffentlicht');

        $this->actingAs($user)->post(route('admin.taxonomy.categories.store'), [
            'name' => 'Entwurf Kat',
            'slug' => 'entwurf-kat',
            'status' => 'draft',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->get(route('public.categories.show', ['slug' => 'entwurf-kat']))
            ->assertNotFound();
    }

    public function test_sitemap_lists_indexable_urls(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Sitemap Kat',
            'slug' => 'sitemap-kat',
            'status' => 'published',
        ]);

        $this->actingAs($user)->post(route('admin.course-catalog.courses.store'), [
            'title' => 'Sitemap Kurs',
            'slug' => 'sitemap-kurs',
            'short_description' => str_repeat('c', 20),
            'language_code' => 'de',
            'status' => CourseStatus::Published->value,
            'primary_category_id' => $category->id,
            'tag_ids' => [],
            'audience_ids' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
        ])->assertRedirect();

        $this->get(route('public.sitemap'))
            ->assertOk()
            ->assertSee('sitemap-kurs')
            ->assertSee('sitemap-kat');
    }
}
