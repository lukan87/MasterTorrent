{{-- =========================
    PREMIUM FANART HEADER
========================= --}}

<div class="container px-xl-5 px-lg-4 px-3">

    <div class="premium-media-card">

        {{-- BACKDROP GLOW --}}
        <div class="premium-backdrop"></div>

        <div class="row g-4 align-items-start position-relative">

            {{-- =========================
                POSTER COLUMN
            ========================= --}}
            <div class="col-12 col-sm-4 col-md-3 col-lg-3">

                <div class="premium-poster-wrapper {{ $torrent->trashed() ? 'deleted-poster' : '' }}">

                    <img
                        {{-- src="{{ $display['fanart']['poster'] ?? $display['poster'] ?? $torrent->poster }}" --}}
                        src="{{ $torrent->poster }}"
                        loading="lazy"
                        class="premium-poster"
                        alt="{{ $display['title'] }}"
                    >

                    {{-- Deleted --}}
                    @if($torrent->trashed())
                        <div class="deleted-overlay">
                            <span>Torrent Deleted</span>
                        </div>
                    @endif

                    {{-- Overlay --}}
                    <div class="poster-overlay"></div>

                    {{-- LOGO --}}
                    {{-- @if(!empty($fanartLogo))
                        <div class="fanart-logo">
                            <img src="{{ $display['fanart']['logo'] }}">
                        </div>
                    @endif --}}

                </div>

                {{-- TRAILER --}}
                <div class="premium-trailer-wrapper">

                    @if(!empty($torrent->trailer))

                        @php
                            $embedUrl = str_replace('watch?v=', 'embed/', $torrent->trailer);
                        @endphp

                        <a href="{{ $embedUrl }}?autoplay=1"
                           data-lity
                           class="premium-trailer-btn">

                            <i class="bi bi-play-circle-fill"></i>
                            Watch Trailer

                        </a>

                    @elseif(!empty($display['trailer']))

                        <a href="https://www.youtube.com/embed/{{ $display['trailer']['key'] }}?autoplay=1"
                           data-lity
                           class="premium-trailer-btn">

                            <i class="bi bi-play-circle-fill"></i>
                            Watch Trailer

                        </a>

                    @else

                        <div class="no-trailer">
                            <i class="bi bi-film"></i>
                            No trailer available
                        </div>

                    @endif

                </div>

            </div>

            {{-- =========================
                INFO COLUMN
            ========================= --}}
            <div class="col-12 col-sm-8 col-md-9 col-lg-9">

                <div class="premium-info">

                    {{-- TITLE --}}
                    <div class="title-block">

                        <h1 class="premium-title">

                            {{ $display['title'] }}

                            @if($display['year'])
                                <span class="release-year">
                                    ({{ $display['year'] }})
                                </span>
                            @endif

                        </h1>

                        @if($display['tagline'])
                            <p class="premium-tagline">
                                {{ $display['tagline'] }}
                            </p>
                        @endif

                    </div>

                    {{-- META --}}
                    <div class="premium-meta-row">

                        @if($display['ratings']['rated'])

                            <div class="meta-pill">
                                {!! $display['type'] === 'tv'
                                    ? getTVRatingBadge($display['ratings']['rated'])
                                    : getRatingBadge($display['ratings']['rated']) !!}
                            </div>

                        @endif

                        @if($display['runtime'])

                            <div class="meta-pill">
                                <i class="bi bi-clock"></i>

                                {{ $display['type'] === 'tv'
                                    ? $display['runtime'].'m / episode'
                                    : intdiv($display['runtime'], 60).'h '.($display['runtime'] % 60).'m' }}
                            </div>

                        @endif

                        @if($display['type'] === 'tv' && $display['seasons'])

                            <div class="meta-pill">
                                <i class="bi bi-collection-play"></i>
                                {{ $display['seasons'] }} Seasons
                            </div>

                        @endif

                        @if($display['type'] === 'tv' && $display['episodes'])

                            <div class="meta-pill">
                                <i class="bi bi-tv"></i>
                                {{ $display['episodes'] }} Episodes
                            </div>

                        @endif

                        @if($display['status'])

                            <div class="meta-pill">
                                <i class="bi bi-info-circle"></i>
                                {{ $display['status'] }}
                            </div>

                        @endif

                    </div>

                    {{-- GENRES --}}
                    <div class="premium-genres">

                        @foreach($torrent->genres as $genre)

                            <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}"
                               class="premium-genre-tag">

                                {{ $genre->name }}

                            </a>

                        @endforeach

                    </div>

                    {{-- RATINGS --}}
                    <div class="premium-ratings">

                        @if($display['ratings']['tmdb'] && $torrent->tmdbid)

                            <a href="https://www.themoviedb.org/{{ $display['type'] }}/{{ $torrent->tmdbid }}"
                               target="_blank"
                               class="rating-card tmdb-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['tmdb'] }}
                                </div>

                                <div class="rating-source">
                                    TMDB
                                </div>

                            </a>

                        @endif

                        @if($display['ratings']['imdb'] && $torrent->imdbid)

                            <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}"
                               target="_blank"
                               class="rating-card imdb-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['imdb'] }}
                                </div>

                                <div class="rating-source">
                                    IMDb
                                </div>

                            </a>

                        @endif

                        @if($display['ratings']['rt'])

                            <div class="rating-card rt-card">

                                <div class="rating-value">
                                    {{ $display['ratings']['rt'] }}
                                </div>

                                <div class="rating-source">
                                    RT
                                </div>

                            </div>

                        @endif

                    </div>

                    

                    {{-- OVERVIEW --}}
                    <div class="premium-overview">

                        <h5>
                            {{ $display['overview'] }}
                        </h5>

                    </div>

                    {{-- CREW --}}
                    <div class="premium-facts">

                        @if($display['type'] === 'movie' && $display['director'])

                            <div class="fact-box">

                                <span class="fact-label">
                                    Director
                                </span>

                                <span class="fact-value">
                                    {{ $display['director'] }}
                                </span>

                            </div>

                        @endif

                        @if($display['type'] === 'tv' && !empty($display['creators']))

                            <div class="fact-box">

                                <span class="fact-label">
                                    Creator
                                </span>

                                <span class="fact-value">
                                    {{ implode(', ', $display['creators']) }}
                                </span>

                            </div>

                        @endif

                    </div>

                    {{-- NETWORKS --}}
                    @if(!empty($display['networks']))

                        <div class="premium-network-section">

                            <div class="network-title">

                                <i class="bi bi-building"></i>

                                {{ $display['type'] === 'tv'
                                    ? 'Networks'
                                    : 'Production' }}

                            </div>

                            <div class="network-grid">

                                @foreach($display['networks'] as $n)

                                    @if($n['logo'])

                                        <div class="network-logo-card">

                                            <img
                                                src="{{ $n['logo'] }}"
                                                class="network-logo"
                                                title="{{ $n['name'] }}"
                                            >

                                        </div>

                                    @endif

                                @endforeach

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

