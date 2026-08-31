{{-- Sticky Mini Header --}}
@unless($torrent->trashed())

<div class="game-mini-header glass">

    <div class="mini-header-title">

        <strong>
            {{ $steamData[$torrent->steamid]['data']['name'] ?? 'Game' }}
        </strong>

    </div>

    <a href="{{ route('torrents.download', [$torrent->id, $torrent->slug]) }}"
       class="btn btn-info btn-sm ms-auto rounded-pill px-3">

        <i class="bi bi-download me-1"></i>

        Download

    </a>

</div>


<div class="container-fluid game-page mt-4">

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-4">

            {{-- Poster --}}
            <div class="media-card">

                <img src="{{ $torrent->poster }}"
                     class="poster-img">

                @if(isset($steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']))

                    <button class="btn btn-outline-light w-100 mt-3 rounded-pill"
                            data-bs-toggle="modal"
                            data-bs-target="#trailerModal">

                        <i class="bi bi-play-circle-fill me-1"></i>

                        Watch Trailer

                    </button>

                @endif

            </div>

            {{-- Features --}}
            @if(isset($steamData[$torrent->steamid]['data']['categories']))

                <div class="feature-card mt-4">

                    <h6 class="section-title">

                        Features

                    </h6>

                    <div class="feature-grid">

                        @foreach($steamData[$torrent->steamid]['data']['categories'] as $cat)

                            <div class="feature-item">

                                <i class="bi bi-check-circle-fill text-success"></i>

                                <span>{{ $cat['description'] }}</span>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endif

            {{-- Ratings --}}
            @if(isset($steamData[$torrent->steamid]['data']['ratings']))

                <div class="mt-3 d-flex flex-wrap gap-2">

                    @foreach($steamData[$torrent->steamid]['data']['ratings'] ?? [] as $rating)

                        @php
                            $value = $rating['rating'] ?? null;
                        @endphp

                        @if($value)

                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">

                                {{ strtoupper($value) }}

                            </span>

                        @endif

                    @endforeach

                </div>

            @endif

            {{-- Screenshots --}}
            @if(isset($steamData[$torrent->steamid]['data']['screenshots']))

<div class="screenshot-carousel mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h6 class="section-title mb-0">

            Screenshots

        </h6>

        <span class="small text-muted">

            {{ count($steamData[$torrent->steamid]['data']['screenshots']) }} Images

        </span>

    </div>

    <div id="screenshotsCarousel"
         class="carousel slide"
         data-bs-ride="false">

        <div class="carousel-inner">

            @foreach($steamData[$torrent->steamid]['data']['screenshots'] as $key => $shot)

                <div class="carousel-item @if($key===0) active @endif">

                    <img src="{{ $shot['path_thumbnail'] }}"
                         class="screenshot-img"
                         data-full="{{ $shot['path_full'] }}">

                </div>

            @endforeach

        </div>

        <button class="carousel-control-prev"
                type="button"
                data-bs-target="#screenshotsCarousel"
                data-bs-slide="prev">

            <span class="carousel-control-prev-icon"></span>

        </button>

        <button class="carousel-control-next"
                type="button"
                data-bs-target="#screenshotsCarousel"
                data-bs-slide="next">

            <span class="carousel-control-next-icon"></span>

        </button>

    </div>

</div>

            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-8">

            <div class="info-card">

                {{-- TITLE --}}
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">

                    <div>

                        <h1 class="game-title mb-1">

                            {{ $steamData[$torrent->steamid]['data']['name'] ?? 'N/A' }}

                        </h1>

                        @if(isset($steamData[$torrent->steamid]['data']['release_date']['date']))

                            <p class="text-muted mb-0">

                                Released

                                {{ \Carbon\Carbon::parse($steamData[$torrent->steamid]['data']['release_date']['date'])->format('F j, Y') }}

                            </p>

                        @endif

                    </div>

                </div>

                {{-- Genres --}}
                <div class="mb-4 d-flex flex-wrap gap-2">

                    @foreach($torrent->genres as $genre)

                        <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
                           class="genre-badge">

                            {{ $genre->name }}

                        </a>

                    @endforeach

                </div>

                {{-- Description --}}
                @if(isset($steamData[$torrent->steamid]['data']['short_description']))

                    <div class="short-description">

                        {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['short_description']) !!}

                    </div>

                @endif

                {{-- Meta --}}
                <div class="meta-grid mt-4">

                    @if(!empty($steamData[$torrent->steamid]['data']['developers']))

                        <div class="meta-box">

                            <small>Developer</small>

                            <span>

                                {{ implode(', ', $steamData[$torrent->steamid]['data']['developers']) }}

                            </span>

                        </div>

                    @endif

                    @if(!empty($steamData[$torrent->steamid]['data']['publishers']))

                        <div class="meta-box">

                            <small>Publisher</small>

                            <span>

                                {{ implode(', ', $steamData[$torrent->steamid]['data']['publishers']) }}

                            </span>

                        </div>

                    @endif

                    @if(isset($steamData[$torrent->steamid]['data']['metacritic']['score']))

                        <div class="meta-box">

                            <small>Metacritic</small>

                            <span>

                                {{ $steamData[$torrent->steamid]['data']['metacritic']['score'] }}/100

                            </span>

                        </div>

                    @endif

                    @if(isset($steamData[$torrent->steamid]['data']['price_overview']['final_formatted']))

                        <div class="meta-box">

                            <small>Price</small>

                            <span>

                                {{ $steamData[$torrent->steamid]['data']['price_overview']['final_formatted'] }}

                            </span>

                        </div>

                    @endif

                </div>

                {{-- About --}}
                @if(isset($steamData[$torrent->steamid]['data']['about_the_game']))

                    <div class="about-box mt-4">

                        <h5 class="mb-3">

                            About The Game

                        </h5>

                        {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['about_the_game']) !!}

                    </div>

                @endif

                {{-- Reviews --}}
                @if(isset($steamData[$torrent->steamid]['data']['recommendations']))

                    <div class="reviews-card mt-4">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <h5 class="mb-0">

                                User Reviews

                            </h5>

                            <span class="text-success fw-bold">

                                Very Positive

                            </span>

                        </div>

                        <div class="review-count">

                            {{ number_format($steamData[$torrent->steamid]['data']['recommendations']['total']) }}

                            reviews

                        </div>

                        <div class="progress modern-progress mt-3">

                            <div class="progress-bar bg-success"
                                 style="width:85%">

                            </div>

                        </div>

                    </div>

                @endif

                {{-- Requirements --}}
                <div class="row mt-4 g-3">

                    @if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']))

                        <div class="col-md-6">

                            <div class="req-card">

                                <h6 class="req-title">

                                    Minimum

                                </h6>

                                {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['minimum']) !!}

                            </div>

                        </div>

                    @endif

                    @if(isset($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']))

                        <div class="col-md-6">

                            <div class="req-card">

                                <h6 class="req-title">

                                    Recommended

                                </h6>

                                {!! convertCustomTagsToHtml($steamData[$torrent->steamid]['data']['pc_requirements']['recommended']) !!}

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

{{-- Trailer Modal --}}
@if(isset($steamData[$torrent->steamid]['data']['movies'][0]['webm']['max']))

<div class="modal fade" id="trailerModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-xl">

        <div class="modal-content bg-dark text-white border-0">

            <div class="modal-header border-secondary">

                <h5 class="modal-title">

                    {{ $steamData[$torrent->steamid]['data']['name'] ?? 'Trailer' }}

                </h5>

                <button class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                </button>

            </div>

            <div class="modal-body">

                <div class="videoWrapper">

                    <video id="trailerVideo" controls>

                        <source src="{{ $steamData[$torrent->steamid]['data']['movies'][0]['webm']['max'] }}"
                                type="video/webm">

                    </video>

                </div>

            </div>

        </div>

    </div>

</div>

@endif

{{-- Screenshot Modal --}}
<div class="modal fade" id="screenshotModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-xl">

        <div class="modal-content bg-dark border-0">

            <div class="modal-body p-0 text-center">

                <img id="screenshotModalImg"
                     src=""
                     style="max-width:100%; max-height:90vh;">

            </div>

        </div>

    </div>

</div>

<style>

/* =========================================
   PAGE
========================================= */

.game-page{

    color:#e5e7eb;

    position:relative;

    z-index:2;
}

body::before{

    content:'';

    position:fixed;

    inset:55px 0 0 0;

    background:
        linear-gradient(
            to bottom,
            rgba(0,0,0,.78),
            rgba(0,0,0,.94)
        ),
        url('{{ $torrent->background }}');

    background-size:cover;

    background-position:center;

    z-index:-1;
}

/* =========================================
   CARDS
========================================= */

.media-card,
.info-card,
.feature-card,
.screenshot-carousel{

    background:
        rgba(20,20,20,.5);

    backdrop-filter:blur(10px);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:20px;

    padding:1.2rem;

    box-shadow:
        0 10px 30px rgba(0,0,0,.3);
}

/* =========================================
   POSTER
========================================= */

.poster-img{

    width:100%;

    border-radius:16px;

    box-shadow:
        0 20px 40px rgba(0,0,0,.6);
}

/* =========================================
   TITLE
========================================= */

.game-title{

    font-size:2.2rem;

    font-weight:800;

    color:#fff;
}

/* =========================================
   GENRES
========================================= */

.genre-badge{

    display:inline-flex;

    align-items:center;

    padding:8px 14px;

    border-radius:999px;

    background:
        rgba(255,255,255,.06);

    border:
        1px solid rgba(255,255,255,.08);

    color:#fff;

    text-decoration:none;

    font-size:.88rem;

    transition:.2s ease;
}

.genre-badge:hover{

    background:
        rgba(59,130,246,.16);

    color:#93c5fd;
}

/* =========================================
   FEATURES
========================================= */

.feature-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(160px,1fr));

    gap:.7rem;
}

