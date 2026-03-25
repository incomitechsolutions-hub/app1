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
    $moduleRows = array_values($moduleRows ?? []);
    if ($moduleRows === []) {
        $moduleRows = [['title' => '', 'description' => '', 'duration_hours' => '', 'sort_order' => 0]];
    }

    $objectiveRows = old('objectives');
    if ($objectiveRows === null && $course) {
        $objectiveRows = $course->learningObjectives->map(fn ($o) => [
            'objective_text' => $o->objective_text,
            'sort_order' => $o->sort_order,
        ])->values()->all();
    }
    $objectiveRows = array_values($objectiveRows ?? []);
    if ($objectiveRows === []) {
        $objectiveRows = [['objective_text' => '', 'sort_order' => 0]];
    }

    $prereqRows = old('prerequisites');
    if ($prereqRows === null && $course) {
        $prereqRows = $course->prerequisites->map(fn ($p) => [
            'prerequisite_text' => $p->prerequisite_text,
            'sort_order' => $p->sort_order,
        ])->values()->all();
    }
    $prereqRows = array_values($prereqRows ?? []);
    if ($prereqRows === []) {
        $prereqRows = [['prerequisite_text' => '', 'sort_order' => 0]];
    }
@endphp

<div class="admin-panel space-y-4 p-6" x-data="{ modules: {{ \Illuminate\Support\Js::from($moduleRows) }} }">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-900">Module (optional)</h2>
        <button type="button" @click="modules.push({ title: '', description: '', duration_hours: '', sort_order: modules.length })"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            + Zeile
        </button>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Titel</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Beschreibung</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Dauer (h)</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Sort.</th>
                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, index) in modules" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <input type="text" :name="'modules[' + index + '][title]'" x-model="row.title"
                                class="w-full min-w-[8rem] rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2">
                            <textarea :name="'modules[' + index + '][description]'" x-model="row.description" rows="2"
                                class="w-full min-w-[12rem] rounded border border-slate-200 px-2 py-1 text-sm"></textarea>
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" step="0.25" min="0" :name="'modules[' + index + '][duration_hours]'" x-model="row.duration_hours"
                                class="w-20 rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" min="0" :name="'modules[' + index + '][sort_order]'" x-model="row.sort_order"
                                class="w-16 rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="modules.splice(index, 1)" x-show="modules.length > 1"
                                class="text-sm text-rose-600 hover:underline">Entfernen</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    @error('modules')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="admin-panel space-y-4 p-6" x-data="{ objectives: {{ \Illuminate\Support\Js::from($objectiveRows) }} }">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-900">Lernziele (optional)</h2>
        <button type="button" @click="objectives.push({ objective_text: '', sort_order: objectives.length })"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            + Zeile
        </button>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Lernziel</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Sort.</th>
                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, index) in objectives" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <textarea :name="'objectives[' + index + '][objective_text]'" x-model="row.objective_text" rows="2"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-sm"></textarea>
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" min="0" :name="'objectives[' + index + '][sort_order]'" x-model="row.sort_order"
                                class="w-16 rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="objectives.splice(index, 1)" x-show="objectives.length > 1"
                                class="text-sm text-rose-600 hover:underline">Entfernen</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    @error('objectives')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="admin-panel space-y-4 p-6" x-data="{ prerequisites: {{ \Illuminate\Support\Js::from($prereqRows) }} }">
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-lg font-medium text-slate-900">Strukturierte Voraussetzungen (optional)</h2>
        <button type="button" @click="prerequisites.push({ prerequisite_text: '', sort_order: prerequisites.length })"
            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            + Zeile
        </button>
    </div>
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Voraussetzung</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase text-slate-600">Sort.</th>
                    <th class="px-3 py-2 text-right text-xs font-medium uppercase text-slate-600">Aktion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="(row, index) in prerequisites" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-slate-500" x-text="index + 1"></td>
                        <td class="px-3 py-2">
                            <textarea :name="'prerequisites[' + index + '][prerequisite_text]'" x-model="row.prerequisite_text" rows="2"
                                class="w-full rounded border border-slate-200 px-2 py-1 text-sm"></textarea>
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" min="0" :name="'prerequisites[' + index + '][sort_order]'" x-model="row.sort_order"
                                class="w-16 rounded border border-slate-200 px-2 py-1 text-sm">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" @click="prerequisites.splice(index, 1)" x-show="prerequisites.length > 1"
                                class="text-sm text-rose-600 hover:underline">Entfernen</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    @error('prerequisites')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
