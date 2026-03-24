@extends('layouts.admin')

@section('title', $title)
@section('breadcrumb', $title)

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="rounded-2xl border border-slate-100 bg-white px-6 py-8 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $title }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                Dieser Bereich ist noch in Arbeit. Die Navigation ist bereits vorbereitet.
            </p>
            <p class="mt-5">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Zurück zum Dashboard
                </a>
            </p>
        </div>
    </div>
@endsection
