import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f2f9fe',
                    100: '#e6f3fd',
                    200: '#b3dbf8',
                    300: '#80c3f3',
                    400: '#4daaee',
                    500: '#0086e6',
                    600: '#0072c4',
                    700: '#005ea1',
                    800: '#004373',
                    900: '#002845',
                },
                secondary: {
                    50: '#fafdf2',
                    100: '#f5fae6',
                    200: '#e1f1b3',
                    300: '#cde880',
                    400: '#b9de4d',
                    500: '#9bd000',
                    600: '#84b100',
                    700: '#6d9200',
                    800: '#4e6800',
                    900: '#2f3e00',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
