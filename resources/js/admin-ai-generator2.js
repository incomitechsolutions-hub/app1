function csrfToken() {
    return document.querySelector('input[name="_token"]')?.value ?? '';
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
    const backBtn = modal.querySelector('#ai2-back');
    const nextBtn = modal.querySelector('#ai2-next');
    const closeBtn = modal.querySelector('[data-close]');

    const state = {
        step: 1,
        analysisId: null,
        generated: {},
        keywords: [],
        selected: [],
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
    };

    const render = () => {
        progress.style.width = `${(state.step / 6) * 100}%`;
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
            const groups = ['primary', 'longtail', 'semantic', 'related'];
            const html = groups.map((g) => {
                const list = state.keywords.filter((k) => (k.type || 'related') === g);
                if (!list.length) return '';
                return `<h4 class="mt-3 font-semibold">${g}</h4>` + list.map((k, idx) => `
                    <label class="flex items-center gap-2 rounded border p-2 text-sm">
                        <input type="checkbox" data-kw="${idx}" ${state.selected.includes(k.keyword) ? 'checked' : ''}>
                        <span class="font-medium">${k.keyword}</span>
                        <span class="ml-auto text-xs text-slate-500">${k.intent || ''} | R${k.relevance_score} C${k.commercial_score}</span>
                    </label>`).join('');
            }).join('');
            const seo = state.generated.seo || {};
            body.innerHTML = `
                <h3 class="text-lg font-semibold">2 Keyword SEO</h3>
                <div class="flex gap-2">
                    <button type="button" id="ai2-select-recommended" class="rounded border px-2 py-1 text-xs">Empfohlene</button>
                    <button type="button" id="ai2-reset" class="rounded border px-2 py-1 text-xs">Reset</button>
                </div>
                <div class="max-h-80 space-y-2 overflow-auto">${html}</div>
                <div class="mt-3 grid gap-2">
                    <input id="ai2-seo-title" class="rounded border px-2 py-1" value="${seo.seo_title || ''}" placeholder="SEO Titel">
                    <textarea id="ai2-seo-desc" class="rounded border px-2 py-1" rows="2" placeholder="Meta Description">${seo.meta_description || ''}</textarea>
                    <input id="ai2-focus-keyword" class="rounded border px-2 py-1" value="${seo.focus_keyword || ''}" placeholder="Fokus Keyword">
                    <div class="flex gap-2"><input id="ai2-seo-slug" class="flex-1 rounded border px-2 py-1" value="${(state.generated.base || {}).slug || ''}" placeholder="Slug">
                    <button type="button" id="ai2-regen-seo-title" class="rounded border px-2 py-1 text-xs">Neu generieren SEO Titel</button></div>
                </div>`;
            nextBtn.textContent = 'Weiter zur Content-Erstellung';
            return;
        }

        if (state.step === 3) {
            body.innerHTML = `<h3 class="text-lg font-semibold">3 SEO</h3><p>SEO-Felder werden in die bestehende SEO-Maske übernommen.</p>`;
            nextBtn.textContent = 'Weiter';
            return;
        }
        if (state.step === 4) {
            body.innerHTML = `<h3 class="text-lg font-semibold">4 Basisdaten</h3><p>Basiseinstellungen werden in die vorhandenen Kursfelder geschrieben.</p>`;
            nextBtn.textContent = 'Weiter';
            return;
        }
        if (state.step === 5) {
            body.innerHTML = `<h3 class="text-lg font-semibold">5 Details</h3><p>Beschreibung, Module, Lernziele, FAQ werden befüllt.</p>`;
            nextBtn.textContent = 'Weiter';
            return;
        }
        body.innerHTML = `<h3 class="text-lg font-semibold">6 Review</h3><p>Prüfen und als Entwurf speichern.</p>
            <label class="mt-2 flex items-center gap-2 text-sm text-slate-500"><input type="checkbox" disabled> Auch Kursvarianten erzeugen</label>`;
        nextBtn.textContent = 'Als Entwurf speichern';
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
            const res = await fetch(endpoint.keywordDiscovery, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Keyword Discovery fehlgeschlagen');
            state.analysisId = json.analysis_id;
            state.keywords = Array.isArray(json.keywords) ? json.keywords : [];
            state.selected = state.keywords.filter((k) => k.selected).map((k) => k.keyword);
            state.generated = json.generated || {};
            return true;
        } catch (e) {
            alert(e.message || 'Fehler');
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
        };
        const res = await fetch(endpoint.saveSelection, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify(bodyPayload),
        });
        if (!res.ok) {
            const j = await res.json();
            throw new Error(j.message || 'Speichern der Auswahl fehlgeschlagen');
        }

        if (state.generated.seo) {
            state.generated.seo.seo_title = document.getElementById('ai2-seo-title')?.value || state.generated.seo.seo_title;
            state.generated.seo.meta_description = document.getElementById('ai2-seo-desc')?.value || state.generated.seo.meta_description;
            state.generated.seo.focus_keyword = document.getElementById('ai2-focus-keyword')?.value || state.generated.seo.focus_keyword;
        }
        if (state.generated.base) {
            state.generated.base.slug = document.getElementById('ai2-seo-slug')?.value || state.generated.base.slug;
        }
        applyGeneratedData(state.generated, state);
    }

    openBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        state.step = 1;
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
            const checkboxes = body.querySelectorAll('input[data-kw]');
            state.selected = [];
            checkboxes.forEach((cb) => {
                if (cb.checked) {
                    const kw = state.keywords[Number(cb.getAttribute('data-kw'))];
                    if (kw) state.selected.push(kw.keyword);
                }
            });
            if (!state.selected.length) {
                alert('Bitte mindestens ein Keyword auswählen.');
                return;
            }
            try {
                await persistSelection();
            } catch (e) {
                alert(e.message || 'Fehler');
                return;
            }
            state.step = 3;
            render();
            return;
        }
        if (state.step < 6) {
            state.step += 1;
            render();
            return;
        }
        document.getElementById('course-create-form')?.submit();
    });

    body.addEventListener('click', async (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (target.id === 'ai2-select-recommended') {
            state.selected = state.keywords.filter((k) => k.selected).map((k) => k.keyword);
            render();
        } else if (target.id === 'ai2-reset') {
            state.selected = [];
            render();
        } else if (target.id === 'ai2-regen-seo-title') {
            const payload = {
                field_name: 'seo_title',
                current_context: { topic: state.topic, selected_primary_keyword: state.selected[0] || state.topic },
                selected_keywords: state.selected,
                course_context: {},
            };
            const res = await fetch(endpoint.regenerateField, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (res.ok) {
                const input = document.getElementById('ai2-seo-title');
                if (input) input.value = json.value || '';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', initAiGenerator2);

