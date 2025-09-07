@extends('layouts.app')

@section('title',  'Browse Torrents')

@section('content')




@if($movieOfTheDay)
<div class="movie-highlight">
    <div class="poster">
        <img src="{{ $movieOfTheDay->poster ?? '/images/default-poster.jpg' }}" 
             alt="{{ $movieOfTheDay->clean_name ?? str_replace('.', ' ', $movieOfTheDay->name) }}">
    </div>
    <div class="details">
        <div class="info-card">
            <h2 class="tagline">🎬 Movie of the Day</h2>
            <h1 class="title">
    <a href="{{ route('torrents.show', $movieOfTheDay->id) }}">
        {{ $movieOfTheDay->clean_name ?? str_replace('.', ' ', $movieOfTheDay->name) }}
    </a>
</h1>

            <p class="category">📂 {{ $movieOfTheDay->category->name ?? 'Unknown' }}</p>
            <div class="stats">
                <span class="badge1 seeders">🌱 {{ $movieOfTheDay->seeders }} Seeders</span>
                <span class="badge1 leechers">⬇️ {{ $movieOfTheDay->leechers }} Leechers</span>
                <span class="badge1 completed">✅ {{ $movieOfTheDay->times_completed }} Completed</span>
            </div>
        </div>
    </div>
</div>
@endif


