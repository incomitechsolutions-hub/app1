<?php

namespace App\Domain\PromptManagement\Http\Controllers\Admin;

use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Http\Requests\Admin\StoreAiPromptRequest;
use App\Domain\PromptManagement\Http\Requests\Admin\UpdateAiPromptRequest;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Domain\PromptManagement\Services\PromptService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
            'useCaseSelectOptions' => $this->useCaseSelectOptions(),
            'filterUseCase' => $request->query('use_case'),
        ]);
    }

    public function create(): View
    {
        return view('admin.prompts.create', [
            'useCaseSelectOptions' => $this->useCaseSelectOptions(),
            'useCaseDeleteUrlTemplate' => route('admin.prompt-management.use-cases.destroy', ['slug' => '__slug__']),
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
            'useCaseSelectOptions' => $this->useCaseSelectOptions(),
            'useCaseDeleteUrlTemplate' => route('admin.prompt-management.use-cases.destroy', ['slug' => '__slug__']),
        ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function useCaseSelectOptions(): array
    {
        $options = [];
        $enumValues = $this->prompts->builtInUseCaseValues();

        foreach (PromptUseCase::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->label(),
                'is_custom' => false,
            ];
        }

        $customSlugs = AiPrompt::query()
            ->whereNotIn('use_case', $enumValues)
            ->distinct()
            ->orderBy('use_case')
            ->pluck('use_case');

        foreach ($customSlugs as $slug) {
            $slug = (string) $slug;
            if ($slug === '') {
                continue;
            }
            $options[] = [
                'value' => $slug,
                'label' => $slug.' · '.__('eigen'),
                'is_custom' => true,
            ];
        }

        return $options;
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

    public function destroyUseCase(string $slug): JsonResponse
    {
        $this->authorize('delete', new AiPrompt());

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return response()->json([
                'ok' => false,
                'message' => __('Ungültiger Anwendungsfall-Slug.'),
            ], 422);
        }

        if (in_array($slug, $this->prompts->builtInUseCaseValues(), true)) {
            return response()->json([
                'ok' => false,
                'message' => __('Standard-Anwendungsfälle können nicht gelöscht werden.'),
            ], 422);
        }

        $deleted = $this->prompts->deleteCustomUseCase($slug);
        if ($deleted < 1) {
            return response()->json([
                'ok' => false,
                'message' => __('Der Anwendungsfall wurde nicht gefunden.'),
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'message' => __('Anwendungsfall gelöscht (:count Prompt(s)).', ['count' => $deleted]),
            'deleted_count' => $deleted,
            'slug' => $slug,
        ]);
    }
}
