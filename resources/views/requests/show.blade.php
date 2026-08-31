@extends('layouts.app')

@section('content')

<div class="request-show-page">

    <div class="container py-5">

        {{-- =========================================
            HERO HEADER
        ========================================= --}}
        <div class="request-hero mb-4">

            <div class="hero-left">

                <div class="request-kicker">

                    COMMUNITY REQUEST

                </div>

                <h1 class="request-title">

                    {{ $request->name }}

                </h1>

                <div class="request-meta">

                    @if ($request->category)

                        <span class="meta-pill">

                            <i class="bi bi-tags-fill"></i>

                            {{ $request->category->name }}

                        </span>

                    @endif

                    <span class="meta-pill">

                        <i class="bi bi-calendar3"></i>

                        {{ $request->created_at->format('d M Y') }}

                    </span>

                    <span class="meta-pill">

                        <i class="bi bi-person-circle"></i>

                        {{ $request->requester->name ?? 'Unknown' }}

                    </span>

                </div>

            </div>

            <div class="hero-status">

                @if ($request->filled === 'yes')

                    <div class="status-badge filled">

                        <i class="bi bi-check-circle-fill"></i>

                        Filled

                    </div>

                @else

                    <div class="status-badge open">

                        <i class="bi bi-clock-history"></i>

                        Open Request

                    </div>

                @endif

            </div>

        </div>

        <div class="row g-4">

            {{-- =========================================
                POSTER
            ========================================= --}}
            <div class="col-lg-3 col-md-4">

                <div class="modern-card poster-card">

                    <div class="poster-wrapper">

                        @if ($request->image)

                            <img src="{{ $request->image }}"
                                 class="request-poster"
                                 alt="{{ $request->name }}">

                        @else

                            <div class="no-poster">

                                <i class="bi bi-image"></i>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

            {{-- =========================================
                DETAILS
            ========================================= --}}
            <div class="col-lg-9 col-md-8">

                <div class="modern-card h-100">

                    <div class="modern-card-header">

                        <h5>

                            <i class="bi bi-info-circle-fill text-info me-2"></i>

                            Request Information

                        </h5>

                    </div>

                    <div class="modern-card-body">

                        {{-- LINKS --}}
                        <div class="info-grid">

                            @if ($request->imdb_url)

                                <a href="{{ $request->imdb_url }}"
                                   target="_blank"
                                   class="info-link imdb-link">

                                    <div class="info-icon">

                                        <i class="bi bi-film"></i>

                                    </div>

                                    <div>

                                        <div class="info-title">

                                            IMDb

                                        </div>

                                        <div class="info-subtitle">

                                            Open IMDb Page

                                        </div>

                                    </div>

                                    <i class="bi bi-box-arrow-up-right ms-auto"></i>

                                </a>

                            @endif

                            @if ($request->tmdb_url)

                                <a href="{{ $request->tmdb_url }}"
                                   target="_blank"
                                   class="info-link tmdb-link">

                                    <div class="info-icon">

                                        <i class="bi bi-camera-reels-fill"></i>

                                    </div>

                                    <div>

                                        <div class="info-title">

                                            TMDB

                                        </div>

                                        <div class="info-subtitle">

                                            Open TMDB Page

                                        </div>

                                    </div>

                                    <i class="bi bi-box-arrow-up-right ms-auto"></i>

                                </a>

                            @endif

                            @if ($request->steam_url)

                                <a href="{{ $request->steam_url }}"
                                   target="_blank"
                                   class="info-link steam-link">

                                    <div class="info-icon">

                                        <i class="bi bi-steam"></i>

                                    </div>

                                    <div>

                                        <div class="info-title">

                                            Steam

                                        </div>

                                        <div class="info-subtitle">

                                            Open Steam Store

                                        </div>

                                    </div>

                                    <i class="bi bi-box-arrow-up-right ms-auto"></i>

                                </a>

                            @endif

                        </div>

                        {{-- DESCRIPTION --}}
                        @if ($request->description)

                            <div class="description-box mt-4">

                                <div class="description-title">

                                    <i class="bi bi-card-text me-2"></i>

                                    Description

                                </div>

                                <p class="description-text">

                                    {{ $request->description }}

                                </p>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

        {{-- =========================================
            STATUS CARD
        ========================================= --}}
        <div class="modern-card mt-4">

            <div class="modern-card-header">

                <h5>

                    <i class="bi bi-check2-circle text-success me-2"></i>

                    Request Status

                </h5>

            </div>

            <div class="modern-card-body">

                @if ($request->filled === 'yes')

                    <div class="status-box success-box">

                        <div class="status-icon">

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                        <div>

                            <div class="status-title">

                                Request Filled Successfully

                            </div>

                            <div class="status-subtitle">

                                @if($request->filledBy)

                                    Filled by
                                    <strong>{{ $request->filledBy->name }}</strong>

                                @endif

                                • {{ $request->updated_at->format('d M Y') }}

                            </div>

                            @if ($request->link)

                                <a href="{{ $request->link }}"
                                   target="_blank"
                                   class="torrent-link-btn">

                                    <i class="bi bi-download me-2"></i>

                                    Download Torrent

                                </a>

                            @endif

                        </div>

                    </div>

                @else

                    <div class="status-box pending-box">

                        <div class="status-icon">

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <div>

                            <div class="status-title">

                                This request has not been filled yet.

                            </div>

                            <div class="status-subtitle">

                                Uploaders can fill this request by posting a torrent link.

                            </div>

                        </div>

                    </div>

                @endif

            </div>

        </div>

        {{-- =========================================
            FILL REQUEST
        ========================================= --}}
        @if ($request->filled === 'no')

            <div class="modern-card mt-4">

                <div class="modern-card-header">

                    <h5>

                        <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>

                        Fill This Request

                    </h5>

                </div>

                <div class="modern-card-body">

                    <form action="{{ route('requests.fill', $request->id) }}"
                          method="POST">

                        @csrf

                        <div class="mb-4">

                            <label class="modern-label">

                                Torrent Link

                            </label>

                            <input type="text"
                                   class="form-control modern-input"
                                   name="link"
                                   placeholder="Paste torrent URL here..."
                                   required>

                        </div>

                        <button class="btn modern-submit-btn">

                            <i class="bi bi-check-circle-fill me-2"></i>

                            Fill Request

                        </button>

                    </form>

                </div>

            </div>

        @endif

        {{-- BACK --}}
        <div class="mt-4 text-center">

            <a href="{{ route('requests.index') }}"
               class="back-btn">

                <i class="bi bi-arrow-left-circle me-2"></i>

                Back to Requests

            </a>

        </div>

    </div>

