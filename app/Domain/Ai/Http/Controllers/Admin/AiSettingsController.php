<?php

namespace App\Domain\Ai\Http\Controllers\Admin;

use App\Domain\Ai\Http\Requests\Admin\TestAiConnectionRequest;
use App\Domain\Ai\Http\Requests\Admin\UpdateAiSettingsRequest;
use App\Domain\Ai\Models\AiSetting;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\OpenAiChatService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiSettingsController extends Controller
{
    public function __construct(
        private readonly AiSettingsService $settings,
        private readonly OpenAiChatService $openAi
    ) {}

    public function edit(): View
    {
        $this->authorize('manageAiSettings');

        $settings = AiSetting::singleton();

        return view('admin.ai.settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateAiSettingsRequest $request): RedirectResponse
    {
        $this->authorize('manageAiSettings');

        $this->settings->update(AiSetting::singleton(), $request->validated());

        return redirect()
            ->route('admin.ai.settings.edit')
            ->with('status', __('KI-Einstellungen wurden gespeichert.'));
    }

    public function test(TestAiConnectionRequest $request): RedirectResponse
    {
        $this->authorize('manageAiSettings');

        $data = $request->validated();
        $stored = AiSetting::singleton();

        $apiKey = $data['api_key_override'] ?? null;
        if (! is_string($apiKey) || $apiKey === '') {
            $apiKey = $stored->openai_api_key;
        }

        if (! is_string($apiKey) || $apiKey === '') {
            return back()
                ->withInput()
                ->with('ai_test_error', __('Bitte zuerst einen API-Key speichern oder unten einen Key für den Test eintragen.'));
        }

        $baseUrl = $stored->openai_base_url ?: 'https://api.openai.com/v1';
        $model = $stored->default_model ?: 'gpt-4o-mini';

        $result = $this->openAi->sendChatMessage(
            $apiKey,
            $baseUrl,
            $model,
            $data['test_message']
        );

        if (! $result['ok']) {
            return back()
                ->withInput()
                ->with('ai_test_error', $result['error'] ?? __('Verbindung fehlgeschlagen.'));
        }

        return back()
            ->withInput()
            ->with('ai_test_reply', $result['reply'] ?? '');
    }
}
