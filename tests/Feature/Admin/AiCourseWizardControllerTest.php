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
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompting',
            'subtopics' => ['Grundlagen'],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'seo_title',
            'field_path' => 'seo.seo_title',
            'analysis_id' => $analysis->id,
            'generation_input' => [
                'topic' => 'Prompting',
                'target_audience' => 'Einsteiger',
                'level' => 'basic',
                'duration_days' => 2,
            ],
            'current_context' => [
                'topic' => 'Prompting',
                'selected_primary_keyword' => 'prompt engineering kurs',
            ],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
        ]);

        $response->assertOk()
            ->assertJsonPath('field_name', 'seo_title')
            ->assertJsonStructure(['field_name', 'value', 'source', 'provider_attempted', 'fallback_reason']);
        $this->assertNotSame('', (string) $response->json('value'));
    }

    public function test_authenticated_user_can_generate_concept_after_keyword_selection(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompt Engineering',
            'subtopics' => ['Grundlagen'],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs', 'prompt workshop'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 20,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.generate-concept'), [
            'analysis_id' => $analysis->id,
            'selected_keywords' => ['prompt engineering kurs', 'prompt workshop'],
            'generation_input' => [
                'topic' => 'Prompt Engineering',
                'target_audience' => 'Produktteams',
            ],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'seo_strategy' => ['primary_keyword', 'search_intent', 'target_density'],
                'concept' => ['positioning', 'learning_promise', 'modules', 'learning_objectives'],
            ]);
    }

    public function test_authenticated_user_can_generate_fields_from_approved_concept(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompt Engineering',
            'subtopics' => ['Grundlagen'],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 20,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.generate-fields'), [
            'analysis_id' => $analysis->id,
            'selected_keywords' => ['prompt engineering kurs'],
            'generation_input' => [
                'topic' => 'Prompt Engineering',
                'target_audience' => 'Produktteams',
            ],
            'seo_strategy' => [
                'primary_keyword' => 'prompt engineering kurs',
            ],
            'approved_concept' => [
                'positioning' => 'Praxisnahe Prompt-Schulung fuer Teams.',
                'learning_promise' => 'Teilnehmende erstellen reproduzierbare Prompts.',
                'target_audience_summary' => 'Produktteams und Fachbereiche.',
                'modules' => [
                    ['title' => 'Grundlagen', 'description' => 'Prompt-Bausteine', 'duration_hours' => 2, 'sort_order' => 0],
                ],
                'learning_objectives' => [
                    ['objective_text' => 'Prompt-Struktur anwenden', 'sort_order' => 0],
                ],
                'prerequisites' => [
                    ['prerequisite_text' => 'Interesse an KI', 'sort_order' => 0],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'generated' => [
                    'seo' => ['focus_keyword', 'seo_title'],
                    'base' => ['title', 'subtitle'],
                    'details' => ['short_description', 'modules', 'objectives'],
                ],
            ])
            ->assertJsonPath('generated.seo.focus_keyword', 'prompt engineering kurs')
            ->assertJsonPath('generated.base.subtitle', 'Praxisnahe Prompt-Schulung fuer Teams.')
            ->assertJsonPath('generated.details.short_description', 'Teilnehmende erstellen reproduzierbare Prompts.');
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
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompting',
            'subtopics' => [],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'seo_title',
            'field_path' => 'seo.seo_title',
            'analysis_id' => $analysis->id,
            'generation_input' => ['topic' => 'Prompting'],
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
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompting',
            'subtopics' => [],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);
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
            'field_path' => 'base.subtitle',
            'analysis_id' => $analysis->id,
            'generation_input' => ['topic' => 'Prompting'],
            'current_context' => [
                'topic' => 'Prompting',
                'selected_primary_keyword' => 'prompt engineering kurs',
            ],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
            'prompt_id' => $prompt->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('field_name', 'subtitle')
            ->assertJsonStructure(['field_name', 'value', 'source', 'provider_attempted', 'fallback_reason']);
        $this->assertNotSame('', (string) $response->json('value'));
    }

    public function test_regenerate_field_requires_analysis_context(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'title',
            'field_path' => 'base.title',
        ])->assertStatus(422);
    }

    public function test_regenerate_field_accepts_prompt_title_with_160_characters(): void
    {
        $user = User::factory()->create();
        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => 'Prompting',
            'subtopics' => [],
            'raw_google_response' => [],
            'raw_ai_response' => [],
            'selected_primary_keyword' => 'prompt engineering kurs',
            'selected_keywords' => ['prompt engineering kurs'],
            'selected_clusters' => [],
            'seo_opportunity_score' => 10,
            'created_by' => $user->id,
        ]);

        $title160 = str_repeat('a', 160);

        $response = $this->actingAs($user)->postJson(route('admin.course-catalog.ai-wizard.regenerate-field'), [
            'field_name' => 'title',
            'field_path' => 'base.title',
            'analysis_id' => $analysis->id,
            'generation_input' => ['topic' => 'Prompting'],
            'selected_keywords' => ['prompt engineering kurs'],
            'course_context' => [],
            'prompt_text' => 'Gib eine klare, kurze Formulierung.',
            'save_prompt' => true,
            'prompt_title' => $title160,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('ai_prompts', [
            'title' => $title160,
            'use_case' => 'course-wizard-regenerate',
        ]);
    }
}

