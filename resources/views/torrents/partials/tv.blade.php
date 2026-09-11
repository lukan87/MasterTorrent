@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])

{{-- =========================
     CAST SECTION
========================= --}}

@if(!empty($display['cast']))

<div class="cast-section mt-5">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h3 class="cast-title mb-1">
                Featured Cast
            </h3>

            <p class="cast-subtitle mb-0">
                Meet the actors behind the story
            </p>
        </div>

        <div class="cast-count-badge">
            <i class="bi bi-people-fill"></i>
            {{ count($display['cast']) }} Featured Actors
        </div>

    </div>

    {{-- CAST ROW --}}
    <div class="cast-row">

        @foreach($display['cast'] as $actor)

            <div class="cast-card text-center">

                {{-- IMAGE --}}
                <div class="actor-image-wrapper">

                    <img
                        src="{{ $actor['photo'] ?? '/images/not-found.jpg' }}"
                        loading="lazy"
                        class="actor-image"
                        alt="{{ $actor['name'] }}"
                    >

                </div>

                {{-- INFO --}}
                <div class="mt-3">

                    <h6 class="actor-name">

                        @if($actor['id'])

                            <a href="https://www.themoviedb.org/person/{{ $actor['id'] }}"
                               target="_blank"
                               rel="noreferrer">

                                {{ $actor['name'] }}

                            </a>

                        @else

                            {{ $actor['name'] }}

                        @endif

                    </h6>

                    <div class="actor-character">
                        {{ $actor['character'] }}
                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endif

