<!-- resources/views/requests/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Torrent Request</h1>

    <form action="{{ route('requests.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Request Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <input type="hidden" name="requested_by" value="{{ auth()->id() }}">

        <div class="form-group">
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="imdb_url">IMDB URL</label>
            <input type="url" name="imdb_url" id="imdb_url" class="form-control" value="{{ old('imdb_url') }}">
        </div>

        <div class="form-group">
            <label for="tmdb_url">TMDB URL</label>
            <input type="url" name="tmdb_url" id="tmdb_url" class="form-control" value="{{ old('tmdb_url') }}">
        </div>

        <div class="form-group">
            <label for="steam_url">Steam URL</label>
            <input type="url" name="steam_url" id="steam_url" class="form-control" value="{{ old('steam_url') }}">
        </div>

        <div class="form-group">
            <label for="image">Image URL</label>
            <input type="url" name="image" id="image" class="form-control" value="{{ old('image') }}">
        </div>

        <div class="form-group">
            <label for="description">Description (max 500 words)</label>
            <textarea name="description" id="description" class="form-control" rows="5">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success mt-3">Create Request</button>
    </form>
</div>
@endsection
