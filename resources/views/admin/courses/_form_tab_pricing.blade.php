@php
    $defCurrency = old('currency_code', $course?->currency_code ?? $catalogDefaults->default_currency ?? 'EUR');
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
    <h2 class="text-lg font-semibold text-slate-900">Preis</h2>
    <div class="grid gap-4 sm:grid-cols-2 max-w-xl">
        <div>
            <label for="price_tab" class="block text-sm font-medium text-slate-700">Listenpreis</label>
            <input id="price_tab" name="price" type="number" step="0.01" min="0"
                value="{{ old('price', $course?->price) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="currency_code_tab" class="block text-sm font-medium text-slate-700">Währung</label>
            <select id="currency_code_tab" name="currency_code" required
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @foreach (['EUR', 'USD', 'CHF', 'GBP'] as $cur)
                    <option value="{{ $cur }}" @selected(strtoupper($defCurrency) === $cur)>{{ $cur }}</option>
                @endforeach
            </select>
            @error('currency_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

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