</div>

<style>

/* =========================================
   BACKGROUND
========================================= */

body{

    background:
        radial-gradient(
            circle at top,
            #172033,
            #0f172a 45%,
            #020617
        );

    min-height:100vh;
}

/* =========================================
   HERO
========================================= */

.request-hero{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding:32px;

    border-radius:28px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.06),
            rgba(255,255,255,.03)
        );

    border:
        1px solid rgba(255,255,255,.08);

    backdrop-filter:blur(16px);

    box-shadow:
        0 25px 60px rgba(0,0,0,.35);
}

.request-kicker{

    color:#60a5fa;

    font-size:.78rem;

    font-weight:800;

    letter-spacing:2px;

    margin-bottom:10px;
}

.request-title{

    color:white;

    font-size:2.4rem;

    font-weight:900;

    margin:0;

    line-height:1.15;
}

.request-meta{

    display:flex;

    flex-wrap:wrap;

    gap:10px;

    margin-top:16px;
}

.meta-pill{

    display:inline-flex;

    align-items:center;

    gap:7px;

    padding:9px 14px;

    border-radius:999px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid rgba(255,255,255,.06);

    color:#cbd5e1;

    font-size:.82rem;

    font-weight:700;
}

/* =========================================
   STATUS BADGE
========================================= */

.status-badge{

    display:flex;

    align-items:center;

    gap:10px;

    padding:14px 20px;

    border-radius:18px;

    font-weight:800;

    font-size:.95rem;
}

.status-badge.filled{

    background:
        rgba(34,197,94,.15);

    color:#4ade80;

    border:
        1px solid rgba(34,197,94,.2);
}

.status-badge.open{

    background:
        rgba(239,68,68,.15);

    color:#f87171;

    border:
        1px solid rgba(239,68,68,.2);
}

/* =========================================
   CARDS
========================================= */

.modern-card{

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.03)
        );

    border:
        1px solid rgba(255,255,255,.07);

    border-radius:24px;

    overflow:hidden;

    backdrop-filter:blur(14px);

    box-shadow:
        0 20px 50px rgba(0,0,0,.3);
}

.modern-card-header{

    padding:22px 28px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    color:white;
}

.modern-card-body{

    padding:28px;
}

/* =========================================
   POSTER
========================================= */

..poster-wrapper{

    position:relative;

    width:100%;

    display:flex;

    align-items:flex-start;

    justify-content:center;

    padding:14px;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.05),
            rgba(255,255,255,.02)
        );

    border:
        1px solid rgba(255,255,255,.06);

    overflow:visible;
}

