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

<style>

/* =========================================
   GLOBAL
========================================= */

html,
body{
    overflow-x:hidden;
}

/* =========================================
   BACKGROUND
========================================= */

html::before{

    content:'';

    position:fixed;

    top:55px;
    left:0;
    right:0;
    bottom:0;

    background-image:
        linear-gradient(
            to bottom,
            rgba(0,0,0,.45),
            rgba(0,0,0,1)
        ),
        url('{{ $fanartBackground ?? $torrent->background }}');

    background-position:center top;

    background-size:cover;

    background-repeat:no-repeat;

    opacity:.7;

    z-index:-2;
}

html::after{

    content:'';

    position:fixed;

    top:55px;
    left:0;
    right:0;
    bottom:0;

    background:
        linear-gradient(
            to bottom,
            rgba(0,0,0,.05) 0%,
            rgba(0,0,0,.25) 25%,
            rgba(0,0,0,.55) 55%,
            rgba(0,0,0,.85) 80%,
            rgba(0,0,0,1) 100%
        );

    pointer-events:none;

    z-index:-1;
}

/* =========================================
   CAST SECTION
========================================= */

.cast-section{

    position:relative;

    width:100%;

    max-width:100%;

    overflow:hidden;
}

/* =========================================
   HEADER
========================================= */

.cast-title{

    font-size:1.7rem;

    font-weight:800;

    color:#fff;

    margin-bottom:2px;
}

.cast-subtitle{

    color:rgba(255,255,255,.58);

    font-size:.92rem;
}

.cast-count-badge{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:9px 15px;

    border-radius:999px;

    background:rgba(255,255,255,.05);

    border:1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(8px);

    color:#fff;

    font-size:.84rem;

    font-weight:700;
}

/* =========================================
   ROW
========================================= */

.cast-row{

    display:flex;

    flex-wrap:nowrap;

    overflow-x:auto;

    overflow-y:hidden;

    gap:18px;

    width:100%;

    max-width:100%;

    padding:4px 16px 14px;

    scroll-behavior:smooth;

    scrollbar-width:none;

    -webkit-overflow-scrolling:touch;

    box-sizing:border-box;
}

.cast-row::-webkit-scrollbar{
    display:none;
}

/* =========================================
   CARD
========================================= */

.cast-card{

    flex:0 0 auto;

    width:145px;

    min-width:145px;

    max-width:145px;

    padding:15px 10px;

    border-radius:20px;

    background:rgba(255,255,255,.045);

    border:1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(10px);

    transition:
        transform .25s ease,
        background .25s ease,
        border-color .25s ease;

    overflow:hidden;

    box-sizing:border-box;
}

.cast-card:hover{

    transform:translateY(-4px);

    background:rgba(255,255,255,.07);

    border-color:rgba(124,58,237,.25);
}

/* =========================================
   IMAGE
========================================= */

.actor-image-wrapper{

    width:112px;

    height:112px;

    margin:auto;

    border-radius:50%;

    overflow:hidden;

    box-shadow:
        0 8px 20px rgba(0,0,0,.35);
}

.actor-image{

    width:100%;

    height:100%;

    object-fit:cover;

    transition:transform .3s ease;
}

.cast-card:hover .actor-image{
    transform:scale(1.05);
}

/* =========================================
   TEXT
========================================= */

.actor-name{

    font-size:.93rem;

    font-weight:700;

    margin-bottom:5px;
}

.actor-name a{

    color:#fff;

    text-decoration:none;

    transition:color .2s ease;
}

.actor-name a:hover{
    color:#c4b5fd;
}

.actor-character{

    font-size:.8rem;

    line-height:1.35;

    color:rgba(255,255,255,.58);

    overflow:hidden;

    display:-webkit-box;

    -webkit-line-clamp:2;

    -webkit-box-orient:vertical;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .cast-title{
        font-size:1.35rem;
    }

    .cast-row{

        gap:14px;

        padding-left:12px;

        padding-right:12px;
    }

    .cast-card{

        width:125px;

        min-width:125px;

        max-width:125px;

        padding:12px 8px;
    }

    .actor-image-wrapper{

        width:95px;

        height:95px;
    }

    .actor-name{
        font-size:.82rem;
    }

    .actor-character{
        font-size:.72rem;
    }

    .cast-count-badge{

        font-size:.76rem;

        padding:7px 12px;
    }
}

</style>

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

    if(isset($ratings[$rating])) {

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