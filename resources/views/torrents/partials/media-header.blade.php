{{-- =========================
    PREMIUM FANART HEADER
========================= --}}

<div class="container px-xl-5 px-lg-4 px-3">

    <div class="premium-media-card">

        {{-- BACKDROP GLOW --}}
        <div class="premium-backdrop"></div>

        <div class="row g-4 align-items-start position-relative">

            {{-- =========================
                POSTER COLUMN
            ========================= --}}
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">

                <div class="premium-poster-wrapper {{ $torrent->trashed() ? 'deleted-poster' : '' }}">

                    <img
                        {{-- src="{{ $display['fanart']['poster'] ?? $display['poster'] ?? $torrent->poster }}" --}}
                        src="{{ $torrent->poster }}"
                        loading="lazy"
                        class="premium-poster"
                        alt="{{ $display['title'] }}"
                    >

                    {{-- Deleted --}}
                    @if($torrent->trashed())
                        <div class="deleted-overlay">
                            <span>Torrent Deleted</span>
                        </div>
                    @endif

                    {{-- Overlay --}}
                    <div class="poster-overlay"></div>

                    {{-- LOGO --}}
                    {{-- @if(!empty($fanartLogo))
                        <div class="fanart-logo">
                            <img src="{{ $display['fanart']['logo'] }}">
                        </div>
                    @endif --}}

                </div>

                {{-- TRAILER --}}
                <div class="premium-trailer-wrapper">

                    @if(!empty($torrent->trailer))

                        @php
                            $embedUrl = str_replace('watch?v=', 'embed/', $torrent->trailer);
                        @endphp

                        <a href="{{ $embedUrl }}?autoplay=1"
                           data-lity
                           class="premium-trailer-btn">

                            <i class="bi bi-play-circle-fill"></i>
                            Watch Trailer

                        </a>

                    @elseif(!empty($display['trailer']))

                        <a href="https://www.youtube.com/embed/{{ $display['trailer']['key'] }}?autoplay=1"
                           data-lity
                           class="premium-trailer-btn">

                            <i class="bi bi-play-circle-fill"></i>
                            Watch Trailer

                        </a>

                    @else

                        <div class="no-trailer">
                            <i class="bi bi-film"></i>
                            No trailer available
                        </div>

                    @endif

                </div>

            </div>

            {{-- =========================
                INFO COLUMN
            ========================= --}}
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">

                <div class="premium-info">

                    {{-- TITLE --}}
                    <div class="title-block">

                        <h1 class="premium-title">

                            {{ $display['title'] }}

                            @if($display['year'])
                                <span class="release-year">
                                    ({{ $display['year'] }})
                                </span>
                            @endif

                        </h1>

                        @if($display['tagline'])
                            <p class="premium-tagline">
                                {{ $display['tagline'] }}
                            </p>
                        @endif

                    </div>

                    {{-- META --}}
                    <div class="premium-meta-row">

                        @if($display['ratings']['rated'])

                            <div class="meta-pill">
                                {!! $display['type'] === 'tv'
                                    ? getTVRatingBadge($display['ratings']['rated'])
                                    : getRatingBadge($display['ratings']['rated']) !!}
                            </div>

                        @endif

                        @if($display['runtime'])

                            <div class="meta-pill">
                                <i class="bi bi-clock"></i>

                                {{ $display['type'] === 'tv'
                                    ? $display['runtime'].'m / episode'
                                    : intdiv($display['runtime'], 60).'h '.($display['runtime'] % 60).'m' }}
                            </div>

                        @endif

                        @if($display['type'] === 'tv' && $display['seasons'])

                            <div class="meta-pill">
                                <i class="bi bi-collection-play"></i>
                                {{ $display['seasons'] }} Seasons
                            </div>

                        @endif

                        @if($display['type'] === 'tv' && $display['episodes'])

                            <div class="meta-pill">
                                <i class="bi bi-tv"></i>
                                {{ $display['episodes'] }} Episodes
                            </div>

                        @endif

                        @if($display['status'])

                            <div class="meta-pill">
                                <i class="bi bi-info-circle"></i>
                                {{ $display['status'] }}
                            </div>

                        @endif

                    </div>

                    {{-- GENRES --}}
                    <div class="premium-genres">

                        @foreach($torrent->genres as $genre)

                            <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
                               class="premium-genre-tag">

                                {{ $genre->name }}

                            </a>

                        @endforeach

                    </div>

                    {{-- RATINGS --}}
                    <div class="premium-ratings">

                        @if($display['ratings']['tmdb'] && $torrent->tmdbid)

                            <a href="https://www.themoviedb.org/{{ $display['type'] }}/{{ $torrent->tmdbid }}"
                               target="_blank"
                               class="rating-card tmdb-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['tmdb'] }}
                                </div>

                                <div class="rating-source">
                                    TMDB
                                </div>

                            </a>

                        @endif

                        @if($display['ratings']['imdb'] && $torrent->imdbid)

                            <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}"
                               target="_blank"
                               class="rating-card imdb-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['imdb'] }}
                                </div>

                                <div class="rating-source">
                                    IMDb
                                </div>

                            </a>

                        @endif

                        @if($display['ratings']['rt'])

                            <div class="rating-card rt-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['rt'] }}
                                </div>

                                <div class="rating-source">
                                    RT
                                </div>

                            </div>

                        @endif

                    </div>

                    

                    {{-- OVERVIEW --}}
                    <div class="premium-overview">

                        <h5>
                            {{ $display['overview'] }}
                        </h5>

                    </div>

                    {{-- CREW --}}
                    <div class="premium-facts">

                        @if($display['type'] === 'movie' && $display['director'])

                            <div class="fact-box">

                                <span class="fact-label">
                                    Director
                                </span>

                                <span class="fact-value">
                                    {{ $display['director'] }}
                                </span>

                            </div>

                        @endif

                        @if($display['type'] === 'tv' && !empty($display['creators']))

                            <div class="fact-box">

                                <span class="fact-label">
                                    Creator
                                </span>

                                <span class="fact-value">
                                    {{ implode(', ', $display['creators']) }}
                                </span>

                            </div>

                        @endif

                    </div>

                    {{-- NETWORKS --}}
                    @if(!empty($display['networks']))

                        <div class="premium-network-section">

                            <div class="network-title">

                                <i class="bi bi-building"></i>

                                {{ $display['type'] === 'tv'
                                    ? 'Networks'
                                    : 'Production' }}

                            </div>

                            <div class="network-grid">

                                @foreach($display['networks'] as $n)

                                    @if($n['logo'])

                                        <div class="network-logo-card">

                                            <img
                                                src="{{ $n['logo'] }}"
                                                class="network-logo"
                                                title="{{ $n['name'] }}"
                                            >

                                        </div>

                                    @endif

                                @endforeach

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

