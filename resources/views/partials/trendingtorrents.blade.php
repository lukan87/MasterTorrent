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

                <div class="row g-3">

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

                                    {{-- Glow --}}
                                    <div class="modern-tt-glow"></div>

                                    {{-- Poster --}}
                                    <div class="modern-tt-image-wrap">

                                        <img src="{{ $image ?? '/images/noimage.jpg' }}"
                                             class="modern-tt-poster"
                                             alt="{{ $torrent->name }}">

                                        {{-- Rank --}}
                                        <div class="modern-tt-rank">

                                            #{{ $index + 1 }}

                                        </div>

                                        {{-- Overlay --}}
                                        <div class="modern-tt-overlay">

                                            <div class="overlay-stats">

                                                <span class="overlay-seeders">

                                                    <i class="bi bi-arrow-up-circle-fill"></i>

                                                    {{ $torrent->seeders }}

                                                </span>

                                                <span class="overlay-leechers">

                                                    <i class="bi bi-arrow-down-circle-fill"></i>

                                                    {{ $torrent->leechers }}

                                                </span>

                                            </div>

                                        </div>

                                    </div>

                                    {{-- CONTENT --}}
                                    <div class="modern-tt-content">

                                        <div class="modern-tt-title">

                                            {{ $torrent->name }}

                                        </div>

                                        <div class="modern-tt-stats">

                                            <span class="modern-tt-seeders">

                                                ↑ {{ $torrent->seeders }}

                                            </span>

                                            <span class="modern-tt-leechers">

                                                ↓ {{ $torrent->leechers }}

                                            </span>

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

/* =========================================
   WRAPPER
========================================= */

.modern-trending-wrapper{

    overflow:hidden;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    backdrop-filter:blur(18px);

    box-shadow:
        0 18px 50px rgba(0,0,0,.28);
}

/* =========================================
   HEADER
========================================= */

.modern-trending-header{

    padding:22px 26px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.modern-trending-icon{

    width:52px;
    height:52px;

    border-radius:18px;

    display:flex;

    align-items:center;
    justify-content:center;

    background:
        linear-gradient(
            135deg,
            #f97316,
            #ef4444
        );

    color:white;

    font-size:1.2rem;

    box-shadow:
        0 10px 24px rgba(249,115,22,.28);
}

.modern-trending-title{

    margin:0;

    color:white;

    font-size:1.2rem;

    font-weight:800;
}

.modern-trending-subtitle{

    color:rgba(255,255,255,.55);

    font-size:.88rem;

    margin-top:2px;
}

.modern-trending-toggle{

    width:42px;
    height:42px;

    border:none;

    border-radius:14px;

    background:
        rgba(255,255,255,.05);

    color:#cbd5e1;

    transition:.2s ease;
}

.modern-trending-toggle:hover{

    background:
        rgba(255,255,255,.08);

    color:white;
}

/* =========================================
   BODY
========================================= */

.modern-trending-body{

    padding:24px;
}

/* =========================================
   CARD
========================================= */

.modern-tt-card{

    position:relative;

    overflow:hidden;

    border-radius:22px;

    background:
        rgba(255,255,255,.03);

    border:
        1px solid rgba(255,255,255,.05);

    transition:.28s ease;

    height:100%;
}

.modern-tt-card:hover{

    transform:
        translateY(-4px);

    box-shadow:
        0 16px 35px rgba(0,0,0,.3);
}

.modern-tt-glow{

    position:absolute;

    top:-80px;
    right:-80px;

    width:180px;
    height:180px;

    border-radius:50%;

    background:
        radial-gradient(
            circle,
            rgba(249,115,22,.12),
            transparent 70%
        );

    z-index:1;
}

/* =========================================
   IMAGE
========================================= */

.modern-tt-image-wrap{

    position:relative;

    overflow:hidden;

    z-index:2;
}

.modern-tt-poster{

    width:100%;

    height:250px;

    object-fit:cover;

    transition:
        transform .35s ease;
}

.modern-tt-card:hover .modern-tt-poster{

    transform:scale(1.04);
}

/* =========================================
   RANK
========================================= */

.modern-tt-rank{

    position:absolute;

    top:12px;
    left:12px;

    padding:6px 10px;

    border-radius:12px;

    background:
        linear-gradient(
            135deg,
            #facc15,
            #f59e0b
        );

    color:#111;

    font-size:.82rem;

    font-weight:800;

    box-shadow:
        0 6px 18px rgba(245,158,11,.3);
}

/* =========================================
   OVERLAY
========================================= */

.modern-tt-overlay{

    position:absolute;

    inset:0;

    background:
        linear-gradient(
            to top,
            rgba(0,0,0,.72),
            transparent 45%
        );

    display:flex;

    align-items:flex-end;

    justify-content:center;

    padding:14px;

    opacity:0;

    transition:.25s ease;
}

.modern-tt-card:hover .modern-tt-overlay{

    opacity:1;
}

.overlay-stats{

    display:flex;

    gap:14px;

    font-size:.88rem;

    font-weight:700;
}

.overlay-seeders{

    color:#4ade80;
}

.overlay-leechers{

    color:#f87171;
}

/* =========================================
   CONTENT
========================================= */

.modern-tt-content{

    position:relative;

    z-index:2;

    padding:16px;
}

.modern-tt-title{

    color:white;

    font-size:1rem;

    font-weight:700;

    line-height:1.45;

    overflow:hidden;

    display:-webkit-box;

    -webkit-line-clamp:2;

    -webkit-box-orient:vertical;

    min-height:46px;
}

.modern-tt-stats{

    display:flex;

    align-items:center;

    gap:14px;

    margin-top:10px;

    font-size:.88rem;

    font-weight:700;
}

.modern-tt-seeders{

    color:#4ade80;
}

.modern-tt-leechers{

    color:#f87171;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .modern-trending-header{

        padding:18px;
    }

    .modern-trending-body{

        padding:18px;
    }

    .modern-tt-poster{

        height:210px;
    }

    .modern-tt-content{

        padding:14px;
    }

    .modern-tt-title{

        font-size:1rem;

        min-height:42px;
    }

    .modern-tt-stats{

        font-size:.8rem;
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