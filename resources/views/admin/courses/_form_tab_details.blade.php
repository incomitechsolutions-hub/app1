@php
    $tierRows = old('course_discount_tiers');
    if ($tierRows === null && $course) {
        $tierRows = $course->discountTiers->map(fn ($t) => [
            'min_participants' => $t->min_participants,
            'discount_percent' => $t->discount_percent,
            'sort_order' => $t->sort_order,
        ])->values()->all();
    }
    $tierRows = $tierRows ?? [];
    $tierRows = array_pad(array_values($tierRows), 8, []);
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

@include('admin.courses._form_content', ['course' => $course])

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Preisstaffelung (Kurs-Level)</h2>
    <p class="text-sm text-slate-500">
        Gilt nur, wenn am Kurs ein Preis gesetzt ist. Ohne Kurs-Staffeln ist der Rabatt 0%.
    </p>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-slate-700">#</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-700">Mindest-Teilnehmer</th>
                    <th class="px-4 py-2 text-left font-medium text-slate-700">Rabatt (%)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($tierRows as $i => $row)
                    <tr>
                        <td class="px-4 py-2 text-slate-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-2">
                            <input type="number" min="1" name="course_discount_tiers[{{ $i }}][min_participants]"
                                value="{{ $row['min_participants'] ?? '' }}"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" min="0" max="100" name="course_discount_tiers[{{ $i }}][discount_percent]"
                                value="{{ $row['discount_percent'] ?? '' }}"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
