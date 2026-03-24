<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Localization\Services\DefaultLocaleTranslationSync;
use App\Domain\Taxonomy\Http\Requests\Admin\StoreCategoryRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\UpdateCategoryRequest;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Services\CategoryAdminTreeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly DefaultLocaleTranslationSync $translationSync,
        private readonly CategoryAdminTreeService $categoryTree,
    ) {}

    public function index(Request $request): View
    {
        $data = $this->buildIndexViewData($request);

        if ($request->ajax() && $request->boolean('fragment')) {
            return view('admin.categories.partials.index-body', $data);
        }

        return view('admin.categories.index', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIndexViewData(Request $request): array
    {
        $level = $this->resolveLevel($request);
        $status = $this->resolveStatus($request);
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolveSort($request);
        $order = $this->resolveOrder($request);

        $treeRows = $this->categoryTree->buildIndexRows($level, $status, $search, $sort, $order);

        return [
            'treeRows' => $treeRows,
            'level' => $level,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'stats' => [
                'all' => Category::query()->count(),
                'root' => Category::query()->whereNull('parent_id')->count(),
                'child' => Category::query()->whereNotNull('parent_id')->count(),
            ],
        ];
    }

    public function create(Request $request): View
    {
        $presetParentId = $request->query('parent_id');

        return view('admin.categories.create', [
            'parentPickerOptions' => $this->categoryTree->buildParentPickerOptions(null),
            'presetParentId' => is_numeric($presetParentId) ? (int) $presetParentId : null,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create($request->validated());
        $this->translationSync->syncCategory($category);

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde erstellt.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentPickerOptions' => $this->categoryTree->buildParentPickerOptions((int) $category->getKey()),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());
        $this->translationSync->syncCategory($category->fresh());

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde aktualisiert.'));
    }

    public function destroy(Request $request, Category $category): RedirectResponse|JsonResponse
    {
        if ($category->children()->exists()) {
            $message = __('Kategorie kann nicht gelöscht werden, solange Unterkategorien existieren.');

            if ($request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route('admin.taxonomy.categories.index')
                ->with('status', $message);
        }

        if ($category->courses()->exists() || Course::query()->where('primary_category_id', $category->getKey())->exists()) {
            $message = __('Kategorie kann nicht gelöscht werden, solange Kurse zugeordnet sind.');

            if ($request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route('admin.taxonomy.categories.index')
                ->with('status', $message);
        }

        $category->delete();

        $success = __('Kategorie wurde gelöscht.');

        if ($request->ajax()) {
            return response()->json(['message' => $success]);
        }

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', $success);
    }

    private function resolveLevel(Request $request): string
    {
        $level = (string) $request->query('level', (string) $request->route('level', 'all'));

        if (! in_array($level, ['all', 'root', 'child'], true)) {
            return 'all';
        }

        return $level;
    }

    private function resolveStatus(Request $request): string
    {
        $status = (string) $request->query('status', '');
        if ($status === '') {
            return '';
        }

        return in_array($status, ['draft', 'published', 'archived'], true) ? $status : '';
    }

    private function resolveSort(Request $request): string
    {
        $sort = (string) $request->query('sort', 'name');

        return match ($sort) {
            'id', 'name', 'slug', 'status', 'children_count', 'courses_count' => $sort,
            default => 'name',
        };
    }

    private function resolveOrder(Request $request): string
    {
        return strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
    }
}
