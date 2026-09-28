@extends('layouts.admin')
@section('admin-content')
<div class="container-fluid">
    <h1>Manage Movies</h1>
    <form method="GET" action="{{ route('admin.movies.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8"><label for="catalog-search" class="form-label">Search by title</label>
            <input id="catalog-search" type="search" name="search" maxlength="255" value="{{ request('search') }}" class="form-control"></div>
        <div class="col-sm-4"><button class="btn btn-primary" type="submit">Search</button> <a class="btn btn-secondary" href="{{ route('admin.movies.index') }}">Reset</a></div>
    </form>
    <p role="status">{{ number_format($movies->total()) }} results</p>
    <div class="table-responsive" role="region" aria-label="Movies results" tabindex="0">
    <table class="table table-striped">
        <caption>Movies matching your search</caption>
        <thead><tr><th scope="col">Title</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        @forelse($movies as $movie)
            <tr><th scope="row">{{ $movie->name }}</th><td>
                <div class="admin-catalog-actions">
                    <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-warning" aria-label="Edit {{ $movie->name }}">Edit</a>
                    <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST" onsubmit="return confirm('Delete this catalog entry? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" aria-label="Delete {{ $movie->name }}">Delete</button>
                    </form>
                </div>
            </td></tr>
        @empty
            <tr><td colspan="2">No results found. Try a different title or reset the search.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $movies->links('pagination::bootstrap-5') }}
</div>
@endsection
