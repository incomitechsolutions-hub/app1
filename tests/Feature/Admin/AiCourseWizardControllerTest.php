<?php

namespace Tests\Feature\Admin;

use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
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
}

