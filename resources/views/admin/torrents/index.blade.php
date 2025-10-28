@extends('layouts.app')

@section('content')
<div class="min-vh-100 py-5">
    <div class="container">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h2 class="fw-bold mb-3 mb-md-0">🎬 Manage Torrents</h2>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.torrents.index') }}">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6 col-12">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control form-control-lg" 
                                placeholder="🔍 Search torrents..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <select name="status" class="form-select form-select-lg w-100">
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="dead" {{ $status === 'dead' ? 'selected' : '' }}>Dead</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.torrents.index') }}" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Torrent List -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom fw-semibold">
                        <tr>
                            <th class="ps-4">Torrent</th>
                            <th class="text-center">Stats</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($torrents as $torrent)
                            <tr class="border-bottom">
                                <td class="ps-4">
                                    <div class="fw-semibold">
                                        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                                           class="text-decoration-none">
                                            {{ $torrent->name }}
                                        </a>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        ID: {{ $torrent->id }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="text-success fw-bold">{{ $torrent->seeders }}</span> <small>Seeders</small> /
                                    <span class="text-warning fw-bold">{{ $torrent->leechers }}</span> <small>Leechers</small> /
                                    <span class="text-info fw-bold">{{ $torrent->times_completed }}</span> <small>Completed</small>
                                </td>

                                <td class="text-center">
                                    @if($torrent->seeders > 0)
                                        <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 border border-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-2 border border-danger">
                                            Dead
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.torrents.show', $torrent->id) }}" 
                                       class="btn btn-outline-primary btn-sm me-1" 
                                       title="Show Info">
                                        <i class="bi bi-info-circle"></i>
                                    </a>

                                    <form action="{{ route('admin.torrents.destroy', $torrent->id) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-outline-danger btn-sm" 
                                                onclick="return confirm('Are you sure?')" 
                                                title="Delete Torrent">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4"></i><br>
                                    No torrents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="d-md-none p-3">
                @forelse($torrents as $torrent)
                    <div class="border rounded-3 p-3 mb-3 shadow-sm">
                        <div class="fw-bold mb-1">
                            <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                               class="text-decoration-none">
                                {{ $torrent->name }}
                            </a>
                        </div>
                        <div class="small text-muted mb-2">
                            ID: {{ $torrent->id }}
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge bg-success-subtle text-success border border-success">
                                Seeders: {{ $torrent->seeders }}
                            </span>
                            <span class="badge bg-warning-subtle text-warning border border-warning">
                                Leechers: {{ $torrent->leechers }}
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info">
                                Completed: {{ $torrent->times_completed }}
                            </span>
                        </div>

                        <div class="mb-2">
                            @if($torrent->seeders > 0)
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3 py-2">
                                    Active
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger px-3 py-2">
                                    Dead
                                </span>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.torrents.show', $torrent->id) }}" 
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-info-circle"></i> Info
                            </a>

                            <form action="{{ route('admin.torrents.destroy', $torrent->id) }}" 
                                  method="POST" 
                                  class="flex-fill">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-outline-danger btn-sm w-100" 
                                        onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4"></i><br>
                        No torrents found.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $torrents->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; }
    .form-control-lg, .form-select-lg { border-radius: 10px; font-size: 1rem; }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    @media (max-width: 767.98px) {
        .form-control-lg, .form-select-lg {
            font-size: 0.95rem;
            padding: 0.6rem 1rem;
        }
    }
</style>
@endsection
