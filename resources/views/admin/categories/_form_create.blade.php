@php
    $descOld = (string) old('description', '');
    $selectedStatus = (string) old('status', $defaultNewCategoryStatus ?? 'draft');
@endphp

<div class="admin-panel space-y-6 p-6 md:p-8">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">Kategorie-Details</h2>
        <p class="mt-1 text-sm text-slate-500">Name, Slug, Hierarchie und Sichtbarkeit der neuen Kategorie.</p>
    </div>
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name *</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                placeholder="Kategoriename"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">URL-Slug *</label>
            <input id="slug" name="slug" type="text" required value="{{ old('slug') }}"
                placeholder="kategorie-slug"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">
            @error('slug')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-1 block text-sm font-medium text-slate-700">Sortierung</label>
            <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', 0) }}" min="0" step="1"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            <p class="mt-1 text-xs text-slate-500">Niedrigere Werte werden zuerst angezeigt.</p>
            @error('sort_order')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @include('admin.categories._parent_select', [
            'parentPickerOptions' => $parentPickerOptions,
            'presetParentId' => $presetParentId ?? null,
            'category' => null,
            'parentHelp' => 'Kategorie wird als Kind der ausgewählten Kategorie angelegt.',
        ])

        <div class="md:col-span-2">
            <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status *</label>
            <select id="status" name="status" required
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                <option value="draft" @selected($selectedStatus === 'draft')>Entwurf</option>
                <option value="published" @selected($selectedStatus === 'published')>Veröffentlicht</option>
                <option value="archived" @selected($selectedStatus === 'archived')>Archiviert</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Entwurf = intern, Veröffentlicht = aktiv sichtbar, Archiviert = inaktiv.</p>
            @error('status')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2" x-data="{ len: {{ strlen($descOld) }} }">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Beschreibung</label>
            <textarea id="description" name="description" rows="4" maxlength="200"
                @input="len = $event.target.value.length"
                placeholder="Beschreibung der Kategorie (max 200 Zeichen)"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">{{ $descOld }}</textarea>
            <p class="mt-1 text-xs text-slate-500"><span x-text="len"></span> / 200 Zeichen (Max: 200)</p>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
