<?php

namespace App\Domain\Taxonomy\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('sort_order');
        if ($raw === '' || $raw === null) {
            $this->merge(['sort_order' => 0]);
        }

        foreach (['icon_media_asset_id', 'header_media_asset_id'] as $key) {
            $v = $this->input($key);
            if ($v === '' || $v === null) {
                $this->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')],
            'description' => ['nullable', 'string', 'max:200'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'icon_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'header_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'icon_upload' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
            'header_upload' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
        ];
    }
}
