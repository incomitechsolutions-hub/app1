@php
    /** @var \App\Domain\Taxonomy\Models\Category|null $category */
    $selectedParent = old('parent_id', $presetParentId ?? $category?->parent_id);
    $selectedStatus = old('status', $category?->status ?? 'draft');
@endphp

<div class="admin-panel p-6">
    <h2 class="mb-4 text-lg font-semibold text-slate-900">Stammdaten</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name *</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $category?->name) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug *</label>
            <input id="slug" name="slug" type="text" required value="{{ old('slug', $category?->slug) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('slug')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status *</label>
            <select id="status" name="status" required
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="draft" @selected($selectedStatus === 'draft')>Entwurf</option>
                <option value="published" @selected($selectedStatus === 'published')>Veröffentlicht</option>
                <option value="archived" @selected($selectedStatus === 'archived')>Archiviert</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Entwurf = intern, Veröffentlicht = aktiv sichtbar, Archiviert = inaktiv.</p>
            @error('status')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="parent_id" class="mb-1 block text-sm font-medium text-slate-700">Parent-Kategorie</label>
            <select id="parent_id" name="parent_id"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="">— Keine (Hauptkategorie) —</option>
                @foreach ($parentOptions as $parent)
                    <option value="{{ $parent->id }}" @selected((string) $selectedParent === (string) $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @if (!empty($presetParentId))
                <p class="mt-1 text-xs text-slate-500">Unterkategorie-Modus: Parent ist vorausgewählt, kann aber geändert werden.</p>
            @endif
            @error('parent_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Beschreibung</label>
            <textarea id="description" name="description" rows="4"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('description', $category?->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
