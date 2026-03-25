@extends('layouts.admin')

@section('title', 'Programm anlegen')
@section('breadcrumb', 'Programme')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Programm anlegen</h1>
            <a href="{{ route('admin.course-catalog.programs.index') }}"
                class="text-sm text-slate-600 hover:text-slate-900">Zur Übersicht</a>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.programs.store') }}" class="admin-panel space-y-6 p-6">
            @csrf
            @include('admin.programs._form', ['program' => null, 'courses' => $courses])
            <div class="flex flex-wrap gap-3">
                <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.course-catalog.programs.index') }}"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Abbrechen</a>
            </div>
        </form>
    </div>
@endsection
