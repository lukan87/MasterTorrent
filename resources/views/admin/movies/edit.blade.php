@extends('layouts.admin')

@section('admin-content')
<div class="admin-editor-page">
    @include('admin.partials.page-header', ['eyebrow' => 'CATALOG MANAGEMENT', 'title' => 'Edit Movie: '.old('name', $movie->name), 'subtitle' => 'Update the catalog details below.', 'backRoute' => 'admin.movies.index'])

    <form class="admin-form-panel" action="{{ route('admin.movies.update', $movie->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This ensures the request uses the PUT method -->
        <div class="form-group mb-4">
            <label for="name">Title</label>
            <input type="text" id="name" name="name" required maxlength="255" class="form-control" value="{{ old('name', $movie->name) }}">
        </div>

        <div class="form-group mb-4">
            <label for="overview">Plot</label>
            <textarea id="overview" name="overview" class="form-control" rows="9">{{ old('overview', $movie->overview) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    <a class="btn btn-secondary" href="{{ route('admin.movies.index') }}">Cancel</a>
    </form>
    </div>
@endsection
