@extends('layouts.admin')

@section('title', 'Kategorien')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kategorien</h1>
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 text-sm">
                    <a href="{{ route('admin.taxonomy.categories.index') }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ $level === 'all' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Alle
                    </a>
                    <a href="{{ route('admin.taxonomy.categories.main') }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ $level === 'root' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Hauptkategorien
                    </a>
                    <a href="{{ route('admin.taxonomy.categories.sub') }}"
                        class="rounded-md px-3 py-1.5 font-medium transition {{ $level === 'child' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                        Unterkategorien
                    </a>
                </div>
                <a href="{{ route('admin.taxonomy.categories.create') }}"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Neue Kategorie
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Parent</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Kurse</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Unterkategorien</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @switch($category->status)
                                    @case('draft')
                                        Entwurf
                                        @break
                                    @case('published')
                                        Veröffentlicht
                                        @break
                                    @case('archived')
                                        Archiviert
                                        @break
                                    @default
                                        {{ $category->status }}
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->courses_count }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->children_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('admin.taxonomy.categories.edit', $category) }}"
                                        class="font-medium text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-900">
                                        Bearbeiten
                                    </a>
                                    <form method="post" action="{{ route('admin.taxonomy.categories.destroy', $category) }}"
                                        onsubmit="return confirm('Kategorie wirklich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="font-medium text-rose-600 underline decoration-rose-300 underline-offset-4 hover:text-rose-700">
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Noch keine Kategorien vorhanden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
