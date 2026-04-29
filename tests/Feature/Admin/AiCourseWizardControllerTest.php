<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCourseWizardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_regenerate_ai2_section(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'ChatGPT Grundlagen',
            'subtopics' => ['Prompts', 'Use Cases'],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'chatgpt kurs',
            'selected_keywords' => ['chatgpt kurs', 'prompt engineering'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 55,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-section'), [
            'analysis_id' => $analysis->id,
            'section' => 'details',
            'selected_keywords' => ['chatgpt kurs'],
            'generation_input' => [
                'topic' => 'ChatGPT fuer Einsteiger',
                'target_audience' => 'Einsteiger',
                'duration_days' => 2,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('section', 'details')
            ->assertJsonStructure([
                'section',
                'payload' => [
                    'short_description',
                    'long_description',
                    'target_audience_text',
                ],
            ]);
    }

    public function test_authenticated_user_can_regenerate_ai2_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'seo_title',
            'current_context' => [
                'topic' => 'Prompting',
                'selected_primary_keyword' => 'prompt engineering kurs',
            ],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('field_name', 'seo_title');
        $this->assertNotSame('', (string) $response->json('value'));
    }

    public function test_regenerate_section_rejects_unknown_section(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'KI',
            'subtopics' => [],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'ki kurs',
            'selected_keywords' => ['ki kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-section'), [
            'analysis_id' => $analysis->id,
            'section' => 'invalid',
        ])->assertStatus(422);
    }

    public function test_save_selection_persists_custom_keywords(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'KI',
            'subtopics' => [],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => null,
            'selected_keywords' => [],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.save-selection'), [
            'analysis_id' => $analysis->id,
            'selected_keywords' => ['schulung'],
            'selected_primary_keyword' => 'schulung',
            'custom_keywords' => ['schulung', 'weiterbildung'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('course_keywords', [
            'analysis_id' => $analysis->id,
            'keyword' => 'schulung',
            'type' => 'custom',
            'selected' => true,
        ]);
        $this->assertDatabaseHas('course_keywords', [
            'analysis_id' => $analysis->id,
            'keyword' => 'weiterbildung',
            'type' => 'custom',
            'selected' => false,
        ]);
    }

    public function test_prompt_library_endpoints_list_and_store_prompts(): void
    {
        $user = User::factory()->create();
        AiPrompt::query()->create([
            'title' => 'Bestehender Prompt',
            'slug' => 'bestehender-prompt',
            'use_case' => 'course-wizard-regenerate',
            'body' => 'Regenerate nur mit Praxisbeispielen.',
            'placeholder_definitions' => [],
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $listResponse = $this->actingAs($user)
            ->getJson(route('admin.course-catalog.ai-wizard.prompt-library'));

        $listResponse->assertOk()
            ->assertJsonStructure([
                'prompts' => [
                    ['id', 'title', 'body'],
                ],
            ]);

        $storeResponse = $this->actingAs($user)
            ->postJson(route('admin.course-catalog.ai-wizard.prompt-library.store'), [
                'title' => 'Mein Prompt',
                'body' => 'Bitte nur kurze Antworten.',
            ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('prompt.title', 'Mein Prompt');
        $this->assertDatabaseHas('ai_prompts', [
            'title' => 'Mein Prompt',
            'use_case' => 'course-wizard-regenerate',
            'is_active' => true,
        ]);
    }

    public function test_regenerate_field_can_store_inline_prompt_in_library(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'seo_title',
            'current_context' => [
                'topic' => 'Prompting',
                'selected_primary_keyword' => 'prompt engineering kurs',
            ],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
            'prompt_text' => 'Nutze einen sachlichen Ton mit Fokus auf B2B.',
            'save_prompt' => true,
            'prompt_title' => 'B2B Regenerate',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('ai_prompts', [
            'title' => 'B2B Regenerate',
            'use_case' => 'course-wizard-regenerate',
        ]);
    }

    public function test_regenerate_field_accepts_prompt_id_from_library(): void
    {
        $user = User::factory()->create();
        $prompt = AiPrompt::query()->create([
            'title' => 'Prompt from Library',
            'slug' => 'prompt-from-library',
            'use_case' => 'course-wizard-regenerate',
            'body' => 'Nutze einen klaren, praxisnahen Stil.',
            'placeholder_definitions' => [],
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'subtitle',
            'current_context' => [
                'topic' => 'Prompting',
                'selected_primary_keyword' => 'prompt engineering kurs',
            ],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
            'prompt_id' => $prompt->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('field_name', 'subtitle');
        $this->assertNotSame('', (string) $response->json('value'));
    }
}

