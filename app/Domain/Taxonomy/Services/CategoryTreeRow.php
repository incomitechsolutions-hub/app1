<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Taxonomy\Models\Category;

final readonly class CategoryTreeRow
{
    public function __construct(
        public Category $category,
        public int $depth,
    ) {}
}
