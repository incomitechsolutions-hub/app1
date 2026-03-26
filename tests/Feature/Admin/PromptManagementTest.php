<?php

namespace Tests\Feature\Admin;

use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_prompts(): void
    {
        $this->get(route('admin.prompt-management.prompts.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_prompt(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.prompt-management.prompts.store'), [
                'title' => 'Kurs Kurztext',
                'slug' => 'kurs-kurztext',
                'use_case' => PromptUseCase::CourseCreation->value,
                'body' => 'Erzeuge eine Kurzbeschreibung für: …',
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ai_prompts', [
            'slug' => 'kurs-kurztext',
            'use_case' => PromptUseCase::CourseCreation->value,
        ]);
    }

    public function test_authenticated_user_can_create_prompt_with_custom_use_case_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.prompt-management.prompts.store'), [
                'title' => 'Landing Copy',
                'slug' => 'landing-copy',
                'use_case' => 'seo-landing-pages',
                'body' => 'Schreibe eine Einleitung für …',
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ai_prompts', [
            'slug' => 'landing-copy',
            'use_case' => 'seo-landing-pages',
        ]);
    }

    public function test_use_case_must_match_slug_pattern(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.prompt-management.prompts.store'), [
                'title' => 'X',
                'slug' => 'x-slug',
                'use_case' => 'Invalid_Case',
                'body' => 'Body',
                'description' => null,
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertSessionHasErrors('use_case');
    }
}
