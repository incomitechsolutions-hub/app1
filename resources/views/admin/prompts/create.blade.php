@extends('layouts.admin')

@section('title', 'Prompt anlegen')
@section('breadcrumb', 'Prompt-Bibliothek')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <h1 class="text-3xl font-bold text-slate-900">Prompt anlegen</h1>
        <form method="post" action="{{ route('admin.prompt-management.prompts.store') }}" class="admin-panel space-y-6 p-6">
            @csrf
            @include('admin.prompts._form', ['prompt' => null, 'useCases' => $useCases])
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.prompt-management.prompts.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Abbrechen</a>
            </div>
        </form>
    </div>
@endsection