<style>

/* =========================================
   PREMIUM MEDIA HEADER (BALANCED)
========================================= */

.premium-media-card{
    position:relative;
    overflow:hidden;

    padding:28px;
    border-radius:22px;

    background:rgba(15, 15, 20, 0.224);
    backdrop-filter:blur(10px);

    border:1px solid rgba(255,255,255,.06);

    box-shadow:
        0 10px 40px rgba(0,0,0,.45);

    margin-top:25px;
}

/* =========================================
   TOP BANNER
========================================= */

.premium-banner-wrapper{
    display:flex;
    justify-content:center;
    margin-top:20px;
    margin-bottom:-30px;
    position:relative;
    z-index:5;
}

.premium-banner-card{
    padding:10px 18px;

    border-radius:16px;

    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(8px);

    box-shadow:
        0 8px 25px rgba(0,0,0,.35);
}

.premium-banner-card img{
    max-height:70px;
    width:auto;
    display:block;

    filter:drop-shadow(0 4px 12px rgba(0,0,0,.7));
}

/* =========================================
   POSTER
========================================= */

.premium-poster-wrapper{
    position:relative;
    overflow:hidden;

    border-radius:18px;

    box-shadow:
        0 12px 30px rgba(0,0,0,.45);
}

.premium-poster{
    width:100%;
    transition:transform .75s ease;
}

.premium-poster-wrapper:hover .premium-poster{
    transform:scale(1.13);
}

.poster-overlay{
    position:absolute;
    inset:0;

    background:
        linear-gradient(
            to top,
            rgba(0,0,0,.75),
            transparent 45%
        );
}

/* =========================================
   LOGO
========================================= */

.fanart-logo{
    position:absolute;

    left:12px;
    right:12px;
    bottom:12px;

    text-align:center;
}

.fanart-logo img{
    max-height:55px;
    max-width:100%;

    object-fit:contain;

    filter:drop-shadow(0 2px 10px rgba(0,0,0,.8));
}

/* =========================================
   TRAILER BUTTON
========================================= */

.premium-trailer-wrapper{
    margin-top:14px;
}

