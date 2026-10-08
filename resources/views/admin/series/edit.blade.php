@extends('layouts.admin')

@section('admin-content')
<div class="admin-editor-page">
    @include('admin.partials.page-header', ['eyebrow' => 'CATALOG MANAGEMENT', 'title' => 'Edit Series: '.old('name', $series->name), 'subtitle' => 'Update the catalog details below.', 'backRoute' => 'admin.series.index'])
    <form class="admin-form-panel" action="{{ route('admin.series.update', $series->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-4">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required maxlength="255" class="form-control" value="{{ old('name', $series->name) }}">
        </div>

        <div class="form-group mb-4">
            <label for="poster_path">Poster Path</label>
            <input type="text" id="poster_path" name="poster_path" class="form-control" value="{{ old('poster_path', $series->poster_path) }}">
        </div>

        <div class="form-group mb-4">
            <label for="overview">Overview</label>
            <textarea id="overview" name="overview" class="form-control" rows="9">{{ old('overview', $series->overview) }}</textarea>
        </div>

        <div class="form-group mb-4">
            <label for="backdrop_path">Backdrop Path</label>
            <input type="text" id="backdrop_path" name="backdrop_path" class="form-control" value="{{ old('backdrop_path', $series->backdrop_path) }}">
        </div>

        <button type="submit" class="btn btn-success">Update Series</button>
    <a class="btn btn-secondary" href="{{ route('admin.series.index') }}">Cancel</a>
    </form>
    </div>
@endsection
