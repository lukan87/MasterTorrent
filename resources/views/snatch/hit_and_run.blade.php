@extends('layouts.app')

@push('scripts') @vite('resources/js/page-browser.js') @endpush
@section('content')
@include('snatch.partials.styles')
<div data-page-browser data-browse-url="{{ url()->current() }}">
    @include('snatch.hit_and_run-results')
</div>
@endsection
