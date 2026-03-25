<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Taxonomy\Models\Tag;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::query()->orderBy('name')->get();

        return view('admin.taxonomy.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.taxonomy.tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tags,slug'],
        ]);
        $slug = $data['slug'] ?? Str::slug($data['name']);
        if (Tag::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }
        Tag::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.taxonomy.tags.index')
            ->with('status', __('Taxonomy Skill wurde angelegt.'));
    }

    public function edit(Tag $tag): View
    {
        return view('admin.taxonomy.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tags,slug,'.$tag->id],
        ]);
        $tag->update($data);

        return redirect()
            ->route('admin.taxonomy.tags.index')
            ->with('status', __('Taxonomy Skill wurde aktualisiert.'));
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()
            ->route('admin.taxonomy.tags.index')
            ->with('status', __('Taxonomy Skill wurde gelöscht.'));
    }

    public function storeQuick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $base = Str::slug($data['name']);
        $slug = $base;
        $n = 0;
        while (Tag::query()->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }
        $tag = Tag::query()->create([
            'name' => trim($data['name']),
            'slug' => $slug,
        ]);

        return response()->json([
            'tag' => ['id' => $tag->id, 'name' => $tag->name],
        ], 201);
    }
}
