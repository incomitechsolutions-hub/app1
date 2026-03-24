@php
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
