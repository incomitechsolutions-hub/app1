@extends('layouts.admin')

@section('title', 'Anmelden')

@section('content')
    <div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-900">Anmelden</h1>
        <p class="mt-1 text-sm text-slate-600">Admin-Bereich</p>

        <form method="post" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">E-Mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Passwort</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
            </div>
            <div class="flex items-center gap-2">
                <input id="remember" name="remember" type="checkbox" value="1"
                    class="rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                <label for="remember" class="text-sm text-slate-700">Angemeldet bleiben</label>
            </div>
            <button type="submit"
                class="w-full rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Anmelden
            </button>
        </form>
    </div>
@endsection
