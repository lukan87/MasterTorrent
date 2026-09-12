@extends('layouts.app')

@section('content')

{{-- 🎬 HERO BANNER --}}
<div class="hero">

    {{-- Background --}}
    <div class="hero-bg"
        style="background-image: url('{{ $movie['backdrop_path'] 
            ? 'https://image.tmdb.org/t/p/original/' . $movie['backdrop_path'] 
            : '' }}')">
    </div>

    {{-- Overlay --}}
    <div class="hero-overlay"></div>

    {{-- Content --}}
    <div class="container hero-content">

        <div class="row align-items-center">

            {{-- Poster --}}
            <div class="col-md-3 text-center mb-4 mb-md-0">
                <img class="poster shadow"
                     src="{{ $movie['poster_path'] 
                        ? 'https://image.tmdb.org/t/p/w500/' . $movie['poster_path'] 
                        : asset('images/no-poster.png') }}">
            </div>

            {{-- Info --}}
            <div class="col-md-9 text-white">

                <h1 class="mb-2">
                    {{ $movie['title'] }}
                    @if(!empty($movie['release_date']))
                        <span class="year">
                            ({{ substr($movie['release_date'], 0, 4) }})
                        </span>
                    @endif
                </h1>

                <div class="meta mb-3">
                    @if(!empty($movie['vote_average']))
                        <span class="badge-rating">
                            ⭐ {{ number_format($movie['vote_average'], 1) }}
                        </span>
                    @endif

                    @if(!empty($movie['release_date']))
                        <span class="badge-meta">
                            {{ $movie['release_date'] }}
                        </span>
                    @endif

                    @if(Auth::check())
                        <span class="subscribe-wrap">
                            @if($isSubscribed)
                                <form action="{{ route('library.movies.unsubscribe', $tmdbid) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn subscribe-btn">
                                        <i class="bi bi-bell-fill me-1"></i> Unsubscribe
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('library.movies.subscribe', $tmdbid) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn subscribe-btn">
                                        <i class="bi bi-bell me-1"></i> Subscribe
                                    </button>
                                </form>
                            @endif
                        </span>
                    @endif
                </div>

                <p class="overview">
                    {{ $movie['overview'] ?? 'No description available.' }}
                </p>

            </div>

        </div>

    </div>
</div>

{{-- 📦 TORRENTS SECTION --}}
<div class="container py-5">

    <h4 class="text-white mb-4">📥 Available Torrents</h4>

    <div class="torrent-list">

        @forelse($torrents as $torrent)
            <div class="torrent-item">

                <div class="torrent-main">
                    <div class="torrent-name">
                        <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}" 
   class="torrent-item text-decoration-none">
                        {{ $torrent->name }}
                        </a>
                    </div>

                    <div class="torrent-meta">
                        <span>💾 {{ number_format($torrent->size / 1073741824, 2) }} GB</span>
                        <span class="seeders">🌱 {{ $torrent->seeders }}</span>
                    </div>
                </div>

            </div>
        @empty
            <div class="text-muted">No torrents available.</div>
        @endforelse

    </div>

{{-- =========================
         YOU MIGHT ALSO LIKE
    ========================== --}}
    @if(!empty($recommendations))

        <div class="tmdb-recs mt-4">

            <div class="tmdb-recs-header">

                <div>
                    <h5 class="tmdb-recs-title mb-0">
                        <i class="bi bi-stars me-2"></i>
                        You Might Also Like
                    </h5>

                    <div class="tmdb-recs-subtitle">
                        Recommendations from The Movie Database
                    </div>
                </div>

                <a href="https://www.themoviedb.org/movie/{{ $tmdbid }}/recommendations"
                   target="_blank"
                   rel="noreferrer"
                   class="tmdb-recs-link">

                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    View All

                </a>

            </div>

            <div class="tmdb-recs-body">

                <div class="tmdb-recs-row">

                    @foreach($recommendations as $rec)

                        <a href="https://www.themoviedb.org/movie/{{ $rec['id'] }}"
                           target="_blank"
                           rel="noreferrer"
                           class="tmdb-recs-card text-decoration-none">

                            <div class="tmdb-recs-poster-wrap">

                                <img
                                    src="{{ $rec['poster'] ?? '/images/not-found.jpg' }}"
                                    loading="lazy"
                                    class="tmdb-recs-poster"
                                    alt="{{ $rec['title'] }}"
                                >

                                @if(!empty($rec['rating']))

                                    <span class="tmdb-recs-rating">
                                        <i class="bi bi-star-fill"></i>
                                        {{ number_format($rec['rating'], 1) }}
                                    </span>

                                @endif

                            </div>

                            <div class="tmdb-recs-info">

                                <div class="tmdb-recs-name">
                                    {{ $rec['title'] }}
                                </div>

                                @if(!empty($rec['year']))

                                    <div class="tmdb-recs-year">
                                        {{ $rec['year'] }}
                                    </div>

                                @endif

                            </div>

                        </a>

                    @endforeach

                </div>

            </div>

        </div>

    @endif
