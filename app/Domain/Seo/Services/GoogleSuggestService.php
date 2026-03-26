<?php

namespace App\Domain\Seo\Services;

use Illuminate\Support\Facades\Http;

class GoogleSuggestService
{
    private const URL = 'https://suggestqueries.google.com/complete/search';

    /**
     * @return array<int, string>
     */
    public function getSuggestions(string $query): array
    {
        $query = KeywordNormalizer::trimDisplay($query);
        if ($query === '') {
            return [];
        }

        $seeds = $this->seedQueries($query);
        $merged = [];

        foreach ($seeds as $seed) {
            if ($seed === '') {
                continue;
            }
            foreach ($this->fetchForQuery($seed) as $s) {
                $merged[] = $s;
            }
        }

        return $this->dedupePreserveOrder($merged);
    }

    /**
     * @return array<int, string>
     */
    private function fetchForQuery(string $q): array
    {
        try {
            $response = Http::timeout(3)
                ->acceptJson()
                ->get(self::URL, [
                    'client' => 'firefox',
                    'q' => $q,
                    'hl' => 'de',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $json = $response->json();
            if (! is_array($json) || ! isset($json[1]) || ! is_array($json[1])) {
                return [];
            }

            $out = [];
            foreach ($json[1] as $item) {
                if (is_string($item)) {
                    $t = KeywordNormalizer::trimDisplay($item);
                    if ($t !== '') {
                        $out[] = $t;
                    }
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, string>
     */
    private function seedQueries(string $query): array
    {
        $seeds = [$query];
        $tokens = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($tokens) >= 2) {
            $seeds[] = implode(' ', array_slice($tokens, 0, 2));
        }
        if (count($tokens) >= 3) {
            $seeds[] = implode(' ', array_slice($tokens, 0, 3));
        }

        return array_values(array_unique(array_filter($seeds)));
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
}
