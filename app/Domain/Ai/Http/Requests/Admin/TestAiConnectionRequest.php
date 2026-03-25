<?php

namespace App\Domain\Ai\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestAiConnectionRequest extends FormRequest
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
            'test_message' => ['required', 'string', 'max:4000'],
            'api_key_override' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
