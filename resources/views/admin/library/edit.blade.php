@extends('layouts.admin')
@section('admin-content')
<div class="admin-editor-page">
    <h1>{{ $title ? 'Edit' : 'Add' }} {{ $kind === 'movies' ? 'movie' : 'series' }}</h1>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <form class="admin-form-panel" method="POST" action="{{ $title ? route('admin.library.update', [$kind, $title->tmdbid]) : route('admin.library.store', $kind) }}">
        @csrf
        @if($title) @method('PUT') @endif
        @unless($title)
            <label class="form-label" for="tmdbid">TMDB ID</label>
            <input class="form-control mb-3" id="tmdbid" name="tmdbid" type="number" min="1" required value="{{ old('tmdbid') }}">
        @endunless
        <label class="form-label" for="title">Title</label>
        <input class="form-control mb-3" id="title" name="title" required maxlength="255" value="{{ old('title', $title?->title) }}">
        <label class="form-label" for="year">Year</label>
        <input class="form-control mb-3" id="year" name="year" type="number" min="1800" max="2200" value="{{ old('year', $title?->year) }}">
        <label class="form-label" for="rating">Rating</label>
        <input class="form-control mb-3" id="rating" name="rating" type="number" step="0.1" min="0" max="10" value="{{ old('rating', $title?->rating) }}">
        @foreach(['poster_path' => 'TMDB poster path', 'backdrop_path' => 'TMDB backdrop path'] as $field => $label)
            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
            <input class="form-control mb-3" id="{{ $field }}" name="{{ $field }}" maxlength="255" placeholder="/image.jpg" value="{{ old($field, $title?->$field) }}">
        @endforeach
        <label class="form-label" for="overview">Overview</label>
        <textarea class="form-control mb-3" id="overview" name="overview" rows="6" maxlength="50000">{{ old('overview', $online?->overview) }}</textarea>
        <label class="form-label" for="imdb_id">IMDb ID for online player</label>
        <input class="form-control mb-3" id="imdb_id" name="imdb_id" pattern="tt[0-9]+" placeholder="tt1234567" value="{{ old('imdb_id', $online?->imdb_id) }}">
        <input type="hidden" name="online_enabled" value="0">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="online_enabled" name="online_enabled" value="1" @checked(old('online_enabled', $online?->online_enabled ?? false))>
            <label class="form-check-label" for="online_enabled">Enable online playback</label>
        </div>
        <p>Enable playback when this title is available through the online player. IMDb ID is required. Disabling playback keeps the title, torrents, and discussions.</p>
        <button class="btn btn-success">Save title</button>
        <a class="btn btn-secondary" href="{{ route('admin.library.index', $kind) }}">Cancel</a>
    </form>
</div>
@endsection
