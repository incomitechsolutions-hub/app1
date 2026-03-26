<?php

namespace App\Domain\CourseCatalog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KeywordResearchRequest extends FormRequest
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
            'courseIdea' => ['required', 'string', 'max:5000'],
        ];
    }
}
