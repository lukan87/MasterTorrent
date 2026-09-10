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
   FILEIPLAY — FANART BACKGROUND
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
        url('{{ $fanartBackground ?? $torrent->background }}');

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
}

</style>
