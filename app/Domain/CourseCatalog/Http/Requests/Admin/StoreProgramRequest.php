<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('programs', 'slug')],
            'short_description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published'],
            'program_courses' => ['nullable', 'array'],
            'program_courses.*.course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'program_courses.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
