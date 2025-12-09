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
    <a href="{{ route('torrents.show', ['id' => $movieOfTheDay->id, 'slug' => urlencode($movieOfTheDay->slug)]) }}">
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


@include('torrents.partials.indexsearch')



<!-- Torrent Table -->
<div class="card shadow-sm d-none d-md-block border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-grey rounded-thead">
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
                        <span class="text-success"><i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeders"></i></span>
                            @if ($sortColumn == 'seeders')
                                <i class="bi {{ $sortDirection == 'asc' ? 'bi-caret-down-fill' : 'bi-caret-up-fill' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('torrents.index', array_merge(request()->all(), ['sort' => 'leechers', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                         <span class="text-danger"><i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leechers"></i></span>
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
                    {{-- @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::VIP)
                        <th class="d-none d-md-table-cell text-center text-warning">Uploader</th>
                    @endif --}}
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
                                    <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" class="badge bg-secondary">{{ $genre->name }}</a>
                                @endforeach
                            </div>
                        </td>
                        @if (Auth::user()->hit_and_run_count <= 20)
                        <td>
                            <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-secondary btn-sm rounded-circle" data-bs-toggle="tooltip" title="Download Torrent">
                               <i class="bi bi-cloud-arrow-down-fill"></i>
                            </a>
                            
                         @php
    $userSeedboxes = \App\Models\Seedbox::where('user_id', auth()->id())->get();
@endphp

