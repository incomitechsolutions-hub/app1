@php
    $descOld = (string) old('description', '');
    $statusOld = (string) old('status', 'draft');
    $isPublished = $statusOld === 'published';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
    <h2 class="mb-6 text-lg font-semibold text-slate-900">Kategorie-Details</h2>
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name *</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                placeholder="Kategoriename"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">URL-Slug *</label>
            <input id="slug" name="slug" type="text" required value="{{ old('slug') }}"
                placeholder="kategorie-slug"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">
            @error('slug')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-1 block text-sm font-medium text-slate-700">Sortierung</label>
            <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', 0) }}" min="0" step="1"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
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
            <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
            <div x-data="{ active: @json($isPublished) }" class="flex items-center gap-3">
                <input type="hidden" name="status" :value="active ? 'published' : 'draft'">
                <button type="button" role="switch" :aria-checked="active"
                    @click="active = !active"
                    :class="active ? 'bg-sky-600' : 'bg-slate-200'"
                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    <span :class="active ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-6 w-6 translate-x-0.5 transform rounded-full bg-white shadow ring-0 transition"></span>
                </button>
                <span class="text-sm font-medium text-slate-700">Aktiv</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Aktiv = veröffentlicht sichtbar, inaktiv = Entwurf.</p>
            @error('status')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="md:col-span-2" x-data="{ len: {{ strlen($descOld) }} }">
            <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Beschreibung</label>
            <textarea id="description" name="description" rows="4" maxlength="200"
                @input="len = $event.target.value.length"
                placeholder="Beschreibung der Kategorie (max 200 Zeichen)"
                class="w-full rounded-lg border-slate-300 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:ring-slate-500">{{ $descOld }}</textarea>
            <p class="mt-1 text-xs text-slate-500"><span x-text="len"></span> / 200 Zeichen (Max: 200)</p>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
