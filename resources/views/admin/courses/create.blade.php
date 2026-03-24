@extends('layouts.admin')

@section('title', 'Neuer Kurs')
@section('breadcrumb', 'Kurse')

@push('scripts')
    @vite(['resources/js/admin-course-editor.js'])
@endpush

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Neuer Kurs</h1>
                <p class="mt-1 text-sm text-slate-500">Legen Sie einen neuen Kurs an.</p>
            </div>
            <a href="{{ route('admin.course-catalog.courses.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Übersicht
            </a>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.store') }}" class="space-y-6" x-data="{ tab: 'content' }">
            @csrf
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
                <button type="button" @click="tab = 'content'"
                    :class="tab === 'content' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Content
                </button>
                <button type="button" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Details
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
        </form>
    </div>
@endsection