.premium-trailer-btn{
    width:100%;

    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;

    padding:11px 14px;

    border-radius:14px;

    background:
        linear-gradient(135deg,#e50914,#ff4d5a);

    color:#fff;
    text-decoration:none;
    font-weight:600;
    font-size:.95rem;

    transition:.2s ease;

    box-shadow:
        0 8px 20px rgba(229,9,20,.25);
}

.premium-trailer-btn:hover{
    transform:translateY(-2px);
    color:#fff;
}

/* =========================================
   INFO
========================================= */

.premium-info{
    padding-left:10px;
}

.premium-title{
    font-size:clamp(1.7rem,3vw,2.7rem);

    font-weight:800;
    line-height:1.15;

    margin-bottom:8px;

    overflow-wrap:anywhere;
}

.release-year{
    opacity:.65;
    font-weight:400;
}

.premium-tagline{
    color:rgba(255,255,255,.65);

    font-size:.95rem;
    font-style:italic;

    margin-bottom:20px;
}

/* =========================================
   META ROW
========================================= */

.premium-meta-row{
    display:flex;
    flex-wrap:wrap;
    gap:10px;

    margin-bottom:20px;
}

.meta-pill{
    display:flex;
    align-items:center;
    gap:6px;

    padding:7px 12px;

    border-radius:999px;

    background:rgba(255,255,255,.05);

    border:1px solid rgba(255,255,255,.06);

    color:#fff;

    font-size:.85rem;
    font-weight:600;
}

/* =========================================
   GENRES
========================================= */

.premium-genres{
    display:flex;
    flex-wrap:wrap;
    gap:10px;

    margin-bottom:22px;
}

.premium-genre-tag{
    padding:7px 14px;

    border-radius:999px;

    text-decoration:none;

    background:
        linear-gradient(
            135deg,
            rgba(0,123,255,.16),
            rgba(111,66,193,.16)
        );

    border:1px solid rgba(255,255,255,.05);

    color:#fff;

    font-size:.82rem;
    font-weight:600;

    transition:.2s ease;
}

.premium-genre-tag:hover{
    color:#fff;
    transform:translateY(-1px);
}

/* =========================================
   RATINGS
========================================= */

.premium-ratings{
    display:flex;
    flex-wrap:wrap;
    gap:12px;

    margin-bottom:24px;
}

.rating-card{
    min-width:88px;

    padding:12px 14px;

    border-radius:16px;

    text-align:center;
    text-decoration:none;

    background:rgba(255,255,255,.05);

    border:1px solid rgba(255,255,255,.06);

    color:#fff;

    transition:.2s ease;
}

.rating-card:hover{
    transform:translateY(-2px);
    color:#fff;
}

.rating-value{
    font-size:1.3rem;
    font-weight:800;
}

.rating-source{
    font-size:.72rem;
    opacity:.7;
}

/* =========================================
   OVERVIEW
========================================= */

.premium-overview{
    margin-bottom:24px;
}

.premium-overview h5{
    font-size:1.25rem;
    line-height:1.7;
    font-weight:700;

    color:rgba(255,255,255,.84);
}

/* =========================================
   FACTS
========================================= */

.premium-facts{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}

.fact-box{
    padding:12px 16px;

    border-radius:16px;

    background:rgba(255,255,255,.04);

    border:1px solid rgba(255,255,255,.05);
}

.fact-label{
    display:block;

    font-size:.68rem;

    text-transform:uppercase;
    letter-spacing:1px;

    color:rgba(255,255,255,.5);

    margin-bottom:4px;
}

.fact-value{
    font-size:.9rem;
    font-weight:600;
}

/* =========================================
   NETWORKS
========================================= */

.premium-network-section{
    margin-top:26px;
}

.network-title{
    display:flex;
    align-items:center;
    gap:8px;

    margin-bottom:12px;

    font-size:1rem;
    font-weight:700;
}

.network-grid{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

.network-logo-card{
    padding:10px 14px;

    border-radius:14px;

    background:rgba(255,255,255,.04);

    border:1px solid rgba(255,255,255,.05);
}

.network-logo{
    height:26px;
    object-fit:contain;
}

/* =========================================
   DELETED
========================================= */

.deleted-poster img{
    filter:
        grayscale(100%)
        brightness(.5);
}

.deleted-overlay{
    position:absolute;
    inset:0;

    display:flex;
    align-items:center;
    justify-content:center;
}

.deleted-overlay span{
    font-size:1rem;
    font-weight:800;

    color:#ff4d4d;

    border:2px solid rgba(255,0,0,.8);

    padding:8px 14px;

    background:rgba(0,0,0,.5);

    transform:rotate(-12deg);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .premium-media-card{
        padding:18px;
        border-radius:18px;
    }

    .premium-info{
        padding-left:0;
    }

    .premium-title{
        font-size:1.8rem;
        text-align:center;
    }

    .premium-tagline{
        text-align:center;
    }

    .premium-meta-row,
    .premium-genres,
    .premium-ratings,
    .premium-facts{
        justify-content:center;
    }

    .premium-overview h5{
        text-align:center;
    }

    .premium-banner-card img{
        max-height:50px;
    }
}
</style>