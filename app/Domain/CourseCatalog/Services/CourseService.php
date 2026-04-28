<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\CourseKeyword;
use App\Domain\CourseCatalog\Models\CourseKeywordAnalysis;
use App\Domain\CourseCatalog\Models\Course;
use App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting;
use App\Domain\Localization\Services\DefaultLocaleTranslationSync;
use App\Domain\Seo\Services\SeoMetaSyncService;
use App\Domain\Taxonomy\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public function __construct(
        private readonly DefaultLocaleTranslationSync $translationSync,
        private readonly SeoMetaSyncService $seoMetaSync,
        private readonly CourseOpenClassroomGenerator $openClassroomGenerator,
    ) {}

    public function create(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $seo = Arr::pull($data, 'seo', []);
            if (! is_array($seo)) {
                $seo = [];
            }

            if (! array_key_exists('is_s2_modules_enabled', $data)) {
                $data['is_s2_modules_enabled'] = CourseCatalogGlobalSetting::singleton()->default_s2_modules_enabled ?? false;
            }

            $attributes = $this->extractCourseAttributes($data);
            $attributes = $this->normalizeDeliveryFormats($attributes);

            if (blank($attributes['external_course_code'] ?? null)) {
                $attributes['external_course_code'] = $this->generateExternalCourseCode(
                    $attributes['primary_category_id'] ?? null
                );
            }

            $course = new Course($attributes);
            $this->applyPublishingTimestamp($course, $data);
            $course->save();
            $this->syncTaxonomy($course, $data);
            $this->syncChildren($course, $data);
            $this->syncCourseDiscountTiers($course, $data['course_discount_tiers'] ?? []);
            $course->refresh();
            $this->attachKeywordAnalysis($course, $data['wizard_analysis_id'] ?? null);
            $this->assertPublishRules($course);
            $this->openClassroomGenerator->generateForNewCourse($course);
            $this->translationSync->syncCourse($course);
            $this->seoMetaSync->sync($course, $seo);

            return $course->load($this->courseEditRelations());
        });
    }

    public function update(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $seo = Arr::pull($data, 'seo', []);
            if (! is_array($seo)) {
                $seo = [];
            }

            $attributes = $this->extractCourseAttributes($data);
            $attributes = $this->normalizeDeliveryFormats($attributes);
            $course->fill($attributes);
            $this->applyPublishingTimestamp($course, $data);
            $course->save();
            $this->syncTaxonomy($course, $data);
            $this->syncChildren($course, $data);
            $this->syncCourseDiscountTiers($course, $data['course_discount_tiers'] ?? []);
            $course->refresh();
            $this->assertPublishRules($course);
            $this->translationSync->syncCourse($course);
            $this->seoMetaSync->sync($course->fresh(), $seo);

            return $course->load($this->courseEditRelations());
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
            if (array_key_exists('tag_ids', $data)) {
                $syncPayload['tag_ids'] = $data['tag_ids'];
            }
            if (array_key_exists('audience_ids', $data)) {
                $syncPayload['audience_ids'] = $data['audience_ids'];
            }

            if ($syncPayload !== []) {
                $this->syncTaxonomy($course, array_merge([
                    'tag_ids' => $course->tags()->pluck('tags.id')->all(),
                    'audience_ids' => $course->audiences()->pluck('audiences.id')->all(),
                ], $syncPayload));
            }

            $course->refresh();
            $this->assertPublishRules($course);
            $this->translationSync->syncCourse($course);

            return $course->load(['tags', 'audiences', 'difficultyLevel', 'primaryCategory']);
        });
    }

    /**
     * @return list<string>
     */
    protected function courseEditRelations(): array
    {
        return [
            'primaryCategory',
            'difficultyLevel',
            'heroMedia',
            'tags',
            'audiences',
            'modules',
            'learningObjectives',
            'prerequisites',
            'discountTiers',
            'faqs',
            'courseRelations.relatedCourse',
            'openClassrooms',
            'programs',
            'seoMeta',
        ];
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
            'is_s2_modules_enabled',
            'target_audience_text',
            'prerequisites_text',
            'duration_hours',
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
            'delivery_formats',
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

        // S2 Module-Section ist standardmäßig deaktiviert und erst nach redaktionellem Review aktivierbar.
        if ($enum === CourseStatus::Draft) {
            $course->is_s2_modules_enabled = false;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncTaxonomy(Course $course, array $data): void
    {
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
        $this->syncFaqs($course, $data['faqs'] ?? []);
        $this->syncCourseRelations($course, $data['course_relations'] ?? []);
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

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncFaqs(Course $course, array $rows): void
    {
        $course->faqs()->delete();
        foreach ($rows as $index => $row) {
            $q = trim((string) ($row['question'] ?? ''));
            if ($q === '') {
                continue;
            }
            $course->faqs()->create([
                'question' => $q,
                'answer' => trim((string) ($row['answer'] ?? '')),
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
                'is_schema_enabled' => false,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function syncCourseRelations(Course $course, array $rows): void
    {
        $course->courseRelations()->delete();
        $seen = [];
        foreach ($rows as $index => $row) {
            $relatedId = (int) ($row['related_course_id'] ?? 0);
            if ($relatedId < 1 || $relatedId === (int) $course->getKey()) {
                continue;
            }
            if (! Course::query()->whereKey($relatedId)->exists()) {
                continue;
            }
            $type = (string) ($row['relation_type'] ?? 'follow_up');
            $key = $relatedId.'|'.$type;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $course->courseRelations()->create([
                'related_course_id' => $relatedId,
                'relation_type' => $type,
                'sort_order' => isset($row['sort_order']) ? (int) $row['sort_order'] : $index,
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

        if ($course->primary_category_id === null) {
            $errors['primary_category_id'] = [__('A category is required to publish.')];
        } elseif (! Category::query()->whereKey($course->primary_category_id)->exists()) {
            $errors['primary_category_id'] = [__('The selected category is invalid.')];
        }

        if (mb_strlen((string) $course->short_description) < 20) {
            $errors['short_description'] = [__('Published courses require a meaningful short description (at least 20 characters).')];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function normalizeDeliveryFormats(array $attributes): array
    {
        $raw = $attributes['delivery_formats'] ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $formats = array_values(array_unique(array_filter(array_map(
            static fn ($value): string => trim((string) $value),
            $raw
        ), static fn (string $value): bool => $value !== '')));

        $attributes['delivery_formats'] = $formats;
        $attributes['delivery_format'] = $formats[0] ?? null;

        return $attributes;
    }

    protected function generateExternalCourseCode(mixed $primaryCategoryId): string
    {
        if (! is_numeric($primaryCategoryId)) {
            throw ValidationException::withMessages([
                'primary_category_id' => [__('Bitte zuerst eine Kategorie wählen, um die Kurs-ID zu erzeugen.')],
            ]);
        }

        $category = Category::query()->find((int) $primaryCategoryId);
        $prefix = strtoupper(trim((string) ($category?->course_code_prefix ?? '')));

        if ($prefix === '') {
            throw ValidationException::withMessages([
                'primary_category_id' => [__('Die gewählte Kategorie hat kein Kurs-ID-Präfix.')],
            ]);
        }

        $latestCode = Course::query()
            ->where('external_course_code', 'like', $prefix.'-%')
            ->lockForUpdate()
            ->orderByDesc('external_course_code')
            ->value('external_course_code');

        $nextNumber = 1;
        if (is_string($latestCode) && preg_match('/-(\d+)$/', $latestCode, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }

    protected function attachKeywordAnalysis(Course $course, mixed $analysisId): void
    {
        if (! is_numeric($analysisId)) {
            return;
        }

        $analysis = CourseKeywordAnalysis::query()->find((int) $analysisId);
        if (! $analysis) {
            return;
        }

        $analysis->course_id = (int) $course->getKey();
        $analysis->save();

        CourseKeyword::query()
            ->where('analysis_id', $analysis->id)
            ->update(['course_id' => (int) $course->getKey()]);
    }
}