{{-- =========================
     NEXT EPISODE TO AIR
========================= --}}
@if(!empty($display['next_episode_to_air']))

    @php
        $nextEp = $display['next_episode_to_air'];
    @endphp

    <div class="tv-episode-card next-episode mt-5">

        <div class="episode-card-header">
            <div class="episode-icon-box next-icon">
                <i class="bi bi-calendar2-event"></i>
            </div>
            <div>
                <h3 class="episode-card-title mb-0">Next Episode</h3>
                <div class="episode-card-subtitle">Airing soon</div>
            </div>
        </div>

        <div class="episode-card-body">

            @if($nextEp['still_path'])
                <img src="{{ $nextEp['still_path'] }}"
                     alt="{{ $nextEp['name'] }}"
                     class="episode-still"
                     loading="lazy">
            @endif

            <div class="episode-info">

                <div class="episode-title">
                    {{ $nextEp['name'] ?? 'Episode ' . $nextEp['episode_number'] }}
                </div>

                <div class="episode-meta">

                    @if($nextEp['season_number'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-collection-play"></i>
                            S{{ $nextEp['season_number'] }}E{{ $nextEp['episode_number'] }}
                        </span>
                    @endif

                    @if($nextEp['air_date'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-calendar"></i>
                            {{ \Carbon\Carbon::parse($nextEp['air_date'])->format('M d, Y') }}
                        </span>
                    @endif

                    @if($nextEp['vote_average'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-star-fill"></i>
                            {{ number_format($nextEp['vote_average'], 1) }}
                        </span>
                    @endif

                </div>

                @if($nextEp['overview'])
                    <div class="episode-overview">
                        {{ $nextEp['overview'] }}
                    </div>
                @endif

            </div>

        </div>

    </div>

@endif

{{-- =========================
     LAST EPISODE TO AIR
========================= --}}
@if(!empty($display['last_episode_to_air']))

    @php
        $lastEp = $display['last_episode_to_air'];
    @endphp

    <div class="tv-episode-card last-episode mt-4">

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

            @if($lastEp['still_path'])
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

                    @if($lastEp['season_number'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-collection-play"></i>
                            S{{ $lastEp['season_number'] }}E{{ $lastEp['episode_number'] }}
                        </span>
                    @endif

                    @if($lastEp['air_date'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-calendar"></i>
                            {{ \Carbon\Carbon::parse($lastEp['air_date'])->format('M d, Y') }}
                        </span>
                    @endif

                    @if($lastEp['vote_average'])
                        <span class="episode-meta-pill">
                            <i class="bi bi-star-fill"></i>
                            {{ number_format($lastEp['vote_average'], 1) }}
                        </span>
                    @endif

                </div>

                @if($lastEp['overview'])
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
========================= --}}
@if(!empty($display['season_details']))

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
        {{ count($display['season_details']) }} Seasons
    </div>

</div>

        <div class="tv-seasons-row {{ count($display['season_details']) > 10 ? 'seasons-scrollable' : '' }}">

            @foreach($display['season_details'] as $season)

                <div class="season-card">

                    <div class="season-poster-wrap">
                        <img
                            src="{{ $season['poster'] ?? '/images/noposter.jpg' }}"
                            loading="lazy"
                            class="season-poster"
                            alt="{{ $season['name'] }}"
                        >
                        @if($season['vote_average'])
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

                            @if($season['episode_count'])
                                <span>
                                    <i class="bi bi-tv"></i>
                                    {{ $season['episode_count'] }} Episodes
                                </span>
                            @endif

                            @if($season['air_date'])
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    {{ \Carbon\Carbon::parse($season['air_date'])->format('Y') }}
                                </span>
                            @endif

                        </div>

                        @if($season['overview'])
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

@includeWhen(
    $display['type'] === 'movie',
    'torrents.partials.collection'
)

@php
function getTVRatingBadge($rating) {

    $ratings = [

        'TV-Y' => [
            'class' => 'tv-y-rating',
            'icon' => 'bi-balloon-heart',
            'title' => 'TV-Y — All Children.'
        ],

        'TV-Y7' => [
            'class' => 'tv-y7-rating',
            'icon' => 'bi-emoji-sunglasses',
            'title' => 'TV-Y7 — Older Children.'
        ],

        'TV-G' => [
            'class' => 'tv-g-rating',
            'icon' => 'bi-people',
            'title' => 'TV-G — General Audience.'
        ],

        'TV-PG' => [
            'class' => 'tv-pg-rating',
            'icon' => 'bi-exclamation-circle',
            'title' => 'TV-PG — Parental Guidance Suggested.'
        ],

        'TV-14' => [
            'class' => 'tv-14-rating',
            'icon' => 'bi-shield-exclamation',
            'title' => 'TV-14 — Parents Strongly Cautioned.'
        ],

        'TV-MA' => [
            'class' => 'tv-ma-rating',
            'icon' => 'bi-explicit',
            'title' => 'TV-MA — Mature Audience Only.'
        ],

    ];

    $rating = strtoupper($rating);

    if (isset($ratings[$rating])) {

        $r = $ratings[$rating];

        return "<span class='rating-badge {$r['class']}' data-bs-toggle='tooltip' title='{$r['title']}'>
                    <i class='bi {$r['icon']}'></i> {$rating}
                </span>";
    }

    return "<span class='rating-badge'>
                <i class='bi bi-question-circle'></i> {$rating}
            </span>";
}

function getLanguageName($code) {

    $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'ru' => 'Russian',
        'hi' => 'Hindi'
    ];

    return $languages[strtolower($code)] ?? strtoupper($code);
}
@endphp

<style>

/* =========================================================
   FILEIPLAY — GLOBAL
   ========================================================= */

html,
body {
    overflow-x: hidden;
}

/* =========================================================
   FILEIPLAY — TMDB BACKGROUND
   ========================================================= */

html::before {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background-image:
        linear-gradient(
            to bottom,
            rgba(5, 10, 18, .40),
            rgba(5, 10, 18, .96)
        ),
        url('{{ $torrent->background }}');

    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;

    opacity: .68;
    z-index: -2;
}

html::after {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background: linear-gradient(
        to bottom,
        rgba(5, 10, 18, .04) 0%,
        rgba(5, 10, 18, .25) 25%,
        rgba(5, 10, 18, .55) 55%,
        rgba(5, 10, 18, .86) 80%,
        rgba(5, 10, 18, 1) 100%
    );

    pointer-events: none;
    z-index: -1;
}

/* =========================================================
   FILEIPLAY — CAST SECTION
   ========================================================= */

.cast-section {
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    margin-top: 24px;
}

/* Header */

.cast-title {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 3px;
}

.cast-subtitle {
    color: rgba(255, 255, 255, .52);
    font-size: 13px;
}

.cast-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 7px 11px;

    border-radius: .55rem;

    background: rgba(45, 212, 191, .06);
    border: 1px solid rgba(45, 212, 191, .18);

    color: var(--ui-accent);

    font-size: 13px;
    font-weight: 600;
}

.cast-count-badge i {
    font-size: 13px;
}

/* Cast row */

.cast-row {
    display: flex;
    flex-wrap: nowrap;

    gap: 12px;

    width: 100%;
    max-width: 100%;

    overflow-x: auto;
    overflow-y: hidden;

    padding: 3px 2px 12px;

    scroll-behavior: smooth;

    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;

    box-sizing: border-box;
}

.cast-row::-webkit-scrollbar {
    display: none;
}

/* Actor card */

.cast-card {
    flex: 0 0 140px;

    width: 140px;
    min-width: 140px;
    max-width: 140px;

    padding: 12px 9px;

    border-radius: .7rem;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );

    border: 1px solid var(--ui-border);

    box-shadow: 0 8px 22px rgba(0, 0, 0, .25);

    backdrop-filter: blur(10px);

    transition:
        transform .2s ease,
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease;

    overflow: hidden;

    box-sizing: border-box;
}

.cast-card:hover {
    transform: translateY(-3px);

    border-color: rgba(45, 212, 191, .30);

    background: linear-gradient(
        135deg,
        rgba(25, 39, 59, .97),
        rgba(15, 23, 42, .92)
    );

    box-shadow: 0 10px 26px rgba(0, 0, 0, .34);
}

/* Actor image */

.actor-image-wrapper {
    width: 112px;
    height: 112px;

    margin: auto;

    border-radius: 50%;

    overflow: hidden;

    border: 2px solid rgba(45, 212, 191, .14);

    background: #0f172a;

    box-shadow:
        0 6px 18px rgba(0, 0, 0, .38);
}

.actor-image {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform .3s ease;
}

.cast-card:hover .actor-image {
    transform: scale(1.04);
}

/* Actor text */

.actor-name {
    font-size: 13px;
    font-weight: 700;

    line-height: 1.35;

    margin-bottom: 4px;
}

.actor-name a {
    color: rgba(255, 255, 255, .92);

    text-decoration: none;

    transition: color .15s ease;
}

.actor-name a:hover {
    color: var(--ui-accent);
}

.actor-character {
    font-size: 12px;

    line-height: 1.4;

    color: rgba(255, 255, 255, .52);

    overflow: hidden;

    display: -webkit-box;

    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* =========================================================
   FILEIPLAY — RATING BADGES
   ========================================================= */

.rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    padding: 5px 8px;

    border-radius: .45rem;

    background: rgba(255, 255, 255, .04);

    border: 1px solid var(--ui-border);

    color: rgba(255, 255, 255, .82);

    font-size: 12px;
    font-weight: 700;

    line-height: 1;
}

.rating-badge i {
    font-size: 12px;
}

.tv-y-rating,
.tv-y7-rating,
.tv-g-rating {
    color: #86efac;
    border-color: rgba(134, 239, 172, .20);
    background: rgba(134, 239, 172, .06);
}

.tv-pg-rating {
    color: #fde68a;
    border-color: rgba(253, 230, 138, .20);
    background: rgba(253, 230, 138, .06);
}

.tv-14-rating {
    color: #fdba74;
    border-color: rgba(253, 186, 116, .20);
    background: rgba(253, 186, 116, .06);
}

.tv-ma-rating {
    color: #fca5a5;
    border-color: rgba(252, 165, 165, .20);
    background: rgba(252, 165, 165, .06);
}

/* =========================================================
   TV EPISODE CARDS (Next / Last to Air)
   ========================================================= */

.tv-episode-card {
    overflow: hidden;
    border-radius: .85rem;
    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );
    border: 1px solid var(--ui-border);
    box-shadow: 0 14px 36px rgba(0, 0, 0, .28);
    backdrop-filter: blur(14px);
}

.episode-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--ui-border);
    background: rgba(45, 212, 191, .045);
}

.episode-icon-box {
    width: 38px;
    height: 38px;
    border-radius: .55rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.next-icon {
    background: rgba(45, 212, 191, .16);
    color: var(--ui-accent);
}

.last-icon {
    background: rgba(148, 163, 184, .16);
    color: #94a3b8;
}

.episode-card-title {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
}

.episode-card-subtitle {
    color: rgba(255, 255, 255, .42);
    font-size: 12px;
}

.episode-card-body {
    display: flex;
    gap: 14px;
    padding: 16px;
}

.episode-still {
    width: 160px;
    height: 90px;
    object-fit: cover;
    border-radius: .55rem;
    flex: 0 0 auto;
    border: 1px solid rgba(255, 255, 255, .07);
}

.episode-info {
    min-width: 0;
    flex: 1;
}

.episode-title {
    color: rgba(255, 255, 255, .92);
    font-size: 14px;
    font-weight: 700;
}

.episode-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 7px;
}

.episode-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: .45rem;
    background: rgba(255, 255, 255, .04);
    border: 1px solid var(--ui-border);
    color: rgba(255, 255, 255, .68);
    font-size: 11px;
    font-weight: 600;
}

.episode-meta-pill i {
    color: var(--ui-accent);
    font-size: 10px;
}

.episode-overview {
    margin-top: 9px;
    color: rgba(255, 255, 255, .55);
    font-size: 12px;
    line-height: 1.55;

    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* =========================================================
   TV SEASONS
   ========================================================= */

.tv-seasons-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 12px;
}

/* More than 10 seasons */
.tv-seasons-row.seasons-scrollable {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 12px;
    padding-bottom: 12px;
    scroll-behavior: smooth;
}

/* Fixed card width when scrolling */
.tv-seasons-row.seasons-scrollable .season-card {
    flex: 0 0 170px;
    width: 170px;
}

/* Scrollbar */
.tv-seasons-row.seasons-scrollable::-webkit-scrollbar {
    height: 7px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.04);
    border-radius: 10px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-thumb {
    background: rgba(45, 212, 191, 0.45);
    border-radius: 10px;
}

.tv-seasons-row.seasons-scrollable::-webkit-scrollbar-thumb:hover {
    background: rgba(45, 212, 191, 0.7);
}

.tv-seasons-row.seasons-scrollable {
    scrollbar-width: thin;
    scrollbar-color: rgba(45, 212, 191, 0.45)
                     rgba(255, 255, 255, 0.04);
}

.season-card {
    overflow: hidden;
    border-radius: .7rem;
    background: rgba(9, 16, 29, .48);
    border: 1px solid rgba(255, 255, 255, .055);
    transition:
        transform .18s ease,
        border-color .18s ease;
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

/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .cast-section {
        margin-top: 20px;
    }

    .cast-title {
        font-size: 14px;
    }

    .cast-subtitle {
        font-size: 13px;
    }

    .cast-count-badge {
        margin-top: 8px;
        font-size: 12px;
    }

    .cast-row {
        gap: 10px;
    }

    .cast-card {
        flex: 0 0 125px;

        width: 125px;
        min-width: 125px;
        max-width: 125px;

        padding: 10px 7px;
    }

    .actor-image-wrapper {
        width: 96px;
        height: 96px;
    }

    .actor-name {
        font-size: 12px;
    }

    .actor-character {
        font-size: 11px;
    }

    .episode-card-body {
        flex-direction: column;
    }

    .episode-still {
        width: 100%;
        height: auto;
    }

   .tv-seasons-row:not(.seasons-scrollable) {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 9px;
    }

    .tv-seasons-row.seasons-scrollable {
        gap: 9px;
    }

    .tv-seasons-row.seasons-scrollable .season-card {
        flex: 0 0 130px;
        width: 130px;
    }
}

</style>
