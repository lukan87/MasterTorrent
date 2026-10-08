@extends('layouts.app')
@section('title', 'TV Calendar • '.config('app.name'))
@push('styles')
<link rel="stylesheet" href="{{ asset('css/tv-calendar.css') }}?v={{ filemtime(public_path('css/tv-calendar.css')) }}">
@endpush
@section('content')
<div data-calendar-browser data-browse-url="{{ route('tv-calendar.index') }}">
    @include('tv-calendar.results')
</div>
@endsection

@push('scripts')
@vite('resources/js/calendar-browser.js')
@endpush
