<div class="container-fluid mt-4">

```
<div class="modern-trending-wrapper">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="modern-trending-header">

        <div class="modern-trending-heading">

            {{-- Fire icon --}}
            <div class="modern-trending-icon">
                <i class="bi bi-fire"></i>
            </div>

            {{-- Heading text --}}
            <div class="modern-trending-heading-text">

                <h4 class="modern-trending-title">
                    Trending Torrents
                </h4>

                <div class="modern-trending-subtitle">
                    Most active torrents right now
                </div>

            </div>

        </div>

        {{-- Collapse button --}}
        <button
            class="modern-trending-toggle"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#ttTrending"
            aria-expanded="true"
            aria-controls="ttTrending"
            aria-label="Toggle trending torrents"
        >
            <i class="bi bi-chevron-down"></i>
        </button>

    </div>


    {{-- =========================================================
         BODY
    ========================================================== --}}
    <div id="ttTrending" class="collapse show">

        <div class="modern-trending-body">

            {{-- =================================================
                 MORE THAN 10 TORRENTS = HORIZONTAL SCROLL
            ================================================== --}}
            <div class="tt-row @if($trendingTorrents->count() > 10) tt-scroll @endif">

                @foreach($trendingTorrents as $index => $torrent)

                    @php

                        /*
                         * Categories that use the background image
                         * instead of the poster.
                         */
                        $cinemaCategories = [
                            1, 2, 5, 6, 9, 10, 11, 12,
                            16, 17, 18, 19, 24, 25, 31, 32,
                            54, 55, 81, 82,
                            13, 14, 20, 21
                        ];

                        /*
                         * Select image.
                         */
                        $image = in_array(
                            $torrent->category_id,
                            $cinemaCategories
                        )
                            ? $torrent->poster
                            : $torrent->poster;

                        /*
                         * Ranking.
                         */
                        $rank = $index + 1;

                        /*
                         * Special class for top 3.
                         */
                        $rankClass = match ($rank) {
                            1 => 'rank-gold',
                            2 => 'rank-silver',
                            3 => 'rank-bronze',
                            default => ''
                        };

                    @endphp


                    {{-- =================================================
                         TORRENT
                    ================================================== --}}
                    <div class="tt-col">

                        <a
                            href="{{ route('torrents.show', $torrent->id) }}"
                            class="modern-tt-link"
                            aria-label="{{ $torrent->name }}"
                        >

                            <article class="modern-tt-card">


                                {{-- =====================================
                                     POSTER
                                ====================================== --}}
                                <div class="modern-tt-image-wrap">

                                    <img
                                        src="{{ $image ?? '/images/noimage.jpg' }}"
                                        class="modern-tt-poster"
                                        loading="lazy"
                                        alt="{{ $torrent->name }}"
                                    >


                                    {{-- Poster gradient --}}
                                    <div class="modern-tt-gradient"></div>


                                    {{-- Ranking badge --}}
                                    <div class="modern-tt-rank {{ $rankClass }}">

                                        @if($rank === 1)

                                            <i class="bi bi-trophy-fill"></i>

                                        @elseif($rank === 2)

                                            <i class="bi bi-award-fill"></i>

                                        @elseif($rank === 3)

                                            <i class="bi bi-award-fill"></i>

                                        @else

                                            #

                                        @endif

                                        {{ $rank }}

                                    </div>


                                    {{-- =================================
                                         TORRENT NAME
                                         Hidden until hover
                                    ================================== --}}
                                    <div class="modern-tt-image-title">

                                        <div class="modern-tt-name">
                                            {{ $torrent->name }}
                                        </div>

                                    </div>

                                </div>


                                {{-- =====================================
                                     STATS
                                ====================================== --}}
                                <div class="modern-tt-info">

                                    <div class="modern-tt-stats">


                                        {{-- Seeders --}}
                                        <span
                                            class="modern-tt-stat modern-tt-seeders"
                                            title="Seeders"
                                        >

                                            <span class="modern-tt-stat-icon">
                                                <i class="bi bi-arrow-up"></i>
                                            </span>

                                            <span class="modern-tt-stat-number">
                                                {{ $torrent->seeders }}
                                            </span>

                                        </span>


                                        {{-- Leechers --}}
                                        <span
                                            class="modern-tt-stat modern-tt-leechers"
                                            title="Leechers"
                                        >

                                            <span class="modern-tt-stat-icon">
                                                <i class="bi bi-arrow-down"></i>
                                            </span>

                                            <span class="modern-tt-stat-number">
                                                {{ $torrent->leechers }}
                                            </span>

                                        </span>


                                    </div>

                                </div>

                            </article>

                        </a>

                    </div>

                @endforeach

            </div>

        </div>

    </div>

</div>
```

