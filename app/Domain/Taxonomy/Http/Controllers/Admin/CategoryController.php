<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Localization\Services\DefaultLocaleTranslationSync;
use App\Domain\Taxonomy\Http\Requests\Admin\StoreCategoryRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\UpdateCategoryRequest;
use App\Domain\Taxonomy\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly DefaultLocaleTranslationSync $translationSync
    ) {}

    public function index(Request $request): View
    {
        $level = $this->resolveLevel($request);
        $status = $this->resolveStatus($request);
        $search = trim((string) $request->query('search', ''));
        $sort = $this->resolveSort($request);
        $order = $this->resolveOrder($request);

        $query = Category::query()
            ->with('parent')
            ->withCount(['children', 'courses'])
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        if ($level === 'root') {
            $query->whereNull('parent_id');
        } elseif ($level === 'child') {
            $query->whereNotNull('parent_id');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $query->orderBy($sort, $order);
        if ($sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        $categories = $query->paginate(20)->withQueryString();
        $depthMap = $this->buildDepthMap();

        return view('admin.categories.index', [
            'categories' => $categories,
            'level' => $level,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'depthMap' => $depthMap,
            'stats' => [
                'all' => Category::query()->count(),
                'root' => Category::query()->whereNull('parent_id')->count(),
                'child' => Category::query()->whereNotNull('parent_id')->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $presetParentId = $request->query('parent_id');

        return view('admin.categories.create', [
            'parentOptions' => Category::query()->orderBy('name')->get(['id', 'name']),
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
            'parentOptions' => Category::query()
                ->whereKeyNot($category->getKey())
                ->orderBy('name')
                ->get(['id', 'name']),
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

    /**
     * @return array<int, int>
     */
    private function buildDepthMap(): array
    {
        /** @var array<int, int|null> $parents */
        $parents = Category::query()->pluck('parent_id', 'id')->map(
            static fn ($parent) => $parent === null ? null : (int) $parent
        )->all();

        $depthMap = [];

        foreach ($parents as $id => $parentId) {
            $depth = 0;
            $cursor = $parentId;
            $visited = [(int) $id => true];

            while ($cursor !== null && isset($parents[$cursor])) {
                if (isset($visited[$cursor])) {
                    break;
                }

                $visited[$cursor] = true;
                $depth++;
                $cursor = $parents[$cursor];
            }

            $depthMap[(int) $id] = $depth;
        }

        return $depthMap;
    }
}
