@extends('layouts.admin')

@section('title', $program->title)
@section('breadcrumb', 'Programme')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Programm bearbeiten</h1>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                @if ($program->status === 'published')
                    <a href="{{ route('public.programs.show', ['slug' => $program->slug]) }}" target="_blank" rel="noopener"
                        class="text-sky-600 hover:text-sky-800">Öffentliche Seite</a>
                @endif
                <a href="{{ route('admin.course-catalog.programs.index') }}"
                    class="text-slate-600 hover:text-slate-900">Zur Übersicht</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <form method="post" action="{{ route('admin.course-catalog.programs.update', $program) }}" class="admin-panel space-y-6 p-6">
            @csrf
            @method('PUT')
            @include('admin.programs._form', ['program' => $program, 'courses' => $courses])
            <div class="flex flex-wrap gap-3">
                <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.course-catalog.programs.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Abbrechen</a>
            </div>
        </form>

        <div class="admin-panel border-t border-rose-100 p-6">
            <form method="post" action="{{ route('admin.course-catalog.programs.destroy', $program) }}"
                onsubmit="return confirm('Programm wirklich löschen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 hover:bg-red-100">
                    Programm löschen
                </button>
            </form>
        </div>
    </div>
@endsection
