@extends('layouts.admin')

@section('title', 'KI: Kursgenerator')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">KI-Kursgenerator</h1>
            <p class="mt-1 text-sm text-slate-500">Beschreiben Sie den gewünschten Kurs. Anschließend können Sie den Entwurf prüfen und speichern.</p>
        </div>

        @if (session('ai_error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ session('ai_error') }}</div>
        @endif

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generator.generate') }}" class="admin-panel space-y-6 p-6">
            @csrf
            <div>
                <label for="ai_prompt_id" class="block text-sm font-medium text-slate-700">Vorlage (optional)</label>
                <select id="ai_prompt_id" name="ai_prompt_id"
                    class="mt-1 block w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    <option value="">— Keine / nur Systemanweisung —</option>
                    @foreach ($templates as $t)
                        <option value="{{ $t->id }}" @selected(old('ai_prompt_id') == $t->id)>{{ $t->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="brief" class="block text-sm font-medium text-slate-700">Kursidee / Anforderungen</label>
                <textarea id="brief" name="brief" rows="10" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="Thema, Zielgruppe, Dauer, Besonderheiten …">{{ old('brief') }}</textarea>
                @error('brief')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Entwurf generieren
                </button>
                <a href="{{ route('admin.course-catalog.courses.create') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
