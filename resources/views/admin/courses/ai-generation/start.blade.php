@extends('layouts.admin')

@section('title', 'KI: Kursgenerierung')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6" x-data="{ selectedId: '{{ old('ai_prompt_id', '') }}' }">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">KI-Kursgenerator</h1>
            <p class="mt-1 text-sm text-slate-500">Wählen Sie eine Vorlage, füllen Sie Platzhalter und beschreiben Sie die Kursidee. Es wird eine Generierungssitzung angelegt — ohne KI-Aufruf und ohne Speichern als Kurs.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <form method="post" action="{{ route('admin.course-catalog.courses.ai-generation.store') }}" class="admin-panel space-y-6 p-6">
            @csrf
            <div>
                <label for="ai_prompt_id" class="block text-sm font-medium text-slate-700">Vorlage (optional)</label>
                <select id="ai_prompt_id" name="ai_prompt_id" x-model="selectedId"
                    class="mt-1 block w-full max-w-xl rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    <option value="">— Keine / nur Kursidee —</option>
                    @foreach ($templates as $t)
                        <option value="{{ $t->id }}">{{ $t->title }}</option>
                    @endforeach
                </select>
                @error('ai_prompt_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @foreach ($templates as $t)
                @php($keys = $templateMeta[$t->id]['keys'] ?? [])
                @if (count($keys) > 0)
                    <div x-show="selectedId === '{{ (string) $t->id }}'" x-cloak class="space-y-4 rounded-lg border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-sm font-medium text-slate-800">Platzhalter für „{{ $t->title }}“</p>
                        @foreach ($keys as $key)
                            <div>
                                <label for="placeholder_{{ $t->id }}_{{ $key }}" class="block text-sm font-medium text-slate-700">{{ $key }}</label>
                                <input id="placeholder_{{ $t->id }}_{{ $key }}" type="text" name="placeholders[{{ $key }}]"
                                    value="{{ old('placeholders.'.$key) }}"
                                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                                @error('placeholders.'.$key)
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach

            <div>
                <label for="brief" class="block text-sm font-medium text-slate-700">Kursidee / Anforderungen</label>
                <textarea id="brief" name="brief" rows="10" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="Thema, Zielgruppe, Dauer, Besonderheiten …">{{ old('brief') }}</textarea>
                @error('brief')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Sitzung anlegen
                </button>
                <a href="{{ route('admin.course-catalog.courses.create') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
@endsection
