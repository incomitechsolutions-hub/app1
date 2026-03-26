@extends('layouts.admin')

@section('title', 'KI: Kursgenerierung')
@section('breadcrumb', 'Kurse')

@php
    $oldKw = old('keyword_data');
    $oldVariants = is_array($oldKw['keyword_variants'] ?? null) ? $oldKw['keyword_variants'] : [];
    $oldVariants = array_pad(array_slice($oldVariants, 0, 3), 3, '');
    $oldSupporting = is_array($oldKw['supporting_keywords'] ?? null) ? $oldKw['supporting_keywords'] : [];
@endphp

@section('content')
    <div class="mx-auto max-w-3xl space-y-6" x-data="aiGeneratorStart({
        selectedId: @js(old('ai_prompt_id', '')),
        primaryKeyword: @js(old('keyword_data.primary_keyword', '')),
        variants: @json($oldVariants),
        supporting: @json($oldSupporting),
    })">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">KI-Kursgenerator</h1>
            <p class="mt-1 text-sm text-slate-500">Wählen Sie eine Vorlage, füllen Sie Platzhalter und beschreiben Sie die Kursidee. Optional können Sie zuerst Keywords recherchieren und auswählen — diese fließen in den KI-Prompt ein. Nach „Sitzung anlegen“ wird der Entwurf generiert.</p>
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

            <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Keyword-Recherche (optional)</h2>
                        <p class="text-xs text-slate-500">Vorschläge aus Google Suggest, Bewertung und Auswahl für den Prompt.</p>
                    </div>
                    <button type="button" @click="fetchKeywords()"
                        :disabled="kwLoading"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 disabled:opacity-50">
                        <span x-show="!kwLoading">Keywords vorschlagen</span>
                        <span x-show="kwLoading" x-cloak>Lädt …</span>
                    </button>
                </div>
                <p x-show="kwError" x-text="kwError" class="text-sm text-red-600" x-cloak></p>

                <div x-show="ranked.length > 0" x-cloak class="space-y-3">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                        <table class="min-w-full divide-y divide-slate-200 text-xs">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-slate-600">Keyword</th>
                                    <th class="px-3 py-2 text-right font-medium text-slate-600">Score</th>
                                    <th class="px-3 py-2 text-right font-medium text-slate-600">Supporting</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="row in ranked" :key="row.keyword">
                                    <tr>
                                        <td class="px-3 py-2 text-slate-800" x-text="row.keyword"></td>
                                        <td class="px-3 py-2 text-right text-slate-600" x-text="row.score"></td>
                                        <td class="px-3 py-2 text-right">
                                            <input type="checkbox" class="rounded border-slate-300"
                                                :checked="isSupporting(row.keyword)"
                                                @change="toggleSupporting(row.keyword)">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <label for="keyword_primary" class="block text-sm font-medium text-slate-700">Primary Keyword</label>
                        <input type="text" id="keyword_primary" name="keyword_data[primary_keyword]" x-model="primaryKeyword"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Variante 1</label>
                            <input type="text" name="keyword_data[keyword_variants][0]" x-model="variants[0]"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Variante 2</label>
                            <input type="text" name="keyword_data[keyword_variants][1]" x-model="variants[1]"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Variante 3</label>
                            <input type="text" name="keyword_data[keyword_variants][2]" x-model="variants[2]"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                    </div>
                </div>

                <template x-for="kw in supporting" :key="kw">
                    <input type="hidden" name="keyword_data[supporting_keywords][]" :value="kw">
                </template>
            </div>
            @error('keyword_data')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

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

    <script>
        function aiGeneratorStart(config) {
            return {
                selectedId: config.selectedId ?? '',
                kwLoading: false,
                kwError: '',
                ranked: [],
                primaryKeyword: config.primaryKeyword ?? '',
                variants: Array.isArray(config.variants) ? config.variants.slice(0, 3) : ['', '', ''],
                supporting: Array.isArray(config.supporting) ? [...config.supporting] : [],
                async fetchKeywords() {
                    const briefEl = document.getElementById('brief');
                    const courseIdea = briefEl ? briefEl.value.trim() : '';
                    if (!courseIdea) {
                        this.kwError = 'Bitte zuerst die Kursidee eingeben.';
                        return;
                    }
                    this.kwLoading = true;
                    this.kwError = '';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    try {
                        const res = await fetch(@json(route('admin.course-catalog.courses.ai-generation.keyword-research')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token ?? '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ courseIdea }),
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const msg = data.message || data.errors?.courseIdea?.[0] || 'Anfrage fehlgeschlagen.';
                            throw new Error(msg);
                        }
                        this.ranked = data.ranked || [];
                        this.primaryKeyword = data.primary_keyword || '';
                        const v = (data.keyword_variants || []).slice(0, 3);
                        this.variants = [v[0] ?? '', v[1] ?? '', v[2] ?? ''];
                        this.supporting = [...(data.supporting_keywords || [])];
                    } catch (e) {
                        this.kwError = e.message || 'Fehler bei der Keyword-Recherche.';
                    } finally {
                        this.kwLoading = false;
                    }
                },
                toggleSupporting(kw) {
                    const i = this.supporting.indexOf(kw);
                    if (i >= 0) {
                        this.supporting.splice(i, 1);
                    } else {
                        this.supporting.push(kw);
                    }
                },
                isSupporting(kw) {
                    return this.supporting.includes(kw);
                },
            };
        }
    </script>
@endsection
