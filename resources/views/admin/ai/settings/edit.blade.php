@extends('layouts.admin')

@section('title', 'KI-Einstellungen')
@section('breadcrumb', 'KI-Einstellungen')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">KI-Einstellungen</h1>
            <p class="mt-1 text-sm text-slate-500">
                OpenAI (ChatGPT-kompatibel): API-Key und Modell für spätere Kurs-Assistenten. Der Key wird verschlüsselt gespeichert.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if (session('ai_test_error'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                {{ session('ai_test_error') }}
            </div>
        @endif

        @if (session('ai_test_reply'))
            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-sky-800">Antwort (Test)</p>
                <pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap font-sans text-sm">{{ session('ai_test_reply') }}</pre>
            </div>
        @endif

        <form method="post" action="{{ route('admin.ai.settings.update') }}" class="admin-panel space-y-6 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="openai_api_key" class="block text-sm font-medium text-slate-700">OpenAI API-Key</label>
                <input id="openai_api_key" name="openai_api_key" type="password" autocomplete="off"
                    value="{{ old('openai_api_key') }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="@if ($settings->hasOpenAiApiKey())Neuen Key eintragen (leer lassen = unverändert)@else sk-…@endif">
                <p class="mt-1 text-xs text-slate-500">
                    @if ($settings->hasOpenAiApiKey())
                        <span class="text-emerald-700">Es ist ein API-Key gespeichert.</span>
                    @else
                        Noch kein Key gespeichert.
                    @endif
                    Erstellbar unter
                    <a href="https://platform.openai.com/api-keys" class="text-sky-600 underline" target="_blank" rel="noopener">OpenAI API-Keys</a>.
                </p>
                @error('openai_api_key')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="default_model" class="block text-sm font-medium text-slate-700">Standard-Modell</label>
                <input id="default_model" name="default_model" type="text" required
                    value="{{ old('default_model', $settings->default_model) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="gpt-4o-mini">
                <p class="mt-1 text-xs text-slate-500">z. B. <code class="rounded bg-slate-100 px-1">gpt-4o-mini</code> oder <code class="rounded bg-slate-100 px-1">gpt-4o</code></p>
                @error('default_model')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="openai_base_url" class="block text-sm font-medium text-slate-700">API-Basis-URL</label>
                <input id="openai_base_url" name="openai_base_url" type="url" required
                    value="{{ old('openai_base_url', $settings->openai_base_url) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="https://api.openai.com/v1">
                <p class="mt-1 text-xs text-slate-500">Für OpenAI Standard: <code class="rounded bg-slate-100 px-1">https://api.openai.com/v1</code></p>
                @error('openai_base_url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Speichern
                </button>
            </div>
        </form>

        <div class="admin-panel space-y-4 p-6">
            <h2 class="text-lg font-semibold text-slate-900">Verbindung testen</h2>
            <p class="text-sm text-slate-600">Sendet eine kurze Nachricht an die Chat-Completions-API und zeigt die Antwort an (gespeicherter Key und Einstellungen oben).</p>

            <form method="post" action="{{ route('admin.ai.settings.test') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="test_message" class="block text-sm font-medium text-slate-700">Testnachricht</label>
                    <textarea id="test_message" name="test_message" rows="3" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        placeholder="Antworte in einem Satz: Was ist 2+2?">{{ old('test_message', 'Antworte nur mit dem Wort: OK') }}</textarea>
                    @error('test_message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="api_key_override" class="block text-sm font-medium text-slate-700">API-Key nur für diesen Test (optional)</label>
                    <input id="api_key_override" name="api_key_override" type="password" autocomplete="off"
                        value="{{ old('api_key_override') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        placeholder="Leer = gespeicherter Key">
                    @error('api_key_override')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    class="inline-flex rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-900 hover:bg-sky-100">
                    Verbindung testen
                </button>
            </form>
        </div>
    </div>
@endsection