</div>

<style>

/* ================================================================
   FILEIPLAY — TRENDING TORRENTS
   ================================================================ */


/* ================================================================
   MAIN WRAPPER
   ================================================================ */

.modern-trending-wrapper {

    width: 100%;
    max-width: 1700px;

    margin-left: auto;
    margin-right: auto;

    position: relative;

    overflow: hidden;

    border: 1px solid rgba(148, 163, 184, .12);

    border-radius: 18px;

    background:
        radial-gradient(
            circle at 0% 0%,
            rgba(20, 184, 166, .08),
            transparent 30%
        ),
        radial-gradient(
            circle at 100% 100%,
            rgba(59, 130, 246, .06),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            rgba(15, 23, 42, .98),
            rgba(9, 15, 28, .98)
        );

    box-shadow:
        0 20px 50px rgba(0, 0, 0, .25),
        inset 0 1px 0 rgba(255, 255, 255, .025);

}


/* ================================================================
   TOP ACCENT
   ================================================================ */

.modern-trending-wrapper::before {

    content: "";

    position: absolute;

    top: 0;
    left: 8%;
    right: 8%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(20, 184, 166, .7),
            transparent
        );

    opacity: .8;

}


/* ================================================================
   HEADER
   ================================================================ */

.modern-trending-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 1rem;

    padding: 1rem 1.2rem;

    border-bottom:
        1px solid rgba(148, 163, 184, .09);

    background:
        linear-gradient(
            180deg,
            rgba(255, 255, 255, .025),
            transparent
        );

}


/* ================================================================
   HEADER LEFT
   ================================================================ */

.modern-trending-heading {

    display: flex;

    align-items: center;

    gap: .8rem;

    min-width: 0;

}


/* ================================================================
   FIRE ICON
   ================================================================ */

.modern-trending-icon {

    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    position: relative;

    border-radius: 12px;

    color: #f97316;

    font-size: 18px;

    background:
        linear-gradient(
            135deg,
            rgba(249, 115, 22, .16),
            rgba(239, 68, 68, .08)
        );

    border:
        1px solid rgba(249, 115, 22, .25);

    box-shadow:
        0 0 25px rgba(249, 115, 22, .08);

}


/* Fire glow */

.modern-trending-icon::after {

    content: "";

    position: absolute;

    inset: -5px;

    border-radius: 15px;

    border:
        1px solid rgba(249, 115, 22, .08);

    animation:
        trendingPulse 2.5s ease-in-out infinite;

}


@keyframes trendingPulse {

    0%,
    100% {

        opacity: .35;

        transform: scale(.98);

    }

    50% {

        opacity: 1;

        transform: scale(1.04);

    }

}


/* ================================================================
   HEADING TEXT
   ================================================================ */

.modern-trending-heading-text {

    min-width: 0;

}


.modern-trending-title {

    margin: 0;

    color: #f8fafc;

    font-size: 15px;

    font-weight: 800;

    line-height: 1.2;

    letter-spacing: -.15px;

}


.modern-trending-subtitle {

    margin-top: 4px;

    color: #64748b;

    font-size: 11px;

    font-weight: 500;

    line-height: 1.3;

}


/* ================================================================
   COLLAPSE BUTTON
   ================================================================ */

.modern-trending-toggle {

    width: 34px;
    height: 34px;

    flex: 0 0 34px;

    display: flex;

    align-items: center;
    justify-content: center;

    border:
        1px solid rgba(148, 163, 184, .12);

    border-radius: 10px;

    background:
        rgba(255, 255, 255, .025);

    color: #64748b;

    cursor: pointer;

    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease;

}


.modern-trending-toggle i {

    transition:
        transform .3s ease;

}


.modern-trending-toggle:hover {

    color: #5eead4;

    background:
        rgba(20, 184, 166, .08);

    border-color:
        rgba(20, 184, 166, .25);

}


