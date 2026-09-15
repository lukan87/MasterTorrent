@extends('layouts.app')

@section('content')

{{-- =========================================================
    PREMIUM HEADER — reuses the exact torrent detail header
========================================================= --}}
@if(!empty($display) && $torrents->isNotEmpty())

    @include('torrents.partials.media-header', [
        'torrent' => $torrents->first(),
        'display' => $display,
    ])

    {{-- Subscribe control (preserved from the previous hero) --}}
    <div class="container px-xl-5 px-lg-4 px-3">
        <div class="library-subscribe-row">
            @if(Auth::check())
                @if($isSubscribed)
                    <form action="{{ route('library.series.unsubscribe', $tmdbid) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn subscribe-btn">
                            <i class="bi bi-bell-fill me-1"></i> Unsubscribe
                        </button>
                    </form>
                @else
                    <form action="{{ route('library.series.subscribe', $tmdbid) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn subscribe-btn">
                            <i class="bi bi-bell me-1"></i> Subscribe
                        </button>
                    </form>
                @endif
            @endif

            {{-- Subscribers (count + names) beside the subscribe button --}}
            @include('torrents.partials._subscribers-label', ['subscribers' => $subscribers ?? collect()])
        </div>
    </div>

@else

    {{-- Fallback (no torrent / no display data) — rich hero from TMDB/library data --}}
    @php
        $fbTitle = $movie['name'] ?? ($movie['original_name'] ?? 'Series');
        $fbPoster = !empty($movie['poster_path'])
            ? 'https://image.tmdb.org/t/p/w342'.$movie['poster_path']
            : (!empty($libraryEntry?->poster_path) ? 'https://image.tmdb.org/t/p/w342'.$libraryEntry->poster_path : '/images/noposter.jpg');
        $fbBackdrop = !empty($movie['backdrop_path'])
            ? 'https://image.tmdb.org/t/p/w1280'.$movie['backdrop_path']
            : (!empty($libraryEntry?->backdrop_path) ? 'https://image.tmdb.org/t/p/w1280'.$libraryEntry->backdrop_path : null);
        $fbRating = (!empty($movie['vote_average']) ? $movie['vote_average'] : ($libraryEntry->rating ?? null));
        $fbYear = !empty($movie['first_air_date']) ? substr($movie['first_air_date'], 0, 4) : null;
        $fbGenres = collect($movie['genres'] ?? [])->pluck('name')->take(3)->implode(' · ');
    @endphp
    <div class="hero">
        @if($fbBackdrop)
            <div class="hero-bg" style="background-image:url('{{ $fbBackdrop }}')"></div>
        @endif
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="row align-items-center g-4">
                <div class="col-md-3 col-sm-4 col-5 text-center">
                    <img src="{{ $fbPoster }}" class="poster shadow-lg" alt="{{ $fbTitle }}">
                </div>
                <div class="col-md-9 col-sm-8 col-7 text-white">
                    <h1 class="mb-2">
                        {{ $fbTitle }}
                        @if($fbYear)
                            <span class="year">({{ $fbYear }})</span>
                        @endif
                    </h1>
                    @if($fbRating || $fbGenres)
                        <div class="meta mb-3">
                            @if($fbRating)
                                <span class="badge-rating"><i class="bi bi-star-fill me-1"></i>{{ number_format($fbRating, 1) }}</span>
                            @endif
                            @if($fbGenres)
                                <span class="badge-meta">{{ $fbGenres }}</span>
                            @endif
                        </div>
                    @endif
                    <p class="overview">{{ $movie['overview'] ?? 'No description available.' }}</p>
                </div>
            </div>
        </div>
    </div>

@endif

