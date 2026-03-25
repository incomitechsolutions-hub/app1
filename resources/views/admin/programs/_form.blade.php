@php
    use Illuminate\Support\Collection;

    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Domain\CourseCatalog\Models\Course> $courses */
    /** @var \App\Domain\CourseCatalog\Models\Program|null $program */

    $defaultRows = Collection::times(12, fn (int $i) => ['course_id' => '', 'sort_order' => $i - 1])->all();

    if (isset($program) && $program->relationLoaded('courses')) {
        $fromModel = $program->courses->map(fn ($c) => [
            'course_id' => (string) $c->id,
            'sort_order' => (int) $c->pivot->sort_order,
        ])->values()->all();
        $rows = old('program_courses', $fromModel);
    } else {
        $rows = old('program_courses', $defaultRows);
    }

    if (! is_array($rows) || $rows === []) {
        $rows = $defaultRows;
    }

    while (count($rows) < 12) {
        $rows[] = ['course_id' => '', 'sort_order' => count($rows)];
    }
    $rows = array_slice($rows, 0, 12);
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Titel</label>
        <input type="text" name="title" value="{{ old('title', $program?->title ?? '') }}" required
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Slug (URL)</label>
        <input type="text" name="slug" value="{{ old('slug', $program?->slug ?? '') }}" required
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
            placeholder="z. B. it-security-pro">
        @error('slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Kurzbeschreibung</label>
        <textarea name="short_description" rows="3"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('short_description', $program?->short_description ?? '') }}</textarea>
        @error('short_description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status"
            class="mt-1 w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @foreach (['draft' => 'Entwurf', 'published' => 'Veröffentlicht'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $program?->status ?? 'draft') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <h2 class="text-sm font-medium text-slate-900">Kurse im Programm</h2>
        <p class="mt-1 text-xs text-slate-500">Leere Zeilen werden ignoriert. Reihenfolge über „Sortierung“.</p>
        <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-slate-700">Kurs</th>
                        <th class="w-28 px-3 py-2 text-left font-medium text-slate-700">Sortierung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($rows as $index => $row)
                        <tr>
                            <td class="px-3 py-2">
                                <select name="program_courses[{{ $index }}][course_id]"
                                    class="w-full min-w-[14rem] rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                                    <option value="">—</option>
                                    @foreach ($courses as $c)
                                        <option value="{{ $c->id }}" @selected((string) ($row['course_id'] ?? '') === (string) $c->id)>
                                            {{ $c->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="program_courses[{{ $index }}][sort_order]" min="0"
                                    value="{{ (int) ($row['sort_order'] ?? $index) }}"
                                    class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @error('program_courses')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
