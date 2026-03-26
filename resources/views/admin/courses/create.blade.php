@extends('layouts.admin')

@section('title', 'Neuer Kurs')
@section('breadcrumb', 'Kurse')

@push('scripts')
    @vite(['resources/js/admin-course-editor.js'])
@endpush

@section('content')
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ tab: 'content', crawlOpen: false }">
        @if (session('crawl_info'))
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-slate-800">
                {{ session('crawl_info') }}
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Neuer Kurs</h1>
                <p class="mt-1 text-sm text-slate-500">Legen Sie einen neuen Kurs an.</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2">
                <a href="{{ route('admin.course-catalog.courses.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Zur Übersicht
                </a>
                <a href="{{ route('admin.course-catalog.courses.ai-generation.create') }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-900 transition hover:bg-sky-100">
                    AI Generator
                </a>
                <button type="button" @click="crawlOpen = true"
                    class="inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-900 transition hover:bg-violet-100">
                    Webseite crawlen
                </button>
            </div>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.store') }}" class="space-y-6">
            @csrf
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
                <button type="button" @click="tab = 'content'"
                    :class="tab === 'content' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Basiseinstellungen
                </button>
                <button type="button" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Details
                </button>
                <button type="button" @click="tab = 'pricing'"
                    :class="tab === 'pricing' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Preis
                </button>
                <button type="button" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    SEO
                </button>
                <button type="button" @click="tab = 'media'"
                    :class="tab === 'media' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Media
                </button>
            </div>

            <div x-show="tab === 'content'">
                @include('admin.courses._form_tab_content', [
                    'course' => null,
                    'categories' => $categories,
                    'difficultyLevels' => $difficultyLevels,
                    'tags' => $tags,
                    'audiences' => $audiences,
                    'catalogDefaults' => $catalogDefaults,
                ])
            </div>
            <div x-show="tab === 'details'" x-cloak>
                @include('admin.courses._form_tab_details', [
                    'course' => null,
                    'catalogDefaults' => $catalogDefaults,
                    'coursesForRelations' => $coursesForRelations,
                ])
            </div>
            <div x-show="tab === 'pricing'" x-cloak>
                @include('admin.courses._form_tab_pricing', [
                    'course' => null,
                    'catalogDefaults' => $catalogDefaults,
                ])
            </div>
            <div x-show="tab === 'seo'" x-cloak>
                @include('admin.seo._form_course', [
                    'seoMeta' => $seoMeta,
                    'mediaAssets' => $mediaAssets,
                    'course' => null,
                ])
            </div>
            <div x-show="tab === 'media'" x-cloak>
                @include('admin.courses._form_tab_media', ['course' => null])
            </div>

            <div class="admin-panel p-4">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Speichern
                </button>
            </div>

            <div x-cloak x-show="crawlOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
                @keydown.escape.window="crawlOpen = false">
                <div class="max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl" @click.outside="crawlOpen = false">
                    <h2 class="text-lg font-semibold text-slate-900">Webseite crawlen</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Geben Sie eine URL ein. Die Seite wird ausgelesen und die SEO-relevanten Inhalte (z. B. H1, Meta, Headings) werden für den KI-Wizard verwendet.
                    </p>
                    <form method="post" action="{{ route('admin.course-catalog.courses.crawl-from-website') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="source_url" class="block text-sm font-medium text-slate-700">URL</label>
                            <input id="source_url" name="source_url" type="url" required placeholder="https://…"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                            @error('source_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit"
                                class="rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800">
                                Webseite crawlen
                            </button>
                            <button type="button" @click="crawlOpen = false"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Schließen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </form>
    </div>
@endsection
