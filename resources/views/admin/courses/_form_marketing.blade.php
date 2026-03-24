@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;
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
        <div class="sm:col-span-2">
            <label for="booking_url" class="block text-sm font-medium text-slate-700">Buchungslink</label>
            <input id="booking_url" name="booking_url" type="url"
                value="{{ old('booking_url', $course?->booking_url) }}"
                placeholder="https://…"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('booking_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="offer_url" class="block text-sm font-medium text-slate-700">Angebotslink</label>
            <input id="offer_url" name="offer_url" type="url"
                value="{{ old('offer_url', $course?->offer_url) }}"
                placeholder="https://…"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('offer_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
