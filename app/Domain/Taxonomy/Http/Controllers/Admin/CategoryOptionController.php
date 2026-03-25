<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Taxonomy\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryOptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $query = Category::query()
            ->orderBy('name')
            ->limit(80);

        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($sub) use ($like): void {
                $sub->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            });
        }

        $rows = $query->get(['id', 'name'])->map(fn (Category $c) => [
            'id' => $c->id,
            'name' => $c->name,
        ]);

        return response()->json(['data' => $rows]);
    }
}
