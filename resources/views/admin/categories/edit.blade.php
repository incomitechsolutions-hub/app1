@extends('layouts.admin')

@section('title', 'Kategorie bearbeiten')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-slate-900">Kategorie bearbeiten</h1>
            <a href="{{ route('admin.taxonomy.categories.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Zur Übersicht
            </a>
        </div>

        <form method="post" action="{{ route('admin.taxonomy.categories.update', $category) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('admin.categories._form', ['category' => $category, 'parentOptions' => $parentOptions])

            <div class="admin-panel p-4">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Speichern
                </button>
            </div>
        </form>
    </div>
@endsection
