@php
    use App\Domain\CourseCatalog\Enums\CourseStatus;

    $selectedCategories = old('category_ids', $course?->categories->pluck('id')->all() ?? []);
    $selectedTags = old('tag_ids', $course?->tags->pluck('id')->all() ?? []);
    $selectedAudiences = old('audience_ids', $course?->audiences->pluck('id')->all() ?? []);

    $moduleRows = old('modules');
    if ($moduleRows === null && $course) {
        $moduleRows = $course->modules->map(fn ($m) => [
            'title' => $m->title,
            'description' => $m->description,
            'duration_hours' => $m->duration_hours,
            'sort_order' => $m->sort_order,
        ])->values()->all();
    }
    $moduleRows = $moduleRows ?? [];
    $moduleRows = array_pad(array_values($moduleRows), 5, []);

    $objectiveRows = old('objectives');
    if ($objectiveRows === null && $course) {
        $objectiveRows = $course->learningObjectives->map(fn ($o) => [
            'objective_text' => $o->objective_text,
            'sort_order' => $o->sort_order,
        ])->values()->all();
    }
    $objectiveRows = $objectiveRows ?? [];
    $objectiveRows = array_pad(array_values($objectiveRows), 5, []);

    $prereqRows = old('prerequisites');
    if ($prereqRows === null && $course) {
        $prereqRows = $course->prerequisites->map(fn ($p) => [
            'prerequisite_text' => $p->prerequisite_text,
            'sort_order' => $p->sort_order,
        ])->values()->all();
    }
    $prereqRows = $prereqRows ?? [];
    $prereqRows = array_pad(array_values($prereqRows), 5, []);
@endphp

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-medium text-slate-900">Stammdaten</h2>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-medium text-slate-700">Titel</label>
            <input id="title" name="title" type="text" required
                value="{{ old('title', $course?->title) }}"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="slug" class="block text-sm font-medium text-slate-700">Slug (URL)</label>
            <input id="slug" name="slug" type="text" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                value="{{ old('slug', $course?->slug) }}"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            <p class="mt-1 text-xs text-slate-500">Kleinbuchstaben, Zahlen und Bindestriche.</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="language_code" class="block text-sm font-medium text-slate-700">Sprache</label>
            <input id="language_code" name="language_code" type="text" maxlength="16" required
                value="{{ old('language_code', $course?->language_code ?? 'de') }}"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('language_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="duration_hours" class="block text-sm font-medium text-slate-700">Dauer (Stunden)</label>
            <input id="duration_hours" name="duration_hours" type="number" step="0.25" min="0"
                value="{{ old('duration_hours', $course?->duration_hours) }}"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            @error('duration_hours')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
            <select id="status" name="status" required
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                @foreach (CourseStatus::cases() as $case)
                    <option value="{{ $case->value }}"
                        @selected(old('status', $course?->status->value ?? CourseStatus::Draft->value) === $case->value)>
                        {{ $case->value }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="difficulty_level_id" class="block text-sm font-medium text-slate-700">Schwierigkeit</label>
            <select id="difficulty_level_id" name="difficulty_level_id"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
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

        <div class="sm:col-span-2">
            <label for="short_description" class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
            <textarea id="short_description" name="short_description" rows="3"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">{{ old('short_description', $course?->short_description) }}</textarea>
            @error('short_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="long_description" class="block text-sm font-medium text-slate-700">Langtext</label>
            <textarea id="long_description" name="long_description" rows="6"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">{{ old('long_description', $course?->long_description) }}</textarea>
            @error('long_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-medium text-slate-900">Taxonomie</h2>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="category_ids" class="block text-sm font-medium text-slate-700">Kategorien (mind. eine)</label>
            <select id="category_ids" name="category_ids[]" multiple required size="6"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(in_array($cat->id, $selectedCategories, true))>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Strg/Cmd für Mehrfachauswahl.</p>
            @error('category_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="primary_category_id" class="block text-sm font-medium text-slate-700">Primärkategorie</label>
            <select id="primary_category_id" name="primary_category_id"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
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

        <div>
            <label for="tag_ids" class="block text-sm font-medium text-slate-700">Tags</label>
            <select id="tag_ids" name="tag_ids[]" multiple size="5"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags, true))>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            @error('tag_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="audience_ids" class="block text-sm font-medium text-slate-700">Zielgruppen</label>
            <select id="audience_ids" name="audience_ids[]" multiple size="5"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                @foreach ($audiences as $audience)
                    <option value="{{ $audience->id }}" @selected(in_array($audience->id, $selectedAudiences, true))>
                        {{ $audience->name }}
                    </option>
                @endforeach
            </select>
            @error('audience_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

<div class="admin-panel space-y-4 p-6">
    <h2 class="text-lg font-medium text-slate-900">Module (optional)</h2>
    @foreach ($moduleRows as $i => $row)
        <div class="rounded border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-medium text-slate-500">Modul {{ $i + 1 }}</p>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-600">Titel</label>
                    <input type="text" name="modules[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"
                        class="mt-1 block w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-600">Beschreibung</label>
                    <textarea name="modules[{{ $i }}][description]" rows="2"
                        class="mt-1 block w-full rounded border border-slate-300 px-2 py-1.5 text-sm">{{ $row['description'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="block text-xs text-slate-600">Dauer (h)</label>
                    <input type="number" name="modules[{{ $i }}][duration_hours]" step="0.25" min="0"
                        value="{{ $row['duration_hours'] ?? '' }}"
                        class="mt-1 block w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-slate-600">Sortierung</label>
                    <input type="number" name="modules[{{ $i }}][sort_order]" min="0"
                        value="{{ $row['sort_order'] ?? $i }}"
                        class="mt-1 block w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                </div>
            </div>
        </div>
    @endforeach
    @error('modules')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="admin-panel space-y-4 p-6">
    <h2 class="text-lg font-medium text-slate-900">Lernziele (optional)</h2>
    @foreach ($objectiveRows as $i => $row)
        <div>
            <label class="block text-xs text-slate-600">Lernziel {{ $i + 1 }}</label>
            <textarea name="objectives[{{ $i }}][objective_text]" rows="2"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ $row['objective_text'] ?? '' }}</textarea>
            <input type="hidden" name="objectives[{{ $i }}][sort_order]" value="{{ $row['sort_order'] ?? $i }}">
        </div>
    @endforeach
    @error('objectives')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="admin-panel space-y-4 p-6">
    <h2 class="text-lg font-medium text-slate-900">Voraussetzungen (optional)</h2>
    @foreach ($prereqRows as $i => $row)
        <div>
            <label class="block text-xs text-slate-600">Voraussetzung {{ $i + 1 }}</label>
            <textarea name="prerequisites[{{ $i }}][prerequisite_text]" rows="2"
                class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ $row['prerequisite_text'] ?? '' }}</textarea>
            <input type="hidden" name="prerequisites[{{ $i }}][sort_order]" value="{{ $row['sort_order'] ?? $i }}">
        </div>
    @endforeach
    @error('prerequisites')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
