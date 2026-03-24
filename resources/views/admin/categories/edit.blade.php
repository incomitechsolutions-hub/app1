@extends('layouts.admin')

@section('title', 'Kategorie bearbeiten')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kategorie bearbeiten</h1>
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Übersicht
            </a>
        </div>

        <form method="post" action="{{ route('admin.taxonomy.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-6"
            x-data="{ tab: 'details' }">
            @csrf
            @method('PUT')
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
                <button type="button" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Details
                </button>
                <button type="button" @click="tab = 'media'"
                    :class="tab === 'media' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Medien
                </button>
                <button type="button" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    SEO
                </button>
            </div>

            <div x-show="tab === 'details'">
                @include('admin.categories._form', ['category' => $category, 'parentPickerOptions' => $parentPickerOptions])
            </div>
            <div x-show="tab === 'media'" x-cloak>
                @include('admin.categories._form_media', ['mediaAssets' => $mediaAssets, 'category' => $category])
            </div>
            <div x-show="tab === 'seo'" x-cloak>
                @include('admin.seo._form', ['seoMeta' => $seoMeta, 'mediaAssets' => $mediaAssets])
            </div>

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
