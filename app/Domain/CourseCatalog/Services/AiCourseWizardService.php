<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\CourseKeyword;
use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
use Illuminate\Contracts\Auth\Authenticatable;

class AiCourseWizardService
{
    public function __construct(
        private readonly SeoKeywordDiscoveryService $discovery,
        private readonly CourseContentGenerationService $contentGeneration,
        private readonly TaxonomyMatchingService $taxonomyMatching,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function runKeywordDiscovery(array $input, ?Authenticatable $user): array
    {
        $topic = trim((string) ($input['topic'] ?? ''));
        $subtopics = is_array($input['subtopics'] ?? null) ? $input['subtopics'] : [];

        $sources = $this->discovery->discover($topic, $subtopics);
        $all = array_values(array_unique(array_merge(
            $sources['google_keywords'],
            $sources['db_keywords']
        )));

        $keywords = $this->classifyKeywords($all, $topic);
        $clusters = $this->clusters($keywords);
        $tax = $this->taxonomyMatching->match(array_column($keywords, 'keyword'), $topic);

        $analysis = CourseKeywordAnalysis::query()->create([
            'topic' => $topic,
            'subtopics' => $subtopics,
            'raw_google_response' => $sources['google_raw'],
            'raw_ai_response' => ['db_matches' => $sources['db_matches']],
            'selected_primary_keyword' => $keywords[0]['keyword'] ?? null,
            'selected_keywords' => [],
            'selected_clusters' => [],
            'seo_opportunity_score' => (int) min(100, max(1, count($keywords))),
            'created_by' => $user?->getAuthIdentifier(),
        ]);

        foreach ($keywords as $row) {
            CourseKeyword::query()->create([
                'analysis_id' => $analysis->id,
                'keyword' => $row['keyword'],
                'type' => $row['type'],
                'intent' => $row['intent'],
                'source' => $row['source'],
                'relevance_score' => $row['relevance_score'],
                'commercial_score' => $row['commercial_score'],
                'course_fit_score' => $row['course_fit_score'],
                'selected' => $row['selected'],
            ]);
        }

        $generated = $this->contentGeneration->generate($input, array_column($keywords, 'keyword'));

        return [
            'analysis_id' => $analysis->id,
            'keywords' => $keywords,
            'clusters' => $clusters,
            'suggested_seo' => $generated['seo'],
            'suggested_taxonomies' => $tax,
            'generated' => $generated,
        ];
    }

    /**
     * @param  list<string>  $keywords
     * @return list<array<string,mixed>>
     */
    private function classifyKeywords(array $keywords, string $topic): array
    {
        $out = [];
        foreach ($keywords as $k) {
            $kw = trim((string) $k);
            if ($kw === '') {
                continue;
            }

            $wordCount = count(array_filter(preg_split('/\s+/', mb_strtolower($kw)) ?: []));
            $commercial = $this->containsCommercialToken($kw) ? 9 : 4;
            $relevance = str_contains(mb_strtolower($kw), mb_strtolower($topic)) ? 9 : 5;
            $fit = min(10, (int) round(($commercial + $relevance) / 2));
            $type = $this->resolveType($kw, $wordCount);
            $intent = $commercial >= 7 ? 'commercial' : ($wordCount >= 3 ? 'transactional' : 'informational');

            $out[] = [
                'keyword' => $kw,
                'type' => $type,
                'intent' => $intent,
                'source' => ['google', 'db'],
                'relevance_score' => $relevance,
                'commercial_score' => $commercial,
                'course_fit_score' => $fit,
                'selected' => $commercial >= 7 || $relevance >= 8,
            ];
        }

        usort($out, fn ($a, $b) => [$b['course_fit_score'], $b['commercial_score']] <=> [$a['course_fit_score'], $a['commercial_score']]);

        return array_slice($out, 0, 80);
    }

    /**
     * @param  list<array<string,mixed>>  $keywords
     * @return list<array<string,mixed>>
     */
    private function clusters(array $keywords): array
    {
        $buckets = [];
        foreach ($keywords as $kw) {
            $type = (string) ($kw['type'] ?? 'related');
            $buckets[$type][] = $kw['keyword'];
        }

        $clusters = [];
        foreach ($buckets as $type => $items) {
            $clusters[] = [
                'name' => ucfirst($type),
                'type' => $type,
                'keywords' => array_values(array_unique($items)),
            ];
        }

        return $clusters;
    }

    private function containsCommercialToken(string $keyword): bool
    {
        $tokens = ['kurs', 'schulung', 'seminar', 'training', 'workshop', 'online', 'inhouse'];
        $lower = mb_strtolower($keyword);
        foreach ($tokens as $token) {
            if (str_contains($lower, $token)) {
                return true;
            }
        }

        return false;
    }

    private function resolveType(string $keyword, int $wordCount): string
    {
        if ($this->containsCommercialToken($keyword)) {
            return 'primary';
        }
        if ($wordCount >= 3) {
            return 'longtail';
        }
        if (str_contains(mb_strtolower($keyword), 'fuer') || str_contains(mb_strtolower($keyword), 'mit')) {
            return 'semantic';
        }

        return 'related';
    }
}

