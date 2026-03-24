@extends('layouts.admin')

@section('title', 'Sprachen')
@section('breadcrumb', 'Sprachen')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Sprachen</h1>
                <p class="mt-1 text-sm text-slate-500">Content-Locales für Mehrsprachigkeit aktivieren und sortieren</p>
            </div>
            <a href="{{ route('admin.localization.locales.create') }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                + Hinzufügen
            </a>
        </div>

        <form method="get" action="{{ route('admin.localization.locales.index') }}"
            class="admin-panel flex flex-wrap items-center justify-end gap-3 p-4">
            <div class="relative min-w-[200px] flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Suchen..."
                    class="w-full rounded-lg border-slate-300 pl-9 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </span>
            </div>
            <select name="filter_status" onchange="this.form.submit()"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">Alle Status</option>
                <option value="1" @selected($filter_status === '1')>Aktiv</option>
                <option value="0" @selected($filter_status === '0')>Inaktiv</option>
            </select>
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Anwenden</button>
        </form>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">Gesamt</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">Aktiv</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">Inaktiv</p>
                <p class="mt-1 text-2xl font-bold text-slate-600">{{ $stats['inactive'] }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">ID</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Code</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Sortierung</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($locales as $locale)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-500">{{ $locale->id }}</td>
                            <td class="px-4 py-3 font-mono text-slate-900">{{ $locale->code }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $locale->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $locale->sort_order }}</td>
                            <td class="px-4 py-3">
                                @if ($locale->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Aktiv</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600">Inaktiv</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.localization.locales.edit', $locale) }}" class="mr-3 font-medium text-sky-600 hover:text-sky-800">Bearbeiten</a>
                                <form method="post" action="{{ route('admin.localization.locales.destroy', $locale) }}" class="inline" onsubmit="return confirm('Wirklich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-rose-600 hover:text-rose-800">Löschen</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Keine Sprachen angelegt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            {{ $locales->links() }}
        </div>
    </div>
@endsection
