@extends('layouts.app')

@section('content')

<div class="lib-page container-fluid py-3 px-lg-4 px-3">

    {{-- ============================================================
         HERO
         ============================================================ --}}
    @if($featured->count())

        @php
            /*
             * Pick a completely random starting movie.
             */
            $hero = $featured->random();

            /*
             * Remove the starting movie from the rotation list.
             * This prevents the first rotation from immediately
             * showing the same movie again.
             */
            $heroMovies = $featured
                ->reject(fn ($movie) => (int) $movie->tmdbid === (int) $hero->tmdbid)
                ->values();
        @endphp

        <div
            class="lib-hero mb-4"
            id="movieHero"
            style="--hero-bg: url('https://image.tmdb.org/t/p/w1280/{{ $hero->backdrop }}');"
        >

            {{-- Full cinematic background --}}
            <div class="lib-hero-bg"></div>

            {{-- Dark overlays for readability --}}
            <div class="lib-hero-overlay"></div>

            {{-- Hero content --}}
            <div class="lib-hero-content">

                {{-- Movie poster --}}
                <div class="lib-hero-poster-wrap">
                    <img
                        id="heroPoster"
                        class="lib-hero-poster"
                        src="{{ $hero->poster_path
                            ? 'https://image.tmdb.org/t/p/w500' . $hero->poster_path
                            : asset('images/no-poster.png') }}"
                        alt="{{ $hero->title }}"
                    >
                </div>

                {{-- Movie information --}}
                <div class="lib-hero-info">

                    <span class="lib-hero-badge">
                        <i class="bi bi-film me-1"></i>
                        MOVIE LIBRARY
                    </span>

                    <h1 class="lib-hero-title" id="heroTitle">
                        {{ $hero->title }}
                    </h1>

                    <p class="lib-hero-sub" id="heroSub">

                        <i class="bi bi-star-fill text-warning"></i>
                        {{ number_format($hero->rating ?? 0, 1) }}

                        @if($hero->year)
                            <span class="hero-separator">&bull;</span>
                            {{ $hero->year }}
                        @endif
                        
                    </p>

                    <a
                        href="{{ route('library.movies.show', [
                            'tmdbid' => $hero->tmdbid,
                            'slug' => $hero->slug
                        ]) }}"
                        class="lib-btn-hero"
                        id="heroButton"
                    >
                        <i class="bi bi-play-circle me-1"></i>
                        View Details
                    </a>

                </div>

            </div>

        </div>

        {{-- ========================================================
             HERO MOVIE DATA
             ======================================================== --}}
        <script>
            window.libraryHeroMovies = [

                @foreach($heroMovies as $movie)

                    {
                        title: @json($movie->title),

                        tmdbid: @json($movie->tmdbid),

                        slug: @json($movie->slug),

                        url: @json(route('library.movies.show', [
                            'tmdbid' => $movie->tmdbid,
                            'slug' => $movie->slug
                        ])),

                        backdrop: @json($movie->backdrop_path),

                        poster: @json($movie->poster_path),

                        rating: @json($movie->rating ?? 0),

                        year: @json($movie->year)
                    },

                @endforeach

            ];
        </script>

    @endif


    {{-- ============================================================
         HEADER + SEARCH
         ============================================================ --}}
    <div class="lib-header mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h1 class="lib-page-title mb-1">
                    <i class="bi bi-film me-2"></i>
                    Movies
                </h1>

                <p class="lib-page-sub mb-0">
                    All titles in your library
                    &middot;
                    {{ $movies->total() }} titles
                </p>

            </div>

        </div>


        {{-- Search --}}
        <div class="lib-search mt-3">

            <form
                method="GET"
                action="{{ route('library.movies.index') }}"
                class="lib-search-form"
            >

                <i class="bi bi-search lib-search-icon"></i>

                <input
                    type="text"
                    name="q"
                    value="{{ $query ?? '' }}"
                    placeholder="Search your movie library..."
                    class="lib-search-input"
                >

                @if(!empty($query))

                    <a
                        href="{{ route('library.movies.index') }}"
                        class="lib-search-clear"
                        title="Clear search"
                    >
                        <i class="bi bi-x-lg"></i>
                    </a>

                @endif

                <button
                    type="submit"
                    class="lib-search-btn"
                >
                    <i class="bi bi-search me-1"></i>
                    Search
                </button>

            </form>

        </div>


        {{-- Search results --}}
        @if(!empty($query))

            <p class="lib-search-result-text mt-3 mb-0">

                Results for
                <strong>{{ $query }}</strong>

                <span class="text-muted">
                    &middot;
                    {{ $movies->total() }} found
                </span>

            </p>

        @endif

    </div>


    {{-- ============================================================
         MOVIE GRID
         ============================================================ --}}
    <div class="row g-3">

        @forelse($movies as $i => $movie)

            <div
                class="col-6 col-sm-4 col-md-3 col-lg-2 lib-card-col"
                style="--i:{{ $i }}"
            >

                <a
                    href="{{ route('library.movies.show', [
                        'tmdbid' => $movie->tmdbid,
                        'slug' => $movie->slug
                    ]) }}"
                    class="text-decoration-none d-block"
                >

                    <div class="lib-card">

                        {{-- Poster --}}
                        <div class="lib-card-poster">

                            <img
                                src="{{ $movie->poster
                                    ? 'https://image.tmdb.org/t/p/w500' . $movie->poster
                                    : asset('images/no-poster.png') }}"
                                alt="{{ $movie->title }}"
                                loading="lazy"
                            >


                            {{-- Rating --}}
                            @if($movie->rating)

                                <span class="lib-badge lib-badge-rating">

                                    <i class="bi bi-star-fill"></i>

                                    {{ number_format($movie->rating, 1) }}

                                </span>

                            @endif


                            {{-- Year --}}
                            @if($movie->year)

                                <span class="lib-badge lib-badge-year">
                                    {{ $movie->year }}
                                </span>

                            @endif


                            {{-- Seeders --}}
                            @if(!empty($movie->max_seeders))

                                <span class="lib-badge lib-badge-seeders">

                                    <i class="bi bi-arrow-up-circle-fill"></i>

                                    {{ $movie->max_seeders }}

                                </span>

                            @endif


                            {{-- Hover overlay --}}
                            <div class="lib-card-overlay">

                                <div class="lib-card-overlay-inner">

                                    <i class="bi bi-play-circle-fill lib-play-icon"></i>

                                    <span class="lib-overlay-label">
                                        View Details
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- Card information --}}
                        <div class="lib-card-meta">

                            <h5 class="lib-card-title">
                                {{ Str::limit($movie->title, 35) }}
                            </h5>

                            <div class="lib-card-info">

                                @if($movie->year)

                                    <span>
                                        {{ $movie->year }}
                                    </span>

                                @endif

                                @if($movie->max_seeders)

                                    <span>

                                        <i class="bi bi-arrow-up-circle-fill"></i>

                                        {{ $movie->max_seeders }}
                                        seeders

                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        @empty

            <div class="col-12 text-center py-5">

                <div class="lib-empty">

                    <i class="bi bi-film"></i>

                    <h4>
                        No movies found
                    </h4>

                    <p>
                        Try searching for a different title,
                        or check back later.
                    </p>

                </div>

            </div>

        @endforelse

    </div>


    {{-- ============================================================
         PAGINATION
         ============================================================ --}}
    @if($movies->hasPages())

        <div class="d-flex justify-content-center mt-4">

            {{ $movies->links('pagination::bootstrap-5') }}

        </div>

    @endif

