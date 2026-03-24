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

    protected function prepareForValidation(): void
    {
        $code = $this->input('import_locale_code');
        if ($code === '' || $code === null) {
            $this->merge(['import_locale_code' => null]);
        }
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
            'import_locale_code' => ['nullable', 'string', 'max:16', Rule::exists('locales', 'code')],
        ];
    }
}
