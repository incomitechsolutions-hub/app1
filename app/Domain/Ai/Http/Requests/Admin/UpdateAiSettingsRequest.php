<?php

namespace App\Domain\Ai\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiSettingsRequest extends FormRequest
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
            'openai_api_key' => ['nullable', 'string', 'max:2048'],
            'default_model' => ['required', 'string', 'max:64'],
            'openai_base_url' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $url = $this->input('openai_base_url');
        if (is_string($url)) {
            $this->merge(['openai_base_url' => rtrim($url)]);
        }
    }
}
