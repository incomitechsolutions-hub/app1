@php
    /** @var \App\Domain\Taxonomy\Models\Category|null $category */
    /** @var list<array{id: int, depth: int, name: string, label: string, searchName: string}> $parentPickerOptions */
    $selectedParent = old('parent_id', $presetParentId ?? $category?->parent_id);
    $selectedStr = $selectedParent !== null && $selectedParent !== '' ? (string) $selectedParent : '';
@endphp

<div data-parent-picker class="w-full md:col-span-2">
    <label for="parent_id" class="mb-1 block text-sm font-medium text-slate-700">
        {{ $parentLabel ?? 'Übergeordnete Kategorie' }}
    </label>
    <select id="parent_id" name="parent_id" autocomplete="off" data-selected="{{ $selectedStr }}"
        class="parent-select-native block w-full max-w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
        <option value="" @selected($selectedStr === '')>— Keine (Hauptkategorie) —</option>
        @foreach ($parentPickerOptions as $opt)
            <option value="{{ $opt['id'] }}" data-search-name="{{ $opt['searchName'] }}" data-depth="{{ $opt['depth'] }}"
                @selected($selectedStr !== '' && (string) $selectedStr === (string) $opt['id'])>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>
    @if (! empty($presetParentId))
        <p class="mt-1 text-xs text-slate-500">Unterkategorie-Modus: Parent ist vorausgewählt, kann aber geändert werden.</p>
    @endif
    @error('parent_id')
        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
    @enderror
    @isset($parentHelp)
        <p class="mt-1 text-xs text-slate-500">{{ $parentHelp }}</p>
    @endisset
    <div id="ai-parent-rationale" class="mt-2 hidden rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700" data-role="ai-parent-rationale"></div>
</div>
