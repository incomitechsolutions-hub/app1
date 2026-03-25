function readCsrf() {
    return document.querySelector('input[name="_token"]')?.value ?? '';
}

function initGroupDiscountTiers() {
    const root = document.getElementById('group-discount-tiers-root');
    if (!root) {
        return;
    }

    const storeUrl = root.dataset.storeUrl;
    const tierApiPrefix = (root.dataset.tierApiPrefix || '').replace(/\/$/, '');
    const tbody = root.querySelector('[data-tier-tbody]');
    const addMin = root.querySelector('[data-add-min]');
    const addPct = root.querySelector('[data-add-pct]');
    const addBtn = root.querySelector('[data-add-btn]');
    const addReset = root.querySelector('[data-add-reset]');

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    const rowHtml = (tier) => {
        const id = tier.id;
        const min = esc(String(tier.min_participants));
        const pct = esc(String(tier.discount_percent));
        return `
            <tr data-tier-id="${id}">
                <td class="px-4 py-2 text-slate-500" data-col-idx></td>
                <td class="px-4 py-2">
                    <input type="number" min="1" data-tier-min value="${min}" class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2">
                    <input type="number" step="0.01" min="0" max="100" data-tier-pct value="${pct}" class="w-full rounded border border-slate-200 px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-2 text-right whitespace-nowrap">
                    <button type="button" data-tier-save class="mr-2 rounded bg-sky-600 px-2 py-1 text-xs font-semibold text-white hover:bg-sky-700">Speichern</button>
                    <button type="button" data-tier-delete class="rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Löschen</button>
                </td>
            </tr>`;
    };

    const renumber = () => {
        if (!tbody) {
            return;
        }
        [...tbody.querySelectorAll('tr')].forEach((tr, i) => {
            const c = tr.querySelector('[data-col-idx]');
            if (c) {
                c.textContent = String(i + 1);
            }
        });
    };

    const bindRow = (tr) => {
        const id = tr.getAttribute('data-tier-id');
        tr.querySelector('[data-tier-save]')?.addEventListener('click', () => {
            const min = tr.querySelector('[data-tier-min]')?.value;
            const pct = tr.querySelector('[data-tier-pct]')?.value;
            const url = updateTpl.replace('__ID__', id);
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': readCsrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    min_participants: min,
                    discount_percent: pct,
                }),
            })
                .then((r) => {
                    if (!r.ok) {
                        throw new Error('save failed');
                    }
                    return r.json();
                })
                .catch(() => window.alert('Speichern fehlgeschlagen.'));
        });
        tr.querySelector('[data-tier-delete]')?.addEventListener('click', () => {
            if (!window.confirm('Regel löschen?')) {
                return;
            }
            const url = `${tierApiPrefix}/${id}`;
            fetch(url, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': readCsrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
                .then((r) => {
                    if (!r.ok) {
                        throw new Error('delete failed');
                    }
                    tr.remove();
                    renumber();
                })
                .catch(() => window.alert('Löschen fehlgeschlagen.'));
        });
    };

    tbody?.querySelectorAll('tr[data-tier-id]').forEach(bindRow);

    addBtn?.addEventListener('click', () => {
        const min = addMin?.value;
        const pct = addPct?.value;
        if (!min || pct === '' || pct === null) {
            return;
        }
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': readCsrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                min_participants: min,
                discount_percent: pct,
            }),
        })
            .then((r) => {
                if (!r.ok) {
                    throw new Error('create failed');
                }
                return r.json();
            })
            .then((j) => {
                const t = j.tier;
                if (!t || !tbody) {
                    return;
                }
                tbody.insertAdjacentHTML('beforeend', rowHtml(t));
                const tr = tbody.querySelector(`tr[data-tier-id="${t.id}"]`);
                if (tr) {
                    bindRow(tr);
                }
                renumber();
                if (addMin) {
                    addMin.value = '';
                }
                if (addPct) {
                    addPct.value = '';
                }
            })
            .catch(() => window.alert('Anlegen fehlgeschlagen.'));
    });

    addReset?.addEventListener('click', () => {
        if (addMin) {
            addMin.value = '';
        }
        if (addPct) {
            addPct.value = '';
        }
    });

    renumber();
}

document.addEventListener('DOMContentLoaded', initGroupDiscountTiers);
