@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;

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
    <h2 class="text-lg font-medium text-slate-900">Preis, Format &amp; Links</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="price" class="block text-sm font-medium text-slate-700">Preis (EUR, netto)</label>
            <input id="price" name="price" type="number" step="0.01" min="0"
                value="{{ old('price', $course?->price) }}"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="delivery_format" class="block text-sm font-medium text-slate-700">Format</label>
            <select id="delivery_format" name="delivery_format"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <option value="">—</option>
                @foreach (DeliveryFormat::cases() as $fmt)
                    <option value="{{ $fmt->value }}" @selected(old('delivery_format', $course?->delivery_format?->value) === $fmt->value)>
                        @switch($fmt)
                            @case(DeliveryFormat::Online)
                                Online
                                @break
                            @case(DeliveryFormat::Presence)
                                Präsenz
                                @break
                            @case(DeliveryFormat::Hybrid)
                                Hybrid
                                @break
                        @endswitch
                    </option>
                @endforeach
            </select>
            @error('delivery_format')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300"
                    @checked(old('is_featured', $course?->is_featured ?? false))>
                <span class="text-sm font-medium text-slate-700">Als Featured-Kurs hervorheben</span>
            </label>
            @error('is_featured')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Preisstaffelung (Kurs-Level)</h3>
            <p class="text-xs text-slate-500">
                Überschreibt die globalen Rabatt-Stufen nur, wenn am Kurs ein Preis gesetzt ist.
            </p>
        </div>

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
                                <input
                                    type="number"
                                    min="1"
                                    name="course_discount_tiers[{{ $i }}][min_participants]"
                                    value="{{ $row['min_participants'] ?? '' }}"
                                    class="w-full rounded border border-slate-200 px-2 py-1 text-sm"
                                >
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    name="course_discount_tiers[{{ $i }}][discount_percent]"
                                    value="{{ $row['discount_percent'] ?? '' }}"
                                    class="w-full rounded border border-slate-200 px-2 py-1 text-sm"
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
