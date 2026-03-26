<?php

namespace App\Domain\PromptManagement\Services;

use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;
use Illuminate\Database\Eloquent\Collection;

class PromptService
{
    /**
     * @return list<string>
     */
    public function builtInUseCaseValues(): array
    {
        return array_map(static fn (PromptUseCase $c) => $c->value, PromptUseCase::cases());
    }

    public function store(array $data): AiPrompt
    {
        return AiPrompt::query()->create($data);
    }

    public function update(AiPrompt $prompt, array $data): AiPrompt
    {
        $prompt->fill($data);
        $prompt->save();

        return $prompt->fresh();
    }

    public function delete(AiPrompt $prompt): void
    {
        $prompt->delete();
    }

    public function deleteCustomUseCase(string $slug): int
    {
        return AiPrompt::query()
            ->where('use_case', $slug)
            ->delete();
    }

    /**
     * @return Collection<int, AiPrompt>
     */
    public function activeForUseCase(PromptUseCase $useCase): Collection
    {
        return AiPrompt::query()
            ->where('use_case', $useCase->value)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }
}
