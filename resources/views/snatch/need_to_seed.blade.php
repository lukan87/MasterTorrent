@extends('layouts.app')

@push('scripts') @vite('resources/js/page-browser.js') @endpush
@section('content')
@include('snatch.partials.styles')
<div data-page-browser data-browse-url="{{ url()->current() }}">
    @include('snatch.need_to_seed-results')
</div>
@endsection
