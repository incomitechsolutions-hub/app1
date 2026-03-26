<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Localization\Services\DefaultLocaleTranslationSync;
use App\Domain\Media\Models\MediaAsset;
use App\Domain\Media\Services\MediaStorageService;
use App\Domain\Taxonomy\Http\Requests\Admin\BulkUpdateCategoriesRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\CategoryAiFinalizeRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\StoreCategoryRequest;
use App\Domain\Taxonomy\Http\Requests\Admin\UpdateCategoryRequest;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\CategoryTaxonomySetting;
use App\Domain\Seo\Services\SeoMetaSyncService;
use App\Domain\Taxonomy\Services\AiCategoryFormSuggestionService;
use App\Domain\Taxonomy\Services\CategoryAdminTreeService;
use App\Domain\PromptManagement\Enums\PromptUseCase;
use App\Domain\PromptManagement\Models\AiPrompt;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly DefaultLocaleTranslationSync $translationSync,
        private readonly CategoryAdminTreeService $categoryTree,
        private readonly MediaStorageService $mediaStorage,
        private readonly SeoMetaSyncService $seoMetaSync,
        private readonly AiCategoryFormSuggestionService $categoryAiFinalize,
    ) {}

    public function index(Request $request): View
    {
        $data = $this->buildIndexViewData($request);

        if ($request->ajax() && $request->boolean('fragment')) {
            return view('admin.categories.partials.index-body', $data);
        }

        return view('admin.categories.index', $data);
    }

    public function bulkUpdate(BulkUpdateCategoriesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ids = $validated['ids'];
        $status = $validated['bulk_status'];

        DB::transaction(function () use ($ids, $status): void {
            $categories = Category::query()->whereIn('id', $ids)->get();

            foreach ($categories as $category) {
                $category->update(['status' => $status]);
                $this->translationSync->syncCategory($category->fresh());
            }
        });

        $query = $request->only(['level', 'status', 'search', 'sort', 'order']);
        $query = array_filter($query, fn ($v) => $v !== null && $v !== '');

        return redirect()
            ->route('admin.taxonomy.categories.index', $query)
            ->with('status', __(':count Kategorien wurden aktualisiert.', ['count' => count($ids)]));
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
            'mediaAssets' => MediaAsset::query()->orderByDesc('id')->limit(200)->get(),
            'defaultNewCategoryStatus' => CategoryTaxonomySetting::singleton()->default_new_category_status,
            'seoMeta' => null,
            'categoryAiPrompts' => $this->categoryAiPrompts(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $seo = $validated['seo'] ?? [];
        $data = collect($validated)->except(['seo', 'icon_upload', 'header_upload'])->all();
        $data = $this->applyCategoryMediaUploads($request, $data);
        $category = Category::query()->create($data);
        $this->translationSync->syncCategory($category);
        $this->seoMetaSync->sync($category, is_array($seo) ? $seo : []);

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde erstellt.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category->load(['iconMedia', 'headerMedia', 'seoMeta']),
            'parentPickerOptions' => $this->categoryTree->buildParentPickerOptions((int) $category->getKey()),
            'mediaAssets' => MediaAsset::query()->orderByDesc('id')->limit(200)->get(),
            'seoMeta' => $category->seoMeta,
            'categoryAiPrompts' => $this->categoryAiPrompts(),
        ]);
    }

    public function aiFinalize(CategoryAiFinalizeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;

        $allowedIds = collect($this->categoryTree->buildParentPickerOptions($categoryId))
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        $payload = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? '',
            'parent_id' => $validated['parent_id'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'seo' => is_array($validated['seo'] ?? null) ? $validated['seo'] : [],
        ];

        $promptId = isset($validated['ai_prompt_id']) ? (int) $validated['ai_prompt_id'] : null;
        if ($promptId === 0) {
            $promptId = null;
        }

        $result = $this->categoryAiFinalize->finalize($payload, $promptId, $allowedIds);

        if (! $result['ok']) {
            return response()->json([
                'message' => $result['error'] ?? __('KI-Finalisierung fehlgeschlagen.'),
                'raw_reply' => $result['raw_reply'] ?? null,
            ], 422);
        }

        return response()->json([
            'filled' => $result['filled'] ?? [],
            'warnings' => $result['warnings'] ?? [],
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        $seo = $validated['seo'] ?? [];
        $data = collect($validated)->except(['seo', 'icon_upload', 'header_upload'])->all();
        $data = $this->applyCategoryMediaUploads($request, $data);
        $category->update($data);
        $this->translationSync->syncCategory($category->fresh());
        $this->seoMetaSync->sync($category->fresh(), is_array($seo) ? $seo : []);

        return redirect()
            ->route('admin.taxonomy.categories.index')
            ->with('status', __('Kategorie wurde aktualisiert.'));
    }

    public function storeQuick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $base = Str::slug($data['name']);
        $slug = $base;
        $n = 0;
        while (Category::query()->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $maxSort = Category::query()->whereNull('parent_id')->max('sort_order');
        $category = Category::query()->create([
            'name' => trim($data['name']),
            'slug' => $slug,
            'description' => null,
            'parent_id' => null,
            'sort_order' => is_numeric($maxSort) ? ((int) $maxSort + 1) : 0,
            'status' => CategoryTaxonomySetting::singleton()->default_new_category_status,
        ]);

        $this->translationSync->syncCategory($category);

        return response()->json([
            'category' => ['id' => $category->id, 'name' => $category->name],
        ], 201);
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

        if ($category->courses()->exists()) {
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function categoryAiPrompts()
    {
        return AiPrompt::query()
            ->where('use_case', PromptUseCase::CategoryManagement->value)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    private function applyCategoryMediaUploads(Request $request, array $data): array
    {
        if ($request->hasFile('icon_upload')) {
            $data['icon_media_asset_id'] = $this->mediaStorage->store($request->file('icon_upload'))->getKey();
        }
        if ($request->hasFile('header_upload')) {
            $data['header_media_asset_id'] = $this->mediaStorage->store($request->file('header_upload'))->getKey();
        }

        return $data;
    }
}
