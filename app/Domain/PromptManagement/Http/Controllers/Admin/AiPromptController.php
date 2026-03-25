<?php

namespace App\Domain\PromptManagement\Http\Controllers\Admin;

use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Http\Requests\Admin\StoreAiPromptRequest;
use App\Domain\PromptManagement\Http\Requests\Admin\UpdateAiPromptRequest;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Domain\PromptManagement\Services\PromptService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    public function __construct(
        private readonly PromptService $prompts
    ) {
        $this->authorizeResource(AiPrompt::class, 'ai_prompt', [
            'except' => ['show'],
        ]);
    }

    public function index(Request $request): View
    {
        $query = AiPrompt::query()->orderBy('use_case')->orderBy('sort_order')->orderBy('title');

        if ($request->filled('use_case')) {
            $query->where('use_case', (string) $request->query('use_case'));
        }

        $prompts = $query->paginate(30)->withQueryString();

        return view('admin.prompts.index', [
            'prompts' => $prompts,
            'useCases' => PromptUseCase::cases(),
            'filterUseCase' => $request->query('use_case'),
        ]);
    }

    public function create(): View
    {
        return view('admin.prompts.create', [
            'useCases' => PromptUseCase::cases(),
        ]);
    }

    public function store(StoreAiPromptRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        $prompt = $this->prompts->store($data);

        return redirect()
            ->route('admin.prompt-management.prompts.edit', $prompt)
            ->with('status', __('Prompt angelegt.'));
    }

    public function edit(AiPrompt $ai_prompt): View
    {
        return view('admin.prompts.edit', [
            'prompt' => $ai_prompt,
            'useCases' => PromptUseCase::cases(),
        ]);
    }

    public function update(UpdateAiPromptRequest $request, AiPrompt $ai_prompt): RedirectResponse
    {
        $this->prompts->update($ai_prompt, $request->validated());

        return redirect()
            ->route('admin.prompt-management.prompts.edit', $ai_prompt)
            ->with('status', __('Prompt gespeichert.'));
    }

    public function destroy(AiPrompt $ai_prompt): RedirectResponse
    {
        $this->prompts->delete($ai_prompt);

        return redirect()
            ->route('admin.prompt-management.prompts.index')
            ->with('status', __('Prompt gelöscht.'));
    }
}
