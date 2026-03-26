<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KeywordResearchEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_keyword_research(): void
    {
        $this->postJson(route('admin.course-catalog.courses.ai-generation.keyword-research'), [
            'courseIdea' => 'Python',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_receives_ranked_keywords(): void
    {
        Http::fake([
            'suggestqueries.google.com/*' => Http::response([
                'python',
                [
                    'python schulung für anfänger',
                    'python kurs unternehmen',
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('admin.course-catalog.courses.ai-generation.keyword-research'), [
                'courseIdea' => 'Python Grundlagen',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'courseIdea',
                'primary_keyword',
                'keyword_variants',
                'supporting_keywords',
                'all_keywords',
                'ranked',
                'payload' => [
                    'course_idea',
                    'keyword_data' => [
                        'primary_keyword',
                        'keyword_variants',
                        'supporting_keywords',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('ranked'));
    }

    public function test_validation_error_for_missing_course_idea(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('admin.course-catalog.courses.ai-generation.keyword-research'), [])
            ->assertStatus(422);
    }
}
