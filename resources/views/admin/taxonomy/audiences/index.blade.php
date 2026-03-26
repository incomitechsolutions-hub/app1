@extends('layouts.admin')

@section('title', 'Zielgruppen (Taxonomie)')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Zielgruppen (Taxonomie)</h1>
            <a href="{{ route('admin.taxonomy.audiences.create') }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Neu anlegen
            </a>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        <div class="admin-panel overflow-hidden rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-slate-700">Name</th>
                        <th class="px-4 py-2 text-left font-medium text-slate-700">Slug</th>
                        <th class="px-4 py-2 text-right font-medium text-slate-700">Aktionen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($audiences as $audience)
                        <tr class="hover:bg-slate-50/70"
                            data-edit-url="{{ route('admin.taxonomy.audiences.edit', $audience) }}"
                            title="Zeile anklicken zum Bearbeiten">
                            <td class="px-4 py-2 font-medium text-slate-900">{{ $audience->name }}</td>
                            <td class="px-4 py-2 font-mono text-slate-600">{{ $audience->slug }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.taxonomy.audiences.edit', $audience) }}" class="text-sky-600 hover:underline">Bearbeiten</a>
                                <form method="post" action="{{ route('admin.taxonomy.audiences.destroy', $audience) }}" class="ml-3 inline"
                                    onsubmit="return confirm('Löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline">Löschen</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500">Noch keine Einträge.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