{{-- 📦 TORRENTS SECTION --}}
<div class="container py-5">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <h4 class="text-white mb-0">📥 Available Torrents</h4>
        @include('partials._watch-online-btn', ['watchUrl' => $watchUrl ?? null])
    </div>

        @if($torrents->isNotEmpty())
        @php
            $resGroups = $torrents
                ->groupBy(fn ($t) => $t->resolution_label)
                ->map(fn ($g) => $g->sortByDesc('seeders')->values())
                ->sortBy(fn ($g) => $g->first()->resolution_order)
                ->values();
        @endphp

        <div class="res-panels">
            @foreach($resGroups as $group)
                @php
                    $panelId = 'series-res-'.Str::slug($group->first()->resolution_label);
                    $isFirst = $loop->first;
                @endphp
                <div class="res-panel">
                    <button class="res-panel-header {{ $isFirst ? 'is-open' : '' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $panelId }}"
                            aria-expanded="{{ $isFirst ? 'true' : 'false' }}">
                        <span class="res-label">{{ $group->first()->resolution_label }}</span>
                        <span class="res-count">{{ $group->count() }}</span>
                        <i class="bi bi-chevron-down res-chevron"></i>
                    </button>

                    <div id="{{ $panelId }}" class="collapse {{ $isFirst ? 'show' : '' }}">
                        <div class="res-panel-body">
                            @foreach($group as $torrent)
                                <div class="torrent-item">
                                    <div class="torrent-main">
                                        <div class="torrent-name">
                                            <span class="episode-badge">{{ $torrent->episode_label }}</span>
                                            <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}"
                                               class="torrent-link text-decoration-none">
                                                {{ $torrent->name }}
                                            </a>
                                        </div>
                                        <div class="torrent-meta">
                                            <span class="torrent-size">💾 {{ number_format($torrent->size / 1073741824, 2) }} GB</span>
                                            <span class="seeders">🌱 {{ $torrent->seeders }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="torrent-empty">
            <i class="bi bi-box-seam"></i>
            <h4>This title is not in our torrent database yet</h4>
            <p>
                This series is in your library, but no torrent has been uploaded for it yet.
                If you would like to watch it, please submit a request and an uploader may fill it.
            </p>

            @php
                $reqName = $movie['name'] ?? ($libraryEntry->title ?? 'This series');
                $reqTmdb = 'https://www.themoviedb.org/tv/'.$tmdbid;
                $reqImdb = !empty($movie['imdb_id'])
                    ? 'https://www.imdb.com/title/'.$movie['imdb_id'].'/'
                    : null;
                $reqImage = ! empty($movie['poster_path'])
                    ? 'https://image.tmdb.org/t/p/w500'.$movie['poster_path']
                    : ($libraryEntry->poster_path ? 'https://image.tmdb.org/t/p/w500'.$libraryEntry->poster_path : null);
            @endphp

            <a class="btn request-btn"
               href="{{ route('requests.create', array_filter([
                   'name'     => $reqName,
                   'tmdb_url' => $reqTmdb,
                   'imdb_url' => $reqImdb,
                   'image'    => $reqImage,
               ])) }}">
                <i class="bi bi-megaphone me-1"></i> Make a Request
            </a>
        </div>
    @endif