@if($userSeedboxes->isNotEmpty())
    @if($userSeedboxes->count() === 1)
        {{-- Single seedbox: show normal button --}}
        <form action="{{ route('torrents.sendToSeedbox', $torrent) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="seedbox_id" value="{{ $userSeedboxes->first()->id }}">
            <button type="submit" class="btn btn-success btn-sm rounded-circle" data-bs-toggle="tooltip" title="Send to Seedbox">
                <i class="bi bi-cloud-upload-fill"></i>
            </button>
        </form>
    @else
        {{-- Multiple seedboxes: dropdown button --}}
        <div class="btn-group d-inline">
            <button type="button" class="btn btn-success btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false" title="Send to Seedbox">
                <i class="bi bi-cloud-upload-fill"></i>
            </button>
            <ul class="dropdown-menu">
                @foreach($userSeedboxes as $seedbox)
                    <li>
                        <form action="{{ route('torrents.sendToSeedbox', $torrent) }}" method="POST" class="m-0 p-0">
                            @csrf
                            <input type="hidden" name="seedbox_id" value="{{ $seedbox->id }}">
                            <button type="submit" class="dropdown-item">
                                {{ $seedbox->name }}
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif



                        </td>
                        @endif
                     
                        {{-- <td class="text-center"><small><div data-bs-toggle="tooltip" title="{{ \Carbon\Carbon::parse($torrent->created_at)->diffForHumans() }}">{{ \Carbon\Carbon::parse($torrent->created_at)->format('M d, Y @ g:i A') }}</div></small></td>  --}}
                         <td class="text-center"><small>{{ \Carbon\Carbon::parse($torrent->created_at)->format('M d, Y') }}</></small></td> 

                      
                        
                       
                        <td class="text-center"><small>{{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</small></td>
                        <td class="text-center text-success fw-bold">{{ $torrent->seeders }}</td>
                        <td class="text-center text-danger fw-bold">{{ $torrent->leechers }}</td>
                        <td class="text-center text-info fw-bold">{{ $torrent->times_completed }}</td>
                        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                            <td class="d-none d-md-table-cell text-center">
                                <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-warning btn-sm rounded-circle" data-bs-toggle="tooltip" title="Edit Torrent"><i class="bi bi-pencil-square"></i></a>
                                @if (Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <form action="{{ route('torrents.destroy', $torrent->slug) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-circle" data-bs-toggle="tooltip" title="Delete Torrent"><i class="bi bi-trash"></i></button>
                                    </form>
                                   @if (!$torrent->bumped && $torrent->created_at->lt(now()->subDays(30)))
    <form action="{{ route('torrents.bump', $torrent->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success btn-sm rounded-circle" data-bs-toggle="tooltip" title="Bump Torrent">
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
                        <td colspan="10" class="text-center text-muted">No torrents found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>



<!-- MOBILE CARDS -->
<div class="d-md-none">
    @foreach ($torrents as $torrent)
        <div class="torrent-card mb-3 p-3 rounded shadow-sm bg-dark text-light">
            <div class="d-flex align-items-start">
                <img 
                    src="{{ $torrent->category->image }}" 
                    class="rounded me-3 flex-shrink-0" 
                    style="width: 60px; height: 40px; object-fit: cover;"
                    alt="Category Image"
                >
                <div class="flex-grow-1 text-truncate" style="min-width: 0;">
                    <a 
                        href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" 
                        class="fw-semibold text-white d-block text-decoration-none torrent-title"
                    >
                        {{ \Illuminate\Support\Str::limit($torrent->name, 90) }}
                    </a>
                    <div class="text-muted small mt-1">
                        {{ \Carbon\Carbon::parse($torrent->created_at)->diffForHumans() }} • 
                        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <span class="badge bg-success" title="Seeders">{{ $torrent->seeders }}</span>
                        <span class="badge bg-danger" title="Leechers">{{ $torrent->leechers }}</span>
                        <span class="badge bg-info" title="Completed">{{ $torrent->times_completed }}</span>
                    </div>
                      @include('torrents.partials.tags')
                </div>
            </div>

            <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                @if (Auth::check() && Auth::user()->hit_and_run_count <= 20)
                    <a 
                        href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                        class="btn btn-secondary btn-sm rounded-circle"
                        data-bs-toggle="tooltip" 
                        title="Download Torrent"
                    >
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </a>

                    @php
                        $userSeedboxes = \App\Models\Seedbox::where('user_id', auth()->id())->get();
                    @endphp

                    @if($userSeedboxes->isNotEmpty())
                        @if($userSeedboxes->count() === 1)
                            <form 
                                action="{{ route('torrents.sendToSeedbox', $torrent) }}" 
                                method="POST" 
                                class="d-inline"
                            >
                                @csrf
                                <input type="hidden" name="seedbox_id" value="{{ $userSeedboxes->first()->id }}">
                                <button 
                                    type="submit" 
                                    class="btn btn-success btn-sm rounded-circle" 
                                    data-bs-toggle="tooltip" 
                                    title="Send to Seedbox"
                                >
                                    <i class="bi bi-cloud-upload-fill"></i>
                                </button>
                            </form>
                        @else
                            <div class="btn-group d-inline">
                                <button 
                                    type="button" 
                                    class="btn btn-success btn-sm rounded-circle" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false" 
                                    title="Send to Seedbox"
                                >
                                    <i class="bi bi-cloud-upload-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    @foreach($userSeedboxes as $seedbox)
                                        <li>
                                            <form 
                                                action="{{ route('torrents.sendToSeedbox', $torrent) }}" 
                                                method="POST" 
                                                class="m-0 p-0"
                                            >
                                                @csrf
                                                <input type="hidden" name="seedbox_id" value="{{ $seedbox->id }}">
                                                <button type="submit" class="dropdown-item small text-light">
                                                    {{ $seedbox->name }}
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                @endif

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                    <a 
                        href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                        class="btn btn-warning btn-sm rounded-circle"
                        data-bs-toggle="tooltip" 
                        title="Edit Torrent"
                    >
                        <i class="bi bi-pencil-square"></i>
                    </a>

                    @if (Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                        <form 
                            action="{{ route('torrents.destroy', $torrent->slug) }}" 
                            method="POST" 
                            class="d-inline"
                        >
                            @csrf @method('DELETE')
                            <button 
                                type="submit" 
                                class="btn btn-danger btn-sm rounded-circle" 
                                data-bs-toggle="tooltip" 
                                title="Delete Torrent"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                        @if (!$torrent->bumped)
                            <form 
                                action="{{ route('torrents.bump', $torrent->id) }}" 
                                method="POST" 
                                class="d-inline"
                            >
                                @csrf
                                <button 
                                    type="submit" 
                                    class="btn btn-success btn-sm rounded-circle" 
                                    data-bs-toggle="tooltip" 
                                    title="Bump Torrent"
                                >
                                    <i class="bi bi-arrow-up-circle"></i>
                                </button>
                            </form>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
<!-- End MOBILE CARDS -->

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $torrents->links('pagination::bootstrap-5') }}
</div>

<style>
    /* Rounded Table Header */
.rounded-thead th:first-child {
    border-top-left-radius: 0.5rem;
}
.rounded-thead th:last-child {
    border-top-right-radius: 0.5rem;
}

/* Optional: add shadow or background if you want more rounded effect */
.rounded-thead th {
    background-color: #1d1c1c; /* matches table-grey */
}
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
@media (max-width: 868px) {
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

.torrent-card {
    background: #1e1e1e;
    border: 1px solid rgba(255,255,255,0.05);
    color: #ddd;
}
.torrent-card .badge {
    font-size: 0.75rem;
    padding: 4px 7px;
    border-radius: 8px;
}

/* Table header rounding */
.rounded-thead th:first-child { border-top-left-radius: .5rem; }
.rounded-thead th:last-child { border-top-right-radius: .5rem; }

.torrent-card:hover {
    background-color: #252538;
    transform: translateY(-2px);
}

.torrent-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;
    word-break: break-word;
}

    </style>

@endsection