</div>

@endsection


<style>

/* ══════════════════════════════════════════════════════════════
   MOVIE LIBRARY — PREMIUM DARK THEME
   ══════════════════════════════════════════════════════════════ */

.lib-page {
    padding-bottom: 2rem;
}


/* ══════════════════════════════════════════════════════════════
   CINEMATIC HERO
   ══════════════════════════════════════════════════════════════ */

.lib-hero {

    position: relative;

    min-height: 450px;

    overflow: hidden;

    display: flex;
    align-items: stretch;

    background: #050c16;

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    box-shadow:
        0 25px 60px rgba(0, 0, 0, .5),
        0 0 0 1px rgba(255, 255, 255, .04);
}


/* ─────────────────────────────────────────────────────────────
   FULL BACKGROUND
   ───────────────────────────────────────────────────────────── */

.lib-hero-bg {

    position: absolute;

    inset: 0;

    background-image: var(--hero-bg);

    background-size: cover;

    background-position: center center;

    background-repeat: no-repeat;

    transform: scale(1.025);

    transition:
        opacity .55s ease,
        transform 10s ease,
        background-image .55s ease;
}


/* Slow cinematic movement */
.lib-hero:hover .lib-hero-bg {

    transform: scale(1.055);

}


/* ══════════════════════════════════════════════════════════════
   CINEMATIC OVERLAY
   ══════════════════════════════════════════════════════════════ */

