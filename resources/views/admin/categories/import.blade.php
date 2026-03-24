@extends('layouts.admin')

@section('title', 'Kategorie CSV-Import')
@section('breadcrumb', 'Kategorie CSV-Import')

@section('content')
    @php
        $mapping = old('mapping', $defaultMapping ?? []);
        $fallbackStatus = old('fallback_status', 'draft');
        $duplicateStrategy = old('duplicate_strategy', 'skip');
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Kategorie CSV-Import</h1>
                <p class="mt-1 text-sm text-slate-500">CSV hochladen, Felder mappen und Importstrategie im Dialog wählen.</p>
            </div>
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Kategorien-Übersicht
            </a>
        </div>

        @if ($errors->has('import') || $errors->has('upload_token') || $errors->has('mapping'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first('import') ?: $errors->first('upload_token') ?: $errors->first('mapping') }}
            </div>
        @endif

        <form method="post" action="{{ route('admin.taxonomy.categories.import.preview') }}" enctype="multipart/form-data"
            class="admin-panel space-y-4 p-5">
            @csrf
            <h2 class="text-lg font-semibold text-slate-900">1) CSV hochladen</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="csv_file">CSV-Datei</label>
                    <input id="csv_file" type="file" name="csv_file" required
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    @error('csv_file')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="delimiter">Trennzeichen</label>
                    <select id="delimiter" name="delimiter"
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value=";" @selected(old('delimiter', ';') === ';')>Semikolon (;)</option>
                        <option value="," @selected(old('delimiter') === ',')>Komma (,)</option>
                        <option value="|" @selected(old('delimiter') === '|')>Pipe (|)</option>
                        <option value="\t" @selected(old('delimiter') === '\t')>Tab</option>
                    </select>
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="has_header" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                    @checked(old('has_header', '1') === '1')>
                Erste Zeile enthält Header
            </label>
            <div>
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Vorschau laden
                </button>
            </div>
        </form>

        @if ($preview)
            <form method="post" action="{{ route('admin.taxonomy.categories.import.execute') }}"
                class="admin-panel space-y-5 p-5"
                x-data="{ confirmOpen: false, strategy: '{{ $duplicateStrategy }}' }">
                @csrf
                <input type="hidden" name="upload_token" value="{{ $preview['token'] }}">
                <input type="hidden" name="duplicate_strategy" :value="strategy">

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-sm text-slate-700">
                        <p><span class="font-semibold">Datei:</span> {{ $preview['file_name'] }}</p>
                        <p><span class="font-semibold">Datensätze:</span> {{ $preview['row_count'] }}</p>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-slate-900">2) Feld-Mapping</h2>
                    <p class="mt-1 text-sm text-slate-500">Pflichtfelder: <span class="font-semibold">name</span> und <span class="font-semibold">slug</span>.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach (['name', 'slug', 'description', 'parent_id', 'parent_slug', 'status'] as $field)
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">{{ $field }}</label>
                            <select name="mapping[{{ $field }}]"
                                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="">Ignorieren</option>
                                @foreach ($preview['headers'] as $index => $header)
                                    <option value="{{ $index }}" @selected((string) ($mapping[$field] ?? '') === (string) $index)>
                                        {{ $header }} (Spalte {{ $index + 1 }})
                                    </option>
                                @endforeach
                            </select>
                            @error('mapping.'.$field)
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="fallback_status">Fallback-Status</label>
                        <select id="fallback_status" name="fallback_status"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="draft" @selected($fallbackStatus === 'draft')>draft</option>
                            <option value="published" @selected($fallbackStatus === 'published')>published</option>
                            <option value="archived" @selected($fallbackStatus === 'archived')>archived</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600">CSV-Vorschau</h3>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    @foreach ($preview['headers'] as $header)
                                        <th class="px-3 py-2 font-semibold">{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                @forelse ($preview['sample_rows'] as $sampleRow)
                                    <tr>
                                        @foreach ($preview['headers'] as $index => $header)
                                            <td class="px-3 py-2">{{ $sampleRow[$index] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ max(1, count($preview['headers'])) }}" class="px-3 py-3 text-slate-500">Keine Vorschauzeilen verfügbar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <button type="button" @click="confirmOpen = true"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Import ausführen
                    </button>
                </div>

                <div x-cloak x-show="confirmOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50" @click="confirmOpen = false"></div>
                <div x-cloak x-show="confirmOpen" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div role="dialog" aria-modal="true" class="w-full max-w-xl rounded-2xl bg-white p-5 shadow-2xl" @click.stop>
                        <h3 class="text-lg font-semibold text-slate-900">Duplikat-Strategie wählen</h3>
                        <p class="mt-1 text-sm text-slate-500">Wie soll das System bei bereits existierendem Slug reagieren?</p>

                        <div class="mt-4 space-y-3">
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3">
                                <input type="radio" x-model="strategy" value="skip" class="mt-1">
                                <span class="text-sm text-slate-700"><span class="font-semibold">a) Überspringen:</span> bestehende Slugs werden nicht importiert.</span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3">
                                <input type="radio" x-model="strategy" value="update" class="mt-1">
                                <span class="text-sm text-slate-700"><span class="font-semibold">b) Aktualisieren:</span> bestehende Slugs werden per Upsert aktualisiert.</span>
                            </label>
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3">
                                <input type="radio" x-model="strategy" value="fail" class="mt-1">
                                <span class="text-sm text-slate-700"><span class="font-semibold">c) Abbrechen:</span> Import stoppt beim ersten Duplikat vollständig.</span>
                            </label>
                        </div>

                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" @click="confirmOpen = false"
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Zurück
                            </button>
                            <button type="submit"
                                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Jetzt importieren
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

        @if ($result)
            <div class="admin-panel space-y-3 p-5">
                <h2 class="text-lg font-semibold text-slate-900">Import-Ergebnis</h2>
                <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-5">
                    <div class="rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Gesamt</span><p class="font-semibold text-slate-900">{{ $result['summary']['total_rows'] }}</p></div>
                    <div class="rounded-lg bg-emerald-50 px-3 py-2"><span class="text-emerald-700">Neu</span><p class="font-semibold text-emerald-900">{{ $result['summary']['created'] }}</p></div>
                    <div class="rounded-lg bg-sky-50 px-3 py-2"><span class="text-sky-700">Aktualisiert</span><p class="font-semibold text-sky-900">{{ $result['summary']['updated'] }}</p></div>
                    <div class="rounded-lg bg-amber-50 px-3 py-2"><span class="text-amber-700">Übersprungen</span><p class="font-semibold text-amber-900">{{ $result['summary']['skipped'] }}</p></div>
                    <div class="rounded-lg bg-rose-50 px-3 py-2"><span class="text-rose-700">Fehler</span><p class="font-semibold text-rose-900">{{ count($result['errors']) }}</p></div>
                </div>
                @if (! empty($result['errors']))
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3">
                        <p class="text-sm font-medium text-rose-800">Fehlerzeilen</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-700">
                            @foreach ($result['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
