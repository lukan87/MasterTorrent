@php
    $collection = data_get($display, 'collection');
    $collectionMovies = collect(data_get($display, 'collection_movies', []));
    $collectionCollapseId = $collection ? 'collection-'.data_get($collection, 'id') : null;

    $total = $collectionMovies->count();
    $inDb = $collectionMovies->where('in_db', true)->count();
    $percent = $total ? round(($inDb / $total) * 100) : 0;
    $currentTmdbId = data_get($collection, 'current_tmdb_id');
@endphp

@if($display['type'] === 'movie' && $collection && $collectionMovies->isNotEmpty())

<div class="collection-card mt-5 mb-5">

    {{-- HEADER --}}
    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">

        {{-- LEFT: Clickable Collection Name --}}
        <a href="{{ route('collections.show', $display['collection']['id']) }}"
           class="fw-bold fs-5 text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-collection-play"></i>
            {{ $display['collection']['name'] }}
        </a>

        {{-- RIGHT: Collapse Toggle --}}
        <div class="d-flex align-items-center gap-3">

            <small class="text-muted">
                {{ $inDb }} / {{ $total }} in database
            </small>

            <div class="cursor-pointer"
                 data-bs-toggle="collapse"
                 data-bs-target="#{{ $collectionCollapseId }}"
                 aria-expanded="false"
                 role="button">

                <i class="bi bi-chevron-down collapse-icon"></i>
            </div>
        </div>
    </div>

    {{-- COLLAPSIBLE CONTENT --}}
    <div id="{{ $collectionCollapseId }}" class="collapse">
        <div class="card-body p-0">

        {{-- MOVIES --}}
        @foreach($collectionMovies as $movie)
            @php
                $torrents = collect($movie['torrents'] ?? []);
                $isCurrent = $movie['tmdb_id'] === $currentTmdbId;
                 $collapseId = 'movie-'.$movie['tmdb_id'];
            @endphp

            <div class="collection-movie {{ $isCurrent ? 'is-current' : '' }}">

                {{-- MOVIE HEADER --}}
               <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center
     cursor-pointer"
     data-bs-toggle="collapse"
     data-bs-target="#movie-{{ $movie['tmdb_id'] }}"
     aria-expanded="{{ $isCurrent ? 'true' : 'false' }}"
     role="button">

    <div>
        <strong class="fs-5">{{ $movie['title'] }}</strong>

        @if($isCurrent)
            <span class="badge bg-info ms-2">Current</span>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2">
        @if($movie['in_db'])
            <span class="badge bg-success">Available</span>
        @else
            <span class="badge bg-warning text-dark">Missing</span>
        @endif

        <i class="bi bi-chevron-down ms-2"></i>
    </div>
</div>

{{-- COLLAPSIBLE CONTENT --}}
                <div id="{{ $collapseId }}"
                     class="collapse {{ $isCurrent ? 'show' : '' }}">
                    
                   
                {{-- TORRENT TABLE HEADER --}}
                @if($torrents->isNotEmpty())
                <div class="row g-0 px-4 py-2 small text-uppercase text-muted border-bottom">
                    <div class="col-md-5">Torrent</div>
                    <div class="d-none d-md-block col-md-2 text-center">
                        <i class="bi bi-calendar-week fs-5" data-bs-toggle="tooltip" title="Uploaded"></i>
                    </div>
                    <div class="d-none d-md-block col-md-2 text-center">
                        <i class="bi bi-floppy fs-5" data-bs-toggle="tooltip" title="Size"></i>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="bi bi-arrow-up-circle text-success fs-5" data-bs-toggle="tooltip" title="Seeders"></i> /
                        <i class="bi bi-arrow-down-circle text-danger fs-5" data-bs-toggle="tooltip" title="Leechers"></i>
                    </div>
                    <div class="d-none d-md-block col-md-1 text-center">
                        <i class="bi bi-check-circle fs-5" data-bs-toggle="tooltip" title="Completed"></i>
                    </div>
                </div>
                @endif

                {{-- TORRENTS --}}
                @forelse($torrents as $torrent)
                <div class="row g-0 align-items-center px-4 py-2 border-bottom collection-torrent">

                    <div class="col-md-5">
                        <a href="{{ route('torrents.show', $torrent->id) }}"
                           class="fw-semibold text-decoration-none">
                            {{ $torrent->name }}
                        </a>
                    </div>

                    <div class="d-none d-md-block col-md-2 text-center small text-muted">
                        {{ $torrent->created_at->diffForHumans(null, true) }}
                    </div>

                    <div class="d-none d-md-block col-md-2 text-center text-info">
                        {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
                    </div>

                    <div class="col-md-2 text-center fw-bold">
                        <span class="text-success">{{ $torrent->seeders }}</span> /
                        <span class="text-danger">{{ $torrent->leechers }}</span>
                    </div>

                    <div class="d-none d-md-block col-md-1 text-center text-success fw-bold">
                        {{ $torrent->times_completed }}
                    </div>

                </div>
                @empty
                    {{-- MISSING MOVIE --}}
                    <div class="px-4 py-4  text-muted">
                        <p class="mb-2">No torrents available</p>

                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))
                            <a href="{{ route('torrents.create') }}"
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-upload"></i> Upload
                            </a>
                        @elseif(Auth::check())
                            <a href="{{ route('requests.create', ['tmdb' => $movie['tmdb_id']]) }}"
                               class="btn btn-sm btn-outline-warning">
                                Request
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-sm btn-outline-secondary">
                                Login
                            </a>
                        @endif
                    </div>
                @endforelse

            </div>
            </div>
        @endforeach

    </div>
     
