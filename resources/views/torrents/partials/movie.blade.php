@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])



<!-- CAST -->
@if(!empty($display['cast']))

<div class="cast-section mt-5">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="cast-title mb-1">
                Featured Cast
            </h3>

            <p class="cast-subtitle mb-0">
                Main actors and characters
            </p>
        </div>

        <div class="cast-count-badge">

            <i class="bi bi-people-fill"></i>

            {{ count($display['cast']) }} Actors

        </div>

    </div>

    {{-- CAST --}}
    <div class="cast-row">

        @foreach($display['cast'] as $actor)

            <div class="select text-center">

                <div class="actor-image-wrapper">
                    @if(!empty($actor['id']))<a href="{{ route('actors.show', $actor['id']) }}" class="d-block w-100 h-100" aria-label="View {{ $actor['name'] }}'s profile">@endif

                    <img
                        src="{{ $actor['photo'] ?? '/images/not-found.jpg' }}"
                        loading="lazy"
                        class="actor-image"
                        alt="{{ $actor['name'] }}"
                    >
                    @if(!empty($actor['id']))</a>@endif

                </div>

                <div class="mt-3">

                    <h6 class="actor-name">

                        @if(!empty($actor['id']))

                            <a href="{{ route('actors.show', $actor['id']) }}">

                                <b>{{ $actor['name'] }}</b>

                            </a>

                        @else

                            <b>{{ $actor['name'] }}</b>

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

<style>

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
    color: rgba(255,255,255,.52);
    font-size: 13px;
}

.cast-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: .55rem;

    background: rgba(45,212,191,.06);
    border: 1px solid rgba(45,212,191,.18);

    color: var(--ui-accent);
    font-size: 13px;
    font-weight: 600;
}

.cast-count-badge i {
    font-size: 13px;
}

/* Horizontal row */

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

.select {
    flex: 0 0 140px;
    width: 140px;
    min-width: 140px;
    max-width: 140px;

    padding: 12px 9px;

    border-radius: .7rem;

    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );

    border: 1px solid var(--ui-border);

    box-shadow: 0 8px 22px rgba(0,0,0,.25);

    backdrop-filter: blur(10px);

    transition:
        transform .2s ease,
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease;

    overflow: hidden;
    box-sizing: border-box;
}

.select:hover {
    transform: translateY(-3px);

    border-color: rgba(45,212,191,.30);

    background: linear-gradient(
        135deg,
        rgba(25,39,59,.97),
        rgba(15,23,42,.92)
    );

    box-shadow: 0 10px 26px rgba(0,0,0,.34);
}

/* Actor image */

.actor-image-wrapper {
    width: 112px;
    height: 112px;

    margin: auto;

    border-radius: 50%;
    overflow: hidden;

    border: 2px solid rgba(45,212,191,.14);

    background: #0f172a;

    box-shadow:
        0 6px 18px rgba(0,0,0,.38);
}

.actor-image {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform .3s ease;
}

.select:hover .actor-image {
    transform: scale(1.04);
}

/* Actor name */

.actor-name {
    font-size: 13px;
    font-weight: 700;

    line-height: 1.35;

    margin-bottom: 4px;
}

.actor-name a {
    color: rgba(255,255,255,.92);
    text-decoration: none;

    transition: color .15s ease;
}

.actor-name a:hover {
    color: var(--ui-accent);
}

.actor-character {
    font-size: 12px;
    line-height: 1.4;

    color: rgba(255,255,255,.52);

    overflow: hidden;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Mobile */

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

    .select {
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

html,
body {
    overflow-x: hidden;
}

</style>

@includeWhen(
    $display['type'] === 'movie',
    'torrents.partials.collection'
)



@php
function getRatingBadge($rating) {
    switch (strtoupper($rating)) {
        case 'G':
            return "<span class='rating-badge g-rating' data-bs-toggle='tooltip'
                title='G — General Audiences. Suitable for all ages; contains no offensive material or content.'>
                <i class='bi bi-emoji-smile'></i> G
            </span>";

        case 'PG':
            return "<span class='rating-badge pg-rating' data-bs-toggle='tooltip'
                title='PG — Parental Guidance Suggested. Some material may not be suitable for children (e.g. mild language or brief peril).'>
                <i class='bi bi-emoji-neutral'></i> PG
            </span>";

        case 'PG-13':
            return "<span class='rating-badge pg13-rating' data-bs-toggle='tooltip'
                title='PG-13 — Parents Strongly Cautioned. Some material may be inappropriate for children under 13 due to violence, language, or mature themes.'>
                <i class='bi bi-emoji-frown'></i> PG-13
            </span>";

        case 'R':
            return "<span class='rating-badge r-rating' data-bs-toggle='tooltip'
                title='R — Restricted. Under 17 requires accompanying parent or adult guardian. Contains strong language, violence, or adult themes.'>
                <i class='bi bi-shield-exclamation'></i> R
            </span>";

        case 'NC-17':
            return "<span class='rating-badge nc17-rating' data-bs-toggle='tooltip'
                title='NC-17 — Adults Only. No one 17 and under admitted. May contain explicit sexual content or graphic violence.'>
                <i class='bi bi-explicit'></i> NC-17
            </span>";

        default:
            return "<span class='rating-badge unrated-rating' data-bs-toggle='tooltip'
                title='Unrated or not classified by MPAA.'>
                <i class='bi bi-question-circle'></i> $rating
            </span>";
    }
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
    
    return $languages[$code] ?? strtoupper($code);
}
@endphp






<style>

html,
body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

/* Fanart background */

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
            rgba(5,10,18,.38),
            rgba(5,10,18,.94)
        ),
        url('{{ $fanartBackground ?? $torrent->background }}');

    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;

    opacity: .65;

    z-index: -2;
}

/* Darkening layer */

html::after {
    content: '';

    position: fixed;

    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background: linear-gradient(
        to bottom,
        rgba(5,10,18,.05) 0%,
        rgba(5,10,18,.28) 25%,
        rgba(5,10,18,.58) 55%,
        rgba(5,10,18,.86) 80%,
        rgba(5,10,18,1) 100%
    );

    pointer-events: none;

    z-index: -1;
}

</style>