@php
    /** @var \Illuminate\Support\Collection<int, \App\Domain\PromptManagement\Models\AiPrompt>|null $categoryAiPrompts */
    $prompts = $categoryAiPrompts ?? collect();
@endphp

<div id="category-ai-finalize-bar"
    class="admin-panel flex flex-wrap items-end gap-3 p-4 md:p-5"
    data-action-url="{{ route('admin.taxonomy.categories.ai-finalize') }}"
    data-csrf="{{ csrf_token() }}">
    <div class="min-w-[min(100%,220px)] flex-1">
        <label for="category-ai-prompt-select" class="mb-1 block text-xs font-medium text-slate-600">Prompt (Bibliothek)</label>
        <select id="category-ai-prompt-select"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            <option value="">— Standard (eingebaut) —</option>
            @foreach ($prompts as $prompt)
                <option value="{{ $prompt->id }}">{{ $prompt->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end gap-2 pb-0.5">
        <button type="button" id="category-ai-finalize-btn"
            class="inline-flex items-center gap-2 rounded-lg border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-900 shadow-sm transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-60">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
            </svg>
            KI: Felder ergänzen
        </button>
    </div>
    @isset($category)
        <input type="hidden" id="category_ai_category_id" value="{{ $category->getKey() }}">
    @endisset
</div>
<p id="category-ai-finalize-status" class="hidden text-sm text-slate-600" role="status"></p>
