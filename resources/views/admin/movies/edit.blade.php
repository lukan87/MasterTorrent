@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Movie: {{ $movie->name }}</h1>

    <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="name" class="form-control" value="{{ $movie->name }}">
        </div>

        <div class="form-group">
            <label for="description">Plot</label>
            <textarea name="overview" class="form-control">{{ $movie->overview }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
    </div>
@endsection
