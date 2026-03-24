@extends('layouts.admin')

@section('title', 'Neue Kategorie')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Neue Kategorie</h1>
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Übersicht
            </a>
        </div>

        @if ($presetParentId)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Unterkategorie erstellen: Parent-Kategorie wurde bereits vorausgewählt.
            </div>
        @endif

        <form method="post" action="{{ route('admin.taxonomy.categories.store') }}" class="space-y-6">
            @csrf
            @include('admin.categories._form_create', [
                'parentPickerOptions' => $parentPickerOptions,
                'presetParentId' => $presetParentId,
            ])

            <div class="flex flex-wrap items-center justify-end gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm md:px-6">
                <a href="{{ route('admin.taxonomy.categories.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Abbrechen
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Speichern
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin-category-parent-select.js'])
@endpush
