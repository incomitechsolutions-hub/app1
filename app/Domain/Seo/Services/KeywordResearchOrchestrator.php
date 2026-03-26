<?php

namespace App\Domain\Seo\Services;

use App\Domain\CourseCatalog\Services\CourseKeywordPayloadBuilder;

class KeywordResearchOrchestrator
{
    public function __construct(
        private readonly GoogleSuggestService $googleSuggest,
        private readonly KeywordScoringService $scoring,
        private readonly KeywordSelectionService $selection,
        private readonly CourseKeywordPayloadBuilder $payloadBuilder,
    ) {}

    /**
     * @return array{
     *   courseIdea: string,
     *   primary_keyword: string,
     *   keyword_variants: array<int, string>,
     *   supporting_keywords: array<int, string>,
     *   all_keywords: array<int, string>,
     *   ranked: array<int, array{keyword: string, score: int, reasons: array<int, string>}>,
     *   payload: array{course_idea: string, keyword_data: array<string, mixed>}
     * }
     */
    public function execute(string $courseIdea): array
    {
        $courseIdea = KeywordNormalizer::trimDisplay($courseIdea);

        $suggestions = $this->googleSuggest->getSuggestions($courseIdea);
        $candidates = $this->mergeCandidates($courseIdea, $suggestions);
        $ranked = $this->scoring->rank($candidates, $courseIdea);
        $selected = $this->selection->select($courseIdea, $ranked);

        $payload = $this->payloadBuilder->build($courseIdea, [
            'primary_keyword' => $selected['primary_keyword'],
            'keyword_variants' => $selected['keyword_variants'],
            'supporting_keywords' => $selected['supporting_keywords'],
        ]);

        return [
            'courseIdea' => $courseIdea,
            'primary_keyword' => $selected['primary_keyword'],
            'keyword_variants' => $selected['keyword_variants'],
            'supporting_keywords' => $selected['supporting_keywords'],
            'all_keywords' => $selected['all_keywords'],
            'ranked' => $ranked,
            'payload' => $payload,
        ];
    }

    /**
     * @param  array<int, string>  $suggestions
     * @return array<int, string>
     */
    private function mergeCandidates(string $courseIdea, array $suggestions): array
    {
        $merged = [];
        if ($courseIdea !== '') {
            $merged[] = $courseIdea;
        }
        foreach ($suggestions as $s) {
            $merged[] = $s;
        }

        $seen = [];
        $out = [];
        foreach ($merged as $k) {
            $key = KeywordNormalizer::comparisonKey($k);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = KeywordNormalizer::trimDisplay($k);
        }

        return $out;
    }
}
