<button
    type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    x-on:click="
        dark = !dark;
        document.documentElement.classList.toggle('dark', dark);
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    "
    x-bind:aria-label="dark ? 'Helles Design aktivieren' : 'Dunkles Design aktivieren'"
    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:border-primary-300 hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:text-gray-200 dark:hover:border-primary-500 dark:hover:text-white">
    <svg x-show="!dark" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path d="M10 2.5a.75.75 0 0 1 .75.75V5a.75.75 0 0 1-1.5 0V3.25A.75.75 0 0 1 10 2.5ZM10 15a.75.75 0 0 1 .75.75v1.75a.75.75 0 0 1-1.5 0v-1.75A.75.75 0 0 1 10 15ZM4.697 4.697a.75.75 0 0 1 1.06 0L7 5.94A.75.75 0 1 1 5.94 7L4.697 5.757a.75.75 0 0 1 0-1.06ZM13 14.06a.75.75 0 0 1 1.06 0l1.243 1.243a.75.75 0 1 1-1.06 1.06L13 15.12a.75.75 0 0 1 0-1.06ZM2.5 10a.75.75 0 0 1 .75-.75H5a.75.75 0 0 1 0 1.5H3.25A.75.75 0 0 1 2.5 10ZM15 10a.75.75 0 0 1 .75-.75h1.75a.75.75 0 0 1 0 1.5h-1.75A.75.75 0 0 1 15 10ZM5.94 13a.75.75 0 0 1 1.06 1.06l-1.243 1.243a.75.75 0 1 1-1.06-1.06L5.94 13ZM14.06 7a.75.75 0 0 1 1.06-1.06l1.243 1.243a.75.75 0 0 1-1.06 1.06L14.06 7ZM10 6.25a3.75 3.75 0 1 1 0 7.5 3.75 3.75 0 0 1 0-7.5Z" />
    </svg>
    <svg x-show="dark" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path d="M11.79 3.063a.75.75 0 0 0-1.03-.854A7.5 7.5 0 1 0 17.79 13.24a.75.75 0 0 0-.854-1.03 6 6 0 0 1-7.745-7.746Z" />
    </svg>
</button>
