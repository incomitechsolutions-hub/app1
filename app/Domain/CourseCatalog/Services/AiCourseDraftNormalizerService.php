<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Domain\Taxonomy\Models\Audience;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\DifficultyLevel;
use App\Domain\Taxonomy\Models\Tag;
use Illuminate\Support\Str;

/**
 * Maps AI JSON into a draft_payload shape compatible with StoreCourseRequest / CourseService::create.
 */
class AiCourseDraftNormalizerService
{
    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    public function normalizeFromAiJson(array $parsed): array
    {
        $title = trim((string) ($parsed['title'] ?? 'Neuer Kurs'));
        $slug = trim((string) ($parsed['slug'] ?? ''));
        if ($slug === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $slug = Str::slug($title);
            if ($slug === '') {
                $slug = 'kurs-'.Str::lower(Str::random(8));
            }
        }

        $short = trim((string) ($parsed['short_description'] ?? ''));
        if (mb_strlen($short) < 20) {
            $short = $short.str_repeat('.', max(0, 20 - mb_strlen($short)));
        }

        $primaryCategoryId = null;
        $slugCat = trim((string) ($parsed['primary_category_slug'] ?? ''));
        if ($slugCat !== '') {
            $primaryCategoryId = Category::query()->where('slug', $slugCat)->value('id');
        }
        if ($primaryCategoryId === null) {
            $nameCat = trim((string) ($parsed['primary_category_name'] ?? ''));
            if ($nameCat !== '') {
                $primaryCategoryId = Category::query()->where('name', $nameCat)->value('id');
            }
        }

        $difficultyLevelId = null;
        $diffCode = trim((string) ($parsed['difficulty_level_code'] ?? $parsed['difficulty_code'] ?? ''));
        if ($diffCode !== '') {
            $difficultyLevelId = DifficultyLevel::query()->where('code', $diffCode)->value('id');
        }

        $tagIds = $this->resolveTagIds($parsed['tag_slugs'] ?? $parsed['tags'] ?? []);
        $audienceIds = $this->resolveAudienceIds($parsed['audience_slugs'] ?? $parsed['audiences'] ?? []);

        $delivery = $parsed['delivery_format'] ?? null;
        $deliveryFormat = null;
        if (is_string($delivery) && $delivery !== '') {
            try {
                $deliveryFormat = DeliveryFormat::from($delivery)->value;
            } catch (\ValueError) {
                $deliveryFormat = null;
            }
        }

        $modules = $this->normalizeModules($parsed['modules'] ?? []);
        $objectives = $this->normalizeObjectives($parsed['objectives'] ?? $parsed['learning_objectives'] ?? []);
        $prerequisites = $this->normalizePrerequisites($parsed['prerequisites'] ?? []);
        $faqs = $this->normalizeFaqs($parsed['faqs'] ?? []);

        $seoNested = is_array($parsed['seo'] ?? null) ? $parsed['seo'] : [];
        $seo = [
            'seo_title' => trim((string) ($parsed['seo_title'] ?? ($seoNested['seo_title'] ?? ''))),
            'meta_description' => trim((string) ($parsed['meta_description'] ?? ($seoNested['meta_description'] ?? ''))),
            'focus_keyword' => trim((string) ($parsed['focus_keyword'] ?? ($seoNested['focus_keyword'] ?? ''))),
            'canonical_url' => $this->nullIfEmpty($parsed['canonical_url'] ?? ($seoNested['canonical_url'] ?? null)),
            'robots_index' => $this->boolTo01($parsed['robots_index'] ?? ($seoNested['robots_index'] ?? null)),
            'robots_follow' => $this->boolTo01($parsed['robots_follow'] ?? ($seoNested['robots_follow'] ?? null)),
            'og_title' => trim((string) ($parsed['og_title'] ?? ($seoNested['og_title'] ?? ''))),
            'og_description' => trim((string) ($parsed['og_description'] ?? ($seoNested['og_description'] ?? ''))),
            'schema_json' => $parsed['schema_json'] ?? ($seoNested['schema_json'] ?? null),
        ];
        if (isset($seo['schema_json']) && is_array($seo['schema_json'])) {
            $seo['schema_json'] = json_encode($seo['schema_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return [
            'title' => $title,
            'subtitle' => $this->nullIfEmptyString($parsed['subtitle'] ?? null),
            'slug' => $slug,
            'short_description' => $short,
            'long_description' => $this->nullIfEmptyString($parsed['long_description'] ?? null),
            'target_audience_text' => $this->nullIfEmptyString($parsed['target_audience_text'] ?? null),
            'prerequisites_text' => $this->nullIfEmptyString($parsed['prerequisites_text'] ?? null),
            'duration_days' => isset($parsed['duration_days']) && $parsed['duration_days'] !== '' ? (int) $parsed['duration_days'] : null,
            'language_code' => trim((string) ($parsed['language_code'] ?? 'de')) ?: 'de',
            'currency_code' => strtoupper(trim((string) ($parsed['currency_code'] ?? 'EUR'))) ?: 'EUR',
            'status' => CourseStatus::Draft->value,
            'published_at' => null,
            'primary_category_id' => $primaryCategoryId,
            'difficulty_level_id' => $difficultyLevelId,
            'price' => isset($parsed['price']) && $parsed['price'] !== '' && $parsed['price'] !== null ? (float) $parsed['price'] : null,
            'delivery_format' => $deliveryFormat,
            'lessons_count' => isset($parsed['lessons_count']) ? (int) $parsed['lessons_count'] : null,
            'min_participants' => isset($parsed['min_participants']) ? (int) $parsed['min_participants'] : null,
            'instructor_name' => $this->nullIfEmptyString($parsed['instructor_name'] ?? null),
            'certificate_label' => $this->nullIfEmptyString($parsed['certificate_label'] ?? null),
            'is_featured' => (bool) ($parsed['is_featured'] ?? false),
            'booking_url' => $this->nullIfEmpty($parsed['booking_url'] ?? null),
            'offer_url' => $this->nullIfEmpty($parsed['offer_url'] ?? null),
            'tag_ids' => $tagIds,
            'audience_ids' => $audienceIds,
            'modules' => $modules,
            'objectives' => $objectives,
            'prerequisites' => $prerequisites,
            'faqs' => $faqs,
            'course_relations' => [],
            'course_discount_tiers' => [],
            'seo' => $seo,
            'media_icon_enabled' => false,
            'media_header_enabled' => false,
            'media_video_enabled' => false,
            'media_gallery_enabled' => false,
        ];
    }

    /**
     * @param  array<int, mixed>  $slugs
     * @return list<int>
     */
    protected function resolveTagIds(array $slugs): array
    {
        $ids = [];
        foreach ($slugs as $s) {
            $slug = is_string($s) ? trim($s) : '';
            if ($slug === '') {
                continue;
            }
            $id = Tag::query()->where('slug', $slug)->value('id');
            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int, mixed>  $slugs
     * @return list<int>
     */
    protected function resolveAudienceIds(array $slugs): array
    {
        $ids = [];
        foreach ($slugs as $s) {
            $slug = is_string($s) ? trim($s) : '';
            if ($slug === '') {
                continue;
            }
            $id = Audience::query()->where('slug', $slug)->value('id');
            if ($id) {
                $ids[] = (int) $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    protected function normalizeModules(array $rows): array
    {
        $out = [];
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'description' => $this->nullIfEmptyString($row['description'] ?? null),
                'duration_hours' => isset($row['duration_hours']) && $row['duration_hours'] !== '' ? (float) $row['duration_hours'] : null,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $i,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    protected function normalizeObjectives(array $rows): array
    {
        $out = [];
        foreach ($rows as $i => $row) {
            if (is_string($row)) {
                $t = trim($row);
                if ($t === '') {
                    continue;
                }
                $out[] = ['objective_text' => $t, 'sort_order' => $i];

                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $t = trim((string) ($row['objective_text'] ?? $row['text'] ?? ''));
            if ($t === '') {
                continue;
            }
            $out[] = [
                'objective_text' => $t,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $i,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    protected function normalizePrerequisites(array $rows): array
    {
        $out = [];
        foreach ($rows as $i => $row) {
            if (is_string($row)) {
                $t = trim($row);
                if ($t === '') {
                    continue;
                }
                $out[] = ['prerequisite_text' => $t, 'sort_order' => $i];

                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $t = trim((string) ($row['prerequisite_text'] ?? $row['text'] ?? ''));
            if ($t === '') {
                continue;
            }
            $out[] = [
                'prerequisite_text' => $t,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $i,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    protected function normalizeFaqs(array $rows): array
    {
        $out = [];
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['question'] ?? ''));
            if ($q === '') {
                continue;
            }
            $out[] = [
                'question' => $q,
                'answer' => trim((string) ($row['answer'] ?? '')),
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $i,
            ];
        }

        return $out;
    }

    protected function nullIfEmpty(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    protected function nullIfEmptyString(mixed $v): ?string
    {
        return $this->nullIfEmpty($v);
    }

    protected function boolTo01(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        return in_array((string) $v, ['1', 'true', 'yes'], true) ? '1' : '0';
    }
}
