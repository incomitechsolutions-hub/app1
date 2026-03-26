/**
 * Row click → Bearbeiten (wie Kategorien-Index), Aktionen-Spalte mit data-row-action ausgenommen.
 */
function initCourseIndexRowClick() {
    const root = document.querySelector('[data-course-index-root]');
    if (!root) {
        return;
    }

    root.addEventListener('click', (e) => {
        const tr = e.target.closest('tbody tr[data-edit-url]');
        if (!tr || !root.contains(tr)) {
            return;
        }
        if (e.target.closest('[data-row-action]')) {
            return;
        }
        window.location.href = tr.dataset.editUrl;
    });
}

document.addEventListener('DOMContentLoaded', initCourseIndexRowClick);
