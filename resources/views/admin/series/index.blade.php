@extends('layouts.admin')
@section('admin-content')
<div class="admin-catalog-page">
    @include('admin.partials.page-header', ['eyebrow' => 'CATALOG MANAGEMENT', 'title' => 'Manage Series', 'subtitle' => 'Search your catalog and keep every entry up to date.'])
    <form method="GET" action="{{ route('admin.series.index') }}" class="row g-3 align-items-end mb-4 admin-filter-panel">
        <div class="col-sm-8"><label for="catalog-search" class="form-label">Search by title</label>
            <input id="catalog-search" type="search" name="search" maxlength="255" value="{{ request('search') }}" class="form-control"></div>
        <div class="col-sm-4"><button class="btn btn-primary" type="submit">Search</button> <a class="btn btn-secondary" href="{{ route('admin.series.index') }}">Reset</a></div>
    </form>
    <p role="status">{{ number_format($series->total()) }} results</p>
    <div class="table-responsive admin-table-panel" role="region" aria-label="Series results" tabindex="0">
    <table class="table table-striped">
        <caption>Series matching your search</caption>
        <thead><tr><th scope="col">Title</th><th scope="col">Actions</th></tr></thead>
        <tbody>
        @forelse($series as $seriesItem)
            <tr><th scope="row">{{ $seriesItem->name }}</th><td>
                <div class="admin-catalog-actions">
                    <a href="{{ route('admin.series.edit', $seriesItem->id) }}" class="btn btn-sm btn-outline-info" aria-label="Edit {{ $seriesItem->name }}">Edit</a>
                    <form action="{{ route('admin.series.destroy', $seriesItem->id) }}" method="POST" onsubmit="return confirm('Delete this catalog entry? This cannot be undone.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete {{ $seriesItem->name }}">Delete</button>
                    </form>
                </div>
            </td></tr>
        @empty
            <tr><td colspan="2">No results found. Try a different title or reset the search.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $series->links('pagination::bootstrap-5') }}
</div>
@endsection
