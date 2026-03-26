function val(id) {
    const el = document.getElementById(id);
    return el ? el.value : '';
}

function collectCategoryPayload() {
    const seo = {};
    const map = [
        ['seo_seo_title', 'seo_title'],
        ['seo_meta_description', 'meta_description'],
        ['seo_canonical_url', 'canonical_url'],
        ['seo_robots_index', 'robots_index'],
        ['seo_robots_follow', 'robots_follow'],
        ['seo_og_title', 'og_title'],
        ['seo_og_description', 'og_description'],
        ['seo_og_image', 'og_image_media_asset_id'],
        ['seo_schema_json', 'schema_json'],
    ];
    for (const [domId, key] of map) {
        const el = document.getElementById(domId);
        if (el) {
            seo[key] = el.value;
        }
    }

    let parentId = val('parent_id');
    if (parentId === '') {
        parentId = null;
    }

    const categoryIdEl = document.getElementById('category_ai_category_id');

    return {
        name: val('name'),
        slug: val('slug'),
        description: val('description'),
        parent_id: parentId,
        status: val('status') || 'draft',
        seo,
        category_id: categoryIdEl ? categoryIdEl.value : null,
    };
}

function applyFilled(filled) {
    if (filled.description && !String(val('description') || '').trim()) {
        const el = document.getElementById('description');
        if (el) {
            el.value = filled.description;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    const seo = filled.seo;
    if (seo && typeof seo === 'object') {
        const keyToId = {
            seo_title: 'seo_seo_title',
            meta_description: 'seo_meta_description',
            canonical_url: 'seo_canonical_url',
            og_title: 'seo_og_title',
            og_description: 'seo_og_description',
            schema_json: 'seo_schema_json',
        };
        for (const [k, domId] of Object.entries(keyToId)) {
            if (seo[k] == null || seo[k] === '') {
                continue;
            }
            const el = document.getElementById(domId);
            if (el && !el.value) {
                el.value = seo[k];
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }

    if (filled.parent_id != null && filled.parent_id !== '') {
        const select = document.getElementById('parent_id');
        const ts = select?.tomselect;
        if (ts) {
            ts.setValue(String(filled.parent_id), true);
        } else if (select) {
            select.value = String(filled.parent_id);
        }
    }

    const rat = document.getElementById('ai-parent-rationale');
    if (rat) {
        const text = filled.parent_suggestion_rationale;
        if (text) {
            rat.textContent = `KI-Zuordnung: ${text}`;
            rat.classList.remove('hidden');
        } else {
            rat.textContent = '';
            rat.classList.add('hidden');
        }
    }
}

function initCategoryAiFinalize() {
    const bar = document.getElementById('category-ai-finalize-bar');
    const btn = document.getElementById('category-ai-finalize-btn');
    const promptSelect = document.getElementById('category-ai-prompt-select');
    const statusEl = document.getElementById('category-ai-finalize-status');

    if (!bar || !btn) {
        return;
    }

    const url = bar.dataset.actionUrl;
    const csrf = bar.dataset.csrf;

    btn.addEventListener('click', async () => {
        const payload = collectCategoryPayload();
        const aiPromptId = promptSelect?.value || '';
        if (aiPromptId) {
            payload.ai_prompt_id = parseInt(aiPromptId, 10);
        }

        btn.disabled = true;
        if (statusEl) {
            statusEl.textContent = 'KI arbeitet …';
            statusEl.classList.remove('hidden');
        }

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const msg = data.message || data.error || `HTTP ${res.status}`;
                if (statusEl) {
                    statusEl.textContent = msg;
                    statusEl.classList.remove('hidden');
                }
                if (data.raw_reply && window.console) {
                    console.warn('KI Rohantwort:', data.raw_reply);
                }
                return;
            }

            const filled = data.filled || {};
            applyFilled(filled);

            const warnings = data.warnings || [];
            if (statusEl) {
                if (warnings.length) {
                    statusEl.textContent = warnings.join(' ');
                    statusEl.classList.remove('hidden');
                } else {
                    statusEl.textContent = 'Felder wurden ergänzt.';
                    statusEl.classList.remove('hidden');
                }
            }
        } catch (e) {
            if (statusEl) {
                statusEl.textContent = e instanceof Error ? e.message : 'Anfrage fehlgeschlagen.';
                statusEl.classList.remove('hidden');
            }
        } finally {
            btn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', initCategoryAiFinalize);