</div>
</div>
@endif



<style>

   /* ================================
   Collection Card – Glass UI
================================ */
.collection-card {
    backdrop-filter: blur(4px);
        border-radius: 16px;
        overflow: visible;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(146, 142, 142, 0.08);
}

/* ================================
   Header & Progress
================================ */
.collection-card .progress {
    height: 6px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 4px;
    overflow: hidden;
}

.collection-card .progress-bar {
    background: linear-gradient(
        90deg,
        #01b4e4,
        #1a73e8
    );
    box-shadow: 0 0 12px rgba(1, 180, 228, 0.6);
    transition: width 0.6s ease;
}

/* ================================
   Column Header Row
================================ */
.collection-card .row.small {
    background: rgba(255, 255, 255, 0.04);
    font-weight: 600;
    letter-spacing: .06em;
    backdrop-filter: blur(6px);
}

/* ================================
   Rows
================================ */
.collection-row {
    background: rgba(255, 255, 255, 0.02);
    transition:
        background 0.2s ease,
        transform 0.15s ease;
}

.collection-row:hover {
    background: rgba(255, 255, 255, 0.06);
}

/* Current movie highlight */
.collection-row.is-current {
    background: linear-gradient(
        90deg,
        rgba(1, 180, 228, 0.28),
        rgba(1, 180, 228, 0.06)
    );
    border-left: 4px solid #01b4e4;
}

/* Missing movie */
.collection-row.is-missing {
    background: rgba(255, 193, 7, 0.12);
}

/* ================================
   Titles & Links
================================ */
.collection-row a {
    color: #fff;
    font-weight: 600;
    transition: color 0.15s ease;
}

.collection-row a:hover {
    color: #01b4e4;
    text-decoration: none;
}

/* ================================
   Badges
================================ */
.collection-row .badge {
    font-weight: 600;
    letter-spacing: .03em;
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.collection-row .badge.bg-success {
    background: linear-gradient(
        135deg,
        #28a745,
        #5dd879
    );
}

.collection-row .badge.bg-warning {
    background: linear-gradient(
        135deg,
        #ffc107,
        #ffda6a
    );
    color: #111;
}

/* Upload / Request links inside badges */
.collection-row .badge a {
    color: inherit;
    text-decoration: none;
}

/* ================================
   Seed / Leech Emphasis
================================ */
.collection-row .text-success {
    text-shadow: 0 0 8px rgba(40, 167, 69, 0.45);
}

.collection-row .text-danger {
    text-shadow: 0 0 8px rgba(220, 53, 69, 0.45);
}

/* ================================
   Size Column
================================ */
.collection-row .text-info {
    color: #5bc0de !important;
    text-shadow: 0 0 6px rgba(91, 192, 222, 0.35);
}

/* ================================
   Responsive Polish
================================ */
@media (max-width: 768px) {
    .collection-row {
        padding-top: .85rem;
        padding-bottom: .85rem;
    }
}


</style>