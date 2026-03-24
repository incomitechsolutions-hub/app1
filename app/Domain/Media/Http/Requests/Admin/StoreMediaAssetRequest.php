<?php

namespace App\Domain\Media\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaAssetRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
