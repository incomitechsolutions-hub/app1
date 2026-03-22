@extends('layouts.admin')

@section('title', 'Kurs bearbeiten')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900">Kurs bearbeiten</h1>
        <a href="{{ route('admin.course-catalog.courses.show', $course) }}" class="text-sm text-slate-600 hover:text-slate-900">Anzeigen</a>
    </div>

    <form method="post" action="{{ route('admin.course-catalog.courses.update', $course) }}" class="mt-6 space-y-8">
        @csrf
        @method('PUT')
        @include('admin.courses._form', [
            'course' => $course,
            'categories' => $categories,
            'difficultyLevels' => $difficultyLevels,
            'tags' => $tags,
            'audiences' => $audiences,
        ])
        <div class="flex gap-3">
            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Speichern
            </button>
        </div>
    </form>
@endsection
