@extends('layouts.admin')

@section('title', 'Kategorien · Einstellungen')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Taxonomie · Einstellungen</h1>
                <p class="mt-1 text-sm text-slate-500">Standardwerte für neue Kategorien (CSV-Import und manuelle Anlage).</p>
            </div>
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Kategorienliste
            </a>
        </div>

        <form method="post" action="{{ route('admin.taxonomy.category-taxonomy-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Standard-Status</h2>
                <div class="max-w-md">
                    <label for="default_new_category_status" class="mb-1 block text-sm font-medium text-slate-700">Status für neue Kategorien</label>
                    <select id="default_new_category_status" name="default_new_category_status" required
                        class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="draft" @selected(old('default_new_category_status', $settings->default_new_category_status) === 'draft')>Entwurf</option>
                        <option value="published" @selected(old('default_new_category_status', $settings->default_new_category_status) === 'published')>Veröffentlicht</option>
                        <option value="archived" @selected(old('default_new_category_status', $settings->default_new_category_status) === 'archived')>Archiviert</option>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Wird im Formular „Neue Kategorie“ als vorausgewählter Status verwendet.</p>
                    @error('default_new_category_status')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Speichern
                </button>
            </div>
        </form>
    </div>
@endsection
