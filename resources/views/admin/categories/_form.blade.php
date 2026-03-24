@php
    /** @var \App\Domain\Taxonomy\Models\Category|null $category */
    $selectedStatus = old('status', $category?->status ?? 'draft');
    $descOld = (string) old('description', $category?->description ?? '');
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <h2 class="mb-6 text-lg font-semibold text-slate-900">Stammdaten</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name *</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $category?->name) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">URL-Slug *</label>
            <input id="slug" name="slug" type="text" required value="{{ old('slug', $category?->slug) }}"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            @error('slug')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-1 block text-sm font-medium text-slate-700">Sortierung</label>
            <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" min="0" step="1"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            <p class="mt-1 text-xs text-slate-500">Niedrigere Werte werden zuerst angezeigt.</p>
            @error('sort_order')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @include('admin.categories._parent_select', [
            'parentPickerOptions' => $parentPickerOptions,
            'presetParentId' => $presetParentId ?? null,
            'category' => $category,
            'parentLabel' => 'Übergeordnete Kategorie',
        ])

        <div class="md:col-span-2">
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

        <div class="md:col-span-2" x-data="{ len: {{ strlen($descOld) }} }">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Beschreibung</label>
            <textarea id="description" name="description" rows="4" maxlength="200"
                @input="len = $event.target.value.length"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ $descOld }}</textarea>
            <p class="mt-1 text-xs text-slate-500"><span x-text="len"></span> / 200 Zeichen (Max: 200)</p>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
