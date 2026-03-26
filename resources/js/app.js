import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function initRowEditNavigation() {
    const rows = document.querySelectorAll('tr[data-edit-url]');
    rows.forEach((row) => {
        if (row.dataset.rowNavBound === '1') {
            return;
        }
        row.dataset.rowNavBound = '1';
        row.classList.add('cursor-pointer');
        row.setAttribute('tabindex', '0');

        const navigate = () => {
            const url = row.getAttribute('data-edit-url');
            if (url) {
                window.location.href = url;
            }
        };

        row.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                navigate();
                return;
            }
            if (target.closest('a,button,input,select,textarea,label,form,[data-no-row-nav]')) {
                return;
            }
            navigate();
        });

        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                navigate();
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRowEditNavigation);
} else {
    initRowEditNavigation();
}
