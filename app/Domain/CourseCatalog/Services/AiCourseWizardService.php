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
        $topicTokens = $this->tokenize($topic);
        foreach ($keywords as $k) {
            $kw = trim((string) $k);
            if ($kw === '') {
                continue;
            }

            $kwTokens = $this->tokenize($kw);
            $wordCount = count($kwTokens);
            $commercialRaw = $this->scoreCommercialIntent($kw);
            $relevanceRaw = $this->scoreTopicRelevance($kwTokens, $topicTokens);
            $specificityRaw = $this->scoreSpecificity($wordCount);
            $clarityRaw = $this->scoreClarity($kwTokens);

            // Weighted 0-100 score to provide more meaningful prioritization.
            $finalScore = (int) round(
                ($commercialRaw * 0.35)
                + ($relevanceRaw * 0.35)
                + ($specificityRaw * 0.20)
                + ($clarityRaw * 0.10)
            );
            $commercial = (int) max(1, min(10, round($commercialRaw / 10)));
            $relevance = (int) max(1, min(10, round($relevanceRaw / 10)));
            $fit = (int) max(1, min(10, round($finalScore / 10)));
            $type = $this->resolveType($kw, $wordCount);
            $intent = $commercial >= 7 ? 'commercial' : ($wordCount >= 3 ? 'transactional' : 'informational');
            $confidence = $this->resolveConfidence($kwTokens, $topicTokens, $finalScore);

            $out[] = [
                'keyword' => $kw,
                'type' => $type,
                'intent' => $intent,
                'source' => ['google', 'db'],
                'relevance_score' => $relevance,
                'commercial_score' => $commercial,
                'course_fit_score' => $fit,
                'final_score' => $finalScore,
                'confidence' => $confidence,
                'score_breakdown' => [
                    'commercial_intent' => $commercialRaw,
                    'topic_relevance' => $relevanceRaw,
                    'specificity' => $specificityRaw,
                    'clarity' => $clarityRaw,
                ],
                'selected' => $finalScore >= 64 || ($commercial >= 7 && $relevance >= 7),
            ];
        }

        usort($out, fn ($a, $b) => [$b['final_score'], $b['course_fit_score'], $b['commercial_score']] <=> [$a['final_score'], $a['course_fit_score'], $a['commercial_score']]);

        return array_slice($out, 0, 80);
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $value): array
    {
        return array_values(array_filter(preg_split('/[^a-z0-9äöüß]+/iu', mb_strtolower($value)) ?: []));
    }

    private function scoreCommercialIntent(string $keyword): int
    {
        $tokens = ['kurs', 'schulung', 'seminar', 'training', 'workshop', 'online', 'inhouse', 'zertifikat', 'anbieter', 'buchen'];
        $lower = mb_strtolower($keyword);
        $hits = 0;
        foreach ($tokens as $token) {
            if (str_contains($lower, $token)) {
                $hits++;
            }
        }

        return (int) min(100, 30 + ($hits * 20));
    }

    /**
     * @param  list<string>  $keywordTokens
     * @param  list<string>  $topicTokens
     */
    private function scoreTopicRelevance(array $keywordTokens, array $topicTokens): int
    {
        if ($keywordTokens === [] || $topicTokens === []) {
            return 45;
        }

        $overlap = count(array_intersect($keywordTokens, $topicTokens));
        $ratio = $overlap / max(1, count($topicTokens));

        return (int) max(35, min(100, round(35 + ($ratio * 65))));
    }

    private function scoreSpecificity(int $wordCount): int
    {
        if ($wordCount <= 1) {
            return 35;
        }
        if ($wordCount === 2) {
            return 62;
        }
        if ($wordCount === 3) {
            return 80;
        }
        if ($wordCount <= 5) {
            return 92;
        }

        return 70;
    }

    /**
     * @param  list<string>  $keywordTokens
     */
    private function scoreClarity(array $keywordTokens): int
    {
        if ($keywordTokens === []) {
            return 30;
        }

        $unique = count(array_unique($keywordTokens));
        $ratio = $unique / max(1, count($keywordTokens));

        return (int) max(40, min(100, round($ratio * 100)));
    }

    /**
     * @param  list<string>  $keywordTokens
     * @param  list<string>  $topicTokens
     */
    private function resolveConfidence(array $keywordTokens, array $topicTokens, int $finalScore): string
    {
        $overlap = count(array_intersect($keywordTokens, $topicTokens));
        if ($finalScore >= 75 && $overlap >= 1) {
            return 'high';
        }
        if ($finalScore >= 55) {
            return 'medium';
        }

        return 'low';
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

