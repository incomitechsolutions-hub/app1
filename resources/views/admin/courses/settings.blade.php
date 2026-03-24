@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;
    use App\Domain\CourseCatalog\Enums\GroupDiscountLayout;

    /** @var \App\Domain\CourseCatalog\Models\CourseCatalogGlobalSetting $settings */
    $tierRows = old('group_discount_tiers');
    if ($tierRows === null) {
        $tierRows = $settings->groupDiscountTiers->map(fn ($t) => [
            'min_participants' => $t->min_participants,
            'discount_percent' => $t->discount_percent,
            'sort_order' => $t->sort_order,
        ])->values()->all();
    }
    $tierRows = array_pad(array_values($tierRows), 8, []);
@endphp

@extends('layouts.admin')

@section('title', 'Kurs-Einstellungen')
@section('breadcrumb', 'Kurse')

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Kurs-Einstellungen</h1>
            <p class="mt-1 text-sm text-slate-500">Konfigurieren Sie Standardwerte und Rabatt-Einstellungen für Kurse</p>
        </div>

        <form method="post" action="{{ route('admin.course-catalog.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="admin-panel space-y-6 p-6">
                <h2 class="text-lg font-semibold text-slate-900">Standardwerte</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="default_currency" class="block text-sm font-medium text-slate-700">Standard-Währung</label>
                        <select id="default_currency" name="default_currency" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                            @foreach (['EUR', 'USD', 'CHF', 'GBP'] as $cur)
                                <option value="{{ $cur }}" @selected(old('default_currency', $settings->default_currency) === $cur)>{{ $cur }}</option>
                            @endforeach
                        </select>
                        @error('default_currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="default_delivery_format" class="block text-sm font-medium text-slate-700">Standard-Format</label>
                        <select id="default_delivery_format" name="default_delivery_format" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                            @foreach (DeliveryFormat::cases() as $fmt)
                                <option value="{{ $fmt->value }}" @selected(old('default_delivery_format', $settings->default_delivery_format?->value) === $fmt->value)>
                                    @switch($fmt)
                                        @case(DeliveryFormat::Online) Online @break
                                        @case(DeliveryFormat::Presence) Präsenz @break
                                        @case(DeliveryFormat::Hybrid) Hybrid @break
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                        @error('default_delivery_format')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="default_language_code" class="block text-sm font-medium text-slate-700">Standard-Sprache</label>
                        <input id="default_language_code" name="default_language_code" type="text" maxlength="16" required
                            value="{{ old('default_language_code', $settings->default_language_code) }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @error('default_language_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="default_min_participants" class="block text-sm font-medium text-slate-700">Mindest-Teilnehmer</label>
                        <input id="default_min_participants" name="default_min_participants" type="number" min="1" required
                            value="{{ old('default_min_participants', $settings->default_min_participants) }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @error('default_min_participants')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="tax_rate_percent" class="block text-sm font-medium text-slate-700">Steuersatz (%)</label>
                        <input id="tax_rate_percent" name="tax_rate_percent" type="number" step="0.01" min="0" max="100" required
                            value="{{ old('tax_rate_percent', $settings->tax_rate_percent) }}"
                            class="mt-1 block w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @error('tax_rate_percent')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="admin-panel space-y-6 p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-slate-900">Early Bird Rabatt</h2>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="hidden" name="early_bird_enabled" value="0">
                        <input type="checkbox" name="early_bird_enabled" value="1" class="peer sr-only"
                            @checked(old('early_bird_enabled', $settings->early_bird_enabled ?? false))>
                        <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all peer-checked:bg-sky-600 peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="early_bird_days_before" class="block text-sm font-medium text-slate-700">Tage vor Kursbeginn</label>
                        <input id="early_bird_days_before" name="early_bird_days_before" type="number" min="0"
                            value="{{ old('early_bird_days_before', $settings->early_bird_days_before) }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        <p class="mt-1 text-xs text-slate-500">Anzahl der Tage vor Kursbeginn für Early-Bird-Rabatt</p>
                        @error('early_bird_days_before')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="early_bird_discount_percent" class="block text-sm font-medium text-slate-700">Rabatt (%)</label>
                        <input id="early_bird_discount_percent" name="early_bird_discount_percent" type="number" step="0.01" min="0" max="100"
                            value="{{ old('early_bird_discount_percent', $settings->early_bird_discount_percent) }}"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        <p class="mt-1 text-xs text-slate-500">Prozentualer Rabatt für Early Bird</p>
                        @error('early_bird_discount_percent')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="admin-panel space-y-6 p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-slate-900">Gruppenrabatt</h2>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="hidden" name="group_discount_enabled" value="0">
                        <input type="checkbox" name="group_discount_enabled" value="1" class="peer sr-only"
                            @checked(old('group_discount_enabled', $settings->group_discount_enabled))>
                        <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all peer-checked:bg-sky-600 peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                <div>
                    <label for="group_discount_layout" class="block text-sm font-medium text-slate-700">Layout-Variante</label>
                    <select id="group_discount_layout" name="group_discount_layout" required
                        class="mt-1 block w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @foreach (GroupDiscountLayout::cases() as $layout)
                            <option value="{{ $layout->value }}" @selected(old('group_discount_layout', $settings->group_discount_layout?->value) === $layout->value)>
                                @if ($layout === GroupDiscountLayout::Layout1) Layout 1: Tabelle @else Layout 2: Dynamischer Rechner @endif
                            </option>
                        @endforeach
                    </select>
                    @error('group_discount_layout')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                                        <input type="number" name="group_discount_tiers[{{ $i }}][min_participants]" min="1"
                                            value="{{ $row['min_participants'] ?? '' }}"
                                            class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="group_discount_tiers[{{ $i }}][discount_percent]" step="0.01" min="0" max="100"
                                            value="{{ $row['discount_percent'] ?? '' }}"
                                            class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Speichern
                </button>
            </div>
        </form>

        <div class="admin-panel space-y-6 p-6">
            <h2 class="text-lg font-semibold text-slate-900">Gutscheincodes</h2>
            <p class="text-sm text-slate-500">Erstellen Sie Gutscheincodes für Rabatte.</p>

            <form method="post" action="{{ route('admin.course-catalog.settings.coupons.store') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label for="coupon_code" class="block text-sm font-medium text-slate-700">Code</label>
                    <input id="coupon_code" name="code" type="text" required
                        class="mt-1 block w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                </div>
                <div>
                    <label for="coupon_discount" class="block text-sm font-medium text-slate-700">Rabatt (%)</label>
                    <input id="coupon_discount" name="discount_percent" type="number" step="0.01" min="0" max="100" required
                        class="mt-1 block w-full max-w-[8rem] rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="coupon_active" class="rounded border-slate-300" checked>
                    <label for="coupon_active" class="text-sm text-slate-700">Aktiv</label>
                </div>
                <div class="flex-1 min-w-[12rem]">
                    <label for="coupon_notes" class="block text-sm font-medium text-slate-700">Notizen</label>
                    <input id="coupon_notes" name="notes" type="text"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
                </div>
                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    + Hinzufügen
                </button>
            </form>

            @if ($coupons->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-slate-700">Code</th>
                                <th class="px-4 py-2 text-left font-medium text-slate-700">Rabatt</th>
                                <th class="px-4 py-2 text-left font-medium text-slate-700">Status</th>
                                <th class="px-4 py-2 text-right font-medium text-slate-700">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($coupons as $coupon)
                                <tr>
                                    <td class="px-4 py-2 font-mono font-medium">{{ $coupon->code }}</td>
                                    <td class="px-4 py-2">{{ number_format((float) $coupon->discount_percent, 2, ',', '.') }}%</td>
                                    <td class="px-4 py-2">{{ $coupon->is_active ? 'Aktiv' : 'Inaktiv' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <form method="post" action="{{ route('admin.course-catalog.settings.coupons.destroy', $coupon) }}" class="inline"
                                            onsubmit="return confirm('Gutschein löschen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Löschen</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
