@extends('layouts.admin')

@section('title', $prompt->title)
@section('breadcrumb', 'Prompt-Bibliothek')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif
        <h1 class="text-3xl font-bold text-slate-900">Prompt bearbeiten</h1>
        <form method="post" action="{{ route('admin.prompt-management.prompts.update', $prompt) }}" class="admin-panel space-y-6 p-6">
            @csrf
            @method('PUT')
            @include('admin.prompts._form', ['prompt' => $prompt, 'useCaseSelectOptions' => $useCaseSelectOptions])
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Speichern</button>
                <a href="{{ route('admin.prompt-management.prompts.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Abbrechen</a>
            </div>
        </form>
        <div class="admin-panel border-t border-rose-100 p-6">
            <form method="post" action="{{ route('admin.prompt-management.prompts.destroy', $prompt) }}"
                onsubmit="return confirm('Prompt löschen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 hover:bg-red-100">Löschen</button>
            </form>
        </div>
    </div>
@endsection
