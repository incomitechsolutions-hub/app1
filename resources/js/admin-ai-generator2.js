function csrfToken() {
    return document.querySelector('input[name="_token"]')?.value ?? '';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function deepClone(value) {
    if (value === undefined || value === null) return {};
    try {
        return JSON.parse(JSON.stringify(value));
    } catch (_err) {
        return {};
    }
}

function getByPath(obj, path) {
    return path.split('.').reduce((acc, key) => (acc && Object.prototype.hasOwnProperty.call(acc, key) ? acc[key] : undefined), obj);
}

function setByPath(obj, path, value) {
    const keys = path.split('.');
    let current = obj;
    keys.forEach((key, index) => {
        if (index === keys.length - 1) {
            current[key] = value;
            return;
        }
        if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    });
}

function normalizeKeyword(value) {
    return String(value ?? '').trim().toLowerCase();
}

function toSlug(value) {
    return String(value ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .replace(/-{2,}/g, '-');
}

function setInputValue(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = value ?? '';
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

function setSelectValue(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.tomselect) {
        if (Array.isArray(value)) {
            el.tomselect.setValue(value);
        } else {
            el.tomselect.setValue(value ?? '');
        }
    } else if (Array.isArray(value)) {
        Array.from(el.options).forEach((opt) => {
            opt.selected = value.includes(opt.value);
        });
    } else {
        el.value = value ?? '';
    }
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

function findAlpineData(key) {
    const nodes = document.querySelectorAll('[x-data]');
    for (const node of nodes) {
        const stack = node._x_dataStack;
        if (!Array.isArray(stack) || !stack[0] || typeof stack[0] !== 'object') continue;
        if (Object.prototype.hasOwnProperty.call(stack[0], key)) {
            return stack[0];
        }
    }
    return null;
}

function applyGeneratedData(generated, state) {
    const seo = generated.seo ?? {};
    setInputValue('seo_seo_title', seo.seo_title ?? '');
    setInputValue('seo_meta_description', seo.meta_description ?? '');
    setInputValue('seo_focus_keyword', seo.focus_keyword ?? '');
    setInputValue('seo_tags_csv', seo.tags_csv ?? '');
    setInputValue('seo_og_title', seo.og_title ?? '');
    setInputValue('seo_og_description', seo.og_description ?? '');
    setInputValue('seo_schema_json', seo.schema_json ?? '');
    setInputValue('seo_landing_page_url', seo.landing_page_url ?? '');
    setInputValue('seo_canonical_url', seo.canonical_url ?? '');
    setSelectValue('seo_robots_index', seo.robots_index ?? '1');
    setSelectValue('seo_robots_follow', seo.robots_follow ?? '1');

    const base = generated.base ?? {};
    setSelectValue('status', 'draft');
    setInputValue('published_at', base.published_at ?? new Date().toISOString().slice(0, 16));
    setInputValue('author_name', base.author_name ?? '');
    setInputValue('content_version', base.content_version ?? '1.0');
    setInputValue('title', base.title ?? '');
    setInputValue('subtitle', base.subtitle ?? '');
    setInputValue('slug', base.slug ?? toSlug(base.title ?? ''));
    setInputValue('language_code', base.language_code ?? 'de');
    setInputValue('duration_hours', base.duration_hours ?? '');
    setSelectValue('delivery_format_content', Array.isArray(base.delivery_formats) ? base.delivery_formats : []);
    setSelectValue('difficulty_level_id', base.difficulty_level_id ?? '');
    setSelectValue('primary_category_id', base.primary_category_id ? String(base.primary_category_id) : '');
    setSelectValue('tag_ids', Array.isArray(base.tag_ids) ? base.tag_ids.map(String) : []);
    setSelectValue('audience_ids', Array.isArray(base.audience_ids) ? base.audience_ids.map(String) : []);
    setInputValue('min_participants', base.min_participants ?? '');
    if (base.price !== undefined && base.price !== null) {
        setInputValue('price_tab', base.price);
    }

    const details = generated.details ?? {};
    setInputValue('short_description', details.short_description ?? '');
    const longHidden = document.getElementById('long_description');
    if (longHidden) {
        longHidden.value = details.long_description ?? '';
        longHidden.dispatchEvent(new Event('input', { bubbles: true }));
        const trix = document.querySelector('trix-editor[input="long_description"]');
        if (trix && trix.editor) {
            trix.editor.loadHTML(details.long_description ?? '');
        }
    }
    setInputValue('target_audience_text', details.target_audience_text ?? '');
    setInputValue('prerequisites_text', details.prerequisites_text ?? '');

    const modulesData = findAlpineData('modules');
    if (modulesData && Array.isArray(details.modules)) modulesData.modules = details.modules;
    const objectivesData = findAlpineData('objectives');
    if (objectivesData && Array.isArray(details.objectives)) objectivesData.objectives = details.objectives;
    const prerequisitesData = findAlpineData('prerequisites');
    if (prerequisitesData && Array.isArray(details.prerequisites)) prerequisitesData.prerequisites = details.prerequisites;
    const faqData = findAlpineData('faqs');
    if (faqData && Array.isArray(details.faqs)) faqData.faqs = details.faqs;

    if (state.analysisId) {
        setInputValue('wizard_analysis_id', state.analysisId);
    }
}

const DRAFT_FIELD_CONFIG = [
    { section: 'seo', label: 'SEO Titel', path: 'seo.seo_title', type: 'input', fieldName: 'seo_title' },
    { section: 'seo', label: 'Meta Description', path: 'seo.meta_description', type: 'textarea', rows: 2, fieldName: 'meta_description' },
    { section: 'seo', label: 'Focus Keyword', path: 'seo.focus_keyword', type: 'input', fieldName: 'focus_keyword' },
    { section: 'seo', label: 'Tags CSV', path: 'seo.tags_csv', type: 'input', fieldName: 'tags_csv' },
    { section: 'base', label: 'Titel', path: 'base.title', type: 'input', fieldName: 'title' },
    { section: 'base', label: 'Untertitel', path: 'base.subtitle', type: 'input', fieldName: 'subtitle' },
    { section: 'base', label: 'Slug', path: 'base.slug', type: 'input', fieldName: 'slug' },
    { section: 'base', label: 'Autor', path: 'base.author_name', type: 'input', fieldName: 'author_name' },
    { section: 'base', label: 'Dauer Stunden', path: 'base.duration_hours', type: 'input', fieldName: 'duration_hours' },
    { section: 'details', label: 'Kurzbeschreibung', path: 'details.short_description', type: 'textarea', rows: 3, fieldName: 'short_description' },
    { section: 'details', label: 'Langbeschreibung', path: 'details.long_description', type: 'textarea', rows: 4, fieldName: 'long_description' },
    { section: 'details', label: 'Zielgruppe Text', path: 'details.target_audience_text', type: 'textarea', rows: 2, fieldName: 'target_audience_text' },
    { section: 'details', label: 'Voraussetzungen Text', path: 'details.prerequisites_text', type: 'textarea', rows: 2, fieldName: 'prerequisites_text' },
    { section: 'details', label: 'Module (JSON)', path: 'details.modules', type: 'json', rows: 5, fieldName: 'modules' },
    { section: 'details', label: 'Lernziele (JSON)', path: 'details.objectives', type: 'json', rows: 5, fieldName: 'objectives' },
    { section: 'details', label: 'Prerequisites (JSON)', path: 'details.prerequisites', type: 'json', rows: 4, fieldName: 'prerequisites' },
    { section: 'details', label: 'FAQ (JSON)', path: 'details.faqs', type: 'json', rows: 5, fieldName: 'faqs' },
];

function createModal() {
    const modal = document.createElement('div');
    modal.id = 'ai-generator-2-modal';
    modal.className = 'fixed inset-0 z-[90] hidden bg-slate-900/80';
    modal.innerHTML = `
        <div class="h-full w-full overflow-y-auto p-6">
            <div class="mx-auto max-w-6xl rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">AI Generator 2</h2>
                    <button type="button" data-close class="rounded border px-3 py-1 text-sm">Schliessen</button>
                </div>
                <div class="mb-4 h-2 w-full rounded bg-slate-100"><div id="ai2-progress" class="h-2 rounded bg-sky-500" style="width:16%"></div></div>
                <div id="ai2-feedback" class="mb-3 hidden rounded border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"></div>
                <div id="ai2-step-body" class="space-y-4"></div>
                <div class="mt-6 flex justify-between">
                    <button type="button" id="ai2-back" class="rounded border px-4 py-2 text-sm">Zurueck</button>
                    <button type="button" id="ai2-next" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Weiter</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal);
    return modal;
}

function initAiGenerator2() {
    const openBtn = document.getElementById('open-ai-generator-2');
    const root = document.getElementById('ai-generator-2-root');
    if (!openBtn || !root) return;

    const modal = createModal();
    const body = modal.querySelector('#ai2-step-body');
    const progress = modal.querySelector('#ai2-progress');
    const feedback = modal.querySelector('#ai2-feedback');
    const backBtn = modal.querySelector('#ai2-back');
    const nextBtn = modal.querySelector('#ai2-next');
    const closeBtn = modal.querySelector('[data-close]');

    const state = {
        step: 1,
        totalSteps: 4,
        analysisId: null,
        generated: {},
        draftGenerated: {},
        keywords: [],
        selected: [],
        dirtyFields: new Set(),
        customKeywords: [],
        promptLibrary: [],
        selectedPromptId: '',
        inlinePromptText: '',
        saveInlinePrompt: false,
        promptTitle: '',
        topic: '',
        subtopics: '',
        targetAudience: '',
        level: '',
        durationDays: '',
        focus: '',
    };

    const endpoint = {
        keywordDiscovery: root.dataset.keywordDiscoveryUrl,
        saveSelection: root.dataset.saveSelectionUrl,
        regenerateField: root.dataset.regenerateFieldUrl,
        regenerateSection: root.dataset.regenerateSectionUrl,
        promptLibrary: root.dataset.promptLibraryUrl,
        savePrompt: root.dataset.promptLibraryStoreUrl,
    };

    const syncSeoFromSelectedKeywords = () => {
        if (!state.draftGenerated.seo) state.draftGenerated.seo = {};
        const tags = state.selected.join(', ');
        state.draftGenerated.seo.tags_csv = tags;
        const fallback = state.selected[0] || state.topic;
        if (!state.draftGenerated.seo.focus_keyword || !state.selected.includes(state.draftGenerated.seo.focus_keyword)) {
            state.draftGenerated.seo.focus_keyword = fallback;
        }
    };

    const upsertKeyword = (keyword, type = 'custom', selected = true, source = ['custom']) => {
        const cleaned = String(keyword ?? '').trim();
        if (!cleaned) return;
        const normalized = normalizeKeyword(cleaned);
        const existing = state.keywords.find((row) => normalizeKeyword(row.keyword) === normalized);
        if (existing) {
            existing.type = existing.type || type;
            existing.source = Array.isArray(existing.source) ? existing.source : source;
            return;
        }
        state.keywords.push({
            keyword: cleaned,
            type,
            intent: 'custom',
            relevance_score: 5,
            commercial_score: 5,
            selected,
            source,
        });
    };

    const collectCustomKeywords = () => {
        const customSet = new Map();
        state.keywords.forEach((row) => {
            const isCustom = (row.type || '') === 'custom' || (Array.isArray(row.source) && row.source.includes('custom'));
            if (!isCustom) return;
            const cleaned = String(row.keyword ?? '').trim();
            const normalized = normalizeKeyword(cleaned);
            if (!normalized || customSet.has(normalized)) return;
            customSet.set(normalized, cleaned);
        });
        return Array.from(customSet.values());
    };

    const selectedPromptBody = () => {
        if (!state.selectedPromptId) return '';
        const selected = state.promptLibrary.find((item) => String(item.id) === String(state.selectedPromptId));
        return selected?.body || '';
    };

    const setFeedback = (message, kind = 'warn') => {
        if (!feedback) return;
        if (!message) {
            feedback.classList.add('hidden');
            feedback.textContent = '';
            return;
        }
        feedback.className = 'mb-3 rounded border px-3 py-2 text-sm';
        if (kind === 'error') {
            feedback.classList.add('border-red-200', 'bg-red-50', 'text-red-900');
        } else if (kind === 'ok') {
            feedback.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-900');
        } else {
            feedback.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
        }
        feedback.textContent = message;
        feedback.classList.remove('hidden');
    };

    const request = async (url, payload, method = 'POST') => {
        let res;
        try {
            const init = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            };
            if (method !== 'GET') {
                init.body = JSON.stringify(payload ?? {});
            }
            res = await fetch(url, {
                ...init,
            });
        } catch (_networkErr) {
            throw new Error('Netzwerkfehler. Bitte Verbindung pruefen und erneut versuchen.');
        }

        const contentType = res.headers.get('content-type') || '';
        let data = {};
        if (contentType.includes('application/json')) {
            data = await res.json();
        } else {
            const text = await res.text();
            data = { message: text.slice(0, 200) };
        }

        if (res.ok) {
            return data;
        }

        if (res.status === 419) throw new Error('Sitzung abgelaufen. Bitte Seite neu laden.');
        if (res.status === 401 || res.status === 403) throw new Error('Keine Berechtigung fuer diese Aktion.');
        if (res.status === 429) throw new Error('Zu viele Anfragen. Bitte kurz warten und erneut versuchen.');
        if (res.status === 422) throw new Error(data.message || 'Eingaben sind ungueltig.');
        if (res.status >= 500) throw new Error('Serverfehler bei der AI-Verarbeitung.');

        throw new Error(data.message || 'Anfrage fehlgeschlagen.');
    };

    const renderDraftField = (field) => {
        const current = getByPath(state.draftGenerated, field.path);
        const isDirty = state.dirtyFields.has(field.path);
        const dirtyClass = isDirty ? 'border-amber-400 bg-amber-50' : 'border-slate-300 bg-white';
        const value = field.type === 'json'
            ? escapeHtml(JSON.stringify(current ?? [], null, 2))
            : escapeHtml(current ?? '');

        if (field.type === 'textarea' || field.type === 'json') {
            return `
                <label class="block text-xs font-semibold text-slate-600">${field.label}</label>
                <textarea data-draft-path="${field.path}" data-draft-type="${field.type}" rows="${field.rows || 3}" class="w-full rounded border ${dirtyClass} px-3 py-2 text-sm">${value}</textarea>
                <div class="mt-1 flex justify-end">
                    <button type="button" data-regen-field="${field.fieldName}" data-draft-path="${field.path}" class="rounded border px-2 py-1 text-xs">Feld neu generieren</button>
                </div>
            `;
        }

        return `
            <label class="block text-xs font-semibold text-slate-600">${field.label}</label>
            <div class="flex gap-2">
                <input data-draft-path="${field.path}" data-draft-type="${field.type}" class="flex-1 rounded border ${dirtyClass} px-3 py-2 text-sm" value="${value}">
                <button type="button" data-regen-field="${field.fieldName}" data-draft-path="${field.path}" class="rounded border px-2 py-1 text-xs">Neu</button>
            </div>
        `;
    };

    const render = () => {
        setFeedback('');
        progress.style.width = `${(state.step / state.totalSteps) * 100}%`;
        if (state.step === 1) {
            body.innerHTML = `
                <h3 class="text-lg font-semibold">1 Thema</h3>
                <label class="block text-sm">Thema *</label><input id="ai2-topic" class="w-full rounded border px-3 py-2" value="${state.topic}">
                <label class="block text-sm">Unterthemen (Komma)</label><input id="ai2-subtopics" class="w-full rounded border px-3 py-2" value="${state.subtopics}">
                <label class="block text-sm">Zielgruppe</label><input id="ai2-target" class="w-full rounded border px-3 py-2" value="${state.targetAudience}">
                <div class="grid grid-cols-3 gap-3">
                    <input id="ai2-level" placeholder="Niveau" class="rounded border px-3 py-2" value="${state.level}">
                    <input id="ai2-duration" placeholder="Dauer (Tage)" class="rounded border px-3 py-2" value="${state.durationDays}">
                    <input id="ai2-focus" placeholder="Fokus" class="rounded border px-3 py-2" value="${state.focus}">
                </div>`;
            nextBtn.textContent = 'SEO Keywords analysieren';
            return;
        }

        if (state.step === 2) {
            const groups = ['primary', 'longtail', 'semantic', 'related', 'custom'];
            const html = groups.map((g) => {
                const list = state.keywords.filter((k) => (k.type || 'related') === g);
                if (!list.length) return '';
                return `<h4 class="mt-3 font-semibold">${g}</h4>` + list.map((k, idx) => `
                    <label class="flex items-center gap-2 rounded border p-2 text-sm">
                        <input type="checkbox" data-kw-keyword="${escapeHtml(k.keyword)}" ${state.selected.includes(k.keyword) ? 'checked' : ''}>
                        <span class="font-medium">${k.keyword}</span>
                        <span class="ml-auto text-xs text-slate-500">${k.intent || ''} | R${k.relevance_score} C${k.commercial_score}</span>
                    </label>`).join('');
            }).join('');
            const seo = state.draftGenerated.seo || {};
            body.innerHTML = `
                <h3 class="text-lg font-semibold">2 Keyword SEO</h3>
                <div class="flex gap-2">
                    <button type="button" id="ai2-select-recommended" class="rounded border px-2 py-1 text-xs">Empfohlene</button>
                    <button type="button" id="ai2-reset" class="rounded border px-2 py-1 text-xs">Reset</button>
                </div>
                <div class="rounded border border-slate-200 p-2">
                    <label class="block text-xs font-semibold text-slate-600">Eigenes Keyword hinzufügen</label>
                    <div class="mt-1 flex gap-2">
                        <input id="ai2-custom-keyword-input" class="flex-1 rounded border px-2 py-1 text-sm" placeholder="z. B. schulung, weiterbildung">
                        <button type="button" id="ai2-add-custom-keyword" class="rounded border px-2 py-1 text-xs">Hinzufügen</button>
                    </div>
                </div>
                <div class="max-h-80 space-y-2 overflow-auto">${html}</div>
                <div class="mt-3 grid gap-2">
                    <input id="ai2-seo-title" class="rounded border px-2 py-1" value="${seo.seo_title || ''}" placeholder="SEO Titel">
                    <textarea id="ai2-seo-desc" class="rounded border px-2 py-1" rows="2" placeholder="Meta Description">${seo.meta_description || ''}</textarea>
                    <input id="ai2-focus-keyword" class="rounded border px-2 py-1" value="${seo.focus_keyword || ''}" placeholder="Fokus Keyword">
                    <div class="flex gap-2"><input id="ai2-seo-slug" class="flex-1 rounded border px-2 py-1" value="${(state.draftGenerated.base || {}).slug || ''}" placeholder="Slug"></div>
                </div>`;
            nextBtn.textContent = 'Weiter zur Vorschau';
            return;
        }

        if (state.step === 3) {
            const promptOptions = state.promptLibrary.map((prompt) => `<option value="${prompt.id}" ${String(prompt.id) === String(state.selectedPromptId) ? 'selected' : ''}>${escapeHtml(prompt.title)}</option>`).join('');
            const seoFields = DRAFT_FIELD_CONFIG.filter((field) => field.section === 'seo').map(renderDraftField).join('');
            const baseFields = DRAFT_FIELD_CONFIG.filter((field) => field.section === 'base').map(renderDraftField).join('');
            const detailFields = DRAFT_FIELD_CONFIG.filter((field) => field.section === 'details').map(renderDraftField).join('');
            body.innerHTML = `
                <h3 class="text-lg font-semibold">3 Vorschau & Bearbeiten</h3>
                <p class="text-sm text-slate-600">Hier koennen Inhalte bearbeitet oder neu generiert werden. Gelb markierte Felder wurden manuell geaendert.</p>
                <section class="rounded border border-slate-200 p-3">
                    <h4 class="font-semibold">Prompt fuer Neu-Generierung</h4>
                    <div class="mt-2 grid gap-2">
                        <select id="ai2-prompt-library-select" class="rounded border px-2 py-1 text-sm">
                            <option value="">Kein Library-Prompt</option>
                            ${promptOptions}
                        </select>
                        <textarea id="ai2-inline-prompt-text" class="rounded border px-2 py-1 text-sm" rows="3" placeholder="Optional eigener Prompt fuer Regenerate...">${escapeHtml(state.inlinePromptText)}</textarea>
                        <input id="ai2-inline-prompt-title" class="rounded border px-2 py-1 text-sm" value="${escapeHtml(state.promptTitle)}" placeholder="Titel fuer Speichern in Library (optional)">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" id="ai2-save-inline-prompt" ${state.saveInlinePrompt ? 'checked' : ''}> Prompt in Library speichern</label>
                        <div class="flex justify-end">
                            <button type="button" id="ai2-save-prompt-library-btn" class="rounded border px-2 py-1 text-xs">Prompt jetzt speichern</button>
                        </div>
                    </div>
                </section>
                <div class="space-y-4">
                    <section class="rounded border border-slate-200 p-3">
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="font-semibold">SEO</h4>
                            <button type="button" data-regen-section="seo" class="rounded border px-2 py-1 text-xs">Abschnitt neu generieren</button>
                        </div>
                        <div class="grid gap-2">${seoFields}</div>
                    </section>
                    <section class="rounded border border-slate-200 p-3">
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="font-semibold">Basisdaten</h4>
                            <button type="button" data-regen-section="base" class="rounded border px-2 py-1 text-xs">Abschnitt neu generieren</button>
                        </div>
                        <div class="grid gap-2">${baseFields}</div>
                    </section>
                    <section class="rounded border border-slate-200 p-3">
                        <div class="mb-2 flex items-center justify-between">
                            <h4 class="font-semibold">Details</h4>
                            <button type="button" data-regen-section="details" class="rounded border px-2 py-1 text-xs">Abschnitt neu generieren</button>
                        </div>
                        <div class="grid gap-2">${detailFields}</div>
                    </section>
                </div>`;
            nextBtn.textContent = 'Weiter';
            return;
        }
        body.innerHTML = `<h3 class="text-lg font-semibold">4 Uebernehmen</h3>
            <p class="text-sm text-slate-600">Die Werte werden erst jetzt in das Kursformular geschrieben. Danach koennen Sie ausserhalb des Wizards weiter anpassen und normal speichern.</p>`;
        nextBtn.textContent = 'In Formular uebernehmen';
    };

    async function runDiscovery() {
        const payload = {
            topic: document.getElementById('ai2-topic')?.value || '',
            subtopics: (document.getElementById('ai2-subtopics')?.value || '').split(',').map((v) => v.trim()).filter(Boolean),
            target_audience: document.getElementById('ai2-target')?.value || '',
            level: document.getElementById('ai2-level')?.value || '',
            duration_days: document.getElementById('ai2-duration')?.value || '',
            focus: document.getElementById('ai2-focus')?.value || '',
        };
        state.topic = payload.topic;
        state.subtopics = (payload.subtopics || []).join(', ');
        state.targetAudience = payload.target_audience;
        state.level = payload.level;
        state.durationDays = String(payload.duration_days || '');
        state.focus = payload.focus;
        if (!payload.topic) {
            alert('Thema ist Pflicht.');
            return false;
        }

        nextBtn.disabled = true;
        nextBtn.textContent = 'Analyse läuft...';
        try {
            const json = await request(endpoint.keywordDiscovery, payload);
            state.analysisId = json.analysis_id;
            state.keywords = Array.isArray(json.keywords) ? json.keywords : [];
            state.selected = state.keywords.filter((k) => k.selected).map((k) => k.keyword);
            state.generated = json.generated || {};
            state.draftGenerated = deepClone(state.generated);
            state.dirtyFields = new Set();
            state.customKeywords = [];
            syncSeoFromSelectedKeywords();
            return true;
        } catch (e) {
            setFeedback(e.message || 'Fehler', 'error');
            return false;
        } finally {
            nextBtn.disabled = false;
        }
    }

    async function persistSelection() {
        const selectedKeywords = state.selected;
        const bodyPayload = {
            analysis_id: state.analysisId,
            selected_keywords: selectedKeywords,
            selected_primary_keyword: selectedKeywords[0] || null,
            selected_clusters: [],
            custom_keywords: collectCustomKeywords(),
        };
        await request(endpoint.saveSelection, bodyPayload);

        if (state.draftGenerated.seo) {
            state.draftGenerated.seo.seo_title = document.getElementById('ai2-seo-title')?.value || state.draftGenerated.seo.seo_title;
            state.draftGenerated.seo.meta_description = document.getElementById('ai2-seo-desc')?.value || state.draftGenerated.seo.meta_description;
            state.draftGenerated.seo.focus_keyword = document.getElementById('ai2-focus-keyword')?.value || state.draftGenerated.seo.focus_keyword;
        }
        if (state.draftGenerated.base) {
            state.draftGenerated.base.slug = document.getElementById('ai2-seo-slug')?.value || state.draftGenerated.base.slug;
        }
        syncSeoFromSelectedKeywords();
    }

    async function loadPromptLibrary() {
        try {
            const data = await request(endpoint.promptLibrary, {}, 'GET');
            state.promptLibrary = Array.isArray(data.prompts) ? data.prompts : [];
        } catch (_e) {
            state.promptLibrary = [];
        }
    }

    async function saveInlinePromptToLibrary() {
        const bodyText = (state.inlinePromptText || '').trim();
        if (!bodyText) {
            setFeedback('Bitte zuerst Prompt-Text eingeben.', 'warn');
            return;
        }
        const title = (state.promptTitle || '').trim() || `AI2 Prompt ${new Date().toISOString().slice(0, 19).replace('T', ' ')}`;
        const data = await request(endpoint.savePrompt, {
            title,
            body: bodyText,
            description: 'AI Generator 2 Regenerate Prompt',
        });
        const prompt = data.prompt;
        if (prompt && prompt.id) {
            state.promptLibrary.unshift(prompt);
            state.selectedPromptId = String(prompt.id);
        }
        setFeedback('Prompt wurde in der Library gespeichert.', 'ok');
    }

    function buildPromptPayload() {
        const selectedBody = selectedPromptBody();
        const inline = (state.inlinePromptText || '').trim();
        const promptText = inline !== '' ? inline : selectedBody;
        return {
            prompt_id: state.selectedPromptId ? Number(state.selectedPromptId) : null,
            prompt_text: promptText !== '' ? promptText : null,
            save_prompt: state.saveInlinePrompt && inline !== '',
            prompt_title: state.promptTitle || null,
        };
    }

    async function regenerateField(fieldName, draftPath, trigger) {
        const context = {
            topic: state.topic,
            selected_primary_keyword: state.selected[0] || state.topic,
            current_value: getByPath(state.draftGenerated, draftPath),
        };
        if (trigger) trigger.disabled = true;
        try {
            const json = await request(endpoint.regenerateField, {
                field_name: fieldName,
                current_context: context,
                selected_keywords: state.selected,
                course_context: state.draftGenerated,
                ...buildPromptPayload(),
            });
            setByPath(state.draftGenerated, draftPath, json.value || '');
            state.dirtyFields.delete(draftPath);
            render();
            setFeedback(`Feld "${fieldName}" wurde aktualisiert.`, 'ok');
        } catch (e) {
            setFeedback(e.message || 'Feld-Update fehlgeschlagen.', 'error');
        } finally {
            if (trigger) trigger.disabled = false;
        }
    }

    async function regenerateSection(section, trigger) {
        if (trigger) trigger.disabled = true;
        try {
            const json = await request(endpoint.regenerateSection, {
                analysis_id: state.analysisId,
                section,
                selected_keywords: state.selected,
                generation_input: {
                    topic: state.topic,
                    subtopics: state.subtopics.split(',').map((item) => item.trim()).filter(Boolean),
                    target_audience: state.targetAudience,
                    level: state.level,
                    duration_days: state.durationDays ? Number(state.durationDays) : null,
                    focus: state.focus,
                },
                ...buildPromptPayload(),
            });
            const payload = json.payload && typeof json.payload === 'object' ? json.payload : {};
            setByPath(state.draftGenerated, section, payload);
            Array.from(state.dirtyFields).forEach((path) => {
                if (path.startsWith(`${section}.`)) {
                    state.dirtyFields.delete(path);
                }
            });
            render();
            setFeedback(`Abschnitt "${section}" wurde neu generiert.`, 'ok');
        } catch (e) {
            setFeedback(e.message || 'Abschnitt-Update fehlgeschlagen.', 'error');
        } finally {
            if (trigger) trigger.disabled = false;
        }
    }

    function resetState() {
        state.step = 1;
        state.analysisId = null;
        state.generated = {};
        state.draftGenerated = {};
        state.keywords = [];
        state.selected = [];
        state.dirtyFields = new Set();
        state.customKeywords = [];
        state.promptLibrary = [];
        state.selectedPromptId = '';
        state.inlinePromptText = '';
        state.saveInlinePrompt = false;
        state.promptTitle = '';
    }

    openBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        resetState();
        loadPromptLibrary();
        render();
    });

    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    backBtn.addEventListener('click', () => {
        if (state.step > 1) {
            state.step -= 1;
            render();
        }
    });

    nextBtn.addEventListener('click', async () => {
        if (state.step === 1) {
            const ok = await runDiscovery();
            if (!ok) return;
            state.step = 2;
            render();
            return;
        }
        if (state.step === 2) {
            const checkboxes = body.querySelectorAll('input[data-kw-keyword]');
            state.selected = [];
            checkboxes.forEach((cb) => {
                if (cb.checked) {
                    const keyword = String(cb.getAttribute('data-kw-keyword') || '').trim();
                    if (keyword !== '') state.selected.push(keyword);
                }
            });
            if (!state.selected.length) {
                setFeedback('Bitte mindestens ein Keyword auswaehlen.', 'warn');
                return;
            }
            try {
                await persistSelection();
            } catch (e) {
                setFeedback(e.message || 'Fehler', 'error');
                return;
            }
            state.step = 3;
            await loadPromptLibrary();
            render();
            return;
        }
        if (state.step < state.totalSteps) {
            state.step += 1;
            render();
            return;
        }
        applyGeneratedData(state.draftGenerated, state);
        modal.classList.add('hidden');
    });

    body.addEventListener('click', async (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.id === 'ai2-select-recommended') {
            state.selected = state.keywords.filter((k) => k.selected).map((k) => k.keyword);
            syncSeoFromSelectedKeywords();
            render();
        } else if (target.id === 'ai2-reset') {
            state.selected = [];
            syncSeoFromSelectedKeywords();
            render();
        } else if (target.id === 'ai2-add-custom-keyword') {
            const input = document.getElementById('ai2-custom-keyword-input');
            const raw = input ? input.value : '';
            raw.split(',').map((part) => part.trim()).filter(Boolean).forEach((keyword) => {
                upsertKeyword(keyword, 'custom', true, ['custom']);
                if (!state.selected.includes(keyword)) {
                    state.selected.push(keyword);
                }
            });
            state.customKeywords = collectCustomKeywords();
            syncSeoFromSelectedKeywords();
            if (input) input.value = '';
            render();
        } else if (target.id === 'ai2-save-prompt-library-btn') {
            target.disabled = true;
            try {
                await saveInlinePromptToLibrary();
                render();
            } catch (e) {
                setFeedback(e.message || 'Prompt konnte nicht gespeichert werden.', 'error');
            } finally {
                target.disabled = false;
            }
        } else if (target.dataset.regenField) {
            await regenerateField(target.dataset.regenField, target.dataset.draftPath || '', target);
        } else if (target.dataset.regenSection) {
            await regenerateSection(target.dataset.regenSection, target);
        }
    });

    body.addEventListener('input', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement) && !(target instanceof HTMLTextAreaElement)) return;
        const draftPath = target.dataset?.draftPath;
        if (!draftPath) return;
        const type = target.dataset?.draftType || 'input';
        let value = target.value;
        if (type === 'json') {
            try {
                value = JSON.parse(target.value || '[]');
            } catch (_err) {
                // Keep last valid JSON state until user fixes syntax.
                return;
            }
        }
        setByPath(state.draftGenerated, draftPath, value);
        state.dirtyFields.add(draftPath);
        target.classList.remove('border-slate-300', 'bg-white');
        target.classList.add('border-amber-400', 'bg-amber-50');
    });

    body.addEventListener('change', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.id === 'ai2-prompt-library-select' && target instanceof HTMLSelectElement) {
            state.selectedPromptId = target.value;
            return;
        }
        if (target.id === 'ai2-save-inline-prompt' && target instanceof HTMLInputElement) {
            state.saveInlinePrompt = target.checked;
            return;
        }
        if (target.matches('input[data-kw-keyword]') && target instanceof HTMLInputElement) {
            const keyword = String(target.getAttribute('data-kw-keyword') || '').trim();
            if (!keyword) return;
            if (target.checked && !state.selected.includes(keyword)) {
                state.selected.push(keyword);
            } else if (!target.checked) {
                state.selected = state.selected.filter((item) => item !== keyword);
            }
            syncSeoFromSelectedKeywords();
        }
    });

    body.addEventListener('keyup', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.id === 'ai2-inline-prompt-text' && target instanceof HTMLTextAreaElement) {
            state.inlinePromptText = target.value;
        }
        if (target.id === 'ai2-inline-prompt-title' && target instanceof HTMLInputElement) {
            state.promptTitle = target.value;
        }
    });
}

document.addEventListener('DOMContentLoaded', initAiGenerator2);

