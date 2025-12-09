@extends('layouts.app')

@section('content')

<h1 class="page-title mb-4">Torrent List</h1>

<!-- Search Form -->
<form action="{{ route('torrents.adult') }}" method="GET" class="search-form mb-4 p-3 shadow-sm rounded bg-dark text-white">
    <div class="row g-3">
        <div class="col-md-4 col-12">
            <label for="keyword" class="form-label">🔍 Keyword</label>
            <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Search Torrent by name..." value="{{ request('keyword') }}">
        </div>
        <div class="col-md-3 col-12">
            <label for="category" class="form-label">📁 Category</label>
            <select name="category" id="category" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories->whereIn('id', [27, 34, 60]) as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-12">
            <label for="torrent_status" class="form-label">📊 Torrent Status</label>
            <select name="torrent_status" id="torrent_status" class="form-select">
                <option value="active" {{ request('torrent_status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="dead" {{ request('torrent_status') == 'dead' ? 'selected' : '' }}>Dead (Seeders = 0)</option>
                <option value="free" {{ request('torrent_status') == 'free' ? 'selected' : '' }}>Free</option>
                <option value="double" {{ request('torrent_status') == 'double' ? 'selected' : '' }}>Double</option>
                <option value="seedbox" {{ request('torrent_status') == 'seedbox' ? 'selected' : '' }}>Seedbox</option>
            </select>
        </div>
        <div class="col-md-1 col-12 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                <i class="bi bi-search me-1"></i> Search
            </button>
        </div>
    </div>
</form>

<!-- Torrent Table -->
<div class="card shadow-sm border-0 bg-dark text-white">
    <div class="table-responsive overflow-hidden">
        <table class="table table-hover align-middle mb-0 text-white">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">Category</th>
                    <th>Name</th>
                    @if (Auth::user()->hit_and_run_count <= 10)<th></th>@endif
                    <th class="text-center"><i class="bi bi-stopwatch"></i></th>
                    <th class="text-center">
                        <a href="{{ route('torrents.adult', array_merge(request()->all(), ['sort' => 'size', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-pie-chart-fill"></i>
                            @if ($sortColumn == 'size')<i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>@endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.adult', array_merge(request()->all(), ['sort' => 'seeders', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i>
                            @if ($sortColumn == 'seeders')<i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>@endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.adult', array_merge(request()->all(), ['sort' => 'leechers', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i>
                            @if ($sortColumn == 'leechers')<i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>@endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.adult', array_merge(request()->all(), ['sort' => 'times_completed', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                            <i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i>
                            @if ($sortColumn == 'times_completed')<i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>@endif
                        </a>
                    </th>
                    
                    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                        <th class="text-center d-none d-md-table-cell text-info">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse ($adult as $torrent)
                <tr class="torrent-row">
                    <td class="text-center">
                        <img src="{{ url($torrent->category->image) }}" class="torrent-category-img">
                    </td>
                    <td>
                        <a class="torrent-name text-white" href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="<img src='{{ $torrent->poster }}' class='img-fluid rounded'>">
                            {{ $torrent->name }}
                        </a>
                        <div class="mt-1">
                            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
    
        @if(isset($torrent->uploader->id))
            <a href="{{ route('profile.show', ['id' => $torrent->uploader->id]) }}"
               class="badge rounded-pill fw-semibold" data-bs-toggle="tooltip" title="Uploader"
               style="font-size: 0.90rem; background: rgba(123, 123, 123, 0.05); color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class ?? '') }};">
                <i class="bi bi-arrow-90deg-up"></i>{{ $torrent->uploader->name ?? 'Unknown' }}
            </a>
        @else
            <span class="badge bg-secondary rounded-pill px-3 py-2" style="font-size: 0.75rem;">Unknown</span>
        @endif
    
@endif 
                            @foreach($torrent->genres as $genre)
                                <a href="{{ route('torrents.adult', ['genre' => $genre->id]) }}" class="badge bg-secondary">{{ $genre->name }}</a>
                            @endforeach
                        </div>
                        @include('torrents.partials.tags')
                       
                    </td>
                    @if (Auth::user()->hit_and_run_count <= 20)
                        <td class="text-center">
                            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Download Torrent">
                                <i class="bi bi-file-earmark-arrow-down-fill"></i>
                            </a>
                            @php
                                $userSeedboxes = \App\Models\Seedbox::where('user_id', auth()->id())->get();
                            @endphp
                            @if($userSeedboxes->isNotEmpty())
                                @if($userSeedboxes->count() === 1)
                                    <form action="{{ route('torrents.sendToSeedbox', $torrent) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="seedbox_id" value="{{ $userSeedboxes->first()->id }}">
                                        <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Send to Seedbox">
                                            <i class="bi bi-cloud-upload-fill"></i>
                                        </button>
                                    </form>
                                @else
                                    <div class="btn-group d-inline">
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="dropdown" aria-expanded="false" title="Send to Seedbox">
                                            <i class="bi bi-cloud-upload-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach($userSeedboxes as $seedbox)
                                                <li>
                                                    <form action="{{ route('torrents.sendToSeedbox', $torrent) }}" method="POST" class="m-0 p-0">
                                                        @csrf
                                                        <input type="hidden" name="seedbox_id" value="{{ $seedbox->id }}">
                                                        <button type="submit" class="dropdown-item">{{ $seedbox->name }}</button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        </td>
                    @endif
                    <td class="text-center"><small>{{ \Carbon\Carbon::parse($torrent->created_at)->format('d-M-Y') }}</small></td>
                    <td class="text-center">{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</td>
                    <td class="text-center text-success fw-bold">{{ $torrent->seeders }}</td>
                    <td class="text-center text-danger fw-bold">{{ $torrent->leechers }}</td>
                    <td class="text-center text-info fw-bold">{{ $torrent->times_completed }}</td>
                   
                    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                        <td class="d-none d-md-table-cell text-center">
                            <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-warning btn-sm action-btn"><i class="bi bi-pencil-square"></i></a>
                            @if (Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                <form action="{{ route('torrents.destroy', $torrent->slug) }}" method="POST" style="display: inline-block;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm action-btn"><i class="bi bi-trash"></i></button>
                                </form>
                                    </form>
                                   @if (!$torrent->bumped && $torrent->created_at->lt(now()->subDays(30)))
                                      <form action="{{ route('torrents.bump', $torrent->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Bump Torrent">
                                         <i class="bi bi-arrow-up-circle"></i>
                                        </button>
                                      </form>
                                @endif
                            @endif
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">No torrents found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="pagination-container d-flex justify-content-center mt-4">
    {{ $adult->links('pagination::bootstrap-5') }}
</div>

<style>
/* General */
.search-form {
    background: linear-gradient(90deg, #1c1c1c, #2a2a2a);
    border-radius: 12px;
}
.search-form .form-label { color: #fff; }

/* Table */
.table-dark { background-color: #2a2a2a !important; color: #fff; }
.table-hover tbody tr:hover { background-color: rgba(255,127,80,0.1); transform: scale(1.01); transition: all 0.2s ease-in-out; overflow: hidden; }

/* Torrent Name */
.torrent-name {
    
    max-width: 650px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 600;
    color: #ff7f50;
    transition: color 0.2s ease;
}
.torrent-name:hover { color: #ffa07a; }

/* Torrent Genres */
.torrent-genres { display: flex; flex-wrap: wrap; gap: 3px; }
.torrent-genres .badge:hover { transform: scale(1.1); }

/* Category Image */
.torrent-category-img { max-width: 87px; height: auto; border-radius: 10px; }

/* Seedbox Dropdown */
.btn-group .dropdown-menu { max-width: 180px; word-wrap: break-word; }

/* Buttons */
.btn-primary { background: linear-gradient(90deg, #ff7f50, #ff4500); border: none; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,69,0,0.4); }

/* Responsive */
@media (max-width: 768px) {
    .torrent-genres { text-align: center; }
    .table-responsive { overflow-x: auto; }
    .search-form .row > div { margin-bottom: 10px; }
}
</style>

@endsection
