@extends('layouts.admin')

@section('admin-content')
<div class="admin-editor-page">
    @include('admin.partials.page-header', ['eyebrow' => 'CATALOG MANAGEMENT', 'title' => 'Edit Torrent: '.old('name', $torrent->name), 'subtitle' => 'Update the catalog details below.', 'backRoute' => 'admin.torrents.index'])

    <form class="admin-form-panel" action="{{ route('admin.torrents.update', $torrent->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group mb-4">
            <label for="name">Title</label>
            <input type="text" id="name" name="name" required maxlength="255" class="form-control" value="{{ old('name', $torrent->name) }}">
        </div>

        <div class="form-group mb-4">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="9">{{ old('description', $torrent->description) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    <a class="btn btn-secondary" href="{{ route('admin.torrents.index') }}">Cancel</a>
    </form>
    </div>
@endsection
