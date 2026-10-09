@extends('layouts.admin')
@section('admin-content')
<div class="admin-catalog-page">
    <h1>Manage {{ ucfirst($kind) }} Library</h1>
    <p>Manage torrent titles and online playback in one catalogue.</p>
    <a class="btn btn-primary mb-3" href="{{ route('admin.library.create', $kind) }}">Add title</a>
    <a class="btn btn-secondary mb-3" href="{{ route($kind.'.create') }}">Import from TMDB</a>
    <form method="GET" class="d-flex gap-2 mb-3">
        <input type="search" name="q" class="form-control" aria-label="Search titles" value="{{ request('q') }}" maxlength="200" placeholder="Search titles">
        <button class="btn btn-primary">Search</button>
    </form>
    <div class="table-responsive"><table class="table table-striped">
        <thead><tr><th>Title</th><th>Year</th><th>Online playback</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($titles as $title)
            <tr>
                <td><a href="{{ route('library.'.$kind.'.show', [$title->tmdbid, $title->slug]) }}">{{ $title->title }}</a></td>
                <td>{{ $title->year }}</td>
                <td>{{ $service->playable($online->get($title->tmdbid)) ? 'Enabled' : 'Disabled' }}</td>
                <td><a class="btn btn-sm btn-outline-info" href="{{ route('admin.library.edit', [$kind, $title->tmdbid]) }}">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="4">No titles found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $titles->links('pagination::bootstrap-5') }}
</div>
@endsection