.lib-hero-overlay {

    position: absolute;

    inset: 0;

    background:

        /* Left-to-right readability */
        linear-gradient(
            90deg,
            rgba(5, 12, 22, .98) 0%,
            rgba(5, 12, 22, .90) 20%,
            rgba(5, 12, 22, .65) 42%,
            rgba(5, 12, 22, .30) 68%,
            rgba(5, 12, 22, .40) 100%
        ),

        /* Bottom fade */
        linear-gradient(
            0deg,
            rgba(5, 12, 22, .95) 0%,
            rgba(5, 12, 22, .35) 50%,
            rgba(5, 12, 22, .08) 100%
        );

}


/* Accent edge */

.lib-hero::after {

    content: "";

    position: absolute;

    inset: 0;

    border-left: 3px solid var(--ui-accent);

    pointer-events: none;

}


/* ══════════════════════════════════════════════════════════════
   HERO CONTENT
   ══════════════════════════════════════════════════════════════ */

.lib-hero-content {

    position: relative;

    z-index: 2;

    width: 100%;

    display: flex;

    align-items: flex-end;

    gap: 2rem;

    padding: 2rem 2.25rem;

    transition:
        opacity .4s ease,
        transform .4s ease;

}


/* Hero fading state */

.lib-hero-content.hero-fading {

    opacity: 0;

    transform: translateY(12px);

}


/* ══════════════════════════════════════════════════════════════
   POSTER
   ══════════════════════════════════════════════════════════════ */

.lib-hero-poster-wrap {

    flex: 0 0 195px;

    width: 195px;

    height: 292px;

    overflow: hidden;

    border-radius: .7rem;

    background: #0b1220;

    box-shadow:
        0 25px 50px rgba(0, 0, 0, .7),
        0 0 0 1px rgba(255, 255, 255, .14);

    transform: translateY(3px);

}


.lib-hero-poster {

    display: block;

    width: 100%;

    height: 100%;

    object-fit: cover;

    opacity: 1;

    transition:
        opacity .35s ease,
        transform .5s ease;

}


.lib-hero-poster:hover {

    transform: scale(1.025);

}


/* ══════════════════════════════════════════════════════════════
   HERO INFORMATION
   ══════════════════════════════════════════════════════════════ */

.lib-hero-info {

    max-width: 720px;

    padding-bottom: .35rem;

    text-shadow:
        0 2px 15px rgba(0, 0, 0, .8);

}


.lib-hero-badge {

    display: inline-flex;

    align-items: center;

    padding: .3rem .75rem;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 1.8px;

    color: #061311;

    background: var(--ui-accent);

    box-shadow:
        0 6px 18px rgba(0, 0, 0, .35);

}