.modern-trending-toggle[aria-expanded="false"] i {

    transform: rotate(-90deg);

}


/* ================================================================
   BODY
   ================================================================ */

.modern-trending-body {

    padding: 1rem;

}


/* ================================================================
   ROW
   ================================================================ */

.tt-row {

    display: flex;

    flex-wrap: wrap;

    gap: .6rem;

    margin: 0;

}


/* ================================================================
   10 OR FEWER TORRENTS
   ORIGINAL CARD SIZING
   ================================================================ */

.tt-row:not(.tt-scroll) > .tt-col {

    flex: 0 0 auto;

    width:
        calc(
            (100% - (9 * .6rem)) / 10
        );

    max-width:
        calc(
            (100% - (9 * .6rem)) / 10
        );

}


/* ================================================================
   MORE THAN 10 TORRENTS

   IMPORTANT:
   Keep the SAME card width as the normal layout.

   Do NOT use a fixed 112px width.

   The cards keep their normal size and the row
   becomes horizontally scrollable.
   ================================================================ */

.tt-row.tt-scroll {

    flex-wrap: nowrap;

    width: 100%;

    overflow-x: auto;

    overflow-y: hidden;

    padding-bottom: 9px;

    scroll-snap-type: x proximity;

    -webkit-overflow-scrolling: touch;

    scrollbar-width: thin;

    scrollbar-color:
        rgba(20, 184, 166, .45)
        transparent;

}


/* Keep original desktop card width */

.tt-row.tt-scroll > .tt-col {

    flex: 0 0 calc(
        (100% - (9 * .6rem)) / 10
    );

    width: calc(
        (100% - (9 * .6rem)) / 10
    );

    max-width: calc(
        (100% - (9 * .6rem)) / 10
    );

    scroll-snap-align: start;

}


/* ================================================================
   CARD
   ================================================================ */

.modern-tt-card {

    display: flex;

    flex-direction: column;

    width: 100%;

    height: 100%;

    overflow: hidden;

    border:
        1px solid rgba(148, 163, 184, .11);

    border-radius: 10px;

    background:
        rgba(15, 23, 42, .9);

    box-shadow:
        0 4px 12px rgba(0, 0, 0, .16);

    transition:
        transform .28s cubic-bezier(.2, .8, .2, 1),
        border-color .28s ease,
        box-shadow .28s ease;

}


.modern-tt-card:hover {

    transform:
        translateY(-5px);

    border-color:
        rgba(20, 184, 166, .38);

    box-shadow:
        0 16px 32px rgba(0, 0, 0, .38),
        0 0 0 1px rgba(20, 184, 166, .05);

}


/* ================================================================
   LINK
   ================================================================ */

.modern-tt-link {

    display: block;

    width: 100%;
    height: 100%;

    color: inherit;

    text-decoration: none;

}


/* ================================================================
   POSTER
   ================================================================ */

.modern-tt-image-wrap {

    position: relative;

    width: 100%;

    aspect-ratio: 2 / 3;

    overflow: hidden;

    background:
        #020617;

}


.modern-tt-poster {

    display: block;

    width: 100%;

    height: 100%;

    object-fit: cover;

    object-position: center top;

    transition:
        transform .5s cubic-bezier(.2, .8, .2, 1),
        filter .35s ease;

}


/* Poster zoom */

.modern-tt-card:hover .modern-tt-poster {

    transform:
        scale(1.07);

    filter:
        brightness(.72);

}


/* ================================================================
   POSTER GRADIENT
   ================================================================ */

.modern-tt-gradient {

    position: absolute;

    inset: 0;

    pointer-events: none;

    background:
        linear-gradient(
            180deg,
            rgba(0, 0, 0, .38) 0%,
            transparent 28%,
            transparent 55%,
            rgba(0, 0, 0, .88) 100%
        );

}


/* ================================================================
   RANK
   ================================================================ */

.modern-tt-rank {

    position: absolute;

    top: 7px;
    right: 7px;

    min-width: 27px;

    height: 22px;

    padding:
        0 6px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 3px;

    border-radius: 7px;

    background:
        rgba(2, 6, 23, .82);

    border:
        1px solid rgba(255, 255, 255, .09);

    backdrop-filter:
        blur(8px);

    -webkit-backdrop-filter:
        blur(8px);

    color: #e2e8f0;

    font-size: 9px;

    font-weight: 800;

    line-height: 1;

    box-shadow:
        0 4px 10px rgba(0, 0, 0, .25);

}


