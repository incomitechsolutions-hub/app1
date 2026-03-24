<?php

namespace App\Domain\Taxonomy\Http\Controllers\Public;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\View\View;

class PublicCategoryController
{
    public function show(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'parent',
                'children' => fn ($q) => $q->where('status', 'published')->orderBy('sort_order'),
                'seoMeta.ogImageMedia',
            ])
            ->firstOrFail();

        return view('public.categories.show', compact('category'));
    }
}