{{-- =========================
         LAST EPISODE TO AIR
    ========================== --}}
    @php
        $lastEp = $movie['last_episode_to_air'] ?? null;
        if ($lastEp && !empty($lastEp['still_path'])) {
            $lastEp['still_path'] = 'https://image.tmdb.org/t/p/w780' . $lastEp['still_path'];
        }
    @endphp

    @if(!empty($lastEp))

        <div class="tv-episode-card last-episode mt-5">

            <div class="episode-card-header">
                <div class="episode-icon-box last-icon">
                    <i class="bi bi-tv"></i>
                </div>
                <div>
                    <h3 class="episode-card-title mb-0">Last Episode</h3>
                    <div class="episode-card-subtitle">Most recently aired</div>
                </div>
            </div>

            <div class="episode-card-body">

                @if(!empty($lastEp['still_path']))
                    <img src="{{ $lastEp['still_path'] }}"
                         alt="{{ $lastEp['name'] }}"
                         class="episode-still"
                         loading="lazy">
                @endif

                <div class="episode-info">

                    <div class="episode-title">
                        {{ $lastEp['name'] ?? 'Episode ' . $lastEp['episode_number'] }}
                    </div>

                    <div class="episode-meta">

                        @if(!empty($lastEp['season_number']))
                            <span class="episode-meta-pill">
                                <i class="bi bi-collection-play"></i>
                                S{{ $lastEp['season_number'] }}E{{ $lastEp['episode_number'] }}
                            </span>
                        @endif

                        @if(!empty($lastEp['air_date']))
                            <span class="episode-meta-pill">
                                <i class="bi bi-calendar"></i>
                                {{ \Carbon\Carbon::parse($lastEp['air_date'])->format('M d, Y') }}
                            </span>
                        @endif

                        @if(!empty($lastEp['vote_average']))
                            <span class="episode-meta-pill">
                                <i class="bi bi-star-fill"></i>
                                {{ number_format($lastEp['vote_average'], 1) }}
                            </span>
                        @endif

                    </div>

                    @if(!empty($lastEp['overview']))
                        <div class="episode-overview">
                            {{ $lastEp['overview'] }}
                        </div>
                    @endif

                </div>

            </div>

        </div>

    @endif
{{-- =========================
         SEASON DETAILS
    ========================== --}}
    @php
        $seasonDetails = collect($movie['seasons'] ?? [])
            ->filter(fn ($s) => !empty($s['name']))
            ->map(function ($s) {
                $s['poster'] = !empty($s['poster_path'])
                    ? 'https://image.tmdb.org/t/p/w342' . $s['poster_path']
                    : '/images/noposter.jpg';
                return $s;
            })
            ->all();
    @endphp

    @if(!empty($seasonDetails))

        <div class="tv-seasons-section tv-episode-card mt-5">

            <div class="episode-card-header">

                <div class="episode-icon-box last-icon">
                    <i class="bi bi-collection-play"></i>
                </div>

                <div>
                    <h3 class="episode-card-title mb-0">Seasons</h3>
                    <div class="episode-card-subtitle">
                        All available seasons of this series
                    </div>
                </div>

                <div class="ms-auto cast-count-badge">
                    <i class="bi bi-collection-play"></i>
                    {{ count($seasonDetails) }} Seasons
                </div>

            </div>

            <div class="tv-seasons-row {{ count($seasonDetails) > 8 ? 'seasons-scrollable' : '' }}">

                @foreach($seasonDetails as $season)

                    <div class="season-card"
                         role="button"
                         data-season-url="{{ route('library.series.season', [$tmdbid, $season['season_number']]) }}"
                         aria-label="View {{ $season['name'] }} episodes">

                        <div class="season-poster-wrap">
                            <img
                                src="{{ $season['poster'] }}"
                                loading="lazy"
                                class="season-poster"
                                alt="{{ $season['name'] }}"
                            >
                            <div class="season-view-overlay">
                                <i class="bi bi-collection-play"></i>
                                View Episodes
                            </div>
                            @if(!empty($season['vote_average']))
                                <span class="season-rating">
                                    <i class="bi bi-star-fill"></i>
                                    {{ number_format($season['vote_average'], 1) }}
                                </span>
                            @endif
                        </div>

                        <div class="season-info">

                            <div class="season-name">
                                {{ $season['name'] ?? 'Season ' . $season['season_number'] }}
                            </div>

                            <div class="season-meta">

                                @if(!empty($season['episode_count']))
                                    <span>
                                        <i class="bi bi-tv"></i>
                                        {{ $season['episode_count'] }} Episodes
                                    </span>
                                @endif

                                @if(!empty($season['air_date']))
                                    <span>
                                        <i class="bi bi-calendar"></i>
                                        {{ \Carbon\Carbon::parse($season['air_date'])->format('Y') }}
                                    </span>
                                @endif

                            </div>

                            @if(!empty($season['overview']))
                                <div class="season-overview">
                                    {{ $season['overview'] }}
                                </div>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @endif
{{-- =========================
         YOU MIGHT ALSO LIKE
    ========================== --}}
    @if(!empty($recommendations))

        <div class="tmdb-recs mt-5">

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

                <a href="https://www.themoviedb.org/tv/{{ $tmdbid }}/recommendations"
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

                        <a href="https://www.themoviedb.org/tv/{{ $rec['id'] }}"
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
    flex-wrap: wrap;
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
.torrent-item {
    background: #111;
    border: 1px solid rgba(255, 255, 255, .05);
    border-radius: 10px;
    padding: 13px 15px;
    transition: 0.2s ease;
}

