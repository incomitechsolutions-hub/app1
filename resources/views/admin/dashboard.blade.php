@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600">
            Willkommen im Admin-Bereich, {{ auth()->user()->name }}.
        </p>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.course-catalog.courses.index') }}"
            class="rounded-lg border border-slate-200 bg-white p-8 shadow-sm transition hover:border-slate-300">
            <h2 class="font-medium text-slate-900">Kurse</h2>
            <p class="mt-1 text-sm text-slate-600">Katalog verwalten</p>
        </a>
    </div>
@endsection
