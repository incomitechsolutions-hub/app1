<?php

namespace App\Domain\Taxonomy\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewCategoryImportRequest extends FormRequest
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
            'csv_file' => ['required', 'file', 'max:10240', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel'],
            'delimiter' => ['required', 'string', Rule::in([',', ';', '|', '\t'])],
            'has_header' => ['nullable', 'boolean'],
        ];
    }
}
