@extends('layouts.admin')

@section('title', 'Neuer Kurs')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Neuer Kurs</h1>
            <a href="{{ route('admin.course-catalog.courses.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Übersicht
            </a>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.courses.store') }}" class="space-y-6">
            @csrf
            @include('admin.courses._form', [
                'course' => null,
                'categories' => $categories,
                'difficultyLevels' => $difficultyLevels,
                'tags' => $tags,
                'audiences' => $audiences,
            ])
            <div class="admin-panel p-4">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Speichern
                </button>
            </div>
        </form>
    </div>
@endsection
