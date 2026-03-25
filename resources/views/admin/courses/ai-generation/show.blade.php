@extends('layouts.admin')

@section('title', 'KI-Generierungssitzung')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Generierungssitzung #{{ $session->id }}</h1>
                <p class="mt-1 text-sm text-slate-600">Status: {{ $session->status->value }} · Gültig bis:
                    {{ $session->expires_at?->format('d.m.Y H:i') ?? '—' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($session->draft_payload && $session->status->value === 'in_review')
                    <a href="{{ route('admin.course-catalog.courses.ai-generation.wizard', $session) }}"
                        class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-900 hover:bg-sky-100">Zum Review-Wizard</a>
                @endif
                <a href="{{ route('admin.course-catalog.courses.ai-generation.create') }}"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:bg-slate-50">Neue Sitzung</a>
                <a href="{{ route('admin.course-catalog.courses.index') }}"
                    class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Zur Kursübersicht</a>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
        @endif

        <div class="admin-panel space-y-4 p-6">
            <h2 class="text-lg font-medium text-slate-900">Kursidee</h2>
            <p class="whitespace-pre-wrap text-sm text-slate-800">{{ $session->brief }}</p>
        </div>

        @if ($session->interpolated_body)
            <div class="admin-panel space-y-4 p-6">
                <h2 class="text-lg font-medium text-slate-900">Vorlage nach Platzhalter-Ersetzung</h2>
                <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-lg bg-slate-50 p-4 text-xs text-slate-800">{{ $session->interpolated_body }}</pre>
            </div>
        @endif

        <div class="admin-panel space-y-4 p-6">
            <h2 class="text-lg font-medium text-slate-900">Zusammengestellter Prompt</h2>
            <p class="text-sm text-slate-600">Eingabe an die KI (nach erfolgreichem Lauf liegt der strukturierte Entwurf im Wizard).</p>
            <pre class="max-h-[28rem] overflow-auto whitespace-pre-wrap rounded-lg bg-slate-50 p-4 text-xs text-slate-800">{{ $session->compiled_prompt }}</pre>
        </div>
    </div>
@endsection
