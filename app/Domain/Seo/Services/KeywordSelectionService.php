<?php

namespace App\Domain\Seo\Services;

class KeywordSelectionService
{
    /**
     * @param  array<int, array{keyword: string, score: int, reasons: array<int, string>}>  $rankedKeywords
     * @return array{
     *   primary_keyword: string,
     *   keyword_variants: array<int, string>,
     *   supporting_keywords: array<int, string>,
     *   all_keywords: array<int, string>
     * }
     */
    public function select(string $courseIdea, array $rankedKeywords): array
    {
        $courseIdea = KeywordNormalizer::trimDisplay($courseIdea);

        $ordered = [];
        foreach ($rankedKeywords as $row) {
            if (! is_array($row) || empty($row['keyword'])) {
                continue;
            }
            $k = KeywordNormalizer::trimDisplay((string) $row['keyword']);
            if ($k !== '') {
                $ordered[] = $k;
            }
        }

        $ordered = $this->dedupePreserveOrder($ordered);

        if ($ordered === []) {
            return $this->fallbackSelection($courseIdea);
        }

        $primary = $ordered[0];
        $variants = array_slice($ordered, 1, 3);
        $supporting = array_slice($ordered, 4);

        $all = $this->dedupePreserveOrder(array_merge([$primary], $variants, $supporting));

        return [
            'primary_keyword' => $primary,
            'keyword_variants' => $variants,
            'supporting_keywords' => $supporting,
            'all_keywords' => $all,
        ];
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<int, string>
     */
    private function dedupePreserveOrder(array $keywords): array
    {
        $seen = [];
        $out = [];
        foreach ($keywords as $k) {
            $key = KeywordNormalizer::comparisonKey($k);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = KeywordNormalizer::trimDisplay($k);
        }

        return $out;
    }

    /**
     * @return array{
     *   primary_keyword: string,
     *   keyword_variants: array<int, string>,
     *   supporting_keywords: array<int, string>,
     *   all_keywords: array<int, string>
     * }
     */
    private function fallbackSelection(string $courseIdea): array
    {
        $base = $courseIdea === '' ? 'Kurs' : $courseIdea;
        $primary = $base.' schulung';
        $variants = [
            $base.' training',
            $base.' kurs',
            $base.' für unternehmen',
        ];
        $supporting = [];
        $all = $this->dedupePreserveOrder(array_merge([$primary], $variants));

        return [
            'primary_keyword' => $primary,
            'keyword_variants' => $variants,
            'supporting_keywords' => $supporting,
            'all_keywords' => $all,
        ];
    }
}
