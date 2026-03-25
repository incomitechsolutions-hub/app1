@php
    $faqRows = old('faqs');
    if ($faqRows === null && $course) {
        $faqRows = $course->faqs->map(fn ($f) => [
            'question' => $f->question,
            'answer' => $f->answer,
            'sort_order' => $f->sort_order,
        ])->values()->all();
    }
    $faqRows = array_values($faqRows ?? []);
    if ($faqRows === []) {
        $faqRows = [['question' => '', 'answer' => '', 'sort_order' => 0]];
    }

    $relRows = old('course_relations');
    if ($relRows === null && $course) {
        $relRows = $course->courseRelations->map(fn ($r) => [
            'related_course_id' => $r->related_course_id,
            'relation_type' => $r->relation_type,
            'sort_order' => $r->sort_order,
        ])->values()->all();
    }
    $relRows = array_values($relRows ?? []);
    if ($relRows === []) {
        $relRows = [['related_course_id' => '', 'relation_type' => 'follow_up', 'sort_order' => 0]];
    }

    $currentCourseId = $course?->id;
@endphp

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Beschreibungen</h2>
    <div class="space-y-4">
        <div>
            <label for="short_description" class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
            <textarea id="short_description" name="short_description" rows="3"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('short_description', $course?->short_description) }}</textarea>
            @error('short_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="long_description" class="mb-1 block text-sm font-medium text-slate-700">Ausführliche Beschreibung</label>
            <input id="long_description" type="hidden" name="long_description" value="{{ old('long_description', $course?->long_description) }}">
            <trix-editor input="long_description" class="trix-content min-h-[200px] rounded-lg border border-slate-300 shadow-sm"></trix-editor>
            @error('long_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Zielgruppe &amp; Lernziele</h2>
    <div class="space-y-4">
        <div>
            <label for="target_audience_text" class="block text-sm font-medium text-slate-700">Zielgruppe</label>
            <textarea id="target_audience_text" name="target_audience_text" rows="4" placeholder="Für wen ist dieser Kurs geeignet?"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('target_audience_text', $course?->target_audience_text) }}</textarea>
            @error('target_audience_text')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="prerequisites_text" class="block text-sm font-medium text-slate-700">Voraussetzungen</label>
            <textarea id="prerequisites_text" name="prerequisites_text" rows="4" placeholder="Welche Vorkenntnisse werden benötigt?"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('prerequisites_text', $course?->prerequisites_text) }}</textarea>
            @error('prerequisites_text')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Kurs-Details</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="lessons_count" class="block text-sm font-medium text-slate-700">Anzahl Lektionen</label>
            <input id="lessons_count" name="lessons_count" type="number" min="0"
                value="{{ old('lessons_count', $course?->lessons_count) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('lessons_count')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="min_participants" class="block text-sm font-medium text-slate-700">Mindest-Teilnehmer</label>
            <input id="min_participants" name="min_participants" type="number" min="0"
                value="{{ old('min_participants', $course?->min_participants ?? $catalogDefaults->default_min_participants) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('min_participants')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="instructor_name" class="block text-sm font-medium text-slate-700">Dozent</label>
            <input id="instructor_name" name="instructor_name" type="text" placeholder="Name des Dozenten"
                value="{{ old('instructor_name', $course?->instructor_name) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('instructor_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="certificate_label" class="block text-sm font-medium text-slate-700">Zertifikat</label>
            <input id="certificate_label" name="certificate_label" type="text" placeholder="z. B. Zertifizierter Kurs"
                value="{{ old('certificate_label', $course?->certificate_label) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('certificate_label')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@include('admin.courses._form_content', ['course' => $course])

<div class="admin-panel space-y-4 p-6" x-data="{ faqs: {{ \Illuminate\Support\Js::from($faqRows) }} }">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-900">FAQ</h2>
        <button type="button" @click="faqs.push({ question: '', answer: '', sort_order: faqs.length })"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            + Zeile
        </button>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Frage</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Antwort</th>
                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, index) in faqs" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <input type="text" :name="'faqs[' + index + '][question]'" x-model="row.question"
                                class="w-full min-w-[8rem] rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2">
                            <textarea :name="'faqs[' + index + '][answer]'" x-model="row.answer" rows="2"
                                class="w-full min-w-[12rem] rounded border border-slate-200 px-2 py-1 text-sm"></textarea>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="faqs.splice(index, 1)" x-show="faqs.length > 1"
                                class="text-sm text-rose-600 hover:underline">Entfernen</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    @error('faqs')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="admin-panel space-y-4 p-6" x-data="{ rels: {{ \Illuminate\Support\Js::from($relRows) }} }">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-900">Verwandte Kurse</h2>
        <button type="button" @click="rels.push({ related_course_id: '', relation_type: 'follow_up', sort_order: rels.length })"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            + Zeile
        </button>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Kurs</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Beziehung</th>
                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, index) in rels" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <select :name="'course_relations[' + index + '][related_course_id]'" x-model="row.related_course_id"
                                class="w-full min-w-[12rem] rounded border border-slate-200 px-2 py-1 text-sm">
                                <option value="">—</option>
                                @foreach ($coursesForRelations as $oc)
                                    @if ($currentCourseId && (int) $oc->id === (int) $currentCourseId)
                                        @continue
                                    @endif
                                    <option value="{{ $oc->id }}">{{ $oc->title }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <select :name="'course_relations[' + index + '][relation_type]'" x-model="row.relation_type"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                                <option value="follow_up">Fortführung</option>
                                <option value="extension">Erweiterung</option>
                                <option value="complementary">Ergänzend</option>
                            </select>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="rels.splice(index, 1)" x-show="rels.length > 1"
                                class="text-sm text-rose-600 hover:underline">Entfernen</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@if ($course && $course->programs->isNotEmpty())
    <div class="admin-panel space-y-3 p-6">
        <h2 class="text-lg font-semibold text-slate-900">Programme</h2>
        <p class="text-sm text-slate-500">Dieser Kurs ist Bestandteil folgender Programme (Verwaltung unter „Programme“).</p>
        <ul class="list-inside list-disc text-sm text-slate-800">
            @foreach ($course->programs as $program)
                <li>
                    <a href="{{ route('admin.course-catalog.programs.edit', $program) }}" class="font-medium text-sky-600 hover:underline">{{ $program->title }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
