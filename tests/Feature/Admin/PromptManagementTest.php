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
}