.lib-hero-title {

    color: #f8fafc;

    font-size: 2.6rem;

    font-weight: 800;

    line-height: 1.08;

    margin-top: 1rem;

    margin-bottom: .5rem;

    text-shadow:
        0 3px 20px rgba(0, 0, 0, .9);

}


.lib-hero-sub {

    color: #dbe5f0;

    font-size: 14px;

    margin-bottom: 1.2rem;

}


.hero-separator {

    margin: 0 .2rem;

    opacity: .65;

}


/* ══════════════════════════════════════════════════════════════
   HERO BUTTON
   ══════════════════════════════════════════════════════════════ */

.lib-btn-hero {

    display: inline-flex;

    align-items: center;

    gap: .4rem;

    padding: .65rem 1.15rem;

    border-radius: .6rem;

    font-size: 13px;

    font-weight: 700;

    color: #061311;

    background: var(--ui-accent);

    border: 0;

    text-decoration: none;

    box-shadow:
        0 10px 25px rgba(0, 0, 0, .35);

    transition:
        transform .18s ease,
        filter .18s ease,
        box-shadow .18s ease;

}


.lib-btn-hero:hover {

    filter: brightness(1.08);

    color: #061311;

    transform: translateY(-2px);

    box-shadow:
        0 14px 30px rgba(0, 0, 0, .45);

}


/* ══════════════════════════════════════════════════════════════
   HEADER
   ══════════════════════════════════════════════════════════════ */

.lib-page-title {

    color: #f1f5f9;

    font-size: 1.5rem;

    font-weight: 800;

}


.lib-page-title i {

    color: var(--ui-accent);

}


.lib-page-sub {

    color: var(--ui-text-muted);

    font-size: 13px;

}


/* ══════════════════════════════════════════════════════════════
   SEARCH
   ══════════════════════════════════════════════════════════════ */

.lib-search-form {

    display: flex;

    align-items: center;

    gap: .5rem;

    padding: .35rem .65rem;

    background: var(--ui-surface);

    border: 1px solid var(--ui-border);

    border-radius: .6rem;

    transition:
        border-color .18s,
        box-shadow .18s;

}


.lib-search-form:focus-within {

    border-color: var(--ui-accent);

    box-shadow:
        0 0 0 2px rgba(99, 210, 198, .1);

}


.lib-search-icon {

    color: var(--ui-accent);

    font-size: 14px;

}


.lib-search-input {

    flex: 1;

    border: 0;

    outline: 0;

    background: transparent;

    color: #e2e8f0;

    font-size: 13px;

    min-width: 180px;

}


.lib-search-input::placeholder {

    color: #64748b;

}


.lib-search-clear {

    color: #64748b;

    font-size: 12px;

    padding: 4px 6px;

    transition: color .15s;

}


.lib-search-clear:hover {

    color: #f1f5f9;

}


.lib-search-btn {

    display: inline-flex;

    align-items: center;

    gap: .3rem;

    padding: .4rem .75rem;

    border: 0;

    border-radius: .45rem;

    font-weight: 700;

    font-size: 13px;

    color: #061311;

    background: var(--ui-accent);

    cursor: pointer;

    white-space: nowrap;

    transition: filter .15s;

}


.lib-search-btn:hover {

    filter: brightness(1.08);

}


.lib-search-result-text {

    color: var(--ui-text-muted);

    font-size: 13px;

}


.lib-search-result-text strong {

    color: #f1f5f9;

}


/* ══════════════════════════════════════════════════════════════
   MOVIE CARD
   ══════════════════════════════════════════════════════════════ */

.lib-card {

    position: relative;

    border-radius: .65rem;

    overflow: hidden;

    background: var(--ui-surface);

    border: 1px solid var(--ui-border);

    transition:
        transform .28s cubic-bezier(.22,1,.36,1),
        box-shadow .28s,
        border-color .28s;

}


.lib-card:hover {

    transform:
        translateY(-6px)
        scale(1.02);

    z-index: 5;

    box-shadow:
        0 20px 40px rgba(0, 0, 0, .55),
        0 0 20px rgba(99, 210, 198, .06);

    border-color:
        rgba(99, 210, 198, .25);

}


