@extends('layouts.app')

@section('content')
<div data-page-browser data-browse-url="{{ url()->current() }}">
@include('profile.comments-results')
</div>

@vite('resources/js/page-browser.js')
@endsection