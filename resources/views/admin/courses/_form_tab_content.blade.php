@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;

    $selectedCategories = old('category_ids', $course?->categories->pluck('id')->all() ?? []);
    $selectedTags = old('tag_ids', $course?->tags->pluck('id')->all() ?? []);
    $selectedAudiences = old('audience_ids', $course?->audiences->pluck('id')->all() ?? []);
    $defLang = old('language_code', $course?->language_code ?? $catalogDefaults->default_language_code ?? 'de');
    $defCurrency = old('currency_code', $course?->currency_code ?? $catalogDefaults->default_currency ?? 'EUR');
@endphp

<div class="admin-panel space-y-6 p-6">
    <div class="flex items-start justify-between gap-4">
        <h2 class="text-lg font-semibold text-slate-900">Grundinformationen</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-medium text-slate-700">Titel</label>
            <input id="title" name="title" type="text" required value="{{ old('title', $course?->title) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="subtitle" class="block text-sm font-medium text-slate-700">Untertitel</label>
            <input id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $course?->subtitle) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('subtitle')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="language_code" class="block text-sm font-medium text-slate-700">Sprache</label>
            <input id="language_code" name="language_code" type="text" maxlength="16" required value="{{ $defLang }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('language_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="slug" class="block text-sm font-medium text-slate-700">Slug (URL)</label>
            <input id="slug" name="slug" type="text" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                value="{{ old('slug', $course?->slug) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="external_course_code" class="block text-sm font-medium text-slate-700">Kurs-ID</label>
            <input id="external_course_code" name="external_course_code" type="text"
                value="{{ old('external_course_code', $course?->external_course_code) }}" placeholder="z. B. KURS0001"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('external_course_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="delivery_format_content" class="block text-sm font-medium text-slate-700">Format</label>
            <select id="delivery_format_content" name="delivery_format"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="">—</option>
                @foreach (DeliveryFormat::cases() as $fmt)
                    <option value="{{ $fmt->value }}" @selected(old('delivery_format', $course?->delivery_format?->value) === $fmt->value)>
                        @switch($fmt)
                            @case(DeliveryFormat::Online) Online @break
                            @case(DeliveryFormat::Presence) Präsenz @break
                            @case(DeliveryFormat::Hybrid) Hybrid @break
                        @endswitch
                    </option>
                @endforeach
            </select>
            @error('delivery_format')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="duration_days" class="block text-sm font-medium text-slate-700">Dauer (Tage)</label>
            <input id="duration_days" name="duration_days" type="number" min="0" max="3660"
                value="{{ old('duration_days', $course?->duration_days) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('duration_days')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-slate-700">Preis</label>
            <input id="price" name="price" type="number" step="0.01" min="0"
                value="{{ old('price', $course?->price) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="currency_code" class="block text-sm font-medium text-slate-700">Währung</label>
            <select id="currency_code" name="currency_code" required
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @foreach (['EUR', 'USD', 'CHF', 'GBP'] as $cur)
                    <option value="{{ $cur }}" @selected(strtoupper($defCurrency) === $cur)>{{ $cur }}</option>
                @endforeach
            </select>
            @error('currency_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="difficulty_level_id" class="block text-sm font-medium text-slate-700">Level</label>
            <select id="difficulty_level_id" name="difficulty_level_id"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="">—</option>
                @foreach ($difficultyLevels as $level)
                    <option value="{{ $level->id }}"
                        @selected((string) old('difficulty_level_id', $course?->difficulty_level_id) === (string) $level->id)>
                        {{ $level->label }}
                    </option>
                @endforeach
            </select>
            @error('difficulty_level_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2 flex items-center gap-2 pt-2">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" id="is_featured"
                class="rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                @checked(old('is_featured', $course?->is_featured ?? false))>
            <label for="is_featured" class="text-sm font-medium text-slate-700">Als empfohlen markieren</label>
            @error('is_featured')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="category_ids" class="block text-sm font-medium text-slate-700">Kategorien (mind. eine)</label>
            <select id="category_ids" name="category_ids[]" multiple required size="5"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(in_array($cat->id, $selectedCategories, true))>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="primary_category_id" class="block text-sm font-medium text-slate-700">Primärkategorie</label>
            <select id="primary_category_id" name="primary_category_id"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="">—</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        @selected((string) old('primary_category_id', $course?->primary_category_id) === (string) $cat->id)>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('primary_category_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="booking_url" class="block text-sm font-medium text-slate-700">Buchungslink</label>
            <input id="booking_url" name="booking_url" type="url" value="{{ old('booking_url', $course?->booking_url) }}"
                placeholder="https://…"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('booking_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="offer_url" class="block text-sm font-medium text-slate-700">Angebotslink</label>
            <input id="offer_url" name="offer_url" type="url" value="{{ old('offer_url', $course?->offer_url) }}"
                placeholder="https://…"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('offer_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Beschreibungen</h2>
    <div class="space-y-4">
        <div>
            <label for="short_description" class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
            <textarea id="short_description" name="short_description" rows="3"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('short_description', $course?->short_description) }}</textarea>
            @error('short_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="long_description" class="mb-1 block text-sm font-medium text-slate-700">Ausführliche Beschreibung</label>
            <input id="long_description" type="hidden" name="long_description" value="{{ old('long_description', $course?->long_description) }}">
            <trix-editor input="long_description" class="trix-content min-h-[200px] rounded-lg border border-slate-300 shadow-sm"></trix-editor>
            @error('long_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Zielgruppe &amp; Lernziele</h2>
    <div class="space-y-4">
        <div>
            <label for="target_audience_text" class="block text-sm font-medium text-slate-700">Zielgruppe</label>
            <textarea id="target_audience_text" name="target_audience_text" rows="4" placeholder="Für wen ist dieser Kurs geeignet?"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('target_audience_text', $course?->target_audience_text) }}</textarea>
            @error('target_audience_text')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="prerequisites_text" class="block text-sm font-medium text-slate-700">Voraussetzungen</label>
            <textarea id="prerequisites_text" name="prerequisites_text" rows="4" placeholder="Welche Vorkenntnisse werden benötigt?"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('prerequisites_text', $course?->prerequisites_text) }}</textarea>
            @error('prerequisites_text')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Taxonomie</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="tag_ids" class="block text-sm font-medium text-slate-700">Tags</label>
            <select id="tag_ids" name="tag_ids[]" multiple size="5"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags, true))>{{ $tag->name }}</option>
                @endforeach
            </select>
            @error('tag_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="audience_ids" class="block text-sm font-medium text-slate-700">Zielgruppen (Taxonomie)</label>
            <select id="audience_ids" name="audience_ids[]" multiple size="5"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @foreach ($audiences as $audience)
                    <option value="{{ $audience->id }}" @selected(in_array($audience->id, $selectedAudiences, true))>{{ $audience->name }}</option>
                @endforeach
            </select>
            @error('audience_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

@include('admin.courses._form_content', ['course' => $course])
