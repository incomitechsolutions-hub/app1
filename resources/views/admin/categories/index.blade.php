@extends('layouts.admin')

@section('title', 'Kategorien')
@section('breadcrumb', 'Kategorien')

@section('content')
    @php
        $params = request()->query();
        $sortUrl = function (string $column) use ($params, $sort, $order): string {
            $nextOrder = $sort === $column && $order === 'asc' ? 'desc' : 'asc';
            return route('admin.taxonomy.categories.index', array_merge($params, ['sort' => $column, 'order' => $nextOrder]));
        };
        $statusLabel = function (string $status): string {
            return match ($status) {
                'draft' => 'Entwurf',
                'published' => 'Veröffentlicht',
                'archived' => 'Archiviert',
                default => $status,
            };
        };
        $statusBadgeClass = function (string $status): string {
            return match ($status) {
                'published' => 'bg-emerald-100 text-emerald-700',
                'archived' => 'bg-slate-200 text-slate-600',
                default => 'bg-amber-100 text-amber-700',
            };
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="rounded-xl border bg-white p-4 shadow-sm transition hover:border-sky-200 hover:shadow {{ $level === 'all' ? 'border-sky-300' : 'border-slate-200' }}">
                <p class="text-xs text-slate-500">Gesamt</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['all'] }}</p>
            </a>
            <a href="{{ route('admin.taxonomy.categories.main') }}"
                class="rounded-xl border bg-white p-4 shadow-sm transition hover:border-sky-200 hover:shadow {{ $level === 'root' ? 'border-sky-300' : 'border-slate-200' }}">
                <p class="text-xs text-slate-500">Hauptkategorien</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['root'] }}</p>
            </a>
            <a href="{{ route('admin.taxonomy.categories.sub') }}"
                class="rounded-xl border bg-white p-4 shadow-sm transition hover:border-sky-200 hover:shadow {{ $level === 'child' ? 'border-sky-300' : 'border-slate-200' }}">
                <p class="text-xs text-slate-500">Unterkategorien</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['child'] }}</p>
            </a>
        </div>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Kategorien</h1>
                <p class="mt-1 text-sm text-slate-500">Verwaltung von Kurskategorien und Unterkategorien</p>
            </div>
            <div class="inline-flex items-center gap-2">
                <a href="{{ route('admin.taxonomy.categories.import') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Einstellungen
                </a>
                <a href="{{ route('admin.taxonomy.categories.create') }}"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Neue Kategorie
                </a>
            </div>
        </div>

        <form method="get" action="{{ route('admin.taxonomy.categories.index') }}"
            class="admin-panel flex flex-wrap items-center gap-3 p-4">
            <input type="hidden" name="level" value="{{ $level }}">
            <div class="min-w-[240px] flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Suchen (Name, Slug, Beschreibung)..."
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <select name="status"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">Alle Status</option>
                <option value="draft" @selected($status === 'draft')>Entwurf</option>
                <option value="published" @selected($status === 'published')>Veröffentlicht</option>
                <option value="archived" @selected($status === 'archived')>Archiviert</option>
            </select>
            <select name="order"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="asc" @selected($order === 'asc')>Aufsteigend</option>
                <option value="desc" @selected($order === 'desc')>Absteigend</option>
            </select>
            <button type="submit"
                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Anwenden
            </button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('id') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                ID
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Name
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('slug') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Slug
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Parent</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('children_count') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Kinder
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('courses_count') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Kurse
                            </a>
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">
                            <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1 hover:text-slate-900">
                                Status
                            </a>
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        @php
                            $depth = $depthMap[$category->id] ?? 0;
                        @endphp
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-500">{{ $category->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        @if ($category->parent_id)
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6 6.75A.75.75 0 0 1 6.75 6h6.5a.75.75 0 0 1 0 1.5H7.5v5.75A.75.75 0 0 1 6 13.25V6.75Z"/><path d="M12.25 10.5a.75.75 0 0 1 1.06 0l2 2a.75.75 0 0 1 0 1.06l-2 2a.75.75 0 1 1-1.06-1.06L13.72 13l-1.47-1.44a.75.75 0 0 1 0-1.06Z"/></svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5.25A2.25 2.25 0 0 1 4.25 3h3.568a2.25 2.25 0 0 1 1.591.659l.932.932a.75.75 0 0 0 .53.219h5.879A2.25 2.25 0 0 1 19 7.06v7.69A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Z"/></svg>
                                        @endif
                                    </span>
                                    <span class="ml-3 font-medium text-slate-900" style="padding-left: {{ $depth * 14 }}px">
                                        {{ $category->name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->parent?->name ?? 'Hauptkategorie' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->children_count }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->courses_count }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadgeClass($category->status) }}">
                                    {{ $statusLabel($category->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('admin.taxonomy.categories.create', ['parent_id' => $category->id]) }}"
                                        class="font-medium text-emerald-600 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-700">
                                        Unterkategorie
                                    </a>
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
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Keine Kategorien für den aktuellen Filter gefunden.</td>
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
