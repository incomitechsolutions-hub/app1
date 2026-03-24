@extends('layouts.admin')

@section('title', 'Neue Kategorie')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
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
            @include('admin.categories._form', [
                'category' => null,
                'parentOptions' => $parentOptions,
                'presetParentId' => $presetParentId,
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
