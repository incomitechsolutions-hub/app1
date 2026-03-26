<?php

namespace App\Domain\CourseCatalog\Services;

/**
 * Merges partial AI JSON into an existing draft_payload (section-wise).
 *
 * @phpstan-type DraftPayload array<string, mixed>
 */
class AiCourseDraftMerger
{
    /**
     * @param  DraftPayload  $draft
     * @param  array<string, mixed>  $partial
     * @return DraftPayload
     */
    public function merge(string $section, array $draft, array $partial): array
    {
        $out = $draft;

        $scalarKeys = match ($section) {
            'basics' => ['title', 'subtitle', 'slug', 'language_code', 'currency_code', 'duration_days', 'primary_category_id', 'difficulty_level_id', 'is_featured', 'booking_url', 'offer_url', 'lessons_count', 'min_participants', 'instructor_name', 'certificate_label'],
            'pricing' => ['price', 'currency_code', 'duration_days', 'delivery_format', 'price_research_parse_warning', 'price_research_raw'],
            'details_copy' => ['short_description', 'long_description', 'target_audience_text', 'prerequisites_text'],
            default => [],
        };

        foreach ($scalarKeys as $key) {
            if (array_key_exists($key, $partial)) {
                $out[$key] = $partial[$key];
            }
        }

        if ($section === 'pricing' && isset($partial['price_research']) && is_array($partial['price_research'])) {
            $out['price_research'] = $partial['price_research'];
        }

        if ($section === 'basics' || $section === 'details_copy') {
            if (isset($partial['tag_ids']) && is_array($partial['tag_ids'])) {
                $out['tag_ids'] = array_values(array_map('intval', $partial['tag_ids']));
            }
            if (isset($partial['audience_ids']) && is_array($partial['audience_ids'])) {
                $out['audience_ids'] = array_values(array_map('intval', $partial['audience_ids']));
            }
        }

        $listKeys = [
            'modules' => 'modules',
            'objectives' => 'objectives',
            'prerequisites' => 'prerequisites',
            'faqs' => 'faqs',
            'course_relations' => 'course_relations',
            'course_discount_tiers' => 'course_discount_tiers',
        ];

        if (isset($listKeys[$section]) && isset($partial[$listKeys[$section]]) && is_array($partial[$listKeys[$section]])) {
            $out[$listKeys[$section]] = $partial[$listKeys[$section]];
        }

        if ($section === 'seo' && isset($partial['seo']) && is_array($partial['seo'])) {
            $out['seo'] = array_merge(is_array($out['seo'] ?? null) ? $out['seo'] : [], $partial['seo']);
        }

        if ($section === 'details_copy') {
            foreach (['modules', 'objectives', 'prerequisites', 'faqs'] as $k) {
                if (isset($partial[$k]) && is_array($partial[$k])) {
                    $out[$k] = $partial[$k];
                }
            }
        }

        return $out;
    }
}
