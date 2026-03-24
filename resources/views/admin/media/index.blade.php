@extends('layouts.admin')

@section('title', 'Medien')
@section('breadcrumb', 'Medien')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Medienverwaltung</h1>
                <p class="mt-1 text-sm text-slate-500">Dateien hochladen und für Kurse sowie Kategorien wiederverwenden.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Neue Datei hochladen</h2>
            <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="flex flex-col gap-4 md:flex-row md:items-end">
                @csrf
                <input type="hidden" name="search" value="{{ $search }}">
                <div class="min-w-0 flex-1">
                    <label for="media-file" class="mb-1 block text-sm font-medium text-slate-700">Datei *</label>
                    <input id="media-file" type="file" name="file" required accept="image/*,.svg"
                        class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                    @error('file')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="min-w-[200px] flex-1">
                    <label for="media-alt" class="mb-1 block text-sm font-medium text-slate-700">Alt-Text (optional)</label>
                    <input id="media-alt" type="text" name="alt_text" value="{{ old('alt_text') }}" maxlength="255"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    @error('alt_text')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Hochladen
                </button>
            </form>
        </div>

        <form method="get" action="{{ route('admin.media.index') }}" class="admin-panel flex flex-wrap items-center gap-3 p-4">
            <div class="relative min-w-[200px] flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Dateiname oder Alt-Text…"
                    class="w-full rounded-lg border-slate-300 pl-9 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </span>
            </div>
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Suchen</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Vorschau</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">ID</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Dateiname</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Typ</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-700">Alt-Text</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assets as $asset)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                @if (str_starts_with((string) $asset->mime_type, 'image/') || str_ends_with(strtolower($asset->file_name), '.svg'))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($asset->disk)->url($asset->file_path) }}" alt=""
                                        class="h-12 w-12 rounded border border-slate-200 object-cover">
                                @else
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded border border-slate-200 bg-slate-50 text-xs text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $asset->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $asset->file_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $asset->mime_type ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $asset->alt_text ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">Noch keine Medien. Laden Sie oben eine Datei hoch.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assets->hasPages())
            <div class="border-t border-slate-100 pt-4">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
@endsection
