@extends('layouts.app')

@section('content')





    <h1>Movies</h1>

    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('movies.create') }}" class="btn btn-primary">Add Movie</a>
    @endif

    <div style='display:block;height:50px'></div>
    <form action="{{ route('movies.search-movie') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="name">Search for a Movie</label>
            <input type="text" name="name" id="name" class="form-control" required placeholder="Enter movie name">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $movies->links('pagination::bootstrap-5') }}
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
        @forelse ($movies as $movie)
            <div class="col-md-6 col-lg-2 mb-4">
                <div class="card h-100">
                <a href="{{ route('movies.show', ['id' => $movie->id, 'slug' => $movie->slug]) }}">
    <img src="https://www.themoviedb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}" class="card-img-top" alt="{{ $movie->name }}">
</a>

                    <div class="card-body">
                        <h5 class="card-title">{{ $movie->name }}</h5>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-danger">No movies found.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $movies->links('pagination::bootstrap-5') }}
    </div>

    @if(session('status'))
    <script>
        swal({
            title: "Success!",
            text: "{{ session('status') }}",
            icon: "success",
            button: "OK",
        });
    </script>
@endif

@if(session('error'))
    <script>
        swal({
            title: "Error!",
            text: "{{ session('error') }}",
            icon: "error",
            button: "OK",
        });
    </script>
@endif
@endsection