.modern-tt-rank i {

    font-size: 9px;

}


/* Gold */

.modern-tt-rank.rank-gold {

    color: #fbbf24;

    border-color:
        rgba(251, 191, 36, .28);

    background:
        rgba(120, 70, 0, .72);

}


/* Silver */

.modern-tt-rank.rank-silver {

    color: #e2e8f0;

}


/* Bronze */

.modern-tt-rank.rank-bronze {

    color: #fb923c;

}


/* ================================================================
   TORRENT NAME
   Hidden by default
   ================================================================ */

.modern-tt-image-title {

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    padding:
        2.5rem .65rem .7rem;

    opacity: 0;

    transform:
        translateY(8px);

    pointer-events: none;

    background:
        linear-gradient(
            180deg,
            transparent 0%,
            rgba(0, 0, 0, .9) 100%
        );

    transition:
        opacity .25s ease,
        transform .25s ease;

}


/* Show title on hover */

.modern-tt-card:hover .modern-tt-image-title {

    opacity: 1;

    transform:
        translateY(0);

}


/* Title text */

.modern-tt-name {

    color: #ffffff;

    font-size: 10px;

    font-weight: 700;

    line-height: 1.3;

    text-align: left;

    overflow: hidden;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    text-overflow: ellipsis;

    text-shadow:
        0 2px 8px rgba(0, 0, 0, .95);

}


/* ================================================================
   INFO BAR
   ================================================================ */

.modern-tt-info {

    padding:
        .48rem .55rem;

    background:
        linear-gradient(
            180deg,
            rgba(15, 23, 42, .98),
            rgba(9, 15, 28, .98)
        );

    border-top:
        1px solid rgba(148, 163, 184, .07);

}


/* ================================================================
   STATS
   ================================================================ */

.modern-tt-stats {

    display: flex;

    align-items: center;

    gap: .55rem;

    min-width: 0;

}


.modern-tt-stat {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    font-size: 9px;

    font-weight: 800;

    line-height: 1;

}


/* ================================================================
   STAT ICON
   ================================================================ */

.modern-tt-stat-icon {

    width: 17px;
    height: 17px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 5px;

    font-size: 8px;

}


/* ================================================================
   SEEDERS
   ================================================================ */

.modern-tt-seeders {

    color: #4ade80;

}


.modern-tt-seeders .modern-tt-stat-icon {

    background:
        rgba(74, 222, 128, .1);

}


/* ================================================================
   LEECHERS
   ================================================================ */

.modern-tt-leechers {

    color: #fb7185;

}


.modern-tt-leechers .modern-tt-stat-icon {

    background:
        rgba(251, 113, 133, .1);

}


/* ================================================================
   SCROLLBAR
   ================================================================ */

.tt-row.tt-scroll::-webkit-scrollbar {

    height: 7px;

}


.tt-row.tt-scroll::-webkit-scrollbar-track {

    background:
        rgba(255, 255, 255, .025);

    border-radius:
        999px;

}


.tt-row.tt-scroll::-webkit-scrollbar-thumb {

    background:
        linear-gradient(
            90deg,
            rgba(20, 184, 166, .25),
            rgba(20, 184, 166, .55)
        );

    border-radius:
        999px;

}


.tt-row.tt-scroll::-webkit-scrollbar-thumb:hover {

    background:
        rgba(20, 184, 166, .75);

}


/* ================================================================
   TABLET / SMALL DESKTOP
   Keep original responsive sizing
   ================================================================ */

@media (max-width: 1300px) {

    .tt-row:not(.tt-scroll) > .tt-col {

        width:
            calc(
                (100% - (7 * .6rem)) / 8
            );

        max-width:
            calc(
                (100% - (7 * .6rem)) / 8
            );

    }


    .tt-row.tt-scroll > .tt-col {

        flex-basis:
            calc(
                (100% - (7 * .6rem)) / 8
            );

        width:
            calc(
                (100% - (7 * .6rem)) / 8
            );

        max-width:
            calc(
                (100% - (7 * .6rem)) / 8
            );

    }

}


