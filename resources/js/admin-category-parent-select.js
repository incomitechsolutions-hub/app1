import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

function initParentPickers() {
    document.querySelectorAll('[data-parent-picker]').forEach((wrap) => {
        const select = wrap.querySelector('select[name="parent_id"]');
        const jsonEl = wrap.querySelector('[data-parent-picker-json]');
        if (!select || !jsonEl) {
            return;
        }

        let raw = [];
        try {
            raw = JSON.parse(jsonEl.textContent || '[]');
        } catch {
            raw = [];
        }

        const selected = select.dataset.selected || '';

        const options = [
            {
                value: '',
                text: '— Keine (Hauptkategorie) —',
                depth: -1,
                searchName: '',
            },
            ...raw.map((o) => ({
                value: String(o.id),
                text: o.label,
                depth: o.depth,
                searchName: o.searchName,
            })),
        ];

        new TomSelect(select, {
            allowEmptyOption: true,
            options,
            valueField: 'value',
            labelField: 'text',
            searchField: ['searchName'],
            plugins: ['dropdown_input'],
            maxOptions: null,
            create: false,
            controlClass:
                'ts-control !flex !min-h-[42px] !w-full !items-center !rounded-lg !border !border-slate-300 !bg-white !px-3 !py-2 !text-sm shadow-sm focus-within:!border-slate-500 focus-within:!ring-1 focus-within:!ring-slate-500',
            dropdownClass: 'ts-dropdown !z-50 !rounded-lg !border !border-slate-200 !bg-white !shadow-lg',
            score(search) {
                const q = search.toLowerCase().trim();
                return function scoreItem(item) {
                    if (q === '') {
                        return 1;
                    }
                    if (item.value === '') {
                        return 1;
                    }
                    const sn = typeof item.searchName === 'string' ? item.searchName : '';
                    return sn.startsWith(q) ? 1 : 0;
                };
            },
            render: {
                option(data, escape) {
                    if (data.value === '') {
                        return `<div class="px-2 py-1.5 text-sm text-slate-600">${escape(data.text)}</div>`;
                    }
                    const cls =
                        Number(data.depth) === 0 ? 'font-semibold text-emerald-700' : 'text-slate-800';
                    return `<div class="px-2 py-1.5 text-sm ${cls}">${escape(data.text)}</div>`;
                },
                item(data, escape) {
                    return `<div class="truncate text-sm text-slate-900">${escape(data.text)}</div>`;
                },
                no_results() {
                    return '<div class="px-2 py-2 text-sm text-slate-500">Keine Treffer</div>';
                },
            },
            onInitialize() {
                const v = selected === '' ? '' : String(selected);
                this.setValue(v, true);
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', initParentPickers);
