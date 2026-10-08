@extends('layouts.app')

@push('scripts')
    @vite('resources/js/library-browser.js')
@endpush

@section('content')

<div class="lib-page container-fluid py-3 px-lg-4 px-3">

    {{-- ============================================================
         HERO
         ============================================================ --}}
    @if($featured->count())

        @php
            /*
             * Pick a completely random starting series.
             */
            $hero = $featured->random();

            /*
             * Remove the starting series from the rotation list.
             * This prevents the same series appearing immediately
             * after the initial hero.
             */
            $heroMovies = $featured
                ->reject(fn ($item) => (int) $item->tmdbid === (int) $hero->tmdbid)
                ->values();
        @endphp

        <div
            class="lib-hero lib-hero-series mb-4"
            id="seriesHero"
            style="--hero-bg: url('https://image.tmdb.org/t/p/w1280/{{ $hero->backdrop }}');"
        >

            {{-- Full cinematic background --}}
            <div class="lib-hero-bg"></div>

            {{-- Cinematic overlay --}}
            <div class="lib-hero-overlay"></div>

            {{-- Hero content --}}
            <div class="lib-hero-content">

                {{-- Series poster --}}
                <div class="lib-hero-poster-wrap">

                    <img
    id="heroPoster"
    class="lib-hero-poster"
    src="{{ !empty($hero->poster_path)
        ? 'https://image.tmdb.org/t/p/w500' . $hero->poster_path
        : (!empty($hero->poster)
            ? 'https://image.tmdb.org/t/p/w500' . $hero->poster
            : asset('images/no-poster.png')) }}"
    alt="{{ $hero->title }}"
>

                </div>


                {{-- Series information --}}
                <div class="lib-hero-info">

                    <span class="lib-hero-badge lib-hero-badge-series">

                        <i class="bi bi-tv me-1"></i>

                        SERIES LIBRARY

                    </span>


                    <h1
                        class="lib-hero-title"
                        id="heroTitle"
                    >
                        {{ $hero->title }}
                    </h1>


                    <p
                        class="lib-hero-sub"
                        id="heroSub"
                    >

                        <i class="bi bi-star-fill text-warning"></i>

                        {{ number_format($hero->rating ?? 0, 1) }}

                        @if($hero->year)

                            <span class="hero-separator">&bull;</span>

                            {{ $hero->year }}

                        @endif

                        <span class="hero-separator">&bull;</span>

                        {{ $series->total() }} titles available

                    </p>


                    <a
                        href="{{ route('library.series.show', [
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
             HERO SERIES DATA
             ======================================================== --}}
        <script>

            window.libraryHeroSeries = [

                @foreach($heroMovies as $item)

                    {
                        title: @json($item->title),

                        tmdbid: @json($item->tmdbid),

                        slug: @json($item->slug),

                        url: @json(route('library.series.show', [
                            'tmdbid' => $item->tmdbid,
                            'slug' => $item->slug
                        ])),

                        backdrop: @json($item->backdrop),

                        poster: @json($item->poster_path ?? $item->poster ?? null),

                        rating: @json($item->rating ?? 0),

                        year: @json($item->year)
                    },

                @endforeach

            ];

        </script>

    @endif


    <div data-library-browser data-browse-url="{{ route('library.series.index') }}">
        @include('library.series.results')
    </div>

</div>

@endsection


<style>
@include('library.partials.search-styles')

/* ══════════════════════════════════════════════════════════════
   SERIES LIBRARY — PREMIUM DARK THEME
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

    background: var(--theme-surface-alt, #03080e);

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    box-shadow:
        0 25px 60px var(--theme-shadow, rgba(0, 0, 0, .5)),
        0 0 0 1px var(--theme-shadow, rgba(255, 255, 255, .04));

}


/* Full background */

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


/* Cinematic slow zoom */

.lib-hero:hover .lib-hero-bg {

    transform: scale(1.055);

}


/* ══════════════════════════════════════════════════════════════
   OVERLAY
   ══════════════════════════════════════════════════════════════ */

.lib-hero-overlay {

    position: absolute;

    inset: 0;

    background:

        linear-gradient(
            90deg,
            rgba(3,8,14,.98) 0%,
            rgba(3,8,14,.90) 20%,
            rgba(3,8,14,.65) 42%,
            rgba(3,8,14,.30) 68%,
            rgba(3,8,14,.40) 100%
        ),

        linear-gradient(
            0deg,
            rgba(3,8,14,.95) 0%,
            rgba(3,8,14,.35) 50%,
            rgba(3,8,14,.08) 100%
        );

}


/* Accent border */

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

    background: #070c15;

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
        0 2px 15px var(--theme-shadow, rgba(0, 0, 0, .8));

}


