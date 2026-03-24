<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Taxonomy\Models\Category;
use Illuminate\Database\Eloquent\Builder;

final class CategoryAdminTreeService
{
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
            'name' => strcmp((string) $a->name, (string) $b->name),
            'slug' => strcmp((string) $a->slug, (string) $b->slug),
            'status' => strcmp((string) $a->status, (string) $b->status),
            'children_count' => ($a->children_count ?? 0) <=> ($b->children_count ?? 0),
            'courses_count' => ($a->courses_count ?? 0) <=> ($b->courses_count ?? 0),
            default => strcmp((string) $a->name, (string) $b->name),
        };

        if ($cmp !== 0) {
            return $cmp * $mult;
        }

        return $a->getKey() <=> $b->getKey();
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
