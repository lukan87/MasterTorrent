<div class="container-fluid mt-4 tt-wrapper">

    <div class="modern-trending-wrapper">

        {{-- HEADER --}}
        <div class="modern-trending-header">

            <div class="d-flex align-items-center gap-3">

                <div class="modern-trending-icon">

                    <i class="bi bi-fire"></i>

                </div>

                <div>

                    <h4 class="modern-trending-title">

                        Trending Torrents

                    </h4>

                    <div class="modern-trending-subtitle">

                        Most active torrents right now

                    </div>

                </div>

            </div>

            <button class="modern-trending-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#ttTrending"
                    aria-expanded="true">

                <i class="bi bi-chevron-down"></i>

            </button>

        </div>

        {{-- BODY --}}
        <div id="ttTrending"
             class="collapse show">

            <div class="modern-trending-body">

                <div class="row g-3 tt-row @if($trendingTorrents->count() > 6) tt-scroll @endif">

                    @foreach($trendingTorrents as $index => $torrent)

                        @php

                            $cinemaCategories = [
                                1,2,5,6,9,10,11,12,16,17,18,19,24,25,31,32,54,55,81,82,
                                13,14,20,21
                            ];

                            $image = in_array($torrent->category_id, $cinemaCategories)
                                ? $torrent->background
                                : $torrent->poster;

                        @endphp

                        <div class="col-xl-2 col-lg-3 col-md-4 col-6">

                            <a href="{{ route('torrents.show', $torrent->id) }}"
                               class="text-decoration-none">

                                <div class="modern-tt-card">

                                    {{-- Poster with info overlay --}}
                                    <div class="modern-tt-image-wrap">

                                        <img src="{{ $image ?? '/images/noimage.jpg' }}"
                                             class="modern-tt-poster"
                                             loading="lazy"
                                             alt="{{ $torrent->name }}">

                                        {{-- Rank --}}
                                        <div class="modern-tt-rank">

                                            #{{ $index + 1 }}

                                        </div>

                                        {{-- Info overlay --}}
                                        <div class="modern-tt-overlay">

                                            <div class="modern-tt-title">{{ $torrent->name }}</div>

                                            <div class="modern-tt-stats">

                                                <span class="modern-tt-seeders">

                                                    <i class="bi bi-arrow-up-circle-fill"></i> {{ $torrent->seeders }}

                                                </span>

                                                <span class="modern-tt-leechers">

                                                    <i class="bi bi-arrow-down-circle-fill"></i> {{ $torrent->leechers }}

                                                </span>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </a>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>

    </div>

</div>

<style>
/* =========================================================
   FILEIPLAY TRENDING TORRENTS
   Matches the News / Poll visual language
   Maximum font size: 14px
========================================================= */

.modern-trending-wrapper {
    overflow: hidden;
    border: 1px solid var(--ui-border);
    border-radius: 1rem;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    box-shadow: 0 10px 30px rgba(0, 0, 0, .22);
}

/* Header */
.modern-trending-header {
    padding: 1rem 1.15rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--ui-border);
}

.modern-trending-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    border-radius: .7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(20, 184, 166, .10);
    border: 1px solid rgba(20, 184, 166, .28);
    color: var(--ui-accent);
    font-size: 14px;
}

.modern-trending-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
}

.modern-trending-subtitle {
    margin-top: 2px;
    color: #94a3b8;
    font-size: 12px;
    line-height: 1.4;
}

.modern-trending-toggle {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid var(--ui-border);
    border-radius: .6rem;
    background: rgba(255, 255, 255, .035);
    color: #94a3b8;
    transition: .2s ease;
}

.modern-trending-toggle:hover,
.modern-trending-toggle:focus {
    background: rgba(20, 184, 166, .10);
    border-color: var(--ui-accent);
    color: var(--ui-accent);
}

/* Body */
.modern-trending-body {
    padding: 1rem;
}

/* Torrent card */
.modern-tt-card {
    position: relative;
    overflow: hidden;
    aspect-ratio: 2 / 3;
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    background: rgba(15, 23, 42, .55);
    transition:
        transform .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}

.modern-tt-card:hover {
    transform: translateY(-3px);
    border-color: rgba(20, 184, 166, .35);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
}

/* Poster */
.modern-tt-image-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
    background: #0f172a;
}

.modern-tt-poster {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    transition: transform .25s ease;
}

.modern-tt-card:hover .modern-tt-poster {
    transform: scale(1.04);
}

/* Rank */
.modern-tt-rank {
    position: absolute;
    top: 9px;
    left: 9px;
    padding: 3px 7px;
    border: 1px solid var(--ui-border);
    border-radius: .5rem;
    background: rgba(15, 23, 42, .90);
    color: var(--ui-accent);
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
}

/* Info overlay */
.modern-tt-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, .82),
        rgba(0, 0, 0, .18) 55%,
        rgba(0, 0, 0, .15) 72%,
        transparent
    );
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    padding: .55rem .6rem;
    pointer-events: none;
}

.modern-tt-title {
    color: #fff;
    font-size: .8rem;
    font-weight: 700;
    line-height: 1.2;
    text-shadow: 0 1px 2px rgba(0, 0, 0, .6);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.modern-tt-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 4px;
    font-size: 11.5px;
    font-weight: 600;
}

.modern-tt-seeders {
    color: #4ade80;
}

.modern-tt-leechers {
    color: #f87171;
}

/* Desktop: horizontal scroll when more than 6 items */
.tt-scroll {
    flex-wrap: nowrap;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(20, 184, 166, .25) transparent;
    margin-left: 0;
    margin-right: 0;
    padding-bottom: 8px;
}
.tt-scroll::-webkit-scrollbar {
    height: 6px;
}
.tt-scroll::-webkit-scrollbar-thumb {
    background: rgba(20, 184, 166, .22);
    border-radius: 10px;
}
.tt-scroll > [class*="col-"] {
    flex: 0 0 calc(100% / 6);
    width: calc(100% / 6);
    max-width: calc(100% / 6);
    scroll-snap-align: start;
}

/* Mobile */
@media (max-width: 768px) {
    .modern-trending-header {
        padding: .85rem;
    }

    .modern-trending-body {
        padding: .75rem;
    }

    .modern-trending-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .modern-tt-title {
        font-size: .78rem;
    }

    .modern-tt-stats {
        font-size: 11px;
    }

    /* Horizontal scroll row (mirrors the Random Online panels) */
    .tt-row {
        flex-wrap: nowrap;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(20, 184, 166, .25) transparent;
        margin-left: 0;
        margin-right: 0;
        padding-bottom: 6px;
    }

    .tt-row::-webkit-scrollbar {
        height: 5px;
    }

    .tt-row::-webkit-scrollbar-thumb {
        background: rgba(20, 184, 166, .22);
        border-radius: 10px;
    }

    .tt-row > [class*="col-"] {
        flex: 0 0 130px;
        width: 130px;
        max-width: 130px;
        scroll-snap-align: start;
    }
}
</style>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const key = "trendingAccordionState";

    const collapse = document.getElementById("ttTrending");

    if(localStorage.getItem(key) === "closed"){

        collapse.classList.remove("show");

    }

    collapse.addEventListener("shown.bs.collapse", function(){

        localStorage.setItem(key,"open");

    });

    collapse.addEventListener("hidden.bs.collapse", function(){

        localStorage.setItem(key,"closed");

    });

});

</script>