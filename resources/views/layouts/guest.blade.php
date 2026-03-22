<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Anmelden') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-lg items-center justify-between gap-4 px-4 py-3">
                <a href="{{ url('/') }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">{{ config('app.name') }}</a>
            </div>
        </header>
        <main class="flex flex-1 items-start justify-center px-4 py-10 sm:py-16">
            @yield('content')
        </main>
    </div>
</body>
</html>