.torrent-item:hover {
    background: #1a1a1a;
    border-color: rgba(59, 130, 246, .25);
}

.torrent-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.torrent-name {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.torrent-link {
    font-weight: 600;
    color: #fff;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.torrent-link:hover {
    color: #60a5fa;
}

.torrent-meta {
    display: flex;
    gap: 15px;
    color: #aaa;
    flex-shrink: 0;
}

.seeders {
    color: #4caf50;
    font-weight: 600;
}

/* Episode badge (series torrents: Full Season, S01E05, Seasons 1-4, etc.) */
.episode-badge {
    flex-shrink: 0;
    padding: 3px 8px;
    border-radius: 6px;
    background: rgba(59, 130, 246, .14);
    border: 1px solid rgba(59, 130, 246, .28);
    color: #93c5fd;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

/* Resolution panels */
.res-panels {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.res-panel {
    border: 1px solid rgba(255, 255, 255, .07);
    border-radius: 12px;
    overflow: hidden;
    background: rgba(9, 16, 29, .45);
}

.res-panel-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    border: none;
    padding: 14px 18px;
    cursor: pointer;
    text-align: left;
    transition: background .15s ease;
}

.res-panel-header:hover {
    background: rgba(45, 212, 191, .05);
}

.res-panel-header .res-label {
    font-size: 15px;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: .2px;
}

.res-panel-header .res-count {
    display: inline-flex;
    align-items: center;
    padding: 2px 9px;
    border-radius: 999px;
    background: rgba(45, 212, 191, .12);
    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 700;
}

.res-panel-header .res-chevron {
    margin-left: auto;
    color: #64748b;
    transition: transform .2s ease;
}

.res-panel-header.is-open .res-chevron {
    transform: rotate(180deg);
}

.res-panel-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 4px 14px 14px;
}

@media (max-width: 575px) {
    .torrent-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .torrent-meta {
        width: 100%;
        justify-content: space-between;
    }
    .torrent-link {
        white-space: normal;
    }
}

/* ── TORRENT EMPTY STATE (no uploads yet) ────────────────────── */
.torrent-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: rgba(15, 23, 42, .55);
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: .85rem;
}
.torrent-empty i {
    font-size: 42px;
    color: var(--ui-accent);
    opacity: .55;
    display: block;
    margin-bottom: .75rem;
}
.torrent-empty h4 {
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 .5rem;
}
.torrent-empty p {
    color: #64748b;
    font-size: 13px;
    max-width: 480px;
    margin: 0 auto 1.1rem;
}
.torrent-empty .request-btn {
    background: rgba(45, 212, 191, .10);
    border: 1px solid rgba(45, 212, 191, .25);
    color: var(--ui-accent);
    font-weight: 600;
    border-radius: .55rem;
    padding: .45rem 1.1rem;
    font-size: 13px;
    transition: background .15s, border-color .15s;
}
.torrent-empty .request-btn:hover {
    background: rgba(45, 212, 191, .20);
    border-color: rgba(45, 212, 191, .45);
    color: var(--ui-accent);
}

/* =========================
   EPISODE CARD (Last Episode)
========================= */
.tv-episode-card {
    background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(0, 0, 0, .24);
}

.episode-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: rgba(45, 212, 191, .045);
    border-bottom: 1px solid var(--ui-border);
}

.episode-icon-box {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: .6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.last-icon {
    background: rgba(45, 212, 191, .16);
    color: var(--ui-accent);
}

.episode-card-title {
    color: #fff;
    font-size: 15px;
    font-weight: 700;
}

.episode-card-subtitle {
    color: rgba(255, 255, 255, .5);
    font-size: 12px;
}

.episode-card-body {
    display: flex;
    gap: 16px;
    padding: 16px;
}

.episode-still {
    width: 260px;
    max-width: 260px;
    border-radius: .6rem;
    object-fit: cover;
    flex: 0 0 auto;
}

.episode-title {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 8px;
}

.episode-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}

.episode-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: .45rem;
    background: rgba(255, 255, 255, .06);
    border: 1px solid rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .78);
    font-size: 12px;
    font-weight: 600;
}