<style>
/* =========================================================
   FILEIPLAY PREMIUM MEDIA HEADER
   Dark glass / teal forum style
   ========================================================= */

.premium-media-card {
    position: relative;
    overflow: hidden;
    padding: 24px;
    border-radius: .85rem;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    backdrop-filter: blur(14px);
    box-shadow: 0 18px 45px rgba(0,0,0,.32);
    margin-top: 20px;
}

.premium-backdrop {
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: .08;
}

.premium-poster-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: .7rem;
    border: 1px solid var(--ui-border);
    background: #0f172a;
    box-shadow: 0 12px 30px rgba(0,0,0,.38);
}

.premium-poster {
    display: block;
    width: 100%;
    height: auto;
    transition: transform .35s ease;
}

.premium-poster-wrapper:hover .premium-poster {
    transform: scale(1.035);
}

.poster-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(
        to top,
        rgba(0,0,0,.58),
        transparent 48%
    );
}

.fanart-logo {
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 12px;
    text-align: center;
}

.fanart-logo img {
    max-height: 55px;
    max-width: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 10px rgba(0,0,0,.8));
}

.deleted-poster img {
    filter: grayscale(100%) brightness(.5);
}

.deleted-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,.20);
}

.deleted-overlay span {
    color: #f87171;
    border: 1px solid rgba(248,113,113,.55);
    border-radius: .55rem;
    padding: 7px 12px;
    background: rgba(15,23,42,.82);
    font-size: 13px;
    font-weight: 700;
    transform: rotate(-8deg);
}

.premium-trailer-wrapper {
    margin-top: 12px;
}

.premium-trailer-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 12px;
    border-radius: .55rem;
    background: rgba(239,68,68,.12);
    border: 1px solid rgba(239,68,68,.28);
    color: #fca5a5;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}