.lib-hero-badge {

    display: inline-flex;

    align-items: center;

    padding: .3rem .75rem;

    border-radius: 999px;

    font-size: var(--site-font-small, 13px);

    font-weight: 800;

    letter-spacing: 1.8px;

    color: var(--theme-on-action, #061311);

    background: var(--theme-teal-action, var(--ui-accent));

    box-shadow:
        0 6px 18px var(--theme-shadow, rgba(0, 0, 0, .35));

}


/* Series badge */

.lib-hero-badge-series {

    background: var(--theme-teal-action, var(--ui-accent));

}


/* Title */

.lib-hero-title {

    color: var(--theme-text, #f8fafc);

    font-size: 2.6rem;

    font-weight: 800;

    line-height: 1.08;

    margin-top: 1rem;

    margin-bottom: .5rem;

    text-shadow:
        0 3px 20px var(--theme-shadow, rgba(0, 0, 0, .9));

}


/* Metadata */

.lib-hero-sub {

    color: var(--theme-text, #dbe5f0);

    font-size: var(--site-font-body, 13px);

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

    font-size: var(--site-font-body, 13px);

    font-weight: 700;

    color: var(--theme-on-action, #061311);

    background: var(--theme-teal-action, var(--ui-accent));

    border: 0;

    text-decoration: none;

    box-shadow:
        0 10px 25px var(--theme-shadow, rgba(0, 0, 0, .35));

    transition:
        transform .18s ease,
        filter .18s ease,
        box-shadow .18s ease;

}


.lib-btn-hero:hover {

    filter: brightness(1.08);

    color: var(--theme-text, #061311);

    transform: translateY(-2px);

    box-shadow:
        0 14px 30px var(--theme-shadow, rgba(0, 0, 0, .45));

}


/* ══════════════════════════════════════════════════════════════
   HEADER
   ══════════════════════════════════════════════════════════════ */

.lib-page-title {

    color: var(--theme-text, #f1f5f9);

    font-size: 1.5rem;

    font-weight: 800;

}


.lib-page-title i {

    color: var(--ui-accent);

}


.lib-page-sub {

    color: var(--ui-text-muted);

    font-size: var(--site-font-body, 13px);

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
        0 0 0 2px var(--theme-shadow, rgba(99,210,198,.1));

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

    color: var(--theme-text, #e2e8f0);

    font-size: var(--site-font-body, 13px);

    min-width: 180px;

}


.lib-search-input::placeholder {

    color: var(--theme-muted, #64748b);

}


.lib-search-clear {

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-small, 13px);

    padding: 4px 6px;

    transition: color .15s;

}


.lib-search-clear:hover {

    color: var(--theme-text, #f1f5f9);

}


.lib-search-btn {

    display: inline-flex;

    align-items: center;

    gap: .3rem;

    padding: .4rem .75rem;

    border: 0;

    border-radius: .45rem;

    font-weight: 700;

    font-size: var(--site-font-body, 13px);

    color: var(--theme-on-action, #061311);

    background: var(--theme-teal-action, var(--ui-accent));

    cursor: pointer;

    white-space: nowrap;

    transition: filter .15s;

}


.lib-search-btn:hover {

    filter: brightness(1.08);

}


.lib-search-result-text {

    color: var(--ui-text-muted);

    font-size: var(--site-font-body, 13px);

}


.lib-search-result-text strong {

    color: var(--theme-text, #f1f5f9);

}


/* ══════════════════════════════════════════════════════════════
   SERIES CARDS
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
        0 20px 40px var(--theme-shadow, rgba(0,0,0,.55)),
        0 0 20px var(--theme-shadow, rgba(99,210,198,.06));

    border-color:
        var(--theme-teal-border, rgba(99,210,198,.25));

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

    font-size: var(--site-font-small, 13px);

    font-weight: 800;

    line-height: 1;

    box-shadow:
        0 4px 12px var(--theme-shadow, rgba(0,0,0,.35));

    pointer-events: none;

}


.lib-badge-rating {

    top: 8px;

    left: 8px;

    color: var(--theme-on-action, #061311);

    background: var(--theme-teal-action, var(--ui-accent));

}


.lib-badge-rating i {

    color: var(--theme-text, #061311);

    font-size: 9px;

}


.lib-badge-year {

    top: 8px;

    right: 8px;

    color: var(--theme-text, #f8fafc);

    background:
        var(--theme-surface-alt, rgba(3,8,14,.72));

    backdrop-filter: blur(4px);

}


.lib-badge-seeders {

    bottom: 8px;

    left: 8px;

    color: var(--theme-green-text, #22c55e);

    background:
        var(--theme-surface-alt, rgba(3,8,14,.82));

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
            rgba(3,8,14,.92) 0%,
            rgba(3,8,14,.45) 50%,
            transparent 100%
        );

    opacity: 0;

    transition:
        opacity .25s ease;

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

    font-size: var(--site-font-small, 13px);

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

    color: var(--theme-text, #e2e8f0);

    font-size: var(--site-font-body, 13px);

    font-weight: 700;

    margin: 0;

    line-height: 1.25;

    transition:
        color .18s;

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

    font-size: var(--site-font-small, 13px);

    color: var(--ui-text-muted);

}


.lib-card-info i {

    color: var(--theme-green-text, #22c55e);

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

    color: var(--theme-text, #f1f5f9);

    font-size: var(--site-font-body, 13px);

    font-weight: 700;

}


.lib-empty p {

    margin: 0;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-body, 13px);

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

    color: var(--theme-text, #cbd5e1);

    background: var(--ui-surface);

    border: 1px solid var(--ui-border);

    border-radius: .5rem;

    font-size: var(--site-font-body, 13px);

    text-align: center;

    transition: all .18s;

}


.lib-page .pagination .page-link:hover {

    color: var(--ui-accent);

    background:
        var(--theme-teal-soft, rgba(99,210,198,.07));

    border-color:
        var(--theme-teal-border, rgba(99,210,198,.3));

}


.lib-page .pagination .page-item.active .page-link {

    color: var(--theme-on-action, #061311);

    background: var(--theme-teal-action, var(--ui-accent));

    border-color: var(--ui-accent);

}


.lib-page .pagination .page-item.disabled .page-link {

    color: var(--theme-muted, #475569);

    background:
        var(--theme-surface, rgba(10,15,27,.55));

    border-color:
        var(--theme-border, rgba(148,163,184,.1));

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
   TABLET
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
   MOBILE
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

        font-size: var(--site-font-small, 13px);

        letter-spacing: 1.2px;

        padding: .25rem .55rem;

    }


    .lib-hero-sub {

        font-size: var(--site-font-small, 13px);

        margin-bottom: .8rem;

    }


    .lib-btn-hero {

        padding: .5rem .85rem;

        font-size: var(--site-font-small, 13px);

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

        font-size: var(--site-font-small, 13px);

    }

}

</style>


{{-- ================================================================
     SERIES HERO ROTATION
     ================================================================ --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const series = window.libraryHeroSeries || [];


    /*
     * Nothing to rotate.
     */
    if (series.length === 0) {

        return;

    }


    const hero =
        document.getElementById('seriesHero');


    const heroBg =
        hero
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
     * Make sure all hero elements exist.
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
     * This completely randomises the rotation order.
     */
    for (
        let i = series.length - 1;
        i > 0;
        i--
    ) {

        const j =
            Math.floor(
                Math.random() * (i + 1)
            );


        [
            series[i],
            series[j]
        ] = [
            series[j],
            series[i]
        ];

    }


    /*
     * Start at the first item in the shuffled list.
     */
    let currentIndex = 0;


    /*
     * Change hero series.
     */
    function changeHero() {

        /*
         * Fade information out.
         */
        heroContent.classList.add(
            'hero-fading'
        );


        setTimeout(function () {

            /*
             * Move to next random series.
             */
            currentIndex++;


            /*
             * End of current shuffled list.
             */
            if (
                currentIndex >= series.length
            ) {

                /*
                 * Shuffle again before
                 * beginning another cycle.
                 */
                for (
                    let i = series.length - 1;
                    i > 0;
                    i--
                ) {

                    const j =
                        Math.floor(
                            Math.random() * (i + 1)
                        );


                    [
                        series[i],
                        series[j]
                    ] = [
                        series[j],
                        series[i]
                    ];

                }


                currentIndex = 0;

            }


            const item =
                series[currentIndex];


            /*
             * Change full background.
             */
            hero.style.setProperty(
                '--hero-bg',
                `url('https://image.tmdb.org/t/p/w1280/${item.backdrop}')`
            );


            /*
             * Change poster.
             */
            heroPoster.style.opacity = '0';


            setTimeout(function () {

                heroPoster.src = item.poster
    ? `https://image.tmdb.org/t/p/w500${item.poster}`
    : '{{ asset('images/no-poster.png') }}';

    


                heroPoster.alt =
                    item.title;


                heroPoster.style.opacity =
                    '1';

            }, 150);


            /*
             * Change title.
             */
            heroTitle.textContent =
                item.title;


            /*
             * Change rating.
             */
            let subtitle =
                '<i class="bi bi-star-fill text-warning"></i> ' +
                Number(
                    item.rating || 0
                ).toFixed(1);


            /*
             * Change year.
             */
            if (item.year) {

                subtitle +=
                    ' <span class="hero-separator">&bull;</span> ' +
                    item.year;

            }


            /*
             * Number of series in library.
             */
            subtitle +=
                ' <span class="hero-separator">&bull;</span> ' +
                '{{ $series->total() }} titles available';


            heroSub.innerHTML =
                subtitle;


            /*
             * Change View Details URL.
             */
            heroButton.href =
                item.url;


            /*
             * Fade information back in.
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
    if (series.length > 0) {

        setInterval(
            changeHero,
            4000
        );

    }

});

</script>