.episode-overview {
    color: rgba(255, 255, 255, .6);
    font-size: 13px;
    line-height: 1.55;
}

/* =========================
   SEASONS
========================= */
.tv-seasons-section {
    padding-bottom: 16px;
}

.tv-seasons-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    padding: 16px 16px 4px;
}

.tv-seasons-row.seasons-scrollable {
    display: flex;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar {
    display: none;
}

.tv-seasons-row.seasons-scrollable .season-card {
    flex: 0 0 150px;
    width: 150px;
    min-width: 150px;
}

.season-card {
    overflow: hidden;
    border-radius: .7rem;
    background: rgba(9, 16, 29, .48);
    border: 1px solid rgba(255, 255, 255, .055);
    transition: transform .18s ease, border-color .18s ease;
}

.season-card:hover {
    transform: translateY(-3px);
    border-color: rgba(45, 212, 191, .28);
}

.season-poster-wrap {
    position: relative;
    aspect-ratio: 2 / 3;
    overflow: hidden;
    background: #0f172a;
}

.season-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .25s ease;
}

.season-card:hover .season-poster {
    transform: scale(1.04);
}

.season-rating {
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

.season-info {
    padding: 10px;
}

.season-name {
    color: rgba(255, 255, 255, .85);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
}

.season-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
    color: rgba(255, 255, 255, .48);
    font-size: 11px;
    font-weight: 600;
}

.season-meta i {
    color: var(--ui-accent);
    font-size: 10px;
}

.season-overview {
    margin-top: 7px;
    color: rgba(255, 255, 255, .52);
    font-size: 11px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
/* =========================================================
   LIBRARY SUBSCRIBE ROW
========================================================= */
.library-subscribe-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-top: 14px;
    border-radius: .8rem;
    background: rgba(9, 16, 29, .55);
    border: 1px solid var(--ui-border);
}
.library-subscribe-row .subscribe-btn {
    background: rgba(45, 212, 191, .08);
    border: 1px solid rgba(45, 212, 191, .22);
    color: var(--ui-accent);
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}
.library-subscribe-row .subscribe-btn:hover {
    background: rgba(45, 212, 191, .16);
    border-color: rgba(45, 212, 191, .38);
    color: var(--ui-accent);
}

/* Season card "view episodes" overlay */
.season-view-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    background: rgba(5, 10, 18, .72);
    opacity: 0;
    transition: opacity .18s ease;
    pointer-events: none;
}
.season-card:hover .season-view-overlay {
    opacity: 1;
}
.season-view-overlay i {
    font-size: 20px;
    color: var(--ui-accent);
}

/* =========================================================
   SEASON MODAL
========================================================= */
.season-modal-content {
    background: linear-gradient(150deg, #101a2c, #0b1120);
    border: 1px solid var(--ui-border);
    border-radius: .9rem;
    color: rgba(255, 255, 255, .88);
}
.season-modal-header {
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid var(--ui-border);
    background: rgba(45, 212, 191, .04);
}
.season-modal-poster {
    width: 64px;
    height: 92px;
    object-fit: cover;
    border-radius: .5rem;
    border: 1px solid var(--ui-border);
    background: #0f172a;
}
.season-modal-subtitle {
    color: rgba(255, 255, 255, .55);
    font-size: 13px;
    margin-top: 3px;
}
.season-modal-body {
    max-height: 70vh;
    overflow-y: auto;
}
.season-modal-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: rgba(255, 255, 255, .6);
    padding: 40px 0;
}
.season-modal-overview {
    color: rgba(255, 255, 255, .78);
    font-size: 14px;
    line-height: 1.6;
}
.season-modal-section-title {
    margin: 18px 0 10px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--ui-accent);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Episode list */
