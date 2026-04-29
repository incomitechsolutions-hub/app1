<?php

namespace App\Domain\CourseCatalog\Services;

use App\Services\Ai\AiProviderFactory;

class FieldRegenerationService
{
    public function __construct(private readonly AiProviderFactory $providerFactory) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function regenerate(string $fieldName, array $context): string
    {
        return $this->regenerateWithMeta($fieldName, $context)['value'];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array{value: string, source: 'provider'|'fallback', provider_attempted: bool, fallback_reason: string|null}
     */
    public function regenerateWithMeta(string $fieldName, array $context): array
    {
        $provider = $this->providerFactory->make();
        if ($provider) {
            $payload = [
                'field_name' => $fieldName,
                'field_path' => (string) ($context['field_path'] ?? ''),
                'current_context' => $context,
            ];
            if (is_string($context['prompt_text'] ?? null) && trim((string) $context['prompt_text']) !== '') {
                $payload['prompt_text'] = trim((string) $context['prompt_text']);
            }
            $result = $provider->regenerateField([
                ...$payload,
            ]);
            $value = $result['value'] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return [
                    'value' => $value,
                    'source' => 'provider',
                    'provider_attempted' => true,
                    'fallback_reason' => null,
                ];
            }

            $reason = data_get($result, '_meta.reason');

            return [
                'value' => $this->heuristic($fieldName, $context),
                'source' => 'fallback',
                'provider_attempted' => true,
                'fallback_reason' => is_string($reason) && $reason !== '' ? $reason : 'empty_value',
            ];
        }

        return [
            'value' => $this->heuristic($fieldName, $context),
            'source' => 'fallback',
            'provider_attempted' => false,
            'fallback_reason' => 'provider_unavailable',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function heuristic(string $fieldName, array $context): string
    {
        $topic = trim((string) ($context['topic'] ?? 'Kurs'));
        $keyword = trim((string) ($context['selected_primary_keyword'] ?? $topic));

        return match ($fieldName) {
            'seo_title' => $topic.' Kurs | '.$keyword,
            'meta_description' => "Praxisnahe {$topic} Schulung fuer Unternehmen. Jetzt Informationen anfragen.",
            'focus_keyword' => $keyword,
            'title' => $topic.' Training',
            'subtitle' => 'Praxisorientierte Schulung',
            'short_description' => "{$topic} kompakt und direkt anwendbar.",
            default => $keyword,
        };
    }
}

