<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'media_icon_enabled' => $this->boolean('media_icon_enabled'),
            'media_header_enabled' => $this->boolean('media_header_enabled'),
            'media_video_enabled' => $this->boolean('media_video_enabled'),
            'media_gallery_enabled' => $this->boolean('media_gallery_enabled'),
        ]);
        if ($this->input('delivery_format') === '' || $this->input('delivery_format') === null) {
            $this->merge(['delivery_format' => null]);
        }
        if ($this->input('external_course_code') === '') {
            $this->merge(['external_course_code' => null]);
        }
        if ($this->input('price') === '' || $this->input('price') === null) {
            $this->merge(['price' => null]);
        }
        if ($this->input('currency_code') === '' || $this->input('currency_code') === null) {
            $this->merge(['currency_code' => 'EUR']);
        }
        if ($this->input('published_at') === '') {
            $this->merge(['published_at' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('courses', 'slug')],
            'external_course_code' => ['nullable', 'string', 'max:64', Rule::unique('courses', 'external_course_code')],
            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'target_audience_text' => ['nullable', 'string'],
            'prerequisites_text' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:3660'],
            'language_code' => ['required', 'string', 'max:16'],
            'currency_code' => ['required', 'string', 'size:3'],
            'status' => ['required', new Enum(CourseStatus::class)],
            'published_at' => ['nullable', 'date'],
            'primary_category_id' => ['nullable', 'exists:categories,id'],
            'difficulty_level_id' => ['nullable', 'exists:difficulty_levels,id'],
            'hero_media_asset_id' => ['nullable', 'exists:media_assets,id'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'content_version' => ['nullable', 'string', 'max:32'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'course_discount_tiers' => ['nullable', 'array'],
            'course_discount_tiers.*.min_participants' => ['nullable', 'integer', 'min:1'],
            'course_discount_tiers.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delivery_format' => ['nullable', new Enum(DeliveryFormat::class)],
            'lessons_count' => ['nullable', 'integer', 'min:0'],
            'min_participants' => ['nullable', 'integer', 'min:0'],
            'instructor_name' => ['nullable', 'string', 'max:255'],
            'certificate_label' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['boolean'],
            'booking_url' => ['nullable', 'url', 'max:2048'],
            'offer_url' => ['nullable', 'url', 'max:2048'],
            'ai_prompt_source' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'average_rating' => ['nullable', 'numeric', 'between:0,5'],
            'ratings_count' => ['nullable', 'integer', 'min:0'],
            'media_icon_enabled' => ['boolean'],
            'media_header_enabled' => ['boolean'],
            'media_video_enabled' => ['boolean'],
            'media_gallery_enabled' => ['boolean'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'audience_ids' => ['nullable', 'array'],
            'audience_ids.*' => ['integer', 'exists:audiences,id'],
            'modules' => ['nullable', 'array'],
            'modules.*.title' => ['nullable', 'string', 'max:255'],
            'modules.*.description' => ['nullable', 'string'],
            'modules.*.duration_hours' => ['nullable', 'numeric', 'min:0'],
            'modules.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'objectives' => ['nullable', 'array'],
            'objectives.*.objective_text' => ['nullable', 'string', 'max:2000'],
            'objectives.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*.prerequisite_text' => ['nullable', 'string', 'max:2000'],
            'prerequisites.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'seo' => ['nullable', 'array'],
            'seo.seo_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:1000'],
            'seo.focus_keyword' => ['nullable', 'string', 'max:255'],
            'seo.tags_csv' => ['nullable', 'string', 'max:2000'],
            'seo.preview_image_url' => ['nullable', 'string', 'max:2048'],
            'seo.landing_page_url' => ['nullable', 'string', 'max:2048'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.robots_index' => ['nullable', 'in:0,1'],
            'seo.robots_follow' => ['nullable', 'in:0,1'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:1000'],
            'seo.og_image_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'seo.schema_json' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $primary = $this->input('primary_category_id');
            $cats = array_map('intval', $this->input('category_ids', []));
            if ($primary !== null && $primary !== '' && ! in_array((int) $primary, $cats, true)) {
                $validator->errors()->add(
                    'primary_category_id',
                    __('Primary category must be included in categories.')
                );
            }
        });
    }
}