.lib-card-poster {

    position: relative;

    overflow: hidden;

    aspect-ratio: 2 / 3;

}


.lib-card-poster img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition:
        transform .4s cubic-bezier(.22,1,.36,1);

}


.lib-card:hover .lib-card-poster img {

    transform: scale(1.08);

}


/* ══════════════════════════════════════════════════════════════
   BADGES
   ══════════════════════════════════════════════════════════════ */

.lib-badge {

    position: absolute;

    z-index: 3;

    padding: .2rem .45rem;

    border-radius: .35rem;

    font-size: 11px;

    font-weight: 800;

    line-height: 1;

    box-shadow:
        0 4px 12px rgba(0,0,0,.35);

    pointer-events: none;

}


.lib-badge-rating {

    top: 8px;

    left: 8px;

    color: #061311;

    background: var(--ui-accent);

}


.lib-badge-rating i {

    color: #061311;

    font-size: 9px;

}


.lib-badge-year {

    top: 8px;

    right: 8px;

    color: #f8fafc;

    background: rgba(5,12,22,.72);

    backdrop-filter: blur(4px);

}


.lib-badge-seeders {

    bottom: 8px;

    left: 8px;

    color: #22c55e;

    background: rgba(5,12,22,.82);

    backdrop-filter: blur(4px);

}


.lib-badge-seeders i {

    font-size: 9px;

}


/* ══════════════════════════════════════════════════════════════
   CARD OVERLAY
   ══════════════════════════════════════════════════════════════ */

.lib-card-overlay {

    position: absolute;

    inset: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            to top,
            rgba(5,12,22,.92) 0%,
            rgba(5,12,22,.45) 50%,
            transparent 100%
        );

    opacity: 0;

    transition: opacity .25s ease;

}


.lib-card:hover .lib-card-overlay {

    opacity: 1;

}


.lib-card-overlay-inner {

    display: flex;

    flex-direction: column;

    align-items: center;

    gap: .35rem;

    transform: translateY(10px);

    transition:
        transform .28s cubic-bezier(.22,1,.36,1);

}


.lib-card:hover .lib-card-overlay-inner {

    transform: translateY(0);

}


.lib-play-icon {

    font-size: 36px;

    color: var(--ui-accent);

    filter:
        drop-shadow(
            0 4px 12px rgba(99,210,198,.35)
        );

}


.lib-overlay-label {

    font-size: 12px;

    font-weight: 700;

    color: #f1f5f9;

    letter-spacing: .5px;

    text-transform: uppercase;

}


/* ══════════════════════════════════════════════════════════════
   CARD META
   ══════════════════════════════════════════════════════════════ */

.lib-card-meta {

    padding: .6rem .65rem .7rem;

}


.lib-card-title {

    color: #e2e8f0;

    font-size: .82rem;

    font-weight: 700;

    margin: 0;

    line-height: 1.25;

    transition: color .18s;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;

}


.lib-card:hover .lib-card-title {

    color: var(--ui-accent);

}


.lib-card-info {

    display: flex;

    align-items: center;

    gap: .5rem;

    margin-top: .25rem;

    font-size: 11px;

    color: var(--ui-text-muted);

}


.lib-card-info i {

    color: #22c55e;

    font-size: 9px;

}


/* ══════════════════════════════════════════════════════════════
   EMPTY STATE
   ══════════════════════════════════════════════════════════════ */

.lib-empty {

    padding: 3rem 0;

}


.lib-empty i {

    display: block;

    margin-bottom: .65rem;

    font-size: 38px;

    color: var(--ui-accent);

    opacity: .6;

}


.lib-empty h4 {

    margin: 0 0 .25rem;

    color: #f1f5f9;

    font-size: 14px;

    font-weight: 700;

}


.lib-empty p {

    margin: 0;

    color: #64748b;

    font-size: 13px;

}


