@extends('layouts.app')
@push('scripts') @vite('resources/js/page-browser.js') @endpush
@section('content')
<div data-page-browser data-browse-url="{{ url()->current() }}">
    @include('forum.category-results')
</div>
@include('forum.partials.category-css')
@include('forum.partials.common-css')
@include('forum.partials.back-to-top')
@endsection
