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
   FILEIPLAY REQUEST SHOW
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.request-show-page {
    color: #e2e8f0;
}

.request-hero,
.modern-card {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px rgba(0,0,0,.18);
}

.request-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    bottom: 50px;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.05rem 1.2rem;
    border-left: 3px solid var(--ui-accent, #22d3ee);
    border-radius: .85rem;
}

.request-hero::after {
    content: "";
    position: absolute;
    top: -100px;
    right: -90px;
    width: 210px;
    height: 210px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34,211,238,.09), transparent 70%);
    pointer-events: none;
}

.hero-left {
    min-width: 0;
}

.request-kicker {
    margin-bottom: .3rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.request-title {
    margin: 0;
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.request-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .55rem;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .55rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .5rem;
    background: rgba(255,255,255,.035);
    color: rgba(226,232,240,.68);
    font-size: 12px;
}

.meta-pill i {
    color: var(--ui-accent, #22d3ee);
}

.hero-status {
    position: relative;
    z-index: 1;
    flex: 0 0 auto;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .7rem;
    border-radius: .55rem;
    font-size: 12px;
    font-weight: 700;
}

.status-badge.filled {
    border: 1px solid rgba(34,197,94,.22);
    background: rgba(34,197,94,.09);
    color: #86efac;
}

.status-badge.open {
    border: 1px solid rgba(34,211,238,.22);
    background: rgba(34,211,238,.08);
    color: var(--ui-accent, #22d3ee);
}

/* Cards */

.modern-card {
    overflow: visible;
    border-radius: .95rem;
}

.modern-card-header {
    padding: .75rem .95rem;
    border-bottom: 1px solid var(--ui-border, rgba(255,255,255,.08));
}

.modern-card-header h5 {
    margin: 0;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
}

.modern-card-header .text-info {
    color: var(--ui-accent, #22d3ee) !important;
}

.modern-card-header .text-success {
    color: #86efac !important;
}

.modern-card-header .text-primary {
    color: var(--ui-accent, #22d3ee) !important;
}

.modern-card-body {
    padding: .95rem;
}

/* Poster */

.poster-card {
    height: 100%;
}

.poster-wrapper {
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: .95rem;
    min-height: 100%;
    overflow: visible;
}

.request-poster {
    width: auto;
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: .65rem;
    object-fit: contain;
    box-shadow: 0 12px 28px rgba(0,0,0,.35);
    transition: transform .2s ease;
}

.request-poster:hover {
    transform: scale(1.015);
}

.no-poster {
    width: 100%;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px dashed var(--ui-border, rgba(255,255,255,.08));
    border-radius: .65rem;
    background: rgba(255,255,255,.025);
    color: rgba(226,232,240,.25);
    font-size: 2.5rem;
}

/* External links */

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .6rem;
}

.info-link {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .7rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .65rem;
    background: rgba(255,255,255,.025);
    color: #f8fafc;
    text-decoration: none;
    transition: border-color .2s ease, background .2s ease, transform .2s ease;
}

.info-link:hover {
    transform: translateY(-1px);
    border-color: rgba(34,211,238,.28);
    background: rgba(34,211,238,.055);
    color: #fff;
}

.info-icon {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .5rem;
    background: rgba(34,211,238,.07);
    color: var(--ui-accent, #22d3ee);
    font-size: 14px;
}

.info-title {
    color: #f8fafc;
    font-size: 13px;
    font-weight: 700;
}

.info-subtitle {
    margin-top: .1rem;
    color: rgba(226,232,240,.45);
    font-size: 11px;
}

/* Description */

.description-box {
    padding: .8rem;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-left: 2px solid rgba(34,211,238,.38);
    border-radius: .65rem;
    background: rgba(255,255,255,.025);
}

.description-title {
    margin-bottom: .45rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
}

.description-text {
    margin: 0;
    color: rgba(226,232,240,.75);
    font-size: 13px;
    line-height: 1.6;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

/* Status */

.status-box {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .85rem;
    border-radius: .7rem;
}

.success-box {
    border: 1px solid rgba(34,197,94,.18);
    background: rgba(34,197,94,.06);
}

.pending-box {
    border: 1px solid rgba(34,211,238,.18);
    background: rgba(34,211,238,.045);
}

.status-icon {
    flex: 0 0 auto;
    font-size: 1.35rem;
}

.success-box .status-icon {
    color: #86efac;
}

.pending-box .status-icon {
    color: var(--ui-accent, #22d3ee);
}

.status-title {
    margin-bottom: .2rem;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
}

.status-subtitle {
    color: rgba(226,232,240,.55);
    font-size: 12px;
    line-height: 1.5;
}

.status-subtitle strong {
    color: #e2e8f0;
}

/* Form */

.modern-label {
    display: block;
    margin-bottom: .4rem;
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 700;
}

.modern-input {
    min-height: 40px;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
    border-radius: .6rem !important;
    background: rgba(255,255,255,.035) !important;
    color: #f8fafc !important;
    font-size: 13px;
    box-shadow: none !important;
}

.modern-input:focus {
    border-color: rgba(34,211,238,.45) !important;
    background: rgba(34,211,238,.035) !important;
    box-shadow: 0 0 0 3px rgba(34,211,238,.08) !important;
}

.modern-input::placeholder {
    color: rgba(226,232,240,.35);
}

/* Buttons */

.torrent-link-btn,
.modern-submit-btn,
.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .5rem .75rem;
    border-radius: .55rem;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: all .2s ease;
}

.torrent-link-btn {
    margin-top: .7rem;
    border: 1px solid rgba(34,211,238,.28);
    background: rgba(34,211,238,.09);
    color: var(--ui-accent, #22d3ee);
}

.torrent-link-btn:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.15);
    color: #fff;
    transform: translateY(-1px);
}

.modern-submit-btn {
    border: 1px solid rgba(34,211,238,.28);
    background: rgba(34,211,238,.10);
    color: var(--ui-accent, #22d3ee);
}

.modern-submit-btn:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.16);
    color: #fff;
    transform: translateY(-1px);
}

.back-btn {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: rgba(255,255,255,.035);
    color: rgba(226,232,240,.72);
}

.back-btn:hover {
    border-color: rgba(34,211,238,.28);
    background: rgba(34,211,238,.06);
    color: #fff;
}

/* Mobile */

@media (max-width: 991.98px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767.98px) {
    .request-hero {
        align-items: flex-start;
        padding: .9rem;
    }

    .request-title {
        font-size: 18px;
    }

    .hero-status {
        width: 100%;
    }

    .status-badge {
        width: 100%;
        justify-content: center;
    }

    .modern-card-body {
        padding: .8rem;
    }

    .poster-wrapper {
        min-height: 0;
    }

    .request-poster {
        max-height: 520px;
    }

    .status-box {
        padding: .75rem;
    }

    .torrent-link-btn,
    .modern-submit-btn,
    .back-btn {
        width: 100%;
    }
}
</style>

@endsection