.premium-trailer-btn:hover {
    background: rgba(239,68,68,.18);
    border-color: rgba(239,68,68,.42);
    color: #fecaca;
    transform: translateY(-1px);
}

.no-trailer {
    width: 100%;
    padding: 9px 12px;
    border-radius: .55rem;
    color: rgba(255,255,255,.48);
    background: rgba(255,255,255,.035);
    border: 1px solid rgba(255,255,255,.06);
    text-align: center;
    font-size: 13px;
}

.premium-info {
    padding-left: 6px;
}

.title-block {
    position: relative;
    padding-left: 12px;
    border-left: 3px solid var(--ui-accent);
}

.premium-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.45;
    margin-bottom: 6px;
    overflow-wrap: anywhere;
}

.release-year {
    color: var(--ui-accent);
    font-weight: 600;
}

.premium-tagline {
    color: rgba(255,255,255,.58);
    font-size: 13px;
    font-style: italic;
    margin-bottom: 16px;
}

.premium-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin: 14px 0;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border-radius: .5rem;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--ui-border);
    color: rgba(255,255,255,.78);
    font-size: 13px;
    font-weight: 600;
}

.meta-pill i {
    color: var(--ui-accent);
}

.premium-genres {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}

.premium-genre-tag {
    padding: 5px 9px;
    border-radius: .45rem;
    text-decoration: none;
    background: rgba(45,212,191,.07);
    border: 1px solid rgba(45,212,191,.16);
    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}

.premium-genre-tag:hover {
    color: #fff;
    background: rgba(45,212,191,.12);
    border-color: rgba(45,212,191,.30);
}

.premium-ratings {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 18px;
}

.rating-card {
    min-width: 72px;
    padding: 8px 10px;
    border-radius: .6rem;
    text-align: center;
    text-decoration: none;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--ui-border);
    color: #fff;
    transition: background .15s ease, border-color .15s ease, transform .15s ease;
}

.rating-card:hover {
    transform: translateY(-1px);
    color: #fff;
    border-color: rgba(45,212,191,.24);
    background: rgba(45,212,191,.05);
}

.rating-value {
    font-size: 14px;
    font-weight: 700;
}

.rating-source {
    color: rgba(255,255,255,.48);
    font-size: 11px;
    margin-top: 2px;
}

.premium-overview {
    margin-bottom: 18px;
    padding: 13px 15px;
    border-left: 2px solid rgba(45,212,191,.45);
    border-radius: .55rem;
    background: rgba(255,255,255,.025);
}

.premium-overview h5 {
    color: rgba(255,255,255,.76);
    font-size: 14px;
    line-height: 1.65;
    font-weight: 500;
    margin: 0;
}

.premium-facts {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.fact-box {
    padding: 9px 12px;
    border-radius: .55rem;
    background: rgba(255,255,255,.035);
    border: 1px solid var(--ui-border);
}

.fact-label {
    display: block;
    color: rgba(255,255,255,.45);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: 3px;
}

.fact-value {
    color: rgba(255,255,255,.82);
    font-size: 13px;
    font-weight: 600;
}

.premium-network-section {
    margin-top: 18px;
}

.network-title {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 9px;
    color: rgba(255,255,255,.72);
    font-size: 13px;
    font-weight: 700;
}

.network-title i {
    color: var(--ui-accent);
}

.network-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.network-logo-card {
    padding: 8px 11px;
    border-radius: .5rem;
    background: rgba(255,255,255,.035);
    border: 1px solid var(--ui-border);
}

.network-logo {
    height: 24px;
    max-width: 110px;
    object-fit: contain;
}

@media (max-width: 768px) {
    .premium-media-card {
        padding: 16px;
        border-radius: .75rem;
    }

    .premium-info {
        padding-left: 0;
    }

    .title-block {
        padding-left: 9px;
    }

    .premium-title {
        font-size: 14px;
        text-align: left;
    }

    .premium-tagline {
        font-size: 13px;
    }

    .premium-meta-row,
    .premium-genres,
    .premium-ratings,
    .premium-facts {
        justify-content: flex-start;
    }

    .premium-overview h5 {
        font-size: 14px;
        text-align: left;
    }

    .rating-card {
        min-width: 68px;
    }
}
</style>
