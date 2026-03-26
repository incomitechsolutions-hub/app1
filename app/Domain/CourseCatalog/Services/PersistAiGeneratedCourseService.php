<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\CourseCatalog\Models\Course;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PersistAiGeneratedCourseService
{
    public function __construct(
        private readonly CourseService $courses
    ) {}

    /**
     * @throws ValidationException
     */
    public function persistFromSession(AiCourseGenerationSession $session): Course
    {
        $payload = $session->draft_payload;
        if (! is_array($payload) || $payload === []) {
            throw ValidationException::withMessages([
                'draft' => __('Der Entwurf ist leer.'),
            ]);
        }

        $data = $this->prepareStorePayload($payload);

        return $this->courses->create($data);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function prepareStorePayload(array $payload): array
    {
        $defaults = [
            'subtitle' => null,
            'external_course_code' => null,
            'hero_media_asset_id' => null,
            'author_name' => null,
            'content_version' => null,
            'ai_prompt_source' => 'ai_course_generation',
            'internal_notes' => null,
            'average_rating' => 0,
            'ratings_count' => 0,
            'course_discount_tiers' => [],
            'tag_ids' => [],
            'audience_ids' => [],
            'modules' => [],
            'objectives' => [],
            'prerequisites' => [],
            'faqs' => [],
            'course_relations' => [],
            'seo' => [],
            'is_featured' => false,
            'media_icon_enabled' => false,
            'media_header_enabled' => false,
            'media_video_enabled' => false,
            'media_gallery_enabled' => false,
        ];

        $out = array_merge($defaults, $payload);

        if (isset($payload['seo']) && is_array($payload['seo'])) {
            $out['seo'] = array_merge($defaults['seo'], $payload['seo']);
        }

        if (($out['average_rating'] ?? null) === null) {
            $out['average_rating'] = 0;
        }
        if (($out['ratings_count'] ?? null) === null) {
            $out['ratings_count'] = 0;
        }

        Arr::forget($out, [
            'ai_taxonomy_rationale',
            'ai_taxonomy_source',
            'ai_taxonomy_warning',
            'ai_taxonomy_raw_reply',
            'ai_taxonomy_unmatched_category_slug',
            'price_research',
            'price_research_parse_warning',
            'price_research_raw',
            'content_variation',
            'ai_difficulty_text',
            'details_ai_raw',
            'modules_raw',
            'objectives_raw',
            'faqs_raw',
            'prerequisites_raw',
            'modules_parse_warning',
            'objectives_parse_warning',
            'faqs_parse_warning',
            'prerequisites_parse_warning',
        ]);

        if (isset($out['seo']) && is_array($out['seo'])) {
            $out['seo'] = Arr::except($out['seo'], [
                'ai_keyword_variants',
                'ai_estimated_density',
                'ai_placements',
            ]);
        }

        return $out;
    }
}
