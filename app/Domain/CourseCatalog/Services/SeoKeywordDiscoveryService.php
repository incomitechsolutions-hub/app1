<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\Tag;
use Illuminate\Support\Facades\Http;

class SeoKeywordDiscoveryService
{
    /**
     * @param  list<string>  $subtopics
     * @return array{google_keywords:list<string>, google_raw:array<string,mixed>, db_keywords:list<string>, db_matches:array<string,mixed>}
     */
    public function discover(string $topic, array $subtopics = []): array
    {
        $googleRaw = [];
        $googleKeywords = [];

        foreach ($this->queries($topic, $subtopics) as $query) {
            $response = $this->googleSuggest($query);
            $googleRaw[$query] = $response;
            $googleKeywords = array_merge($googleKeywords, $response['suggestions']);
        }

        $db = $this->discoverFromDb($topic, $subtopics);

        return [
            'google_keywords' => $this->dedupe($googleKeywords),
            'google_raw' => $googleRaw,
            'db_keywords' => $db['keywords'],
            'db_matches' => $db['matches'],
        ];
    }

    /**
     * @param  list<string>  $subtopics
     * @return list<string>
     */
    private function queries(string $topic, array $subtopics): array
    {
        $base = [
            $topic,
            "{$topic} kurs",
            "{$topic} schulung",
            "{$topic} seminar",
            "{$topic} training",
            "{$topic} workshop",
            "{$topic} online",
            "{$topic} inhouse",
            "{$topic} fuer unternehmen",
            "{$topic} fuer anfaenger",
        ];

        foreach ($subtopics as $subtopic) {
            $s = trim($subtopic);
            if ($s === '') {
                continue;
            }
            $base[] = "{$s} kurs";
            $base[] = "{$s} schulung";
            $base[] = "{$topic} {$s}";
        }

        return $this->dedupe($base);
    }

    /**
     * @return array{query:string,suggestions:list<string>}
     */
    private function googleSuggest(string $query): array
    {
        try {
            $response = Http::timeout(3)->get('https://suggestqueries.google.com/complete/search', [
                'client' => 'firefox',
                'hl' => 'de',
                'gl' => 'DE',
                'q' => $query,
            ]);
        } catch (\Throwable) {
            return ['query' => $query, 'suggestions' => []];
        }

        if (! $response->successful()) {
            return ['query' => $query, 'suggestions' => []];
        }

        $json = $response->json();
        $suggestions = is_array($json[1] ?? null) ? $json[1] : [];

        return [
            'query' => $query,
            'suggestions' => $this->dedupe(array_map(fn ($v) => trim((string) $v), $suggestions)),
        ];
    }

    /**
     * @param  list<string>  $subtopics
     * @return array{keywords:list<string>,matches:array<string,mixed>}
     */
    private function discoverFromDb(string $topic, array $subtopics): array
    {
        $terms = array_values(array_filter(array_map('trim', array_merge([$topic], $subtopics))));
        $keywords = [];
        $matches = [
            'courses' => [],
            'tags' => [],
            'categories' => [],
            'audiences' => [],
        ];

        foreach ($terms as $term) {
            Course::query()
                ->where(function ($q) use ($term): void {
                    $q->where('title', 'like', '%'.$term.'%')
                        ->orWhere('slug', 'like', '%'.$term.'%')
                        ->orWhere('short_description', 'like', '%'.$term.'%')
                        ->orWhere('long_description', 'like', '%'.$term.'%');
                })
                ->limit(15)
                ->get(['title', 'slug'])
                ->each(function ($course) use (&$matches, &$keywords): void {
                    $matches['courses'][] = ['title' => $course->title, 'slug' => $course->slug];
                    $keywords[] = (string) $course->title;
                });

            Tag::query()->where('name', 'like', '%'.$term.'%')->limit(20)->pluck('name')->each(function ($name) use (&$matches, &$keywords): void {
                $matches['tags'][] = $name;
                $keywords[] = (string) $name;
            });

            Category::query()->where('name', 'like', '%'.$term.'%')->limit(20)->pluck('name')->each(function ($name) use (&$matches, &$keywords): void {
                $matches['categories'][] = $name;
                $keywords[] = (string) $name;
            });

            Audience::query()->where('name', 'like', '%'.$term.'%')->limit(20)->pluck('name')->each(function ($name) use (&$matches, &$keywords): void {
                $matches['audiences'][] = $name;
                $keywords[] = (string) $name;
            });
        }

        return [
            'keywords' => $this->dedupe($keywords),
            'matches' => $matches,
        ];
    }

    /**
     * @param  list<string>  $items
     * @return list<string>
     */
    private function dedupe(array $items): array
    {
        $seen = [];
        $result = [];
        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value === '') {
                continue;
            }
            $k = mb_strtolower($value);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $result[] = $value;
        }

        return $result;
    }
}

