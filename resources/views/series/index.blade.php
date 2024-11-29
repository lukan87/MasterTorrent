@extends('layouts.app')

@section('content')

<h1>Series</h1>
@if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('series.create') }}" class="btn btn-primary">Add Series</a>
    @endif

    <div style='display:block;height:50px'></div>
    <form action="{{ route('series.search-series') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="name">Search for a Series</label>
            <input type="text" name="name" id="name" class="form-control" required placeholder="Enter series name">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $series->links('pagination::bootstrap-5') }}
</div>

<!-- Custom CSS for pagination -->
<style>
.pagination .page-link {
    font-size: 0.9rem; /* Adjust font size for pagination links */
    padding: 0.3rem 0.6rem; /* Adjust padding for smaller buttons */
}

.pagination .page-item.active .page-link {
    background-color: #007bff; /* Change background color for active item */
    border-color: #007bff; /* Change border color for active item */
}

.pagination .page-link:hover {
    background-color: rgba(0, 123, 255, 0.1); /* Change hover background color */
}
</style>

<div class="row">
    @forelse ($series as $serie)
        <div class="col-md-6 col-lg-2 mb-4">
            <div class="card h-100">
                <a href="{{ route('series.show', $serie->slug) }}">
                    <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}" class="card-img-top" alt="{{ $serie->name }}">
                </a>
                <div class="card-body">
                    <h5 class="card-title">{{ $serie->name }}</h5>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-danger">No series found.</div>
    @endforelse
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $series->links('pagination::bootstrap-5') }}
</div>

@endsection