/* ══════════════════════════════════════════════════════════════
   PAGINATION
   ══════════════════════════════════════════════════════════════ */

.lib-page .pagination {

    margin-bottom: 0;

}


.lib-page .pagination .page-link {

    min-width: 34px;

    margin: 0 2px;

    padding: .4rem .6rem;

    color: #cbd5e1;

    background: var(--ui-surface);

    border: 1px solid var(--ui-border);

    border-radius: .5rem;

    font-size: 13px;

    text-align: center;

    transition: all .18s;

}


.lib-page .pagination .page-link:hover {

    color: var(--ui-accent);

    background:
        rgba(99,210,198,.07);

    border-color:
        rgba(99,210,198,.3);

}


.lib-page .pagination .page-item.active .page-link {

    color: #061311;

    background: var(--ui-accent);

    border-color: var(--ui-accent);

}


.lib-page .pagination .page-item.disabled .page-link {

    color: #475569;

    background:
        rgba(15,23,42,.55);

    border-color:
        rgba(148,163,184,.1);

}


/* ══════════════════════════════════════════════════════════════
   STAGGER ANIMATION
   ══════════════════════════════════════════════════════════════ */

.lib-card-col {

    animation:
        libFadeUp .45s cubic-bezier(.22,1,.36,1) both;

    animation-delay:
        calc(var(--i, 0) * 40ms);

}


