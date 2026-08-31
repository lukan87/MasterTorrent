@include('torrents.partials.media-header', [
    'torrent' => $torrent,
    'display' => $display
])



<!-- CAST -->
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

                    <img
                        src="{{ $actor['photo'] ?? '/images/not-found.jpg' }}"
                        loading="lazy"
                        class="actor-image"
                        alt="{{ $actor['name'] }}"
                    >

                </div>

                <div class="mt-3">

                    <h6 class="actor-name">

                        @if($actor['id'])

                            <a href="https://www.themoviedb.org/person/{{ $actor['id'] }}"
                               target="_blank"
                               rel="noreferrer">

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

    

/* ======================================
   CAST SECTION
====================================== */

.cast-section{
    position:relative;

    width:100%;
    max-width:100%;

    overflow:hidden;
}
/* ======================================
   HEADER
====================================== */

.cast-title{
    font-size:1.6rem;
    font-weight:800;
    color:#fff;
    margin-bottom:2px;
}

.cast-subtitle{
    color:rgba(255,255,255,.55);
    font-size:.9rem;
}

.cast-count-badge{

    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:8px 14px;

    border-radius:999px;

    background:rgba(255,255,255,.05);

    border:1px solid rgba(255,255,255,.06);

    color:#fff;

    font-size:.85rem;
    font-weight:700;
}

/* ======================================
   ROW
====================================== */

.cast-row{

    display:flex;

    flex-wrap:nowrap;

    gap:18px;

    overflow-x:auto;
    overflow-y:hidden;

    width:100%;
    max-width:100%;

    padding:2px 2px 12px;

    scroll-behavior:smooth;

    scrollbar-width:none;

    -webkit-overflow-scrolling:touch;

    box-sizing:border-box;
}

.cast-row::-webkit-scrollbar{
    display:none;
}

/* IMPORTANT FIX */


/* ======================================
   CARD
====================================== */

.select{

    flex:0 0 140px;

    max-width:140px;
    min-width:140px;

    padding:14px 10px;

    border-radius:18px;

    background:rgba(255,255,255,.045);

    border:1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(8px);

    transition:.25s ease;

    overflow:hidden;

    box-sizing:border-box;
}

.select:hover{

    transform:translateY(-4px);

    background:rgba(255,255,255,.07);

    border-color:rgba(124,58,237,.25);
}

/* ======================================
   IMAGE
====================================== */

.actor-image-wrapper{

    width:120px;
    height:120px;

    margin:auto;

    border-radius:50%;

    overflow:hidden;

    box-shadow:
        0 6px 18px rgba(0,0,0,.35);
}

.actor-image{

    width:100%;
    height:100%;

    object-fit:cover;

    transition:transform .3s ease;
}

.select:hover .actor-image{
    transform:scale(1.05);
}

/* ======================================
   TEXT
====================================== */

.actor-name{

    font-size:.99rem;
    font-weight:700;

    margin-bottom:4px;
}

.actor-name a{

    color:#fff;

    text-decoration:none;
}

.actor-name a:hover{
    color:#c4b5fd;
}

.actor-character{

    font-size:.88rem;

    line-height:1.35;

    color:rgba(255,255,255,.58);

    overflow:hidden;

    display:-webkit-box;

    -webkit-line-clamp:2;

    -webkit-box-orient:vertical;
}

/* ======================================
   MOBILE
====================================== */

@media(max-width:768px){

    .cast-title{
        font-size:1.3rem;
    }

    .cast-row{
        gap:14px;
    }

    .select{
        width:125px;
        padding:12px 8px;
    }

    .actor-image-wrapper{
        width:98px;
        height:98px;
    }

    .actor-name{
        font-size:.82rem;
    }

    .actor-character{
        font-size:.72rem;
    }
}

html, body{
    overflow-x:hidden;
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

  /* =====================================
   Base & Layout
   ===================================== */
html, body {
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

html::before {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
    linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)),
    url('{{ $fanartBackground ?? $torrent->background }}');
    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;
    opacity: 0.7;
    z-index: -1;
}

html::after {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    /* This gradient is what creates the "gets darker as you scroll" effect */
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.05) 0%,
        rgba(0,0,0,0.25) 25%,
        rgba(0,0,0,0.55) 55%,
        rgba(0,0,0,0.85) 80%,
        rgba(0,0,0,1) 100%
    );

    pointer-events: none;
    z-index: -1;
}



</style>

