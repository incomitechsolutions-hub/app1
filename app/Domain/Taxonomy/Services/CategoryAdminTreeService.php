<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class CategoryAdminTreeService
{
    /**
     * @param  list<array{id:int,parent_id:int|null}>  $nodes
     */
    public function persistHierarchy(array $nodes): void
    {
        if ($nodes === []) {
            return;
        }

        DB::transaction(function () use ($nodes): void {
            foreach ($nodes as $node) {
                Category::query()
                    ->whereKey($node['id'])
                    ->update(['parent_id' => $node['parent_id']]);
            }

            /** @var array<int|string, list<int>> $byParent */
            $byParent = [];
            foreach ($nodes as $node) {
                $key = $node['parent_id'] === null ? 'root' : (string) $node['parent_id'];
                if (!isset($byParent[$key])) {
                    $byParent[$key] = [];
                }
                $byParent[$key][] = $node['id'];
            }

            foreach ($byParent as $ids) {
                foreach ($ids as $position => $id) {
                    Category::query()->whereKey($id)->update(['sort_order' => $position]);
                }
            }
        });
    }

    /**
     * @return list<CategoryTreeRow>
     */
    public function buildIndexRows(
        string $level,
        string $status,
        string $search,
        string $sort,
        string $order,
    ): array {
        if ($level === 'all' && $search === '') {
            return $this->buildTreeRows($status, $sort, $order);
        }

        return $this->buildFlatRows($level, $status, $search, $sort, $order);
    }

    /**
     * Flat options for parent select: DFS order, dash-indented labels.
     * When editing, pass the category id to exclude it and its entire subtree.
     *
     * @return list<array{id: int, depth: int, name: string, label: string, searchName: string}>
     */
    public function buildParentPickerOptions(?int $excludeSubtreeRootId): array
    {
        $excludeIds = [];
        if ($excludeSubtreeRootId !== null) {
            $excludeIds = array_merge(
                [$excludeSubtreeRootId],
                $this->collectDescendantIds($excludeSubtreeRootId)
            );
        }
        $exclude = array_flip($excludeIds);

        $categories = Category::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id', 'sort_order']);
        $categories = $categories->filter(fn (Category $c) => ! isset($exclude[$c->getKey()]));

        /** @var array<int|null, list<Category>> $byParent */
        $byParent = [];

        foreach ($categories as $category) {
            $parentKey = $category->parent_id;
            if (! isset($byParent[$parentKey])) {
                $byParent[$parentKey] = [];
            }

            $byParent[$parentKey][] = $category;
        }

        foreach ($byParent as $key => $siblings) {
            usort($byParent[$key], fn (Category $a, Category $b): int => $this->comparePickerSiblings($a, $b));
        }

        $options = [];
        foreach ($byParent[null] ?? [] as $root) {
            $this->appendPickerOptions($root, 0, $byParent, $options);
        }

        return $options;
    }

    /**
     * @return list<int>
     */
    private function collectDescendantIds(int $rootId): array
    {
        $ids = [];
        $queue = [$rootId];

        while ($queue !== []) {
            $id = array_shift($queue);
            $childIds = Category::query()->where('parent_id', $id)->pluck('id');

            foreach ($childIds as $childId) {
                $childId = (int) $childId;
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    private function comparePickerSiblings(Category $a, Category $b): int
    {
        $so = ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0);
        if ($so !== 0) {
            return $so;
        }

        $nameCmp = strcmp((string) $a->name, (string) $b->name);

        if ($nameCmp !== 0) {
            return $nameCmp;
        }

        return $a->getKey() <=> $b->getKey();
    }

    /**
     * @param  array<int|null, list<Category>>  $byParent
     * @param  list<array{id: int, depth: int, name: string, label: string, searchName: string}>  $options
     */
    private function appendPickerOptions(Category $node, int $depth, array $byParent, array &$options): void
    {
        $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';
        $label = $prefix.$node->name;

        $options[] = [
            'id' => (int) $node->getKey(),
            'depth' => $depth,
            'name' => (string) $node->name,
            'label' => $label,
            'searchName' => mb_strtolower((string) $node->name),
        ];

        foreach ($byParent[$node->getKey()] ?? [] as $child) {
            $this->appendPickerOptions($child, $depth + 1, $byParent, $options);
        }
    }

    /**
     * @return list<CategoryTreeRow>
     */
    private function buildTreeRows(string $status, string $sort, string $order): array
    {
        $categories = $this->baseQuery($status)
            ->with('parent')
            ->withCount(['children', 'courses'])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        /** @var array<int|null, list<Category>> $byParent */
        $byParent = [];

        foreach ($categories as $category) {
            $parentKey = $category->parent_id;
            if (! isset($byParent[$parentKey])) {
                $byParent[$parentKey] = [];
            }

            $byParent[$parentKey][] = $category;
        }

        foreach ($byParent as $key => $siblings) {
            usort($byParent[$key], fn (Category $a, Category $b): int => $this->compareCategories($a, $b, $sort, $order));
        }

        $rows = [];
        $roots = $byParent[null] ?? [];

        foreach ($roots as $root) {
            $this->appendDepthFirst($root, 0, $byParent, $rows);
        }

        return $rows;
    }

    /**
     * @param  array<int|null, list<Category>>  $byParent
     * @param  list<CategoryTreeRow>  $rows
     */
    private function appendDepthFirst(Category $node, int $depth, array $byParent, array &$rows): void
    {
        $rows[] = new CategoryTreeRow($node, $depth);
        $children = $byParent[$node->getKey()] ?? [];

        foreach ($children as $child) {
            $this->appendDepthFirst($child, $depth + 1, $byParent, $rows);
        }
    }

    /**
     * @return list<CategoryTreeRow>
     */
    private function buildFlatRows(
        string $level,
        string $status,
        string $search,
        string $sort,
        string $order,
    ): array {
        $query = $this->baseQuery($status)
            ->with('parent')
            ->withCount(['children', 'courses'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $inner) use ($search): void {
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

        $query->orderBy($sort, $order);

        if ($sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        $categories = $query->get();
        $depthMap = $this->buildDepthMap();

        $rows = [];

        foreach ($categories as $category) {
            $rows[] = new CategoryTreeRow($category, $depthMap[$category->getKey()] ?? 0);
        }

        return $rows;
    }

    private function baseQuery(string $status): Builder
    {
        $query = Category::query();

        if ($status !== '') {
            $query->where('status', $status);
        }

        return $query;
    }

    private function compareCategories(Category $a, Category $b, string $sort, string $order): int
    {
        $mult = $order === 'desc' ? -1 : 1;

        $cmp = match ($sort) {
            'id' => $a->getKey() <=> $b->getKey(),
            'name' => $this->compareBySortOrderThenName($a, $b, $order),
            'slug' => strcmp((string) $a->slug, (string) $b->slug),
            'status' => strcmp((string) $a->status, (string) $b->status),
            'children_count' => ($a->children_count ?? 0) <=> ($b->children_count ?? 0),
            'courses_count' => ($a->courses_count ?? 0) <=> ($b->courses_count ?? 0),
            default => $this->compareBySortOrderThenName($a, $b, $order),
        };

        if ($cmp !== 0) {
            return $cmp * $mult;
        }

        return $a->getKey() <=> $b->getKey();
    }

    /**
     * Siblings: sort_order ascending, then name per $order.
     */
    private function compareBySortOrderThenName(Category $a, Category $b, string $order): int
    {
        $so = ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0);
        if ($so !== 0) {
            return $so;
        }

        $nameCmp = strcmp((string) $a->name, (string) $b->name);

        if ($order === 'desc') {
            return -$nameCmp;
        }

        return $nameCmp;
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
