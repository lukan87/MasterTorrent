@extends('layouts.app')
@push('scripts') @vite('resources/js/page-browser.js') @endpush
@section('content')
@include('requests.partials.styles')
<div data-page-browser data-browse-url="{{ route('requests.index') }}">
    @include('requests.index-results')
</div>
@endsection