</div>

<style>

/* HERO */
.hero {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;

    /* 🔥 KEY CHANGE */
    background-position: top center;

    filter: blur(0.15px) brightness(0.5);
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, #0b0b0b 10%, transparent 60%);
}

.hero-content {
    position: relative;
    z-index: 2;
    padding-top: 100px;
}

/* POSTER */
.poster {
    width: 200px;
    border-radius: 10px;
}

/* TITLE */
.year {
    font-weight: 400;
    opacity: 0.7;
    font-size: 1.5rem;
}

/* META */
.meta {
    display: flex;
    gap: 10px;
    align-items: center;
}

.badge-rating {
background: rgba(255,255,255,0.15);
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 1.25rem
}

.badge-meta {
    background: rgba(255,255,255,0.15);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 1.25rem
}

/* SUBSCRIBE */
.subscribe-wrap form {
    display: inline-block;
}

.subscribe-btn {
    background: rgba(59,130,246,.18);
    border: 1px solid rgba(59,130,246,.35);
    color: #9cc7ff;
    font-weight: 600;
    border-radius: 8px;
    padding: 6px 14px;
    transition: 0.15s ease;
}

.subscribe-btn:hover {
    background: rgba(59,130,246,.32);
    border-color: rgba(59,130,246,.55);
    color: #fff;
}

/* OVERVIEW */
.overview {
    max-width: 700px;
    opacity: 0.9;
}

/* TORRENTS */
.torrent-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.torrent-item {
    background: #111;
    border-radius: 10px;
    padding: 15px;
    transition: 0.2s ease;
}

.torrent-item:hover {
    background: #1a1a1a;
}

.torrent-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.torrent-name {
    font-weight: 600;
    color: #fff;
}

.torrent-meta {
    display: flex;
    gap: 15px;
    color: #aaa;
}

.seeders {
    color: #4caf50;
    font-weight: 600;
}

/* =========================
   YOU MIGHT ALSO LIKE (TMDB recs)
========================= */
.tmdb-recs {
    overflow: hidden;
    background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    box-shadow: 0 14px 36px rgba(0, 0, 0, .28);
}

.tmdb-recs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 16px;
    background: rgba(45, 212, 191, .045);
    border-bottom: 1px solid var(--ui-border);
}

.tmdb-recs-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.tmdb-recs-title i {
    color: var(--ui-accent);
}

.tmdb-recs-subtitle {
    margin-top: 3px;
    color: rgba(255, 255, 255, .42);
    font-size: 12px;
}

.tmdb-recs-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 10px;
    border-radius: .5rem;
    background: rgba(45, 212, 191, .07);
    border: 1px solid rgba(45, 212, 191, .18);
    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease;
}

.tmdb-recs-link:hover {
    background: rgba(45, 212, 191, .12);
    border-color: rgba(45, 212, 191, .32);
    color: var(--ui-accent);
}

.tmdb-recs-body {
    padding: 12px;
}

.tmdb-recs-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 3px 2px 8px;
    scroll-behavior: smooth;
    scrollbar-width: none;
}

.tmdb-recs-row::-webkit-scrollbar {
    display: none;
}

.tmdb-recs-card {
    flex: 0 0 140px;
    width: 140px;
    min-width: 140px;
    max-width: 140px;
    padding: 8px;
    background: rgba(9, 16, 29, .48);
    border: 1px solid rgba(255, 255, 255, .055);
    border-radius: .6rem;
    transition: background .15s ease, border-color .15s ease, transform .15s ease;
}

.tmdb-recs-card:hover {
    background: rgba(45, 212, 191, .05);
    border-color: rgba(45, 212, 191, .24);
    transform: translateY(-2px);
}

.tmdb-recs-poster-wrap {
    position: relative;
    aspect-ratio: 2 / 3;
    border-radius: .45rem;
    overflow: hidden;
    background: #0f172a;
}

.tmdb-recs-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}

.tmdb-recs-card:hover .tmdb-recs-poster {
    transform: scale(1.04);
}

.tmdb-recs-rating {
    position: absolute;
    top: 6px;
    right: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 6px;
    border-radius: .4rem;
    background: rgba(5, 10, 18, .82);
    color: #facc15;
    font-size: 11px;
    font-weight: 700;
}

.tmdb-recs-info {
    padding: 7px 2px 2px;
}

.tmdb-recs-name {
    overflow: hidden;
    color: rgba(255, 255, 255, .82);
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.tmdb-recs-year {
    margin-top: 3px;
    color: rgba(255, 255, 255, .42);
    font-size: 11px;
}

@media (max-width: 768px) {
    .tmdb-recs-card {
        flex: 0 0 118px;
        width: 118px;
        min-width: 118px;
        max-width: 118px;
    }
}
</style>

@endsection