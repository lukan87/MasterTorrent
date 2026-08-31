@extends('layouts.app')

@section('title', 'Deleted Torrents')

@section('content')

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-danger fw-bold">
            <i class="bi bi-trash-fill"></i> Deleted Torrents
        </h2>

        <a href="{{ route('torrents.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Browse
        </a>
    </div>

<div class="card mb-4">
    <div class="card-body">

        <form method="GET" class="row g-2">

            {{-- Search --}}
            <div class="col-md-3">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search torrent name...">
            </div>

            {{-- Uploader --}}
            <div class="col-md-2">
                <input type="text"
                       name="uploader"
                       value="{{ request('uploader') }}"
                       class="form-control"
                       placeholder="Uploader">
            </div>

            {{-- Category --}}
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>

                    @foreach(\App\Models\Category::all() as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Deletion Reason --}}
            <div class="col-md-2">
                <input type="text"
                       name="reason"
                       value="{{ request('reason') }}"
                       class="form-control"
                       placeholder="Reason">
            </div>

            {{-- Buttons --}}
            <div class="col-md-3 d-flex gap-2">

                <button class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>

                <a href="{{ route('torrents.deleted') }}" class="btn btn-secondary">
                    Reset
                </a>

            </div>

        </form>

    </div>
</div>
    

    <div class="card shadow-sm">
        <div class="card-body p-0">

            

            {{-- HEADER --}}
            <div class="row g-0 border-bottom small text-uppercase text-muted align-items-center">

                <div class="d-none d-md-block col-md-6 px-3 py-3">Torrent</div>

                <div class="d-none d-md-block col-md-2 text-center py-3">Deleted At</div>

                <div class="d-none d-md-block col-md-2 text-center py-3">Deleted By</div>
                
                <div class="d-none d-md-block col-md-1 text-center py-3">Reason</div>

                <div class="d-none d-md-block col-md-1 text-center py-3">Actions</div>
            </div>

        

            {{-- ROWS --}}
            @forelse($torrents as $torrent)
            <div class="row g-0 align-items-center border-bottom py-3 deleted-row">

                {{-- NAME --}}
                <div class="col-md-6 px-3">
                    <div class="fw-bold text-danger">
                       <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}"
       class="text-danger text-decoration-none"
       data-bs-toggle="tooltip"
       title="View torrent details">

        <i class="bi bi-trash-fill me-1"></i>
        {{ $torrent->name }}

       
    </a>
                    </div>
                    <div class="small text-muted">
                        <span class="badge bg-secondary fs-7">
                            {{ $torrent->uploader?->name ? 'Uploaded by ' . $torrent->uploader?->name : 'Unknown Uploader' }}
                        </span>
                        <span class="badge bg-info ms-1">
                            {{ $torrent->category->name ?? 'Unknown' }}
                        </span>
                    </div>
                </div>

                {{-- DELETED DATE --}}
                <div class="col-md-2 text-center small text-warning fw-bold">
                    {{ $torrent->deleted_at->format('M d, Y H:i') }}
                </div>
                {{-- Deleted By --}}
                <div class="col-md-2 text-center small text-info fw-bold">
                    {{ $torrent->deletedBy->name ?? 'Unknown' }}
                </div>
                {{-- Reason --}}
                <div class="col-md-1 text-center small text-warning">
                    {{ $torrent->deletion_reason ?? '—' }}
                </div>


                {{-- ACTIONS --}}
                <div class="col-md-1 text-center d-flex justify-content-center gap-1">

    {{-- Redownload --}}
    @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
        <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
           class="btn btn-sm btn-primary"
           data-bs-toggle="tooltip"
           title="Redownload Torrent Only If You Have The Original Torrent Files In Your System">
            <i class="bi bi-download"></i>
        </a>
    @endif

    {{-- Restore --}}
    <form action="{{ route('torrents.restore', $torrent->id) }}"
          method="POST"
          class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success"
                data-bs-toggle="tooltip"
                title="Restore Torrent">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
    </form>

    {{-- Force Delete (Admin only) --}}
    @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
        <form action="{{ route('torrents.forceDelete', $torrent->id) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Permanent delete? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-danger"
                    data-bs-toggle="tooltip"
                    title="Permanent Delete">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    @endif

</div>

            </div>
            @empty
            <div class="text-center py-5 text-muted">
                No deleted torrents.
            </div>
            @endforelse

        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $torrents->links('pagination::bootstrap-5') }}
    </div>

</div>

<style>
.deleted-row {
    background: rgba(255, 0, 0, 0.05);
    transition: background 0.2s ease;
}

.deleted-row:hover {
    background: rgba(255, 0, 0, 0.12);
}

.card {
    background: #1c1c1f;
    border: 1px solid rgba(255,255,255,0.05);
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}
</style>

@endsection