<?php

namespace App\Domain\Taxonomy\Http\Controllers\Admin;

use App\Domain\Taxonomy\Models\Audience;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AudienceController extends Controller
{
    public function index(): View
    {
        $audiences = Audience::query()->orderBy('name')->get();

        return view('admin.taxonomy.audiences.index', compact('audiences'));
    }

    public function create(): View
    {
        return view('admin.taxonomy.audiences.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:audiences,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $slug = $data['slug'] ?? Str::slug($data['name']);
        if (Audience::query()->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }
        Audience::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('admin.taxonomy.audiences.index')
            ->with('status', __('Zielgruppe wurde angelegt.'));
    }

    public function edit(Audience $audience): View
    {
        return view('admin.taxonomy.audiences.edit', compact('audience'));
    }

    public function update(Request $request, Audience $audience): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:audiences,slug,'.$audience->id],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $audience->update($data);

        return redirect()
            ->route('admin.taxonomy.audiences.index')
            ->with('status', __('Zielgruppe wurde aktualisiert.'));
    }

    public function destroy(Audience $audience): RedirectResponse
    {
        $audience->delete();

        return redirect()
            ->route('admin.taxonomy.audiences.index')
            ->with('status', __('Zielgruppe wurde gelöscht.'));
    }

    public function storeQuick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $base = Str::slug($data['name']);
        $slug = $base;
        $n = 0;
        while (Audience::query()->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }
        $audience = Audience::query()->create([
            'name' => trim($data['name']),
            'slug' => $slug,
            'description' => null,
        ]);

        return response()->json([
            'audience' => ['id' => $audience->id, 'name' => $audience->name],
        ], 201);
    }
}
