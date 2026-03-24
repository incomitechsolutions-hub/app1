@php
    use App\Domain\CourseCatalog\Enums\CourseStatus;
    use App\Domain\CourseCatalog\Enums\DeliveryMode;

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

<div class="admin-panel space-y-6 p-6">
    <h2 class="text-lg font-semibold text-slate-900">Kurs-Details</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="delivery_mode" class="block text-sm font-medium text-slate-700">Liefermodus</label>
            <select id="delivery_mode" name="delivery_mode"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="">—</option>
                @foreach (DeliveryMode::cases() as $mode)
                    <option value="{{ $mode->value }}" @selected(old('delivery_mode', $course?->delivery_mode?->value) === $mode->value)>
                        @switch($mode)
                            @case(DeliveryMode::LiveOnline) Live-Online @break
                            @case(DeliveryMode::SelfStudy) Selbststudium @break
                            @case(DeliveryMode::BlendedLearning) Blended Learning @break
                        @endswitch
                    </option>
                @endforeach
            </select>
            @error('delivery_mode')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="duration_hours" class="block text-sm font-medium text-slate-700">Dauer (Stunden)</label>
            <input id="duration_hours" name="duration_hours" type="number" step="0.25" min="0"
                value="{{ old('duration_hours', $course?->duration_hours) }}"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
            @error('duration_hours')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
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
            <input id="certificate_label" name="certificate_label" type="text" placeholder="z. B. Zertifizierter Kurs"
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
