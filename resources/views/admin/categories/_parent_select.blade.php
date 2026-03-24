@php
    /** @var \App\Domain\Taxonomy\Models\Category|null $category */
    $selectedParent = old('parent_id', $presetParentId ?? $category?->parent_id);
    $selectedStr = $selectedParent !== null && $selectedParent !== '' ? (string) $selectedParent : '';
@endphp

<div data-parent-picker class="w-full">
    <label for="parent_id" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $parentLabel ?? 'Übergeordnete Kategorie' }}
    </label>
    <select id="parent_id" name="parent_id" class="hidden" autocomplete="off" data-selected="{{ $selectedStr }}"></select>
    <script type="application/json" data-parent-picker-json>@json($parentPickerOptions)</script>
    @if (! empty($presetParentId))
        <p class="mt-1 text-xs text-slate-500">Unterkategorie-Modus: Parent ist vorausgewählt, kann aber geändert werden.</p>
    @endif
    @error('parent_id')
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
    @isset($parentHelp)
        <p class="mt-1 text-xs text-slate-500">{{ $parentHelp }}</p>
    @endisset
</div>
