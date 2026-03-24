@extends('layouts.admin')

@section('title', 'Kurs bearbeiten')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kurs bearbeiten</h1>
            <a href="{{ route('admin.course-catalog.courses.show', $course) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Anzeigen
            </a>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.update', $course) }}" class="space-y-6" x-data="{ tab: 'details' }">
            @csrf
            @method('PUT')
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-1">
                <button type="button" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Details &amp; Taxonomie
                </button>
                <button type="button" @click="tab = 'marketing'"
                    :class="tab === 'marketing' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Preis &amp; Links
                </button>
                <button type="button" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    SEO
                </button>
                <button type="button" @click="tab = 'content'"
                    :class="tab === 'content' ? 'border-sky-600 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800'"
                    class="inline-flex items-center rounded-t-lg border-b-2 px-4 py-2 text-sm font-semibold transition">
                    Inhalt
                </button>
            </div>

            <div x-show="tab === 'details'">
                @include('admin.courses._form_details', [
                    'course' => $course,
                    'categories' => $categories,
                    'difficultyLevels' => $difficultyLevels,
                    'tags' => $tags,
                    'audiences' => $audiences,
                ])
            </div>
            <div x-show="tab === 'marketing'" x-cloak>
                @include('admin.courses._form_marketing', ['course' => $course])
            </div>
            <div x-show="tab === 'seo'" x-cloak>
                @include('admin.seo._form', ['seoMeta' => $seoMeta, 'mediaAssets' => $mediaAssets])
            </div>
            <div x-show="tab === 'content'" x-cloak>
                @include('admin.courses._form_content', ['course' => $course])
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
