<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Models\AiCourseGenerationSession;
use App\Domain\CourseCatalog\Models\Course;
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
            'average_rating' => null,
            'ratings_count' => null,
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

        return $out;
    }
}
