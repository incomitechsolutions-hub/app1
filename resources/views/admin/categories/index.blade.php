@extends('layouts.admin')

@section('title', 'Kategorien')
@section('breadcrumb', 'Kategorien')

@section('content')
    <div id="category-index-root" data-category-index-root data-csrf-token="{{ csrf_token() }}">
        <div id="category-index-swap">
            @include('admin.categories.partials.index-body')
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admin-category-index.js'])
@endpush
