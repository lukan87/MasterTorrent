@extends('layouts.app')

@section('content')
<div data-page-browser data-browse-url="{{ url()->current() }}">
@include('profile.posts-results')
</div>

@vite('resources/js/page-browser.js')
@endsection
