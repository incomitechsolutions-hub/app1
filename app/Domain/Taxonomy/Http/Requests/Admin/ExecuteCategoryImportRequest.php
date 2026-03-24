<?php

namespace App\Domain\Taxonomy\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExecuteCategoryImportRequest extends FormRequest
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
            'upload_token' => ['required', 'string', 'max:120'],
            'mapping' => ['required', 'array'],
            'mapping.name' => ['required', 'string'],
            'mapping.slug' => ['required', 'string'],
            'mapping.description' => ['nullable', 'string'],
            'mapping.parent_id' => ['nullable', 'string'],
            'mapping.parent_slug' => ['nullable', 'string'],
            'mapping.status' => ['nullable', 'string'],
            'fallback_status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'duplicate_strategy' => ['required', 'string', Rule::in(['skip', 'update', 'fail'])],
        ];
    }
}
