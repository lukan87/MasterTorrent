@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Search for a Series</h1>

    <!-- Error Message Display -->
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search Form -->
    <form action="{{ route('series.search') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="series_name">Series Name</label>
            <input type="text" name="series_name" id="series_name" class="form-control" value="{{ old('series_name') }}" required placeholder="Enter series name">
        </div>

        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>
@endsection
