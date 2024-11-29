@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Movie: {{ $torrent->name }}</h1>

    <form action="{{ route('admin.torrents.update', $torrent->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="name" class="form-control" value="{{ $torrent->name }}">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control">{{ $torrent->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
    </div>
@endsection
