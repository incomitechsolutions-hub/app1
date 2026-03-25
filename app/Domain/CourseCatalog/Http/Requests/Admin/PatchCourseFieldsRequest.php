<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PatchCourseFieldsRequest extends FormRequest
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
        return [
            'primary_category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'audience_ids' => ['sometimes', 'array'],
            'audience_ids.*' => ['integer', 'exists:audiences,id'],
            'difficulty_level_id' => ['sometimes', 'nullable', 'integer', 'exists:difficulty_levels,id'],
        ];
    }

    public function withValidator($validator): void
    {
        //
    }
}
