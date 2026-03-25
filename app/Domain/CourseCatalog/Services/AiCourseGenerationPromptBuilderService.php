<?php

namespace App\Domain\CourseCatalog\Services;

class AiCourseGenerationPromptBuilderService
{
    public function __construct(
        private readonly PromptPlaceholderInterpolationService $interpolation
    ) {}

    /**
     * Assembles the full text that will later be sent to the AI (no API call here).
     *
     * @param  array<string, string|null>  $placeholderValues
     * @return array{interpolated_body: ?string, compiled_prompt: string}
     */
    public function build(?string $templateBody, array $placeholderValues, string $brief): array
    {
        $brief = trim($brief);

        $interpolated = null;
        if ($templateBody !== null && $templateBody !== '') {
            $interpolated = $this->interpolation->interpolate($templateBody, $placeholderValues);
        }

        $parts = [];
        if ($interpolated !== null && $interpolated !== '') {
            $parts[] = rtrim($interpolated);
        }

        $parts[] = '---';
        $parts[] = 'Kursidee / Anforderungen:';
        $parts[] = $brief;
        $parts[] = '---';
        $parts[] = 'Hinweis: Dieser Text ist die vorbereitete Eingabe für einen späteren KI-Aufruf (noch nicht ausgeführt).';

        $compiled = implode("\n\n", $parts);

        return [
            'interpolated_body' => $interpolated,
            'compiled_prompt' => $compiled,
        ];
    }
}

