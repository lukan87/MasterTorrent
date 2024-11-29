@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Search for a Movie</h1>

    <!-- Error Message Display -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Form -->
    <form action="{{ route('movies.search') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="movie_name">Movie Name</label>
            <input type="text" name="movie_name" id="movie_name" class="form-control" value="{{ old('movie_name') }}" required placeholder="Enter movie name">
        </div>

        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>
@endsection
