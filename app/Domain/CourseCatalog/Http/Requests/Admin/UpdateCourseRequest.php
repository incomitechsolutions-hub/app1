<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use App\Domain\CourseCatalog\Enums\CourseStatus;
use App\Domain\CourseCatalog\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Course $course */
        $course = $this->route('course');
        $courseId = $course instanceof Course ? $course->getKey() : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('courses', 'slug')->ignore($courseId),
            ],
            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],
            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'language_code' => ['required', 'string', 'max:16'],
            'status' => ['required', new Enum(CourseStatus::class)],
            'primary_category_id' => ['nullable', 'exists:categories,id'],
            'difficulty_level_id' => ['nullable', 'exists:difficulty_levels,id'],
            'hero_media_asset_id' => ['nullable', 'exists:media_assets,id'],
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
