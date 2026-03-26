<?php

namespace App\Domain\Seo\Services;

/**
 * Deterministic normalization for keyword comparison and deduplication.
 */
final class KeywordNormalizer
{
    /**
     * Trim, collapse whitespace, lowercase for comparison keys.
     */
    public static function comparisonKey(string $keyword): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($keyword)) ?? '';

        return mb_strtolower($s, 'UTF-8');
    }

    /**
     * Display-safe trim (no forced lowercase for stored values).
     */
    public static function trimDisplay(string $keyword): string
    {
        return trim(preg_replace('/\s+/u', ' ', $keyword) ?? '');
    }

    /**
     * Tokenize course idea for overlap scoring (min length 3, lowercase).
     *
     * @return array<int, string>
     */
    public static function ideaTokens(string $courseIdea): array
    {
        $lower = mb_strtolower(trim($courseIdea), 'UTF-8');
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($parts)) {
            return [];
        }

        $out = [];
        foreach ($parts as $p) {
            if (mb_strlen($p, 'UTF-8') >= 3) {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Word count for a keyword phrase (whitespace-separated).
     */
    public static function wordCount(string $keyword): int
    {
        $t = self::trimDisplay($keyword);
        if ($t === '') {
            return 0;
        }

        return count(preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY) ?: []);
    }
}