.season-episodes-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.season-episode {
    display: flex;
    gap: 12px;
    padding: 10px;
    border-radius: .6rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid var(--ui-border);
}
.season-episode-num {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    min-height: 44px;
    padding: 0 8px;
    border-radius: .5rem;
    background: rgba(45, 212, 191, .08);
    border: 1px solid rgba(45, 212, 191, .18);
    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 700;
    align-self: flex-start;
}
.season-episode-still {
    flex: 0 0 auto;
    width: 130px;
    height: 74px;
    object-fit: cover;
    border-radius: .45rem;
    border: 1px solid var(--ui-border);
    background: #0f172a;
}
.season-episode-nostill {
    flex: 0 0 auto;
    width: 130px;
    height: 74px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: .45rem;
    background: #0f172a;
    border: 1px solid var(--ui-border);
    color: rgba(255, 255, 255, .25);
    font-size: 22px;
}
.season-episode-info { min-width: 0; }
.season-episode-title {
    color: rgba(255, 255, 255, .9);
    font-size: 14px;
    font-weight: 700;
}
.season-episode-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 4px;
    color: rgba(255, 255, 255, .5);
    font-size: 12px;
}
.season-episode-meta i { color: var(--ui-accent); }
.season-episode-overview {
    margin-top: 6px;
    color: rgba(255, 255, 255, .62);
    font-size: 13px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Cast row */
.season-cast-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 10px;
    overflow-x: auto;
    padding: 3px 2px 8px;
    scrollbar-width: none;
}
.season-cast-row::-webkit-scrollbar { display: none; }
.season-cast-card {
    flex: 0 0 96px;
    width: 96px;
    text-align: center;
    padding: 8px;
    border-radius: .6rem;
    background: rgba(255, 255, 255, .03);
    border: 1px solid var(--ui-border);
}
.season-cast-photo {
    width: 78px;
    height: 78px;
    margin: 0 auto 6px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f172a;
    border: 1px solid var(--ui-border);
    color: rgba(255, 255, 255, .25);
    font-size: 24px;
}
.season-cast-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.season-cast-name {
    color: rgba(255, 255, 255, .85);
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.season-cast-character {
    color: rgba(255, 255, 255, .45);
    font-size: 11px;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 576px) {
    .season-episode-still,
    .season-episode-nostill {
        width: 96px;
        height: 56px;
    }
}

/* Subscriber count + names next to subscribe button */
.subscribers-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: 12.5px;
    color: var(--ui-text-muted);
    line-height: 1.3;
}
.subscribers-label i { color: var(--ui-accent); }
.subscribers-label .subscribers-count { font-weight: 700; color: var(--ui-accent-strong); white-space: nowrap; }
.subscribers-label .subscribers-names { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 260px; }
.subscribers-label .subscribers-more { color: var(--ui-accent); font-weight: 700; }
/* "Watch online" button (shown when the title is in the online catalogue) */
.watch-online-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(34,197,94,.10);
    border: 1px solid rgba(34,197,94,.30);
    color: #4ade80;
    padding: 8px 14px;
    border-radius: .6rem;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}
.watch-online-btn:hover {
    background: rgba(34,197,94,.18);
    border-color: rgba(34,197,94,.44);
    color: #bbf7d0;
    transform: translateY(-1px);
}
.watch-online-btn i { font-size: 15px; }
</style>

