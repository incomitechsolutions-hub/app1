<?php

namespace Tests\Feature\Admin;

use App\Domain\Ai\Models\AiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCourseGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_ai_generator(): void
    {
        $this->get(route('admin.course-catalog.courses.ai-generator'))
            ->assertRedirect(route('login'));
    }

    public function test_generate_and_store_creates_draft_course(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode([
                        'title' => 'Test Kurs AI',
                        'slug' => 'test-kurs-ai',
                        'short_description' => str_repeat('a', 24),
                        'long_description' => 'Langtext',
                        'duration_days' => 2,
                        'price' => 99.5,
                        'language_code' => 'de',
                        'seo_title' => 'SEO Titel',
                        'meta_description' => 'Meta hier',
                    ], JSON_THROW_ON_ERROR)]],
                ],
            ], 200),
        ]);

        $settings = AiSetting::singleton();
        $settings->openai_api_key = 'sk-test-123456789012345678901234567890';
        $settings->default_model = 'gpt-4o-mini';
        $settings->openai_base_url = 'https://api.openai.com/v1';
        $settings->save();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.course-catalog.courses.ai-generator.generate'), [
                'brief' => 'Ein zweitägiger Grundlagenkurs.',
            ])
            ->assertRedirect(route('admin.course-catalog.courses.ai-generator.review'));

        $this->actingAs($user)
            ->post(route('admin.course-catalog.courses.ai-generator.store'), [
                'title' => 'Test Kurs AI',
                'slug' => 'test-kurs-ai',
                'short_description' => str_repeat('a', 24),
                'long_description' => 'Langtext',
                'duration_days' => 2,
                'price' => 99.5,
                'language_code' => 'de',
                'seo' => [
                    'seo_title' => 'SEO Titel',
                    'meta_description' => 'Meta hier',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'slug' => 'test-kurs-ai',
            'title' => 'Test Kurs AI',
        ]);
    }

    public function test_crawl_submit_redirects_with_flash(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.course-catalog.courses.create'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('admin.course-catalog.courses.crawl-from-website'), [
                'source_url' => 'https://example.com/kurs',
            ])
            ->assertRedirect(route('admin.course-catalog.courses.create'))
            ->assertSessionHas('crawl_info');
    }
}
