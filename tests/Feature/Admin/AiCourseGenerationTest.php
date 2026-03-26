<?php

namespace Tests\Feature\Admin;

use App\Domain\Ai\Models\AiSetting;
use App\Domain\CourseCatalog\Enums\AiCourseGenerationSessionStatus;
use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCourseGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_ai_generation_start(): void
    {
        $this->get(route('admin.course-catalog.courses.ai-generation.create'))
            ->assertRedirect(route('login'));
    }

    public function test_legacy_ai_generator_url_redirects_to_start_when_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.course-catalog.courses.ai-generator'))
            ->assertRedirect(route('admin.course-catalog.courses.ai-generation.create'));
    }

    public function test_authenticated_user_can_start_generation_and_land_on_wizard(): void
    {
        $category = Category::query()->create([
            'name' => 'AI Test',
            'slug' => 'ai-test',
            'description' => null,
            'parent_id' => null,
            'status' => 'published',
        ]);

        $audience = Audience::query()->create([
            'name' => 'Tester',
            'slug' => 'tester',
            'description' => null,
        ]);

        $aiJson = json_encode([
            'title' => 'Test Kurs AI',
            'slug' => 'test-kurs-ai',
            'short_description' => str_repeat('a', 24),
            'long_description' => 'Langtext',
            'language_code' => 'de',
            'currency_code' => 'EUR',
            'tag_slugs' => [],
            'audience_slugs' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
            'faqs' => [],
            'seo_title' => 'SEO Titel',
            'meta_description' => 'Meta hier',
        ], JSON_THROW_ON_ERROR);

        $taxJson = json_encode([
            'primary_category_slug' => 'ai-test',
            'audience_slugs' => ['tester'],
            'rationale' => 'Passt zum Testkurs.',
        ], JSON_THROW_ON_ERROR);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        ['message' => ['content' => $aiJson]],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        ['message' => ['content' => $taxJson]],
                    ],
                ], 200),
        ]);

        $settings = AiSetting::singleton();
        $settings->openai_api_key = 'sk-test-123456789012345678901234567890';
        $settings->default_model = 'gpt-4o-mini';
        $settings->openai_base_url = 'https://api.openai.com/v1';
        $settings->save();

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('admin.course-catalog.courses.ai-generation.store'), [
                'brief' => 'Ein zweitägiger Grundlagenkurs zu Prompting.',
            ]);

        $session = AiCourseGenerationSession::query()->first();
        $this->assertNotNull($session);
        $response->assertRedirect(route('admin.course-catalog.courses.ai-generation.wizard', $session));

        $this->assertDatabaseHas('ai_course_generation_sessions', [
            'id' => $session->id,
            'user_id' => $user->id,
            'ai_prompt_id' => null,
            'status' => AiCourseGenerationSessionStatus::InReview->value,
        ]);

        $this->assertNotNull($session->fresh()->draft_payload);
        $this->assertSame('Test Kurs AI', $session->fresh()->draft_payload['title'] ?? null);

        $this->assertSame($category->id, $session->fresh()->draft_payload['primary_category_id'] ?? null);
        $this->assertSame([$audience->id], $session->fresh()->draft_payload['audience_ids'] ?? null);
        $this->assertSame('Passt zum Testkurs.', $session->fresh()->draft_payload['ai_taxonomy_rationale'] ?? null);

        $this->assertDatabaseHas('ai_course_generation_events', [
            'ai_course_generation_session_id' => $session->id,
            'type' => 'ai_request_succeeded',
        ]);
        $this->assertDatabaseHas('ai_course_generation_events', [
            'ai_course_generation_session_id' => $session->id,
            'type' => 'taxonomy_suggestion_succeeded',
        ]);
    }

    public function test_user_cannot_view_another_users_session(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $session = AiCourseGenerationSession::query()->create([
            'user_id' => $owner->id,
            'ai_prompt_id' => null,
            'status' => AiCourseGenerationSessionStatus::InReview,
            'template_snapshot' => null,
            'placeholder_input' => [],
            'brief' => 'Test',
            'interpolated_body' => null,
            'compiled_prompt' => 'compiled',
            'full_prompt_audit' => null,
            'draft_payload' => ['title' => 'T', 'slug' => 't', 'short_description' => str_repeat('x', 24), 'language_code' => 'de', 'currency_code' => 'EUR', 'status' => 'draft'],
            'confirmed_steps' => null,
            'last_regenerated_section' => null,
            'resulting_course_id' => null,
            'last_error' => null,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($other)
            ->get(route('admin.course-catalog.courses.ai-generation.show', $session))
            ->assertForbidden();
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
