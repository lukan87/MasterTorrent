@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="card bg-dark text-light border-secondary p-4 text-center" role="status">
        <i class="bi bi-person-exclamation fs-1 text-info mb-3" aria-hidden="true"></i>
        <h1 class="h3">{{ $missing ? 'Actor not found' : 'Actor information is temporarily unavailable' }}</h1>
        <p class="text-secondary">{{ $missing ? 'This person could not be found on TMDB.' : 'We could not load this profile from TMDB. Please try again in a moment.' }}</p>
        <div class="d-flex justify-content-center gap-2">
            @unless($missing)<a href="{{ url()->current() }}" class="btn btn-info">Try again</a>@endunless
            <a href="{{ route('library.movies.index') }}" class="btn btn-outline-light">Movie library</a>
        </div>
    </div>
</div>
@endsection
