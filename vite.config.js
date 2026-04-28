import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-course-editor.js',
                'resources/js/admin-ai-generator2.js',
                'resources/js/admin-category-index.js',
                'resources/js/admin-course-index.js',
                'resources/js/admin-category-parent-select.js',
                'resources/js/admin-category-ai-finalize.js',
                'resources/js/admin-course-settings.js',
            ],
            refresh: true,
        }),
    ],
});
