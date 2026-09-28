@extends('layouts.admin')

@section('admin-content')
<div class="container">
    <h1>Edit Torrent: {{ old('name', $torrent->name) }}</h1>

    <form action="{{ route('admin.torrents.update', $torrent->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group">
            <label for="name">Title</label>
            <input type="text" id="name" name="name" required maxlength="255" class="form-control" value="{{ old('name', $torrent->name) }}">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control">{{ old('description', $torrent->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    <a class="btn btn-secondary" href="{{ route('admin.torrents.index') }}">Cancel</a>
    </form>
    </div>
@endsection
