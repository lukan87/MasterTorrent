@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <h1>Edit Movie: {{ old('name', $movie->name) }}</h1>

    <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group">
            <label for="name">Title</label>
            <input type="text" id="name" name="name" required maxlength="255" class="form-control" value="{{ old('name', $movie->name) }}">
        </div>

        <div class="form-group">
            <label for="overview">Plot</label>
            <textarea id="overview" name="overview" class="form-control">{{ old('overview', $movie->overview) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    <a class="btn btn-secondary" href="{{ route('admin.movies.index') }}">Cancel</a>
    </form>
    </div>
@endsection