.request-poster{

    width:auto;

    max-width:100%;

    height:auto;

    max-height:none;

    display:block;

    border-radius:18px;

    object-fit:contain;

    box-shadow:
        0 15px 40px rgba(0,0,0,.45);

    transition:.3s ease;
}

.request-poster:hover{

    transform:scale(1.02);
}

.no-poster{

    min-height:380px;

    display:flex;

    align-items:center;
    justify-content:center;

    color:rgba(255,255,255,.35);

    font-size:4rem;
}

/* =========================================
   LINKS
========================================= */

.info-grid{

    display:flex;

    flex-direction:column;

    gap:14px;
}

.info-link{

    display:flex;

    align-items:center;

    gap:16px;

    padding:18px;

    border-radius:18px;

    text-decoration:none;

    transition:.2s ease;

    border:
        1px solid rgba(255,255,255,.06);

    color:white;
}

.info-link:hover{

    transform:translateY(-2px);

    color:white;
}

.imdb-link{

    background:
        rgba(245,197,24,.12);
}

.tmdb-link{

    background:
        rgba(1,180,228,.12);
}

.steam-link{

    background:
        rgba(23,26,33,.65);
}

.info-icon{

    width:52px;
    height:52px;

    border-radius:16px;

    display:flex;

    align-items:center;
    justify-content:center;

    font-size:1.3rem;

    background:
        rgba(255,255,255,.08);
}

.info-title{

    font-weight:800;

    font-size:1rem;
}

.info-subtitle{

    font-size:.82rem;

    color:rgba(255,255,255,.6);
}

/* =========================================
   DESCRIPTION
========================================= */

.description-box{

    padding:24px;

    border-radius:20px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);
}

.description-title{

    color:white;

    font-weight:800;

    margin-bottom:14px;
}

.description-text{

    margin:0;

    line-height:1.8;

    color:rgba(255,255,255,.78);
}

/* =========================================
   STATUS BOX
========================================= */

.status-box{

    display:flex;

    align-items:flex-start;

    gap:18px;

    padding:24px;

    border-radius:22px;
}

.success-box{

    background:
        rgba(34,197,94,.12);

    border:
        1px solid rgba(34,197,94,.18);
}

.pending-box{

    background:
        rgba(239,68,68,.12);

    border:
        1px solid rgba(239,68,68,.18);
}

.status-icon{

    font-size:2rem;
}

.success-box .status-icon{

    color:#4ade80;
}

.pending-box .status-icon{

    color:#f87171;
}

.status-title{

    color:white;

    font-size:1.1rem;

    font-weight:800;

    margin-bottom:6px;
}

.status-subtitle{

    color:rgba(255,255,255,.65);
}

/* =========================================
   BUTTONS
========================================= */

.torrent-link-btn,
.modern-submit-btn,
.back-btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    text-decoration:none;

    border:none;

    transition:.25s ease;
}

.torrent-link-btn{

    margin-top:18px;

    padding:12px 20px;

    border-radius:16px;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    color:white;

    font-weight:800;
}

.modern-submit-btn{

    padding:14px 24px;

    border-radius:18px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:white;

    font-weight:800;

    box-shadow:
        0 15px 35px rgba(59,130,246,.3);
}

.back-btn{

    padding:14px 22px;

    border-radius:18px;

    background:
        rgba(255,255,255,.06);

    border:
        1px solid rgba(255,255,255,.08);

    color:white;

    font-weight:700;
}

.torrent-link-btn:hover,
.modern-submit-btn:hover,
.back-btn:hover{

    transform:translateY(-3px);

    color:white;
}

/* =========================================
   INPUTS
========================================= */

.modern-label{

    display:block;

    margin-bottom:10px;

    color:#cbd5e1;

    font-size:.8rem;

    font-weight:800;

    letter-spacing:1px;

    text-transform:uppercase;
}

.modern-input{

    background:
        rgba(255,255,255,.04) !important;

    border:
        1px solid rgba(255,255,255,.08) !important;

    color:white !important;

    border-radius:18px !important;

    padding:14px 18px !important;
}

.modern-input:focus{

    border-color:
        rgba(59,130,246,.35) !important;

    box-shadow:
        0 0 0 4px rgba(59,130,246,.15) !important;

    background:
        rgba(255,255,255,.06) !important;
}

.modern-input::placeholder{

    color:rgba(255,255,255,.35);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .request-hero{

        padding:24px;
    }

    .request-title{

        font-size:1.7rem;
    }

    .modern-card-body{

        padding:22px;
    }

    .status-box{

        flex-direction:column;
    }

    .modern-submit-btn,
    .back-btn{

        width:100%;
    }
}

</style>

@endsection