<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script>
        (function () {
            var theme = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var shouldUseDark = theme ? theme === 'dark' : prefersDark;

            if (shouldUseDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('meta')
    @stack('head')
</head>
<body
    x-data="{ mobileMenuOpen: false }"
    x-effect="document.body.style.overflow = mobileMenuOpen ? 'hidden' : ''"
    @keydown.escape.window="mobileMenuOpen = false"
    class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-white">
    <div class="flex min-h-screen flex-col">
        @include('partials.public.header')
        @include('partials.public.mobile-menu')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.public.footer')
    </div>

    @stack('scripts')
</body>
</html>
