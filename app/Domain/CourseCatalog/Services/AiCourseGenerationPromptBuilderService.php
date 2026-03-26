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
     * @param  array<string, mixed>  $context
     * @return array{interpolated_body: ?string, compiled_prompt: string}
     */
    public function build(?string $templateBody, array $placeholderValues, string $brief, array $context = []): array
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

        if (! empty($context['keyword_data']) && is_array($context['keyword_data'])) {
            $kd = $context['keyword_data'];
            $primary = trim((string) ($kd['primary_keyword'] ?? ''));
            $variants = is_array($kd['keyword_variants'] ?? null) ? $kd['keyword_variants'] : [];
            $supporting = is_array($kd['supporting_keywords'] ?? null) ? $kd['supporting_keywords'] : [];
            $variantLine = implode(', ', array_map(static fn ($v) => (string) $v, $variants));
            $supportLine = implode(', ', array_map(static fn ($v) => (string) $v, $supporting));
            $lines = [];
            if ($primary !== '') {
                $lines[] = 'Primary: '.$primary;
            }
            if ($variantLine !== '') {
                $lines[] = 'Varianten: '.$variantLine;
            }
            if ($supportLine !== '') {
                $lines[] = 'Supporting: '.$supportLine;
            }
            if ($lines !== []) {
                $parts[] = 'SEO-Keywords (für Inhalte und Terminologie nutzen):';
                $parts[] = implode("\n", $lines);
                $parts[] = 'Baue diese Begriffe natürlich in Titel, Teaser, Texte und Module ein, ohne Keyword-Stuffing.';
            }
        }

        if (! empty($context['crawl']) && is_array($context['crawl'])) {
            $crawl = $context['crawl'];
            $parts[] = 'Quelle (Webseite gecrawlt):';
            $parts[] = implode("\n", array_filter([
                'URL: '.(string) ($crawl['source_url'] ?? ''),
                'Titel: '.(string) ($crawl['title'] ?? ''),
                'H1: '.(string) ($crawl['h1'] ?? ''),
                'Meta Description: '.(string) ($crawl['meta_description'] ?? ''),
                'SEO-Keywords: '.implode(', ', is_array($crawl['seo_keywords'] ?? null) ? $crawl['seo_keywords'] : []),
                'Headings (H2/H3): '.implode(' | ', is_array($crawl['headings'] ?? null) ? $crawl['headings'] : []),
            ]));
            $parts[] = 'Nimm die SEO-relevanten Infos aus der Quelle auf und orientiere Inhalte/Module daran.';
        }

        if (! empty($context['locked_title']) && is_string($context['locked_title'])) {
            $parts[] = 'WICHTIG: Der Kurstitel ist fix und darf nicht geändert werden: '.$context['locked_title'];
        }
        if (! empty($context['locked_subtitle']) && is_string($context['locked_subtitle'])) {
            $parts[] = 'WICHTIG: Der Kurs-Untertitel ist fix und darf nicht geändert werden: '.$context['locked_subtitle'];
        }

        $parts[] = 'Hinweis: Dieser Text ist die vorbereitete Eingabe für einen späteren KI-Aufruf (noch nicht ausgeführt).';

        $compiled = implode("\n\n", $parts);

        return [
            'interpolated_body' => $interpolated,
            'compiled_prompt' => $compiled,
        ];
    }
}

