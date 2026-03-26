@php
    /** @var \App\Domain\PromptManagement\Models\AiPrompt|null $prompt */
    /** @var list<array{value: string, label: string, is_custom?: bool}> $useCaseSelectOptions */
    /** @var string|null $useCaseDeleteUrlTemplate */
    $currentUseCase = (string) old('use_case', $prompt?->use_case ?? '');
    $pickableValues = array_column($useCaseSelectOptions, 'value');
    $isPickable = $currentUseCase === '' || in_array($currentUseCase, $pickableValues, true);
    $firstPick = $useCaseSelectOptions[0]['value'] ?? \App\Domain\PromptManagement\Enums\PromptUseCase::General->value;
    $useCaseOptionsForJs = array_map(static function (array $opt): array {
        return [
            'value' => (string) ($opt['value'] ?? ''),
            'label' => (string) ($opt['label'] ?? ''),
            'isCustom' => (bool) ($opt['is_custom'] ?? false),
        ];
    }, $useCaseSelectOptions);
@endphp

<div class="space-y-4">
    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Titel</label>
        <input id="title" name="title" type="text" required value="{{ old('title', $prompt?->title) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="slug" class="block text-sm font-medium text-slate-700">Slug</label>
        <input id="slug" name="slug" type="text" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            value="{{ old('slug', $prompt?->slug) }}"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        @error('slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="space-y-2" x-data="{
        mode: @js($isPickable ? 'pick' : 'custom'),
        pick: @js($isPickable ? ($currentUseCase !== '' ? $currentUseCase : $firstPick) : ''),
        custom: @js($isPickable ? '' : $currentUseCase),
        firstPick: @js($firstPick),
        options: @js($useCaseOptionsForJs),
        open: false,
        deleteUrlTemplate: @js($useCaseDeleteUrlTemplate ?? ''),
        busySlug: null,
        statusMessage: '',
        errorMessage: '',
        resolved() {
            if (this.mode === 'pick') {
                return this.pick;
            }
            return this.custom.trim();
        },
        selectedLabel() {
            const found = this.options.find((opt) => opt.value === this.pick);
            return found ? found.label : this.pick;
        },
        selectOption(value) {
            this.pick = value;
            this.open = false;
            this.errorMessage = '';
        },
        ensurePickableSelection() {
            const hasPick = this.options.some((opt) => opt.value === this.pick);
            if (hasPick) {
                return;
            }
            if (this.options.length > 0) {
                this.pick = this.options[0].value;
                this.mode = 'pick';
                return;
            }
            this.pick = '';
            this.mode = 'custom';
        },
        async deleteOption(option) {
            this.errorMessage = '';
            this.statusMessage = '';

            if (!option || !option.isCustom || this.busySlug !== null || !this.deleteUrlTemplate) {
                return;
            }

            this.busySlug = option.value;
            try {
                const endpoint = this.deleteUrlTemplate.replace('__slug__', encodeURIComponent(option.value));
                const csrfMeta = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '';
                const csrfInput = document.querySelector('input[name=_token]')?.value ?? '';
                const csrfToken = csrfMeta || csrfInput;
                const response = await fetch(endpoint, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const contentType = response.headers.get('content-type') ?? '';
                const payload = contentType.includes('application/json')
                    ? await response.json().catch(() => ({}))
                    : {};
                if (!response.ok || payload.ok !== true) {
                    if (response.status === 419) {
                        this.errorMessage = 'Sitzung abgelaufen. Bitte Seite neu laden und erneut versuchen.';
                        return;
                    }
                    this.errorMessage = payload.message || `Anwendungsfall konnte nicht gelöscht werden (HTTP ${response.status}).`;
                    return;
                }

                this.options = this.options.filter((opt) => opt.value !== option.value);
                this.statusMessage = payload.message || 'Anwendungsfall gelöscht.';
                this.ensurePickableSelection();
            } catch (error) {
                this.errorMessage = 'Anwendungsfall konnte nicht gelöscht werden.';
            } finally {
                this.busySlug = null;
            }
        },
    }" x-init="if (mode === 'pick' && !pick) pick = firstPick; ensurePickableSelection()">
        <span class="block text-sm font-medium text-slate-700">Anwendungsfall</span>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <select x-model="mode" aria-label="{{ __('Quelle des Anwendungsfalls') }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                <option value="pick">{{ __('Aus Liste wählen') }}</option>
                <option value="custom">{{ __('Neuen Slug eingeben') }}</option>
            </select>
            <input type="hidden" name="use_case" :value="resolved()">
        </div>
        <div x-show="mode === 'pick'" class="mt-2 max-w-md">
            <div class="relative" @click.outside="open = false">
                <button type="button" @click="open = !open" aria-label="{{ __('Anwendungsfall aus Liste') }}"
                    class="flex w-full items-center justify-between rounded-lg border border-slate-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    <span class="truncate" x-text="selectedLabel() || '{{ __('Bitte wählen') }}'"></span>
                    <span class="text-slate-400">▾</span>
                </button>

                <div x-show="open" x-cloak
                    class="absolute z-20 mt-1 max-h-64 w-full overflow-auto rounded-lg border border-slate-300 bg-white shadow-lg">
                    <template x-if="options.length === 0">
                        <div class="px-3 py-2 text-sm text-slate-500">{{ __('Keine Einträge vorhanden.') }}</div>
                    </template>
                    <template x-for="option in options" :key="option.value">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-2 py-1 last:border-b-0">
                            <button type="button" class="min-w-0 flex-1 rounded px-2 py-1 text-left text-sm hover:bg-slate-100"
                                @click="selectOption(option.value)">
                                <span class="truncate" x-text="option.label"></span>
                            </button>
                            <button type="button"
                                x-show="option.isCustom"
                                :disabled="busySlug === option.value"
                                @click.stop="deleteOption(option)"
                                class="rounded px-2 py-1 text-sm font-semibold text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                                title="{{ __('Eintrag löschen') }}">
                                x
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            <p x-show="statusMessage" x-cloak class="mt-1 text-xs text-emerald-700" x-text="statusMessage"></p>
            <p x-show="errorMessage" x-cloak class="mt-1 text-xs text-rose-600" x-text="errorMessage"></p>
        </div>
        <div x-show="mode === 'custom'" x-cloak class="mt-2 max-w-md space-y-1">
            <input type="text" x-model="custom" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" maxlength="96"
                placeholder="z. B. landing-pages"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                :required="mode === 'custom'">
            <p class="text-xs text-slate-500">{{ __('Kleinbuchstaben, Ziffern und Bindestriche; wie ein URL-Slug.') }}</p>
        </div>
        @error('use_case')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Interne Beschreibung (optional)</label>
        <textarea id="description" name="description" rows="2"
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('description', $prompt?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="body" class="block text-sm font-medium text-slate-700">Prompt-Text</label>
        <textarea id="body" name="body" rows="12" required
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('body', $prompt?->body) }}</textarea>
        @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $prompt?->is_active ?? true))>
            Aktiv
        </label>
    </div>
    <div>
        <label for="sort_order" class="block text-sm font-medium text-slate-700">Sortierung</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="65535"
            value="{{ old('sort_order', $prompt?->sort_order ?? 0) }}"
            class="mt-1 w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
        @error('sort_order')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>