.feature-item{

    display:flex;

    align-items:center;

    gap:.6rem;

    padding:.75rem;

    border-radius:12px;

    background:
        rgba(255,255,255,.04);

    font-size:.9rem;
}

/* =========================================
   META
========================================= */

.meta-grid{

    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(180px,1fr));

    gap:1rem;
}

.meta-box{

    background:
        rgba(255,255,255,.04);

    padding:.9rem;

    border-radius:14px;

    display:flex;

    flex-direction:column;

    gap:.25rem;
}

.meta-box small{

    color:#9ca3af;

    font-size:.78rem;

    text-transform:uppercase;

    letter-spacing:.05em;
}

.meta-box span{

    font-weight:600;

    color:#fff;
}

/* =========================================
   ABOUT
========================================= */

.about-box{

    max-height:340px;

    overflow-y:auto;

    padding:1rem;

    border-radius:14px;

    background:
        rgba(255,255,255,.03);

    border-left:
        3px solid #3b82f6;
}

.about-box::-webkit-scrollbar{

    width:6px;
}

.about-box::-webkit-scrollbar-thumb{

    background:
        rgba(255,255,255,.25);

    border-radius:999px;
}

/* =========================================
   REVIEWS
========================================= */

.reviews-card{

    background:
        rgba(255,255,255,.04);

    border-radius:16px;

    padding:1rem;
}

