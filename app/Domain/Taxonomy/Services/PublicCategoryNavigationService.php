<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\Support\Collection;

class PublicCategoryNavigationService
{
    /**
     * @return Collection<int, Category>
     */
    public function topCategoriesWithChildren(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->with([
                'children' => fn ($q) => $q
                    ->where('status', 'published')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}

