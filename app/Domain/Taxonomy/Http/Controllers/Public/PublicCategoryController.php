<?php

namespace App\Domain\Taxonomy\Http\Controllers\Public;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\View\View;

class PublicCategoryController
{
    public function index(): View
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('status', 'published')
            ->with([
                'children' => fn ($q) => $q
                    ->where('status', 'published')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
                'headerMedia',
            ])
            ->withCount([
                'children as published_children_count' => fn ($q) => $q->where('status', 'published'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.categories.index', compact('categories'));
    }

    public function show(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'parent',
                'children' => fn ($q) => $q
                    ->where('status', 'published')
                    ->with('headerMedia')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
                'seoMeta.ogImageMedia',
                'headerMedia',
            ])
            ->firstOrFail();

        return view('public.categories.show', compact('category'));
    }
}