<!-- Search Form -->
<div class="mt-5 card shadow-sm border-0 mb-4">
    <div class="card-header bg-grey">
        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Search Torrents</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('torrents.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <!-- Keyword -->
                <div class="col-md-3">
                    <label for="keyword" class="form-label">🔍 Keyword</label>
                    <input type="text" name="keyword" id="keyword" class="form-control" placeholder="e.g., The Matrix, IMDb URL" value="{{ request('keyword') }}">
                </div>

<!-- Categories Dropdown -->
<div class="col-md-3">
    <label class="form-label">📁 Categories</label>
    <div class="dropdown w-100">
        <button class="btn btn-outline-secondary w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Select Categories
        </button>
        <div class="dropdown-menu p-3 shadow-lg" style="max-height: 450px; overflow-y: auto; width: 750px;">
            <!-- Movies Section -->
            <div class="mb-4">
                <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom border-primary">
                    <i class="bi bi-film me-2"></i>Movies
                </h6>
                <div class="row g-3">
                    @foreach ($categories->whereIn('id', [1, 2, 5, 6, 9, 10, 11, 12, 16, 17, 18, 19, 24, 25, 31, 32, 54, 55, 81, 82]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ str_replace('Movies:', '', $category->name) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- TV Section -->
            <div class="mb-4">
                <h6 class="fw-bold text-success mb-3 pb-2 border-bottom border-success">
                    <i class="bi bi-tv me-2"></i>TV Shows
                </h6>
                <div class="row g-2">
                    @foreach ($categories->whereIn('id', [13, 14, 20, 21]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $category->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Other Categories -->
            <div class="mb-2">
                <h6 class="fw-bold text-warning mb-3 pb-2 border-bottom border-warning">
                    <i class="bi bi-collection me-2"></i>Other Categories
                </h6>
                <div class="row g-2">
                    @foreach ($categories->whereIn('id', [22, 26, 28, 30, 33, 42, 43, 44, 49, 56, 57]) as $category)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $category->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

                <!-- Genre -->
                <div class="col-md-2">
                    <label for="genre" class="form-label">🎬 Genre</label>
                    <select name="genre" id="genre" class="form-select">
                        <option value="">All Genres</option>
                        @foreach($allGenres as $genre)
                            <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                                {{ $genre->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Torrent Status -->
                <div class="col-md-2">
                    <label for="torrent_status" class="form-label">📊 Status</label>
                    <select name="torrent_status" id="torrent_status" class="form-select">
                        <option value="active" {{ request('torrent_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="dead" {{ request('torrent_status') == 'dead' ? 'selected' : '' }}>Dead</option>
                        <option value="free" {{ request('torrent_status') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="double" {{ request('torrent_status') == 'double' ? 'selected' : '' }}>Double</option>
                        <option value="seedbox" {{ request('torrent_status') == 'seedbox' ? 'selected' : '' }}>Seedbox</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Status Buttons -->
{{-- <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
    @php
        $status = request('torrent_status', 'active');
        $statusOptions = [
            'active' => ['label' => 'Active', 'class' => 'success'],
            'dead' => ['label' => 'Dead', 'class' => 'danger'],
            'free' => ['label' => 'Freeleech', 'class' => 'info'],
            'double' => ['label' => 'Double Upload', 'class' => 'warning'],
        ];
    @endphp
    @foreach ($statusOptions as $key => $opt)
        <a href="{{ route('torrents.index', array_merge(request()->except('torrent_status'), ['torrent_status' => $key])) }}"
            class="btn btn-sm btn-{{ $status == $key ? $opt['class'] : 'outline-' . $opt['class'] }}">
            {{ $opt['label'] }}
        </a>
    @endforeach
</div> --}}

<!-- Torrent Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-grey">
                <tr>
                    <th>Category</th>
                    <th>Name</th>
                    @if (Auth::user()->hit_and_run_count <= 20)
                        <th></th>
                    @endif
                    <th class="text-center"><i class="bi bi-stopwatch"></i></th>
                    <th class="text-center">
                        <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'size', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                        <i class="bi bi-pie-chart-fill"></i>
                            @if ($sortColumn == 'size')
                                <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'seeders', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                        <i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i>
                            @if ($sortColumn == 'seeders')
                                <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'leechers', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                        <i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i>
                            @if ($sortColumn == 'leechers')
                                <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'times_completed', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                        <i class="bi bi-download" data-bs-toggle="tooltip" title="Times Completed"></i>
                            @if ($sortColumn == 'times_completed')
                                <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                            @endif
                        </a>
                    </th>
                    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                        <th class="d-none d-md-table-cell text-center text-warning">Uploader</th>
                    @endif
                    @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                        <th class="d-none d-md-table-cell text-center text-info">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($torrents as $torrent)
                    <tr>
                        <td>
                            <a href="/torrents?keyword=&categories[]={{ $torrent->category->id }}&genre=&torrent_status=active">
                                <img src="{{ $torrent->category->image }}" class="rounded-start shadow-sm" style="width: 87px; height: 47px;">
                            </a>
                        </td>
                        <td>
                            <a class="text-muted" href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}"
                               data-bs-toggle="tooltip" data-bs-html="true"
                               data-bs-title="<img src='{{ $torrent->poster }}' loading='lazy'class='img-fluid rounded' style='max-width: 180px;'>">
                                <strong>{{ \Illuminate\Support\Str::limit($torrent->name, 75, ' ...') }}</strong>
                            </a>
                            @include('torrents.partials.tags')
                            <div class="mt-1">
                                @foreach($torrent->genres as $genre)
                                    <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary">{{ $genre->name }}</a>
                                @endforeach
                            </div>
                        </td>
                        @if (Auth::user()->hit_and_run_count <= 20)
                        <td>
                            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-secondary btn-sm rounded-circle" data-bs-toggle="tooltip" title="Download Torrent">
                               <i class="bi bi-cloud-arrow-down-fill"></i>
                            </a>
                        </td>
                        @endif
                     
                        <td class="text-center"><small><div data-bs-toggle="tooltip" title="{{ \Carbon\Carbon::parse($torrent->created_at)->diffForHumans() }}">{{ \Carbon\Carbon::parse($torrent->created_at)->format('M d, Y @ g:i A') }}</div></small></td> 

                        {{-- <td>
                            <small>
    <div data-bs-toggle="tooltip" 
         title="{{ $torrent->created_at->timezone(Auth::user()->timezone ?? 'Europe/London')->diffForHumans() }}">
        {{ $torrent->created_at->timezone(Auth::user()->timezone ?? 'Europe/London')->format('M d, Y @ g:i A') }}
    </div>
</small>

                        </td> --}}
                        
                       
                        <td class="text-center">{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</td>
                        <td class="text-center text-success fw-bold">{{ $torrent->seeders }}</td>
                        <td class="text-center text-danger fw-bold">{{ $torrent->leechers }}</td>
                        <td class="text-center text-info fw-bold">{{ $torrent->times_completed }}</td>
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                            <td class="d-none d-md-table-cell text-center">
                                @if(isset($torrent->uploader->id))
                                    <a href="{{ route('profile.show', ['id' => $torrent->uploader->id]) }}"
                                       class="fw-bold" style="color: {{ \App\Models\UserClass::getClassColor($torrent->uploader->user_class ?? '') }}">
                                        {{ $torrent->uploader->name ?? 'Unknown' }}
                                    </a>
                                @else
                                    <span>Unknown</span>
                                @endif
                            </td>
                        @endif
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                            <td class="d-none d-md-table-cell text-center">
                                <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-warning btn-sm rounded-circle" data-bs-toggle="tooltip" title="Edit Torrent"><i class="bi bi-pencil-square"></i></a>
                                @if (Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <form action="{{ route('torrents.destroy', $torrent->slug) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-circle" data-bs-toggle="tooltip" title="Delete Torrent"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @if (!$torrent->bumped)
                                    <form action="{{ route('torrents.bump', $torrent->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-circle" data-bs-toggle="tooltip" title="Bump Torrent"><i class="bi bi-arrow-up-circle"></i></button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No torrents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $torrents->links('pagination::bootstrap-5') }}
</div>

<style>
.movie-highlight {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    max-width: 900px;
    margin: 40px auto;
    background: linear-gradient(145deg, #1c1c1c, #2a2a2a);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    overflow: hidden;
    transition: transform 0.3s ease;
    animation: glow 1.5s ease-in-out infinite alternate;
}
@keyframes glow {
    0% {
        box-shadow: 0 0 5px #c0b7b4ff, 0 0 10px #5f5755ff, 0 0 15px #aaa7a6ff;
    }
    50% {
        box-shadow: 0 0 10px #5a5959ff, 0 0 20px #2b2a29ff, 0 0 30px #636261ff;
    }
    100% {
        box-shadow: 0 0 5px #5070ffff, 0 0 10px #5350ffff, 0 0 15px #5065c4ff;
    }
}

.movie-highlight:hover {
    transform: translateY(-5px);
}

.poster {
    flex: 0 0 200px;
    position: relative;
}

.poster img {
    width: 100%;
    height: auto;
    border-radius: 20px 0 0 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    transition: transform 0.3s ease;
}

.poster img:hover {
    transform: scale(1.05);
}

.details {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 20px;
}

.info-card {
    color: #fff;
}

.tagline {
    color: #ff7f50;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.title {
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 10px;
    line-height: 1.2;
}

.category {
    font-size: 1rem;
    margin-bottom: 20px;
    color: #bbb;
}

/* Stats badges */
.stats {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.badge1 {
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.badge1:hover {
    transform: scale(1.1);
}

.badge1.seeders {
    background: #28a745;
    color: #fff;
    box-shadow: 0 0 10px #28a745;
}

.badge1.leechers {
    background: #dc3545;
    color: #fff;
    box-shadow: 0 0 10px #dc3545;
}

.badge1.completed {
    background: #007bff;
    color: #fff;
    box-shadow: 0 0 10px #007bff;
}

/* Responsive */
@media (max-width: 768px) {
    .movie-highlight {
        flex-direction: column;
        max-width: 90%;
    }

    .poster img {
        border-radius: 20px 20px 0 0;
    }

    .details {
        padding: 15px;
        text-align: center;
    }

    .stats {
        justify-content: center;
    }
}




    </style>

@endsection
