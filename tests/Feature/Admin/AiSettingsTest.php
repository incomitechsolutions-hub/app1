<?php

namespace Tests\Feature\Admin;

use App\Domain\Ai\Models\AiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ai_settings(): void
    {
        $this->get(route('admin.ai.settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_ai_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.ai.settings.edit'))
            ->assertOk()
            ->assertSee('KI-Einstellungen');
    }

    public function test_authenticated_user_can_save_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.ai.settings.update'), [
                'openai_api_key' => 'sk-test-key-123456789012345678901234',
                'default_model' => 'gpt-4o-mini',
                'openai_base_url' => 'https://api.openai.com/v1',
            ])
            ->assertRedirect(route('admin.ai.settings.edit'));

        $settings = AiSetting::singleton();
        $this->assertSame('gpt-4o-mini', $settings->default_model);
        $this->assertTrue($settings->hasOpenAiApiKey());
    }

    public function test_connection_test_uses_http_and_shows_reply(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Hallo Test']],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.ai.settings.update'), [
                'openai_api_key' => 'sk-test-key-123456789012345678901234',
                'default_model' => 'gpt-4o-mini',
                'openai_base_url' => 'https://api.openai.com/v1',
            ]);

        $this->actingAs($user)
            ->post(route('admin.ai.settings.test'), [
                'test_message' => 'Ping',
            ])
            ->assertRedirect(route('admin.ai.settings.edit'))
            ->assertSessionHas('ai_test_reply', 'Hallo Test');
    }
}
