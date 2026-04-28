<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\Tag;

class TaxonomyMatchingService
{
    /**
     * @param  list<string>  $keywords
     * @return array{category_id:int|null, tag_ids:list<int>, audience_ids:list<int>, tags_to_create:list<string>, audiences_to_create:list<string>}
     */
    public function match(array $keywords, string $topic): array
    {
        $needle = mb_strtolower($topic.' '.implode(' ', $keywords));

        $categoryId = Category::query()
            ->where('status', 'published')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => (int) $c->id, 'score' => $this->score($needle, (string) $c->name)])
            ->sortByDesc('score')
            ->values()
            ->first();

        $matchedTags = Tag::query()
            ->get(['id', 'name'])
            ->filter(fn ($t) => $this->score($needle, (string) $t->name) > 0)
            ->take(8)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $matchedAudiences = Audience::query()
            ->get(['id', 'name'])
            ->filter(fn ($a) => $this->score($needle, (string) $a->name) > 0)
            ->take(5)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return [
            'category_id' => is_array($categoryId) && ($categoryId['score'] ?? 0) > 0 ? (int) $categoryId['id'] : null,
            'tag_ids' => $matchedTags,
            'audience_ids' => $matchedAudiences,
            'tags_to_create' => [],
            'audiences_to_create' => [],
        ];
    }

    private function score(string $haystack, string $label): int
    {
        $name = mb_strtolower(trim($label));
        if ($name === '') {
            return 0;
        }

        if (str_contains($haystack, $name)) {
            return mb_strlen($name);
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $hit = 0;
        foreach ($parts as $part) {
            if ($part !== '' && str_contains($haystack, $part)) {
                $hit++;
            }
        }

        return $hit;
    }
}