@keyframes libFadeUp {

    from {

        opacity: 0;

        transform:
            translateY(16px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* ══════════════════════════════════════════════════════════════
   RESPONSIVE — TABLET
   ══════════════════════════════════════════════════════════════ */

@media (max-width: 768px) {

    .lib-page {

        padding-left: .5rem !important;

        padding-right: .5rem !important;

    }


    .lib-hero {

        min-height: 390px;

        border-radius: .75rem;

    }


    .lib-hero-content {

        gap: 1.25rem;

        padding: 1.25rem;

    }


    .lib-hero-poster-wrap {

        flex: 0 0 125px;

        width: 125px;

        height: 188px;

    }


    .lib-hero-title {

        font-size: 1.75rem;

    }


    .lib-hero-info {

        min-width: 0;

    }

}


/* ══════════════════════════════════════════════════════════════
   RESPONSIVE — MOBILE
   ══════════════════════════════════════════════════════════════ */

@media (max-width: 576px) {

    .lib-hero {

        min-height: 340px;

    }


    .lib-hero-content {

        gap: .9rem;

        padding: 1rem;

    }


    .lib-hero-poster-wrap {

        flex: 0 0 95px;

        width: 95px;

        height: 143px;

    }


    .lib-hero-title {

        font-size: 1.4rem;

        margin-top: .7rem;

    }


    .lib-hero-badge {

        font-size: 9px;

        letter-spacing: 1.2px;

        padding: .25rem .55rem;

    }


    .lib-hero-sub {

        font-size: 12px;

        margin-bottom: .8rem;

    }


    .lib-btn-hero {

        padding: .5rem .85rem;

        font-size: 12px;

    }


    .lib-search-form {

        flex-wrap: wrap;

    }


    .lib-search-btn {

        width: 100%;

        justify-content: center;

    }

}


/* ══════════════════════════════════════════════════════════════
   VERY SMALL PHONES
   ══════════════════════════════════════════════════════════════ */

@media (max-width: 420px) {

    .lib-page-title {

        font-size: 1.2rem;

    }


    .lib-hero {

        min-height: 320px;

    }


    .lib-hero-poster-wrap {

        flex: 0 0 82px;

        width: 82px;

        height: 123px;

    }


    .lib-hero-content {

        gap: .7rem;

        padding: .8rem;

    }


    .lib-hero-title {

        font-size: 1.2rem;

    }


    .lib-hero-sub {

        font-size: 11px;

    }

}

</style>


{{-- ================================================================
     HERO ROTATION JAVASCRIPT
     ================================================================ --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const movies = window.libraryHeroMovies || [];

    /*
     * Nothing to rotate if there are fewer than 1 movies.
     */
    if (movies.length === 0) {
        return;
    }


    const hero = document.getElementById('movieHero');

    const heroBg = hero
        ? hero.querySelector('.lib-hero-bg')
        : null;

    const heroContent =
        hero
            ? hero.querySelector('.lib-hero-content')
            : null;

    const heroPoster =
        document.getElementById('heroPoster');

    const heroTitle =
        document.getElementById('heroTitle');

    const heroSub =
        document.getElementById('heroSub');

    const heroButton =
        document.getElementById('heroButton');


    /*
     * Make sure everything exists.
     */
    if (
        !hero ||
        !heroBg ||
        !heroContent ||
        !heroPoster ||
        !heroTitle ||
        !heroSub ||
        !heroButton
    ) {
        return;
    }


    /*
     * Fisher-Yates shuffle.
     *
     * This completely randomises the order so movies
     * don't simply appear in database order.
     */
    for (
        let i = movies.length - 1;
        i > 0;
        i--
    ) {

        const j =
            Math.floor(
                Math.random() * (i + 1)
            );

        [
            movies[i],
            movies[j]
        ] = [
            movies[j],
            movies[i]
        ];

    }


    /*
     * Start at the first item in the shuffled list.
     *
     * The movie currently displayed by PHP was removed
     * from this array, so we won't immediately show it again.
     */
    let currentIndex = 0;


    /*
     * Change hero movie.
     */
    function changeHero() {

        /*
         * Fade the content out.
         */
        heroContent.classList.add('hero-fading');


        setTimeout(function () {

            /*
             * Move to next random movie.
             */
            currentIndex++;


            /*
             * Start again when we reach the end.
             */
            if (
                currentIndex >= movies.length
            ) {

                /*
                 * Shuffle again before starting
                 * another complete cycle.
                 */
                for (
                    let i = movies.length - 1;
                    i > 0;
                    i--
                ) {

                    const j =
                        Math.floor(
                            Math.random() * (i + 1)
                        );

                    [
                        movies[i],
                        movies[j]
                    ] = [
                        movies[j],
                        movies[i]
                    ];

                }


                currentIndex = 0;

            }


            const movie =
                movies[currentIndex];


            /*
             * Change background.
             */
            hero.style.setProperty(
                '--hero-bg',
                `url('https://image.tmdb.org/t/p/w1280/${movie.backdrop}')`
            );


            /*
             * Change poster.
             */
            heroPoster.style.opacity = '0';


            setTimeout(function () {

                heroPoster.src =
                    movie.poster
                        ? `https://image.tmdb.org/t/p/w500${movie.poster}`
                        : '{{ asset('images/no-poster.png') }}';

                heroPoster.alt =
                    movie.title;

                heroPoster.style.opacity =
                    '1';

            }, 150);


            /*
             * Change title.
             */
            heroTitle.textContent =
                movie.title;


            /*
             * Change rating.
             */
            let subtitle =
                '<i class="bi bi-star-fill text-warning"></i> ' +
                Number(
                    movie.rating || 0
                ).toFixed(1);


            /*
             * Change year.
             */
            if (movie.year) {

                subtitle +=
                    ' <span class="hero-separator">&bull;</span> ' +
                    movie.year;

            }


            /*
             * Number of library titles.
             */
            subtitle +=
                ' <span class="hero-separator">&bull;</span> ' +
                '{{ $movies->total() }} titles available';


            heroSub.innerHTML =
                subtitle;


            /*
             * Change View Details URL.
             */
            heroButton.href =
                movie.url;


            /*
             * Fade content back in.
             */
            setTimeout(function () {

                heroContent.classList.remove(
                    'hero-fading'
                );

            }, 100);


        }, 400);

    }


    /*
     * Rotate every 10 seconds.
     */
    if (movies.length > 0) {

        setInterval(
            changeHero,
            5000
        );

    }

});

</script>