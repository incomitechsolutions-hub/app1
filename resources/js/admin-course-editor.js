import 'trix';
import 'trix/dist/trix.css';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import axios from 'axios';

const tsControl =
    'ts-control !flex !min-h-[42px] !w-full !items-center !rounded-lg !border !border-slate-300 !bg-white !px-3 !py-2 !text-sm shadow-sm focus-within:!border-sky-500 focus-within:!ring-1 focus-within:!ring-sky-500';
const tsDropdown = 'ts-dropdown !z-50 !rounded-lg !border !border-slate-200 !bg-white !shadow-lg';

function getCsrfToken() {
    return document.querySelector('input[name="_token"]')?.value ?? '';
}

function initCourseFormLive() {
    const root = document.querySelector('[data-course-live-root]');
    if (!root) {
        return;
    }

    const live = root.getAttribute('data-live-sync') === '1';
    const patchUrl = root.getAttribute('data-patch-url') || '';
    let debounceTimer;

    const patch = (body) => {
        if (!live || !patchUrl) {
            return;
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            axios
                .patch(patchUrl, body, {
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                })
                .catch((err) => {
                    if (window.console?.error) {
                        console.error(err.response?.data ?? err);
                    }
                });
        }, 450);
    };

    let primaryTom = null;

    const primarySelect = document.getElementById('primary_category_id');
    if (primarySelect) {
        let initialPrimary = [];
        try {
            initialPrimary = JSON.parse(root.getAttribute('data-initial-primary-options') || '[]');
        } catch {
            initialPrimary = [];
        }
        const searchUrl = root.getAttribute('data-category-search-url') || '';

        primaryTom = new TomSelect(primarySelect, {
            plugins: ['dropdown_input'],
            allowEmptyOption: true,
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            options: initialPrimary,
            preload: 'focus',
            loadThrottle: 200,
            maxOptions: 100,
            load(query, callback) {
                const url = `${searchUrl}?q=${encodeURIComponent(query)}`;
                fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then((r) => r.json())
                    .then((json) => callback(json.data ?? []))
                    .catch(() => callback());
            },
            controlClass: tsControl,
            dropdownClass: tsDropdown,
            render: {
                option(data, escape) {
                    return `<div class="px-2 py-1.5 text-sm">${escape(data.name)}</div>`;
                },
                item(data, escape) {
                    return `<div class="truncate text-sm text-slate-900">${escape(data.name)}</div>`;
                },
            },
            onChange() {
                const v = this.getValue();
                patch({ primary_category_id: v ? Number(v) : null });
            },
        });

        const categoryFilter = document.getElementById('category_filter');
        if (categoryFilter && primaryTom) {
            const runFilter = () => {
                const q = categoryFilter.value.trim();
                primaryTom.clearOptions();
                primaryTom.load(q);
                primaryTom.open();
            };
            categoryFilter.addEventListener('input', runFilter);
            categoryFilter.addEventListener('focus', runFilter);
        }
    }

    const tagSelect = document.getElementById('tag_ids');
    if (tagSelect) {
        let initialTags = [];
        try {
            initialTags = JSON.parse(root.getAttribute('data-initial-tags') || '[]');
        } catch {
            initialTags = [];
        }
        const quickUrl = root.getAttribute('data-tag-quick-url') || '';

        new TomSelect(tagSelect, {
            plugins: ['remove_button', 'dropdown_input'],
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            options: initialTags,
            create: live
                ? (input, callback) => {
                      const name = input.trim();
                      if (!name) {
                          callback();
                          return;
                      }
                      fetch(quickUrl, {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              Accept: 'application/json',
                              'X-CSRF-TOKEN': getCsrfToken(),
                              'X-Requested-With': 'XMLHttpRequest',
                          },
                          credentials: 'same-origin',
                          body: JSON.stringify({ name }),
                      })
                          .then((r) => r.json())
                          .then((j) => {
                              if (j.tag) {
                                  callback({ id: j.tag.id, name: j.tag.name });
                              } else {
                                  callback();
                              }
                          })
                          .catch(() => callback());
                  }
                : false,
            createFilter: (input) => input.trim().length >= 2,
            controlClass: tsControl,
            dropdownClass: tsDropdown,
            onChange() {
                patch({ tag_ids: this.getValue().map((v) => Number(v)) });
            },
        });
    }

    const audienceSelect = document.getElementById('audience_ids');
    if (audienceSelect) {
        let initialAud = [];
        try {
            initialAud = JSON.parse(root.getAttribute('data-initial-audiences') || '[]');
        } catch {
            initialAud = [];
        }
        const quickUrl = root.getAttribute('data-audience-quick-url') || '';

        new TomSelect(audienceSelect, {
            plugins: ['remove_button', 'dropdown_input'],
            valueField: 'id',
            labelField: 'name',
            searchField: ['name'],
            options: initialAud,
            create: live
                ? (input, callback) => {
                      const name = input.trim();
                      if (!name) {
                          callback();
                          return;
                      }
                      fetch(quickUrl, {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              Accept: 'application/json',
                              'X-CSRF-TOKEN': getCsrfToken(),
                              'X-Requested-With': 'XMLHttpRequest',
                          },
                          credentials: 'same-origin',
                          body: JSON.stringify({ name }),
                      })
                          .then((r) => r.json())
                          .then((j) => {
                              if (j.audience) {
                                  callback({ id: j.audience.id, name: j.audience.name });
                              } else {
                                  callback();
                              }
                          })
                          .catch(() => callback());
                  }
                : false,
            createFilter: (input) => input.trim().length >= 2,
            controlClass: tsControl,
            dropdownClass: tsDropdown,
            onChange() {
                patch({ audience_ids: this.getValue().map((v) => Number(v)) });
            },
        });
    }

    const levelSelect = document.getElementById('difficulty_level_id');
    if (levelSelect && live) {
        levelSelect.addEventListener('change', () => {
            const v = levelSelect.value;
            patch({ difficulty_level_id: v ? Number(v) : null });
        });
    }
}

document.addEventListener('DOMContentLoaded', initCourseFormLive);
