function showInlineMessage(container, message, variant = 'success') {
    const styles =
        variant === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
            : 'border-amber-200 bg-amber-50 text-amber-900';
    const el = document.createElement('div');
    el.className = `mb-4 rounded-xl border px-4 py-3 text-sm ${styles}`;
    el.setAttribute('role', 'status');
    el.textContent = message;
    container.insertBefore(el, container.firstChild);
    window.setTimeout(() => el.remove(), 6000);
}

function initCategoryIndex() {
    const root = document.querySelector('[data-category-index-root]');
    if (!root) {
        return;
    }

    const swap = document.getElementById('category-index-swap');
    const csrf = root.dataset.csrfToken;
    if (!swap || !csrf) {
        return;
    }

    let searchTimer = null;
    const debounceMs = 400;

    async function loadFragment(url, { pushHistory = true } = {}) {
        const u = new URL(url, window.location.origin);
        u.searchParams.set('fragment', '1');

        const res = await fetch(u.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            const clean = new URL(u);
            clean.searchParams.delete('fragment');
            window.location.href = clean.pathname + clean.search;
            return;
        }

        const html = await res.text();
        swap.innerHTML = html;

        if (pushHistory) {
            const clean = new URL(u);
            clean.searchParams.delete('fragment');
            window.history.pushState({}, '', clean.pathname + clean.search);
        }
    }

    function buildUrlFromForm(form) {
        const params = new URLSearchParams();
        const fd = new FormData(form);
        for (const [key, value] of fd.entries()) {
            if (value === '' && (key === 'search' || key === 'status')) {
                continue;
            }
            params.append(key, value);
        }
        const base = form.getAttribute('action') || window.location.pathname;
        return `${base}?${params.toString()}`;
    }

    root.addEventListener('change', (e) => {
        const form = e.target.closest('form.admin-panel');
        if (!form || !swap.contains(e.target)) {
            return;
        }
        if (e.target.name === 'search') {
            return;
        }
        void loadFragment(buildUrlFromForm(form));
    });

    root.addEventListener('input', (e) => {
        if (!e.target.matches('form.admin-panel input[name="search"]')) {
            return;
        }
        const form = e.target.closest('form.admin-panel');
        if (!form) {
            return;
        }
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            void loadFragment(buildUrlFromForm(form));
        }, debounceMs);
    });

    root.addEventListener('click', (e) => {
        const ajaxNav = e.target.closest('a.js-category-ajax');
        if (ajaxNav && swap.contains(ajaxNav)) {
            e.preventDefault();
            void loadFragment(ajaxNav.href);
            return;
        }

        const tr = e.target.closest('tbody tr[data-edit-url]');
        if (tr && swap.contains(tr)) {
            if (e.target.closest('[data-row-action]')) {
                return;
            }
            window.location.href = tr.dataset.editUrl;
            return;
        }

        const delBtn = e.target.closest('button[data-category-delete]');
        if (delBtn && swap.contains(delBtn)) {
            e.preventDefault();
            const form = delBtn.closest('form');
            if (!form || !window.confirm('Kategorie wirklich löschen?')) {
                return;
            }

            const body = new URLSearchParams();
            body.append('_token', csrf);
            body.append('_method', 'DELETE');

            void fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body,
                credentials: 'same-origin',
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (res.ok) {
                        if (data.message) {
                            showInlineMessage(swap, data.message, 'success');
                        }
                        const clean = new URL(window.location.href);
                        clean.searchParams.delete('fragment');
                        await loadFragment(clean.toString(), { pushHistory: false });
                        return;
                    }
                    const msg =
                        typeof data.message === 'string'
                            ? data.message
                            : 'Löschen nicht möglich.';
                    showInlineMessage(swap, msg, 'error');
                })
                .catch(() => {
                    window.location.reload();
                });
        }
    });

    window.addEventListener('popstate', () => {
        void loadFragment(window.location.href, { pushHistory: false });
    });
}

document.addEventListener('DOMContentLoaded', initCategoryIndex);
