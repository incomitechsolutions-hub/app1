import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

function buildTomOptions(select) {
    return Array.from(select.options).map((opt) => ({
        value: opt.value,
        text: opt.text,
        searchName: (opt.dataset.searchName || opt.text || '').toLowerCase(),
        depth: opt.value === '' ? -1 : Number(opt.dataset.depth ?? 0),
    }));
}

function initParentPickers() {
    document.querySelectorAll('[data-parent-picker]').forEach((wrap) => {
        const select = wrap.querySelector('select[name="parent_id"]');
        if (!select) {
            return;
        }

        const selected = select.dataset.selected || '';
        const originalHtml = select.innerHTML;
        const options = buildTomOptions(select);

        try {
            select.innerHTML = '';
            new TomSelect(select, {
                allowEmptyOption: true,
                options,
                valueField: 'value',
                labelField: 'text',
                searchField: ['searchName', 'text'],
                plugins: ['dropdown_input'],
                maxOptions: null,
                create: false,
                controlClass:
                    'ts-control !flex !min-h-[42px] !w-full !items-center !rounded-lg !border !border-slate-300 !bg-white !px-3 !py-2 !text-sm shadow-sm focus-within:!border-slate-500 focus-within:!ring-1 focus-within:!ring-slate-500',
                dropdownClass:
                    'ts-dropdown !z-50 !max-h-[min(24rem,70vh)] !overflow-y-auto !rounded-lg !border !border-slate-200 !bg-white !shadow-lg',
                score(search) {
                    const q = search.toLowerCase().trim();
                    return function scoreItem(item) {
                        if (q === '') {
                            return 1;
                        }
                        if (item.value === '') {
                            return 0.5;
                        }
                        const sn = typeof item.searchName === 'string' ? item.searchName : '';
                        const tx = typeof item.text === 'string' ? item.text.toLowerCase() : '';
                        if (sn.includes(q) || tx.includes(q)) {
                            return 1;
                        }

                        return 0;
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
        } catch (e) {
            select.innerHTML = originalHtml;
            select.classList.remove('hidden');
            console.error('[parent-picker]', e);
        }
    });
}

document.addEventListener('DOMContentLoaded', initParentPickers);
