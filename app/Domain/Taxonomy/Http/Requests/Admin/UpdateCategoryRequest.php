<?php

namespace App\Domain\Taxonomy\Http\Requests\Admin;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
        /** @var Category $category */
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->getKey() : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:200'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'icon_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'header_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'icon_upload' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
            'header_upload' => ['nullable', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
            'seo' => ['nullable', 'array'],
            'seo.seo_title' => ['nullable', 'string', 'max:255'],
            'seo.meta_description' => ['nullable', 'string', 'max:1000'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.robots_index' => ['nullable', 'in:0,1'],
            'seo.robots_follow' => ['nullable', 'in:0,1'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:1000'],
            'seo.og_image_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'seo.schema_json' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Category|null $category */
            $category = $this->route('category');
            if (! $category instanceof Category) {
                return;
            }

            $rawParentId = $this->input('parent_id');
            if ($rawParentId === null || $rawParentId === '') {
                return;
            }

            $parentId = (int) $rawParentId;
            $categoryId = (int) $category->getKey();

            if ($parentId === $categoryId) {
                $validator->errors()->add('parent_id', __('Eine Kategorie kann nicht ihr eigenes Parent sein.'));

                return;
            }

            if ($this->isDescendantOf($parentId, $categoryId)) {
                $validator->errors()->add('parent_id', __('Zirkuläre Hierarchie ist nicht erlaubt.'));
            }
        });
    }

    private function isDescendantOf(int $candidateParentId, int $categoryId): bool
    {
        $currentId = $candidateParentId;
        $visited = [];

        while ($currentId > 0) {
            if ($currentId === $categoryId) {
                return true;
            }

            if (isset($visited[$currentId])) {
                return true;
            }

            $visited[$currentId] = true;

            $nextParentId = Category::query()->whereKey($currentId)->value('parent_id');
            if ($nextParentId === null) {
                return false;
            }

            $currentId = (int) $nextParentId;
        }

        return false;
    }
}
