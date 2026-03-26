<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\Seo\Services\KeywordNormalizer;

class CourseKeywordPayloadBuilder
{
    /**
     * Shape passed to OpenAI / prompt compilation.
     *
     * @param  array{
     *   primary_keyword?: string,
     *   keyword_variants?: array<int, string>|mixed,
     *   supporting_keywords?: array<int, string>|mixed
     * }  $keywordData
     * @return array{
     *   course_idea: string,
     *   keyword_data: array{
     *     primary_keyword: string,
     *     keyword_variants: array<int, string>,
     *     supporting_keywords: array<int, string>
     *   }
     * }
     */
    public function build(string $courseIdea, array $keywordData): array
    {
        $courseIdea = KeywordNormalizer::trimDisplay($courseIdea);

        $primary = KeywordNormalizer::trimDisplay((string) ($keywordData['primary_keyword'] ?? ''));
        $variants = $this->stringList($keywordData['keyword_variants'] ?? []);
        $supporting = $this->stringList($keywordData['supporting_keywords'] ?? []);

        return [
            'course_idea' => $courseIdea,
            'keyword_data' => [
                'primary_keyword' => $primary,
                'keyword_variants' => $variants,
                'supporting_keywords' => $supporting,
            ],
        ];
    }

    /**
     * @param  mixed  $value
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (! is_string($item)) {
                continue;
            }
            $t = KeywordNormalizer::trimDisplay($item);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return array_values(array_unique($out, SORT_STRING));
    }
}
