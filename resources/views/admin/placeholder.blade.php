@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-6 py-8">
        <h1 class="text-xl font-semibold text-slate-900">{{ $title }}</h1>
        <p class="mt-2 text-sm text-slate-700">
            Dieser Bereich ist noch in Arbeit. Die Navigation ist bereits vorbereitet.
        </p>
        <p class="mt-4">
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-900 underline hover:text-slate-700">
                Zurück zum Dashboard
            </a>
        </p>
    </div>
@endsection
