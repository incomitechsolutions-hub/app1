<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\PromptManagement\Models\AiPrompt;

class PromptPlaceholderInterpolationService
{
    /**
     * Placeholder syntax: {{name}} with name = [a-zA-Z0-9_]+
     *
     * @return list<string>
     */
    public function extractKeysFromBody(string $body): array
    {
        if (preg_match_all('/\{\{([a-zA-Z0-9_]+)\}\}/', $body, $matches)) {
            return array_values(array_unique($matches[1]));
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>|mixed>|null  $definitions
     * @return list<string>
     */
    public function extractKeysFromDefinitions(?array $definitions): array
    {
        $keys = [];
        foreach ($definitions ?? [] as $row) {
            if (is_array($row) && isset($row['name']) && is_string($row['name']) && $row['name'] !== '') {
                $keys[] = $row['name'];
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Keys required for this template (body + optional JSON definitions).
     *
     * @return list<string>
     */
    public function resolvePlaceholderKeys(AiPrompt $prompt): array
    {
        $fromBody = $this->extractKeysFromBody($prompt->body);
        $fromDef = $this->extractKeysFromDefinitions($prompt->placeholder_definitions);

        return array_values(array_unique(array_merge($fromBody, $fromDef)));
    }

    /**
     * Replace {{key}} with values. Unknown keys stay unchanged. Empty string allowed.
     *
     * @param  array<string, string|null>  $values
     */
    public function interpolate(string $body, array $values): string
    {
        return (string) preg_replace_callback(
            '/\{\{([a-zA-Z0-9_]+)\}\}/',
            function (array $m) use ($values): string {
                $key = $m[1];
                if (! array_key_exists($key, $values)) {
                    return $m[0];
                }

                return (string) ($values[$key] ?? '');
            },
            $body
        );
    }
}

