@extends('layouts.app')

@section('title', 'Collection: ' . $collection['name'])

@section('content')

<div class="container mt-4">

    {{-- =========================
       COLLECTION HEADER
    ========================== --}}
    <div class="movie-header-container mb-5">
        <div class="movie-header-card">
            <div class="movie-header-content row">

                <div class="col-12 col-md-3 poster-column">
                    <div class="poster-wrapper">
                        <img src="{{ $collection['poster'] ?? asset('images/noposter.jpg') }}"
                             class="movie-poster"
                             alt="{{ $collection['name'] }}">
                    </div>
                </div>

                <div class="col-12 col-md-9 info-column">
                    <h1 class="movie-title">
                        {{ $collection['name'] }}
                    </h1>

                    <p class="text-muted mt-2 collection-overview">
                        {{ $collection['overview'] }}
                    </p>

                    <div class="mt-3">
                        <span class="collection-status-badge">
                            {{ $collection['uploaded'] }} / {{ $collection['total'] }} Uploaded
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- =========================
       MOVIES IN COLLECTION
    ========================== --}}
    @foreach($movies as $movie)

        @php
            $torrents = collect($movie['torrents'] ?? []);
            $hasTorrents = $torrents->isNotEmpty();
            $primaryTorrent = $torrents->first();
            $isOnline = $movie['is_online'];
        @endphp

        <div class="movie-header-container mb-4">
            <div class="movie-header-card {{ !$hasTorrents ? 'opacity-75' : '' }}">
                <div class="movie-header-content row">

                    {{-- POSTER --}}
                    <div class="col-12 col-md-3 poster-column">
                        <div class="poster-wrapper">
                            <img src="{{ $movie['poster'] }}"
                                 class="movie-poster {{ !$hasTorrents ? 'grayscale' : '' }}"
                                 alt="{{ $movie['title'] }}">
                        </div>

                        @if($isOnline && !$hasTorrents)
                            <div class="collection-online-note text-center mt-2">
                                <i class="bi bi-info-circle"></i>
                                Online entry exists — upload torrent
                            </div>
                        @endif

                        {{-- ACTION --}}
                        <div class="trailer-section text-center mt-3">

                            @if(
                                Auth::check() &&
                                !$hasTorrents &&
                                (
                                    Auth::user()->user_class >= \App\Models\UserClass::UPLOADER ||
                                    Auth::user()->uploadpos === 'yes'
                                )
                            )
                                <a href="{{ route('torrents.create', ['tmdb' => $movie['tmdb_id']]) }}"
                                   class="btn collection-upload-btn me-1">
                                    <i class="bi bi-upload"></i> Upload
                                </a>
                            @endif

                            @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                                @if($isOnline)
                                    <a href="{{ route('movies.show', $movie['movie_id']) }}"
                                       class="btn collection-online-btn">
                                        <i class="bi bi-cloud-check"></i> Uploaded Online
                                    </a>
                                @else
                                    <a href="{{ route('movies.create', [
                                        'tmdb'  => $movie['tmdb_id'],
                                        'title' => $movie['title']
                                    ]) }}"
                                       class="btn collection-add-online-btn">
                                        <i class="bi bi-cloud-plus"></i> Add Online
                                    </a>
                                @endif

                            @endif

                        </div>
                    </div>

                    {{-- INFO --}}
                    <div class="col-12 col-md-9 info-column">

                        <h2 class="movie-title">
                            {{ $movie['title'] }}
                            <span class="release-year">({{ $movie['year'] }})</span>
                        </h2>

                        <p class="text-muted mt-2 collection-overview">
                            {{ $movie['overview'] }}
                        </p>

                        @if($hasTorrents)
                            <div class="collection-torrents mt-3">

                                <h6 class="collection-section-title">
                                    Available Torrents
                                </h6>

                                @foreach($torrents as $torrent)
                                    <div class="collection-torrent-row">

                                        <div class="collection-torrent-info">
                                            <a href="{{ route('torrents.show', $torrent->id) }}"
                                               class="collection-torrent-name">
                                                {{ $torrent->name }}
                                            </a>

                                            <div class="collection-torrent-meta">
                                                {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
                                                • {{ $torrent->created_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        <div class="collection-torrent-seeds">
                                            <span class="seed-value">{{ $torrent->seeders }}</span>
                                            <span class="seed-divider">/</span>
                                            <span class="leech-value">{{ $torrent->leechers }}</span>
                                        </div>

                                    </div>
                                @endforeach

                            </div>
                        @else
                            <p class="collection-no-torrents mt-3">
                                No Torrents Available Yet!!
                            </p>
                        @endif

                    </div>

                </div>
            </div>
        </div>

    @endforeach

</div>

<style>
/* =========================================================
   FILEIPLAY COLLECTION PAGE
   Dark glass / teal forum style
   ========================================================= */

.movie-header-container {
    position: relative;
}

.movie-header-card {
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    overflow: visible;
    box-shadow: 0 14px 35px rgba(0,0,0,.28);
    backdrop-filter: blur(14px);
}

.movie-header-content {
    padding: 22px;
}

.poster-column {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.poster-wrapper {
    width: 100%;
    max-width: 230px;
}

.movie-poster {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 2 / 3;
    object-fit: cover;
    border-radius: .7rem;
    border: 1px solid var(--ui-border);
    box-shadow: 0 12px 30px rgba(0,0,0,.35);
}

.grayscale {
    filter: grayscale(100%) brightness(.68);
}

.info-column {
    padding: 8px 10px;
}

.movie-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 0;
}

.release-year {
    color: var(--ui-accent);
    font-weight: 600;
}

.collection-overview {
    color: rgba(255,255,255,.68) !important;
    font-size: 14px;
    line-height: 1.65;
}

.collection-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: .5rem;
    background: rgba(45,212,191,.10);
    border: 1px solid rgba(45,212,191,.22);
    color: var(--ui-accent);
    font-size: 13px;
    font-weight: 700;
}

.collection-online-note {
    color: var(--ui-accent);
    font-size: 13px;
    line-height: 1.4;
}

.trailer-section {
    width: 100%;
    max-width: 230px;
}

.collection-upload-btn,
.collection-online-btn,
.collection-add-online-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 5px;
    border-radius: .55rem;
    padding: 7px 10px;
    font-size: 13px;
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}

.collection-upload-btn {
    color: #0b1720;
    background: var(--ui-accent);
    border: 1px solid var(--ui-accent);
}

.collection-upload-btn:hover {
    color: #071318;
    background: var(--ui-accent-strong);
    border-color: var(--ui-accent-strong);
    transform: translateY(-1px);
}

.collection-online-btn {
    color: #86efac;
    background: rgba(34,197,94,.08);
    border: 1px solid rgba(34,197,94,.24);
}

.collection-online-btn:hover {
    color: #bbf7d0;
    background: rgba(34,197,94,.14);
    border-color: rgba(34,197,94,.35);
}

.collection-add-online-btn {
    color: var(--ui-accent);
    background: rgba(45,212,191,.07);
    border: 1px solid rgba(45,212,191,.22);
}

.collection-add-online-btn:hover {
    color: #fff;
    background: rgba(45,212,191,.12);
    border-color: rgba(45,212,191,.35);
}

.collection-torrents {
    border-top: 1px solid var(--ui-border);
    padding-top: 14px;
}

.collection-section-title {
    color: rgba(255,255,255,.58);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.collection-torrent-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 12px;
    margin-bottom: 5px;
    border: 1px solid transparent;
    border-bottom-color: rgba(255,255,255,.06);
    border-radius: .55rem;
    transition: background .15s ease, border-color .15s ease;
}

.collection-torrent-row:hover {
    background: rgba(45,212,191,.045);
    border-color: rgba(45,212,191,.12);
}

.collection-torrent-info {
    min-width: 0;
    flex: 1;
}

.collection-torrent-name {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--ui-accent);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.collection-torrent-name:hover {
    color: var(--ui-accent-strong);
    text-decoration: none;
}

.collection-torrent-meta {
    margin-top: 3px;
    color: rgba(255,255,255,.48);
    font-size: 12px;
}

.collection-torrent-seeds {
    flex-shrink: 0;
    min-width: 55px;
    text-align: right;
    font-size: 13px;
    font-weight: 700;
}

.seed-value {
    color: #4ade80;
}

.seed-divider {
    color: rgba(255,255,255,.35);
    margin: 0 2px;
}

.leech-value {
    color: #f87171;
}

.collection-no-torrents {
    color: rgba(255,255,255,.50) !important;
    font-size: 14px;
}

@media (max-width: 767.98px) {
    .movie-header-content {
        padding: 16px;
    }

    .poster-wrapper {
        max-width: 190px;
    }

    .info-column {
        padding: 18px 4px 4px;
    }

    .movie-title {
        font-size: 14px;
    }

    .collection-overview {
        font-size: 14px;
    }

    .trailer-section {
        max-width: 100%;
    }

    .collection-torrent-row {
        align-items: flex-start;
        padding: 9px 8px;
    }

    .collection-torrent-name {
        font-size: 14px;
    }

    .collection-torrent-seeds {
        font-size: 12px;
        min-width: 48px;
    }
}
</style>

@endsection
