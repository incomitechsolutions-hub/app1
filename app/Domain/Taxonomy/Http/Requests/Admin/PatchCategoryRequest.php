<?php

namespace App\Domain\Taxonomy\Http\Requests\Admin;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatchCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
        if ($this->input('sort_order') === '') {
            $this->merge(['sort_order' => 0]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->getKey() : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
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

            $validated = $this->safe()->only(['name', 'slug', 'parent_id', 'sort_order', 'status']);
            if ($validated === []) {
                $validator->errors()->add('fields', __('Keine gültigen Felder übergeben.'));

                return;
            }

            if (! array_key_exists('parent_id', $validated)) {
                return;
            }

            $parentId = $validated['parent_id'];
            if ($parentId === null) {
                return;
            }

            $parentId = (int) $parentId;
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

