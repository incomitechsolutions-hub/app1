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
    let dragState = null;
    let collapsedIds = new Set();
    const debounceMs = 400;

    function getVisibleRows() {
        return [...swap.querySelectorAll('tbody tr[data-category-id]')];
    }

    function initializeInlineFieldSnapshots() {
        swap.querySelectorAll('[data-inline-field]').forEach((el) => {
            el.dataset.lastValue = String(el.value ?? '');
        });
    }

    function applyTreeVisibility() {
        const rows = getVisibleRows();
        rows.forEach((row) => {
            const depth = Number(row.dataset.depth || '0');
            const id = Number(row.dataset.categoryId || '0');
            let hidden = false;
            if (depth > 0) {
                let seekDepth = depth;
                for (let i = rows.indexOf(row) - 1; i >= 0; i -= 1) {
                    const parentRow = rows[i];
                    const parentDepth = Number(parentRow.dataset.depth || '0');
                    if (parentDepth < seekDepth) {
                        const parentId = Number(parentRow.dataset.categoryId || '0');
                        if (collapsedIds.has(parentId)) {
                            hidden = true;
                            break;
                        }
                        seekDepth = parentDepth;
                    }
                }
            }

            row.classList.toggle('hidden', hidden);
            const icon = row.querySelector('[data-tree-toggle-icon]');
            if (icon) {
                icon.textContent = collapsedIds.has(id) ? '▸' : '▾';
            }
        });
    }

    function collectDragBlock(rows, startIndex) {
        const root = rows[startIndex];
        const rootDepth = Number(root.dataset.depth || '0');
        const block = [root];
        for (let i = startIndex + 1; i < rows.length; i += 1) {
            const depth = Number(rows[i].dataset.depth || '0');
            if (depth <= rootDepth) {
                break;
            }
            block.push(rows[i]);
        }
        return block;
    }

    function computeHierarchyFromRows(rows) {
        const stack = [];
        const nodes = [];
        rows.forEach((row) => {
            const id = Number(row.dataset.categoryId || '0');
            const depth = Math.max(0, Number(row.dataset.depth || '0'));
            const parentId = depth === 0 ? null : (stack[depth - 1] ?? null);
            nodes.push({ id, parent_id: parentId });
            stack[depth] = id;
            stack.length = depth + 1;
            row.dataset.parentId = parentId === null ? '' : String(parentId);
        });
        return nodes;
    }

    async function persistHierarchy(nodes) {
        const table = swap.querySelector('table[data-category-reorder-url]');
        const endpoint = table?.dataset.categoryReorderUrl || '';
        if (!endpoint) {
            return false;
        }
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ nodes }),
        });
        const payload = await res.json().catch(() => ({}));
        if (!res.ok || payload.ok !== true) {
            const message = payload.message || 'Reihenfolge konnte nicht gespeichert werden.';
            showInlineMessage(swap, message, 'error');
            return false;
        }
        if (payload.message) {
            showInlineMessage(swap, payload.message, 'success');
        }
        return true;
    }

    function normalizeInlineValue(field, element) {
        const raw = element.value;
        if (field === 'parent_id') {
            return raw === '' ? null : Number(raw);
        }
        if (field === 'sort_order') {
            if (raw === '') {
                return 0;
            }
            const parsed = Number(raw);
            return Number.isNaN(parsed) ? raw : parsed;
        }
        return raw;
    }

    function elementVisualState(element, state) {
        element.classList.remove('border-amber-300', 'border-emerald-300', 'bg-emerald-50', 'bg-amber-50');
        if (state === 'saving') {
            element.classList.add('border-amber-300', 'bg-amber-50');
        } else if (state === 'saved') {
            element.classList.add('border-emerald-300', 'bg-emerald-50');
            window.setTimeout(() => {
                element.classList.remove('border-emerald-300', 'bg-emerald-50');
            }, 1000);
        }
    }

    async function saveInlineField(element) {
        const tr = element.closest('tr[data-category-id]');
        if (!tr) {
            return;
        }
        const endpoint = tr.dataset.patchUrl || '';
        const field = element.dataset.inlineField || '';
        if (!endpoint || !field) {
            return;
        }

        const value = normalizeInlineValue(field, element);
        const current = element.dataset.lastValue ?? String(element.value ?? '');
        const nextComparable = value === null ? '' : String(value);
        if (current === nextComparable) {
            return;
        }

        element.disabled = true;
        elementVisualState(element, 'saving');

        const res = await fetch(endpoint, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ [field]: value }),
        });

        const payload = await res.json().catch(() => ({}));
        element.disabled = false;

        if (!res.ok || payload.ok !== true) {
            const message =
                payload?.errors?.[field]?.[0] ||
                payload?.message ||
                'Speichern fehlgeschlagen.';
            showInlineMessage(swap, message, 'error');
            const fallback = element.dataset.lastValue ?? '';
            element.value = fallback;
            elementVisualState(element, null);
            return;
        }

        const savedValue = payload?.category?.[field];
        if (savedValue !== undefined && savedValue !== null) {
            element.value = String(savedValue);
            element.dataset.lastValue = String(savedValue);
        } else if (savedValue === null) {
            element.value = '';
            element.dataset.lastValue = '';
        } else {
            element.dataset.lastValue = String(element.value ?? '');
        }

        if (field === 'parent_id') {
            tr.dataset.parentId = element.value;
        }

        elementVisualState(element, 'saved');
    }

    function setupRowDragAndDrop() {
        const table = swap.querySelector('table[data-category-reorder-url]');
        const dragEnabled = table?.dataset.dragEnabled === '1';
        const rows = getVisibleRows();
        if (rows.length === 0) {
            return;
        }

        rows.forEach((row) => {
            const handle = row.querySelector('[data-drag-handle]');
            if (!handle || row.dataset.dragBound === '1') {
                return;
            }
            row.dataset.dragBound = '1';
            if (!dragEnabled) {
                handle.classList.add('opacity-40', 'cursor-not-allowed');
                handle.title = 'Ziehen nur in \"Alle Ebenen\" verfügbar';
                return;
            }

            handle.addEventListener('mousedown', () => {
                row.setAttribute('draggable', 'true');
            });
            handle.addEventListener('mouseup', () => {
                row.setAttribute('draggable', 'false');
            });
            handle.addEventListener('mouseleave', () => {
                row.setAttribute('draggable', 'false');
            });
        });

        swap.querySelectorAll('tbody tr[data-category-id]').forEach((row) => {
            if (row.dataset.dropBound === '1') {
                return;
            }
            row.dataset.dropBound = '1';
            if (!dragEnabled) {
                return;
            }

            row.addEventListener('dragstart', (e) => {
                const all = getVisibleRows();
                const index = all.indexOf(row);
                if (index < 0) {
                    return;
                }
                const block = collectDragBlock(all, index);
                dragState = {
                    row,
                    rows: all,
                    block,
                    startHtml: swap.innerHTML,
                };
                row.classList.add('opacity-50');
                if (e.dataTransfer) {
                    e.dataTransfer.effectAllowed = 'move';
                }
            });

            row.addEventListener('dragend', () => {
                row.classList.remove('opacity-50');
                row.setAttribute('draggable', 'false');
            });

            row.addEventListener('dragover', (e) => {
                if (!dragState) {
                    return;
                }
                e.preventDefault();
                row.classList.add('ring-2', 'ring-sky-300');
            });

            row.addEventListener('dragleave', () => {
                row.classList.remove('ring-2', 'ring-sky-300');
            });

            row.addEventListener('drop', async (e) => {
                row.classList.remove('ring-2', 'ring-sky-300');
                if (!dragState) {
                    return;
                }
                e.preventDefault();

                const allRows = getVisibleRows();
                const fromIndex = allRows.indexOf(dragState.row);
                const toIndexRaw = allRows.indexOf(row);
                if (fromIndex < 0 || toIndexRaw < 0) {
                    dragState = null;
                    return;
                }
                const dropAfter = e.clientY > row.getBoundingClientRect().top + row.offsetHeight / 2;

                const movingSet = new Set(dragState.block);
                const remaining = allRows.filter((r) => !movingSet.has(r));
                const targetBaseIndex = remaining.indexOf(row);
                const insertIndex = targetBaseIndex + (dropAfter ? 1 : 0);
                remaining.splice(insertIndex, 0, ...dragState.block);

                // Compute new depth based on horizontal drop position.
                const targetCell = row.querySelector('[data-tree-cell]');
                const baseLeft = targetCell ? targetCell.getBoundingClientRect().left : row.getBoundingClientRect().left;
                const rawDepth = Math.max(0, Math.round((e.clientX - (baseLeft + 72)) / 24));
                const prevRow = insertIndex > 0 ? remaining[insertIndex - 1] : null;
                const maxDepth = prevRow ? Number(prevRow.dataset.depth || '0') + 1 : 0;
                const newDepth = Math.min(rawDepth, maxDepth);
                const oldRootDepth = Number(dragState.block[0].dataset.depth || '0');
                const delta = newDepth - oldRootDepth;
                dragState.block.forEach((blockRow) => {
                    const current = Number(blockRow.dataset.depth || '0');
                    blockRow.dataset.depth = String(Math.max(0, current + delta));
                });

                const tbody = row.closest('tbody');
                if (!tbody) {
                    dragState = null;
                    return;
                }
                remaining.forEach((r) => tbody.appendChild(r));

                const nodes = computeHierarchyFromRows(remaining);
                const ok = await persistHierarchy(nodes);
                if (!ok) {
                    swap.innerHTML = dragState.startHtml;
                    setupRowDragAndDrop();
                }

                dragState = null;
            });
        });
    }

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
        initializeInlineFieldSnapshots();
        applyTreeVisibility();
        setupRowDragAndDrop();

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
        if (e.target.classList.contains('js-category-select-all')) {
            const checked = e.target.checked;
            swap.querySelectorAll('.js-category-row-checkbox').forEach((cb) => {
                cb.checked = checked;
            });
            e.target.indeterminate = false;
            return;
        }
        if (e.target.classList.contains('js-category-row-checkbox')) {
            const all = swap.querySelectorAll('.js-category-row-checkbox');
            const selectAll = swap.querySelector('.js-category-select-all');
            if (selectAll) {
                const n = all.length;
                const checkedCount = [...all].filter((cb) => cb.checked).length;
                selectAll.checked = n > 0 && checkedCount === n;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < n;
            }
            return;
        }
        const form = e.target.closest('form.admin-panel');
        if (!form || !swap.contains(e.target)) {
            if (e.target.matches('[data-inline-field]')) {
                void saveInlineField(e.target);
            }
            return;
        }
        if (e.target.name === 'search') {
            return;
        }
        void loadFragment(buildUrlFromForm(form));
    });

    root.addEventListener('focusin', (e) => {
        if (!e.target.matches('[data-inline-field]')) {
            return;
        }
        e.target.dataset.lastValue = String(e.target.value ?? '');
    });

    root.addEventListener('blur', (e) => {
        if (!e.target.matches('input[data-inline-field]')) {
            return;
        }
        void saveInlineField(e.target);
    }, true);

    root.addEventListener('keydown', (e) => {
        if (!e.target.matches('input[data-inline-field]')) {
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            e.target.blur();
        }
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

        const toggleBtn = e.target.closest('[data-tree-toggle]');
        if (toggleBtn && swap.contains(toggleBtn)) {
            e.preventDefault();
            const tr = toggleBtn.closest('tr[data-category-id]');
            if (!tr) {
                return;
            }
            const id = Number(tr.dataset.categoryId || '0');
            if (collapsedIds.has(id)) {
                collapsedIds.delete(id);
            } else {
                collapsedIds.add(id);
            }
            applyTreeVisibility();
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

    initializeInlineFieldSnapshots();
    applyTreeVisibility();
    setupRowDragAndDrop();
}

document.addEventListener('DOMContentLoaded', initCategoryIndex);