@media (max-width: 1100px) {

    .tt-row:not(.tt-scroll) > .tt-col {

        width:
            calc(
                (100% - (5 * .6rem)) / 6
            );

        max-width:
            calc(
                (100% - (5 * .6rem)) / 6
            );

    }


    .tt-row.tt-scroll > .tt-col {

        flex-basis:
            calc(
                (100% - (5 * .6rem)) / 6
            );

        width:
            calc(
                (100% - (5 * .6rem)) / 6
            );

        max-width:
            calc(
                (100% - (5 * .6rem)) / 6
            );

    }

}


@media (max-width: 900px) {

    .tt-row:not(.tt-scroll) > .tt-col {

        width:
            calc(
                (100% - (4 * .6rem)) / 5
            );

        max-width:
            calc(
                (100% - (4 * .6rem)) / 5
            );

    }


    .tt-row.tt-scroll > .tt-col {

        flex-basis:
            calc(
                (100% - (4 * .6rem)) / 5
            );

        width:
            calc(
                (100% - (4 * .6rem)) / 5
            );

        max-width:
            calc(
                (100% - (4 * .6rem)) / 5
            );

    }

}


/* ================================================================
   MOBILE
   ================================================================ */

@media (max-width: 768px) {

    .modern-trending-wrapper {

        border-radius:
            14px;

    }


    .modern-trending-header {

        padding:
            .8rem .85rem;

    }


    .modern-trending-body {

        padding:
            .7rem;

    }


    .modern-trending-icon {

        width: 36px;
        height: 36px;

        flex-basis: 36px;

        border-radius: 10px;

        font-size: 15px;

    }


    .modern-trending-title {

        font-size: 14px;

    }


    .modern-trending-subtitle {

        font-size: 10px;

    }


    /*
     * On mobile always use horizontal scrolling.
     * This prevents tiny posters.
     */

    .tt-row {

        flex-wrap: nowrap;

        overflow-x: auto;

        overflow-y: hidden;

        gap: .55rem;

        padding-bottom: 7px;

        scroll-snap-type: x proximity;

        -webkit-overflow-scrolling: touch;

    }


    .tt-row > .tt-col,
    .tt-row.tt-scroll > .tt-col {

        flex: 0 0 105px;

        width: 105px;

        max-width: 105px;

        scroll-snap-align: start;

    }


    .modern-tt-card:hover {

        transform:
            translateY(-3px);

    }


    .modern-tt-name {

        font-size: 9px;

    }


    .modern-tt-stat {

        font-size: 8px;

    }

}


/* ================================================================
   SMALL MOBILE
   ================================================================ */

@media (max-width: 420px) {

    .tt-row > .tt-col,
    .tt-row.tt-scroll > .tt-col {

        flex-basis: 96px;

        width: 96px;

        max-width: 96px;

    }


    .modern-trending-header {

        padding:
            .7rem;

    }


    .modern-trending-body {

        padding:
            .55rem;

    }

}


/* ================================================================
   ACCESSIBILITY
   ================================================================ */

@media (prefers-reduced-motion: reduce) {

    .modern-trending-icon::after {

        animation:
            none;

    }


    .modern-tt-card,
    .modern-tt-poster,
    .modern-tt-image-title {

        transition:
            none;

    }

}

</style>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const key = "trendingAccordionState";

    const collapse =
        document.getElementById("ttTrending");

    const toggle =
        document.querySelector(
            '[data-bs-target="#ttTrending"]'
        );


    if (!collapse) {

        return;

    }


    /* =========================================================
       RESTORE SAVED COLLAPSE STATE
    ========================================================== */

    if (
        localStorage.getItem(key) === "closed"
    ) {

        collapse.classList.remove("show");

        if (toggle) {

            toggle.setAttribute(
                "aria-expanded",
                "false"
            );

        }

    }


    /* =========================================================
       OPEN
    ========================================================== */

    collapse.addEventListener(
        "shown.bs.collapse",
        function () {

            localStorage.setItem(
                key,
                "open"
            );

            if (toggle) {

                toggle.setAttribute(
                    "aria-expanded",
                    "true"
                );

            }

        }
    );


    /* =========================================================
       CLOSED
    ========================================================== */

    collapse.addEventListener(
        "hidden.bs.collapse",
        function () {

            localStorage.setItem(
                key,
                "closed"
            );

            if (toggle) {

                toggle.setAttribute(
                    "aria-expanded",
                    "false"
                );

            }

        }
    );

});

</script>
