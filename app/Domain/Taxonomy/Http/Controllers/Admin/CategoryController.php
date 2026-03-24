<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Http\Requests\Admin\StoreCategoryRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\UpdateCategoryRequest;
use App\Domain\Taxonomy\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $level = (string) $request->query('level', (string) $request->route('level', 'all'));

        if (! in_array($level, ['all', 'root', 'child'], true)) {
            $level = 'all';
        }

        $query = Category::query()
            ->with('parent')
            ->withCount(['children', 'courses'])
            ->orderBy('name');

        if ($level === 'root') {
            $query->whereNull('parent_id');
        } elseif ($level === 'child') {
            $query->whereNotNull('parent_id');
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'level' => $level,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'parentOptions' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde erstellt.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => Category::query()
                ->whereKeyNot($category->getKey())
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde aktualisiert.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return redirect()
                ->route('admin.taxonomy.categories.index')
                ->with('status', __('Kategorie kann nicht gelöscht werden, solange Unterkategorien existieren.'));
        }

        if ($category->courses()->exists() || Course::query()->where('primary_category_id', $category->getKey())->exists()) {
            return redirect()
                ->route('admin.taxonomy.categories.index')
                ->with('status', __('Kategorie kann nicht gelöscht werden, solange Kurse zugeordnet sind.'));
        }

        $category->delete();

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde gelöscht.'));
    }
}
