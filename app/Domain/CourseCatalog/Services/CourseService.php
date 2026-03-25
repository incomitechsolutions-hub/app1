<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Localization\Services\DefaultLocaleTranslationSync;
use App\Domain\Seo\Services\SeoMetaSyncService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public function __construct(
        private readonly DefaultLocaleTranslationSync $translationSync,
        private readonly SeoMetaSyncService $seoMetaSync,
    ) {}

    public function create(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $seo = Arr::pull($data, 'seo', []);
            if (! is_array($seo)) {
                $seo = [];
            }

            $course = new Course($this->extractCourseAttributes($data));
            $this->applyPublishingTimestamp($course, $data);
            $course->save();
            $this->syncTaxonomy($course, $data);
            $this->syncChildren($course, $data);
            $this->syncCourseDiscountTiers($course, $data['course_discount_tiers'] ?? []);
            $course->refresh();
            $this->assertPublishRules($course);
            $this->translationSync->syncCourse($course);
            $this->seoMetaSync->sync($course, $seo);

            return $course->load([
                'primaryCategory',
                'difficultyLevel',
                'heroMedia',
                'categories',
                'tags',
                'audiences',
                'modules',
                'learningObjectives',
                'prerequisites',
                'discountTiers',
                'seoMeta',
            ]);
        });
    }

    public function update(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $seo = Arr::pull($data, 'seo', []);
            if (! is_array($seo)) {
                $seo = [];
            }

            $course->fill($this->extractCourseAttributes($data));
            $this->applyPublishingTimestamp($course, $data);
            $course->save();
            $this->syncTaxonomy($course, $data);
            $this->syncChildren($course, $data);
            $this->syncCourseDiscountTiers($course, $data['course_discount_tiers'] ?? []);
            $course->refresh();
            $this->assertPublishRules($course);
            $this->translationSync->syncCourse($course);
            $this->seoMetaSync->sync($course->fresh(), $seo);

            return $course->load([
                'primaryCategory',
                'difficultyLevel',
                'heroMedia',
                'categories',
                'tags',
                'audiences',
                'modules',
                'learningObjectives',
                'prerequisites',
                'discountTiers',
                'seoMeta',
            ]);
        });
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    /**
     * Partial update for admin AJAX (taxonomy / level only).
     *
     * @param  array<string, mixed>  $data
     */
    public function patchFields(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            if (array_key_exists('difficulty_level_id', $data)) {
                $course->difficulty_level_id = $data['difficulty_level_id'];
                $course->save();
            }

            if (array_key_exists('primary_category_id', $data)) {
                $course->primary_category_id = $data['primary_category_id'];
                $course->save();
            }

            $syncPayload = [];
            if (array_key_exists('category_ids', $data)) {
                $syncPayload['category_ids'] = $data['category_ids'];
            }
            if (array_key_exists('tag_ids', $data)) {
                $syncPayload['tag_ids'] = $data['tag_ids'];
            }
            if (array_key_exists('audience_ids', $data)) {
                $syncPayload['audience_ids'] = $data['audience_ids'];
            }

            if ($syncPayload !== []) {
                $this->syncTaxonomy($course, array_merge([
                    'category_ids' => $course->categories()->pluck('categories.id')->all(),
                    'tag_ids' => $course->tags()->pluck('tags.id')->all(),
                    'audience_ids' => $course->audiences()->pluck('audiences.id')->all(),
                ], $syncPayload));
            }

            $course->refresh();
            $this->assertPublishRules($course);
            $this->translationSync->syncCourse($course);

            return $course->load(['categories', 'tags', 'audiences', 'difficultyLevel']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractCourseAttributes(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'title',
            'subtitle',
            'slug',
            'external_course_code',
            'short_description',
            'long_description',
            'target_audience_text',
            'prerequisites_text',
            'duration_hours',
            'duration_days',
            'language_code',
            'currency_code',
            'status',
            'primary_category_id',
            'difficulty_level_id',
            'hero_media_asset_id',
            'published_at',
            'author_name',
            'content_version',
            'price',
            'delivery_format',
            'delivery_mode',
            'lessons_count',
            'min_participants',
            'instructor_name',
            'certificate_label',
            'is_featured',
            'booking_url',
            'offer_url',
            'ai_prompt_source',
            'internal_notes',
            'average_rating',
            'ratings_count',
            'media_icon_enabled',
            'media_header_enabled',
            'media_video_enabled',
            'media_gallery_enabled',
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function applyPublishingTimestamp(Course $course, array $data): void
    {
        $raw = $data['status'] ?? $course->status;
        $enum = $raw instanceof CourseStatus
            ? $raw
            : CourseStatus::from((string) $raw);

        if ($enum === CourseStatus::Published) {
            $course->published_at = $course->published_at ?? now();
        } else {
            $course->published_at = null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncTaxonomy(Course $course, array $data): void
    {
        $categoryIds = array_values(array_unique(array_map('intval', $data['category_ids'] ?? [])));
        $course->categories()->sync($categoryIds);

        $tagIds = array_values(array_unique(array_map('intval', $data['tag_ids'] ?? [])));
        $course->tags()->sync($tagIds);

        $audienceIds = array_values(array_unique(array_map('intval', $data['audience_ids'] ?? [])));
        $course->audiences()->sync($audienceIds);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncChildren(Course $course, array $data): void
    {
        $this->syncModules($course, $data['modules'] ?? []);
        $this->syncObjectives($course, $data['objectives'] ?? []);
        $this->syncPrerequisites($course, $data['prerequisites'] ?? []);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncCourseDiscountTiers(Course $course, array $rows): void
    {
        $course->discountTiers()->delete();
        $rows = array_values(array_filter($rows, fn ($row) => isset($row['min_participants']) && $row['min_participants'] !== '' && $row['min_participants'] !== null));
        foreach ($rows as $index => $row) {
            $course->discountTiers()->create([
                'sort_order' => $index,
                'min_participants' => max(1, (int) $row['min_participants']),
                'discount_percent' => (float) ($row['discount_percent'] ?? 0),
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncModules(Course $course, array $rows): void
    {
        $course->modules()->delete();
        $rows = array_values(array_filter($rows, fn ($row) => filled($row['title'] ?? null)));
        foreach ($rows as $index => $row) {
            $course->modules()->create([
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'duration_hours' => $row['duration_hours'] ?? null,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>|string>  $rows
     */
    protected function syncObjectives(Course $course, array $rows): void
    {
        $course->learningObjectives()->delete();
        $normalized = [];
        foreach ($rows as $index => $row) {
            if (is_string($row)) {
                $text = trim($row);
                if ($text === '') {
                    continue;
                }
                $normalized[] = ['text' => $text, 'sort_order' => $index];

                continue;
            }
            $text = trim((string) ($row['objective_text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $normalized[] = [
                'text' => $text,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
            ];
        }
        foreach ($normalized as $row) {
            $course->learningObjectives()->create([
                'objective_text' => $row['text'],
                'sort_order' => $row['sort_order'],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>|string>  $rows
     */
    protected function syncPrerequisites(Course $course, array $rows): void
    {
        $course->prerequisites()->delete();
        $normalized = [];
        foreach ($rows as $index => $row) {
            if (is_string($row)) {
                $text = trim($row);
                if ($text === '') {
                    continue;
                }
                $normalized[] = ['text' => $text, 'sort_order' => $index];

                continue;
            }
            $text = trim((string) ($row['prerequisite_text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $normalized[] = [
                'text' => $text,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
            ];
        }
        foreach ($normalized as $row) {
            $course->prerequisites()->create([
                'prerequisite_text' => $row['text'],
                'sort_order' => $row['sort_order'],
            ]);
        }
    }

    protected function assertPublishRules(Course $course): void
    {
        if ($course->status !== CourseStatus::Published) {
            return;
        }

        $errors = [];

        if (blank($course->title)) {
            $errors['title'] = [__('A title is required to publish.')];
        }

        if (blank($course->slug)) {
            $errors['slug'] = [__('A slug is required to publish.')];
        }

        if ($course->categories()->count() === 0) {
            $errors['category_ids'] = [__('Courses must belong to at least one category.')];
        }

        if ($course->primary_category_id === null) {
            $errors['primary_category_id'] = [__('A primary category is required to publish.')];
        } elseif (! $course->categories()->where('categories.id', $course->primary_category_id)->exists()) {
            $errors['primary_category_id'] = [__('Primary category must be one of the selected categories.')];
        }

        if (mb_strlen((string) $course->short_description) < 20) {
            $errors['short_description'] = [__('Published courses require a meaningful short description (at least 20 characters).')];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
