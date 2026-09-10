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

        <div class="modal-content">

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

        <div class="modal-content">

            <div class="modal-body p-0 text-center">

                <img id="screenshotModalImg"
                     src=""
                     class="screenshot-modal-image">

            </div>

        </div>

    </div>

</div>

<style>
.game-page{position:relative;z-index:2;color:#e5e7eb}
body::before{content:'';position:fixed;inset:55px 0 0 0;background:linear-gradient(to bottom,rgba(7,12,22,.82),rgba(7,12,22,.96)),url('{{ $torrent->background }}');background-size:cover;background-position:center;z-index:-1}
.media-card,.info-card,.feature-card,.screenshot-carousel{background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.84));border:1px solid var(--ui-border);border-radius:.85rem;box-shadow:0 14px 36px rgba(0,0,0,.28);backdrop-filter:blur(14px)}
.media-card,.feature-card,.screenshot-carousel{padding:16px}.info-card{padding:22px}
.poster-img{display:block;width:100%;border-radius:.65rem;border:1px solid var(--ui-border);box-shadow:0 14px 30px rgba(0,0,0,.42)}
.media-card .btn-outline-light{border-color:var(--ui-border);color:rgba(255,255,255,.78);font-size:13px}
.media-card .btn-outline-light:hover{background:rgba(45,212,191,.08);border-color:rgba(45,212,191,.35);color:var(--ui-accent)}
.section-title{color:#fff;font-size:14px;font-weight:700;margin-bottom:12px;padding-left:10px;border-left:3px solid var(--ui-accent)}
.game-title{margin:0;color:#fff;font-size:22px;line-height:1.3;font-weight:700}
.genre-badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:.45rem;background:rgba(45,212,191,.06);border:1px solid rgba(45,212,191,.18);color:var(--ui-accent);text-decoration:none;font-size:13px;font-weight:600;transition:.15s ease}
.genre-badge:hover{background:rgba(45,212,191,.12);border-color:rgba(45,212,191,.34);color:#99f6e4}
.feature-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:7px}
.feature-item{display:flex;align-items:flex-start;gap:8px;padding:9px;border-radius:.55rem;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.055);color:rgba(255,255,255,.72);font-size:13px;line-height:1.4}
.feature-item i{flex:0 0 auto;margin-top:2px}
.media-card .badge.bg-warning{background:rgba(245,158,11,.12)!important;border:1px solid rgba(245,158,11,.22);color:#fcd34d!important;font-size:12px}
.screenshot-carousel{overflow:hidden}.screenshot-carousel .small{color:rgba(255,255,255,.45)!important;font-size:12px}
.screenshot-carousel .carousel-inner{height:320px;overflow:hidden;border-radius:.65rem;border:1px solid var(--ui-border)}
.screenshot-img{width:100%;height:100%;object-fit:cover;cursor:zoom-in;transition:transform .2s ease}.screenshot-img:hover{transform:scale(1.015)}
.carousel-control-prev-icon,.carousel-control-next-icon{width:34px;height:34px;padding:7px;background-color:rgba(7,13,24,.78);border:1px solid rgba(255,255,255,.10);border-radius:50%;background-size:52%}
.short-description{padding:14px;background:rgba(9,16,29,.48);border:1px solid var(--ui-border);border-left:3px solid var(--ui-accent);border-radius:.65rem;color:rgba(255,255,255,.72);font-size:14px;line-height:1.65}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px}
.meta-box{display:flex;flex-direction:column;gap:3px;padding:11px;border-radius:.6rem;background:rgba(255,255,255,.025);border:1px solid var(--ui-border)}
.meta-box small{color:rgba(255,255,255,.42);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}.meta-box span{color:rgba(255,255,255,.82);font-size:13px;font-weight:600;word-break:break-word}
.about-box{max-height:340px;overflow-y:auto;padding:14px;background:rgba(9,16,29,.42);border:1px solid var(--ui-border);border-left:3px solid var(--ui-accent);border-radius:.65rem;color:rgba(255,255,255,.72);font-size:14px;line-height:1.6}
.about-box::-webkit-scrollbar{width:5px}.about-box::-webkit-scrollbar-thumb{background:rgba(45,212,191,.28);border-radius:999px}
.reviews-card{padding:14px;background:rgba(9,16,29,.42);border:1px solid var(--ui-border);border-radius:.65rem}.reviews-card h5{color:#fff;font-size:14px}.reviews-card .text-success{color:#6ee7b7!important;font-size:13px}.review-count{color:rgba(255,255,255,.55);font-size:13px}
.modern-progress{height:7px;overflow:hidden;border-radius:999px;background:rgba(255,255,255,.08)}.modern-progress .progress-bar{background:#34d399!important}
.req-card{height:100%;padding:14px;background:rgba(9,16,29,.42);border:1px solid var(--ui-border);border-radius:.65rem;color:rgba(255,255,255,.68);font-size:13px;line-height:1.55}.req-title{margin-bottom:10px;color:#fff;font-size:14px;font-weight:700;padding-left:9px;border-left:3px solid var(--ui-accent)}
.game-mini-header{position:fixed;top:62px;left:50%;transform:translate(-50%,-120%);width:calc(100% - 30px);max-width:900px;z-index:1050;display:flex;align-items:center;gap:10px;padding:9px 12px;background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.90));border:1px solid var(--ui-border);border-radius:.7rem;box-shadow:0 12px 30px rgba(0,0,0,.32);backdrop-filter:blur(14px);transition:transform .25s ease}
.game-mini-header.visible{transform:translate(-50%,0)}.mini-header-title{min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:#fff;font-size:14px}
.game-mini-header .btn-info{flex:0 0 auto;border:1px solid rgba(45,212,191,.25);background:rgba(45,212,191,.10);color:var(--ui-accent);font-size:13px}.game-mini-header .btn-info:hover{background:rgba(45,212,191,.17);border-color:rgba(45,212,191,.4);color:#99f6e4}
#trailerModal .modal-content,#screenshotModal .modal-content{background:linear-gradient(135deg,rgba(22,32,51,.98),rgba(15,23,42,.96))!important;border:1px solid var(--ui-border)!important;border-radius:.75rem}
#trailerModal .modal-header{border-bottom-color:var(--ui-border)!important}#trailerModal .modal-title{color:#fff;font-size:14px}#screenshotModalImg{display:block;width:100%;max-height:90vh!important;object-fit:contain}
.videoWrapper{position:relative;width:100%;padding-bottom:56.25%;overflow:hidden;border-radius:.6rem}.videoWrapper video{position:absolute;inset:0;width:100%;height:100%}
@media(max-width:768px){.game-page{margin-top:1rem!important}.media-card,.info-card,.feature-card,.screenshot-carousel{border-radius:.7rem}.media-card,.feature-card,.screenshot-carousel{padding:12px}.info-card{padding:15px}.game-title{font-size:18px}.feature-grid,.meta-grid{grid-template-columns:1fr}.screenshot-carousel .carousel-inner{height:220px}.game-mini-header{top:58px;width:calc(100% - 16px);padding:8px 9px}.mini-header-title{font-size:13px}.game-mini-header .btn-info{padding:5px 9px!important;font-size:12px}}
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