.review-count{

    color:#d1d5db;

    font-size:.95rem;
}

.modern-progress{

    height:10px;

    border-radius:999px;

    background:
        rgba(255,255,255,.08);
}

/* =========================================
   REQUIREMENTS
========================================= */

.req-card{

    background:
        rgba(255,255,255,.04);

    border-radius:16px;

    padding:1rem;

    height:100%;
}

.req-title{

    color:#fff;

    margin-bottom:1rem;
}

/* =========================================
   SCREENSHOTS
========================================= */

.screenshot-carousel .carousel-inner{

    height:320px;

    border-radius:16px;

    overflow:hidden;
}

.screenshot-img{

    width:100%;

    height:100%;

    object-fit:cover;

    cursor:zoom-in;

    transition:.2s ease;
}

.screenshot-img:hover{

    transform:scale(1.02);
}

.carousel-control-prev-icon,
.carousel-control-next-icon{

    background-color:
        rgba(0,0,0,.6);

    border-radius:50%;

    width:42px;

    height:42px;

    background-size:55%;
}

/* =========================================
   MINI HEADER
========================================= */

.game-mini-header{

    position:fixed;

    top:62px;

    left:50%;

    transform:
        translate(-50%, -120%);

    width:calc(100% - 30px);

    max-width:900px;

    z-index:1050;

    display:flex;

    align-items:center;

    gap:1rem;

    padding:.8rem 1rem;

    background:
        rgba(20,20,20,.72);

    backdrop-filter:blur(12px);

    border:
        1px solid rgba(255,255,255,.08);

    border-radius:18px;

    transition:
        transform .25s ease;
}

.game-mini-header.visible{

    transform:
        translate(-50%, 0);
}

.mini-header-title{

    overflow:hidden;

    white-space:nowrap;

    text-overflow:ellipsis;

    font-size:1rem;

    color:#fff;
}

/* =========================================
   VIDEO
========================================= */

.videoWrapper{

    position:relative;

    padding-bottom:56.25%;
}

.videoWrapper video{

    position:absolute;

    width:100%;

    height:100%;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .game-title{

        font-size:1.7rem;
    }

    .screenshot-carousel .carousel-inner{

        height:220px;
    }

    .meta-grid{

        grid-template-columns:1fr;
    }

    .feature-grid{

        grid-template-columns:1fr;
    }

    .game-mini-header{

        top:58px;

        width:calc(100% - 16px);

        padding:.7rem .9rem;
    }

    .mini-header-title{

        font-size:.9rem;
    }

}

</style>

<script>

/* Sticky header */
window.addEventListener('scroll', () => {

    document.querySelector('.game-mini-header')
        .classList.toggle('visible', window.scrollY > 300);

});

/* Screenshot modal */
document.querySelectorAll('.screenshot-img').forEach(img => {

    img.addEventListener('click', () => {

        const modalImg = document.getElementById('screenshotModalImg');

        modalImg.src = img.dataset.full;

        const modal = new bootstrap.Modal(
            document.getElementById('screenshotModal')
        );

        modal.show();

    });

});

/* Trailer */
const trailerModal = document.getElementById('trailerModal');

const trailerVideo = document.getElementById('trailerVideo');

if (trailerModal) {

    trailerModal.addEventListener('shown.bs.modal', () => trailerVideo.play());

    trailerModal.addEventListener('hidden.bs.modal', () => {

        trailerVideo.pause();

        trailerVideo.currentTime = 0;

    });

}

</script>

@endunless