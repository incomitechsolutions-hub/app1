@php
    use App\Domain\CourseCatalog\Enums\DeliveryFormat;
    use App\Domain\CourseCatalog\Enums\CourseStatus;

    $selectedCategories = old('category_ids', $course?->categories->pluck('id')->all() ?? []);
    $selectedTags = old('tag_ids', $course?->tags->pluck('id')->all() ?? []);
    $selectedAudiences = old('audience_ids', $course?->audiences->pluck('id')->all() ?? []);
    $defLang = old('language_code', $course?->language_code ?? $catalogDefaults->default_language_code ?? 'de');
    $defCurrency = old('currency_code', $course?->currency_code ?? $catalogDefaults->default_currency ?? 'EUR');

    $initialCategoryOptions = collect($categories)
        ->filter(fn ($c) => in_array($c->id, $selectedCategories, true))
        ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
        ->values()
        ->all();

    $primaryId = old('primary_category_id', $course?->primary_category_id);
    $initialPrimaryOptions = [];
    if ($primaryId) {
        $pc = $categories->firstWhere('id', (int) $primaryId);
        if ($pc) {
            $initialPrimaryOptions = [['id' => $pc->id, 'name' => $pc->name]];
        }
    }

    $initialTagOptions = collect($tags)
        ->filter(fn ($t) => in_array($t->id, $selectedTags, true))
        ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])
        ->values()
        ->all();

    $initialAudienceOptions = collect($audiences)
        ->filter(fn ($a) => in_array($a->id, $selectedAudiences, true))
        ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])
        ->values()
        ->all();

    $publishedVal = old('published_at');
    if ($publishedVal === null && $course?->published_at) {
        $publishedVal = $course->published_at->format('Y-m-d\TH:i');
    }
    $statusLabels = [
        'draft' => 'Entwurf',
        'review' => 'Review',
        'seo_review' => 'SEO-Review',
        'published' => 'Veröffentlicht',
    ];
@endphp

<div
    data-course-live-root
    data-live-sync="{{ $course ? '1' : '0' }}"
    data-patch-url="{{ $course ? route('admin.course-catalog.courses.patch-fields', $course) : '' }}"
    data-category-search-url="{{ route('admin.taxonomy.categories.options') }}"
    data-tag-quick-url="{{ route('admin.taxonomy.tags.quick-store') }}"
    data-audience-quick-url="{{ route('admin.taxonomy.audiences.quick-store') }}"
    data-initial-categories="{{ e(json_encode($initialCategoryOptions)) }}"
    data-initial-primary-options="{{ e(json_encode($initialPrimaryOptions)) }}"
    data-initial-tags="{{ e(json_encode($initialTagOptions)) }}"
    data-initial-audiences="{{ e(json_encode($initialAudienceOptions)) }}"
    class="space-y-6"
>
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
                <p class="mt-0.5 text-xs text-slate-500">Auswahl wird beim Bearbeiten eines Kurses automatisch gespeichert.</p>
                <label for="category_filter" class="mt-2 block text-xs font-medium text-slate-600">Kategorien filtern</label>
                <input id="category_filter" type="search" placeholder="z. B. AI, Cloud, Führung ..."
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <select id="category_ids" name="category_ids[]" multiple required
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
        </div>
    </div>

    <div class="admin-panel space-y-6 p-6">
        <h2 class="text-lg font-semibold text-slate-900">Taxonomie</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="tag_ids" class="block text-sm font-medium text-slate-700">Taxonomy Skills</label>
                    <a href="{{ route('admin.taxonomy.tags.index') }}" target="_blank" rel="noopener"
                        class="text-xs font-medium text-sky-600 hover:underline">Verwaltung</a>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">Neu: Namen eingeben und mit Enter anlegen (nur beim Bearbeiten).</p>
                <select id="tag_ids" name="tag_ids[]" multiple
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
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label for="audience_ids" class="block text-sm font-medium text-slate-700">Zielgruppen (Taxonomie)</label>
                    <a href="{{ route('admin.taxonomy.audiences.index') }}" target="_blank" rel="noopener"
                        class="text-xs font-medium text-sky-600 hover:underline">Verwaltung</a>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">Neu: Namen eingeben und mit Enter anlegen (nur beim Bearbeiten).</p>
                <select id="audience_ids" name="audience_ids[]" multiple
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

    <div class="admin-panel space-y-6 p-6">
        <h2 class="text-lg font-semibold text-slate-900">Kurs-Details</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="lessons_count" class="block text-sm font-medium text-slate-700">Anzahl Lektionen</label>
                <input id="lessons_count" name="lessons_count" type="number" min="0"
                    value="{{ old('lessons_count', $course?->lessons_count) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('lessons_count')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="min_participants" class="block text-sm font-medium text-slate-700">Mindest-Teilnehmer</label>
                <input id="min_participants" name="min_participants" type="number" min="0"
                    value="{{ old('min_participants', $course?->min_participants ?? $catalogDefaults->default_min_participants) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('min_participants')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="instructor_name" class="block text-sm font-medium text-slate-700">Dozent</label>
                <input id="instructor_name" name="instructor_name" type="text" placeholder="Name des Dozenten"
                    value="{{ old('instructor_name', $course?->instructor_name) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('instructor_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="certificate_label" class="block text-sm font-medium text-slate-700">Zertifikat</label>
                <input id="certificate_label" name="certificate_label" type="text" placeholder="z. B. Zertifizierter Kurs"
                    value="{{ old('certificate_label', $course?->certificate_label) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('certificate_label')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="admin-panel space-y-6 p-6">
        <h2 class="text-lg font-semibold text-slate-900">Veröffentlichung</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    @foreach (CourseStatus::cases() as $case)
                        <option value="{{ $case->value }}"
                            @selected(old('status', $course?->status->value ?? CourseStatus::Draft->value) === $case->value)>
                            {{ $statusLabels[$case->value] ?? $case->value }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="published_at" class="block text-sm font-medium text-slate-700">Veröffentlichungsdatum</label>
                <input id="published_at" name="published_at" type="datetime-local"
                    value="{{ $publishedVal }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('published_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="author_name" class="block text-sm font-medium text-slate-700">Autor</label>
                <input id="author_name" name="author_name" type="text"
                    value="{{ old('author_name', $course?->author_name) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('author_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="content_version" class="block text-sm font-medium text-slate-700">Version</label>
                <input id="content_version" name="content_version" type="text" placeholder="1.0"
                    value="{{ old('content_version', $course?->content_version) }}"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                @error('content_version')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="admin-panel space-y-6 p-6">
        <h2 class="text-lg font-semibold text-slate-900">Interne Informationen</h2>
        <div class="space-y-4">
            <div>
                <label for="ai_prompt_source" class="block text-sm font-medium text-slate-700">Quelle / KI-Prompt</label>
                <textarea id="ai_prompt_source" name="ai_prompt_source" rows="4" placeholder="Original-Prompt oder Quelle für diesen Kurs"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('ai_prompt_source', $course?->ai_prompt_source) }}</textarea>
                @error('ai_prompt_source')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="internal_notes" class="block text-sm font-medium text-slate-700">Interne Notizen</label>
                <textarea id="internal_notes" name="internal_notes" rows="4" placeholder="Interne Anmerkungen für das Team"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('internal_notes', $course?->internal_notes) }}</textarea>
                @error('internal_notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