{{-- =========================================================
    SEASON MODAL
========================================================== --}}
<div class="modal fade" id="seasonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content season-modal-content">
            <div class="modal-header season-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <img class="season-modal-poster" id="seasonModalPoster" src="/images/noposter.jpg" alt="">
                    <div>
                        <h5 class="modal-title mb-0 text-white" id="seasonModalTitle">Loading…</h5>
                        <div class="season-modal-subtitle" id="seasonModalMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body season-modal-body">
                <div id="seasonModalLoading" class="season-modal-loading">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading season…
                </div>
                <div id="seasonModalContent" class="season-modal-content-inner d-none">
                    <p class="season-modal-overview" id="seasonModalOverview"></p>

                    <h6 class="season-modal-section-title">
                        <i class="bi bi-collection-play"></i> Episodes
                    </h6>
                    <div class="season-episodes-list" id="seasonModalEpisodes"></div>

                    <h6 class="season-modal-section-title">
                        <i class="bi bi-people"></i> Cast
                    </h6>
                    <div class="season-cast-row" id="seasonModalCast"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('seasonModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('[data-season-url]').forEach(function (card) {
        card.addEventListener('click', function () {
            openSeason(card.getAttribute('data-season-url'));
        });
    });

    async function openSeason(url) {
        document.getElementById('seasonModalLoading').classList.remove('d-none');
        document.getElementById('seasonModalContent').classList.add('d-none');
        document.getElementById('seasonModalTitle').textContent = 'Season';
        document.getElementById('seasonModalMeta').textContent = '';
        document.getElementById('seasonModalPoster').src = '/images/noposter.jpg';
        modal.show();

        try {
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('request failed');
            renderSeason(await res.json());
        } catch (e) {
            document.getElementById('seasonModalLoading').classList.add('d-none');
            document.getElementById('seasonModalContent').classList.remove('d-none');
            document.getElementById('seasonModalTitle').textContent = 'Unable to load season';
            document.getElementById('seasonModalOverview').textContent =
                'There was a problem loading this season. Please try again.';
        }
    }

    function renderSeason(data) {
        document.getElementById('seasonModalLoading').classList.add('d-none');
        document.getElementById('seasonModalContent').classList.remove('d-none');

        document.getElementById('seasonModalTitle').textContent = data.name || 'Season';
        document.getElementById('seasonModalPoster').src = data.poster || '/images/noposter.jpg';

        const meta = [];
        if (data.air_date) meta.push(formatDate(data.air_date));
        if (data.episodes) meta.push(data.episodes.length + ' Episodes');
        document.getElementById('seasonModalMeta').textContent = meta.join('  ·  ');
        document.getElementById('seasonModalOverview').textContent =
            data.overview || 'No description available.';

        const epWrap = document.getElementById('seasonModalEpisodes');
        epWrap.innerHTML = '';
        if (data.episodes && data.episodes.length) {
            data.episodes.forEach(function (e) { epWrap.appendChild(episodeRow(e)); });
        } else {
            epWrap.innerHTML = '<div class="text-muted">No episode information available.</div>';
        }

        const castWrap = document.getElementById('seasonModalCast');
        castWrap.innerHTML = '';
        if (data.cast && data.cast.length) {
            data.cast.forEach(function (a) { castWrap.appendChild(actorCard(a)); });
        } else {
            castWrap.innerHTML = '<div class="text-muted">No cast information available.</div>';
        }

        if (typeof bootstrap !== 'undefined') {
            modalEl.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                if (!el._bsTooltip) new bootstrap.Tooltip(el);
            });
        }
    }

    function episodeRow(e) {
        const row = document.createElement('div');
        row.className = 'season-episode';
        const still = e.still
            ? '<img class="season-episode-still" src="' + e.still + '" loading="lazy" alt="' + esc(e.name || '') + '">'
            : '<div class="season-episode-nostill"><i class="bi bi-film"></i></div>';
        row.innerHTML =
            '<div class="season-episode-num">E' + (e.episode_number || '?') + '</div>' +
            still +
            '<div class="season-episode-info">' +
                '<div class="season-episode-title">' + esc(e.name || ('Episode ' + (e.episode_number || ''))) + '</div>' +
                '<div class="season-episode-meta">' +
                    (e.air_date ? '<span><i class="bi bi-calendar"></i> ' + formatDate(e.air_date) + '</span>' : '') +
                    (e.rating ? '<span><i class="bi bi-star-fill"></i> ' + e.rating + '</span>' : '') +
                '</div>' +
                (e.overview ? '<div class="season-episode-overview">' + esc(e.overview) + '</div>' : '') +
            '</div>';
        return row;
    }

    function actorCard(a) {
        const card = document.createElement('div');
        card.className = 'season-cast-card';
        const photo = a.photo
            ? '<img src="' + a.photo + '" loading="lazy" alt="' + esc(a.name || '') + '">'
            : '<i class="bi bi-person"></i>';
        card.innerHTML =
            '<div class="season-cast-photo">' + photo + '</div>' +
            '<div class="season-cast-name">' + esc(a.name || 'Unknown') + '</div>' +
            '<div class="season-cast-character">' + esc(a.character || '') + '</div>';
        return card;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const m = months[parseInt(parts[1], 10) - 1];
        if (!m) return dateStr;
        return m + ' ' + parseInt(parts[2], 10) + ', ' + parts[0];
    }

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str == null ? '' : String(str);
        return d.innerHTML;
    }
});
</script>
@endpush

@endsection