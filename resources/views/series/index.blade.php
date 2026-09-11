@extends('layouts.app')

@section('content')

@php
    $backdrop = $featured && $featured->backdrop_path
        ? 'https://image.tmdb.org/t/p/original' . $featured->backdrop_path
        : 'https://image.tmdb.org/t/p/original';
@endphp

<div class="container-fluid px-lg-4 px-3 series-page">

    {{-- HERO --}}
    <div class="series-hero mb-4" style="background-image:url('{{ $backdrop }}')">
        <div class="hero-overlay"></div>

        <div class="hero-content position-relative z-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div>
                    <span class="hero-badge">STREAMING COLLECTION</span>

                    <h1 class="hero-title mt-3">Discover Amazing Series</h1>

                    <p class="hero-subtitle">
                        Browse, search and explore your TV collection.
                    </p>
                </div>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <a href="{{ route('series.create') }}" class="btn btn-modern">
                        <i class="bi bi-plus-lg me-1"></i> Add Series
                    </a>
                @endif

            </div>

            @if(isset($featured) && $featured)
                <div class="featured-box">
                    <span class="featured-label">Featured Series</span>
                    <h3>{{ $featured->name }}</h3>
                </div>
            @endif
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="search-wrapper mb-4">
        <form action="{{ route('series.search-series') }}" method="POST">
            @csrf

            <div class="search-box">
                <i class="bi bi-search search-icon"></i>

                <input
                    type="text"
                    name="name"
                    id="name"
                    class="search-input"
                    placeholder="Search for a TV Series..."
                    required
                >

                <button type="submit" class="search-btn">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>

    {{-- PAGINATION TOP --}}
    <div class="d-flex justify-content-center mb-4">
        {{ $series->links('pagination::bootstrap-5') }}
    </div>

    {{-- GRID --}}
    <div class="row g-3">

        @forelse ($series as $serie)

            <div class="col-6 col-md-4 col-lg-3 col-xl-2">

                <div class="series-card">

                    <a href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}">
                        <img
                            src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}"
                            alt="{{ $serie->name }}"
                            class="series-poster"
                        >
                    </a>

                    <div class="series-overlay">
                        <div>
                            <h5 class="series-title">{{ $serie->name }}</h5>
                        </div>

                        <div class="series-actions">

                            <a
                                href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}"
                                class="btn btn-watch"
                            >
                                <i class="bi bi-eye me-1"></i> View
                            </a>

                            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)

                                <form
                                    action="{{ route('series.destroy', $serie->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete {{ $serie->name }}?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-delete">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>

                            @endif

                        </div>
                    </div>

                </div>

                <div class="series-card-title">
                    {{ $serie->name }}
                </div>

            </div>

        @empty

            <div class="col-12">
                <div class="empty-state">
                    <i class="bi bi-tv"></i>
                    <h3>No Series Found</h3>
                    <p>Try searching for another title.</p>
                </div>
            </div>

        @endforelse

    </div>

    {{-- PAGINATION BOTTOM --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $series->links('pagination::bootstrap-5') }}
    </div>

</div>

<style>
    /* =========================================
       FILEIPLAY SERIES PAGE
       Dark glass + restrained teal accents
    ========================================= */

    .series-page {
        max-width: 1600px;
    }

    /* HERO */
    .series-hero {
        position: relative;
        min-height: 390px;
        margin-top: .35rem;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        background-size: cover;
        background-position: center top;
        border: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        border-radius: .9rem;
        box-shadow: 0 16px 38px rgba(0, 0, 0, .32);
    }

    .series-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        border-left: 3px solid var(--ui-accent, #22d3c5);
        pointer-events: none;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to top,
            rgba(5, 12, 22, .97) 0%,
            rgba(5, 12, 22, .86) 40%,
            rgba(5, 12, 22, .45) 72%,
            rgba(5, 12, 22, .2) 100%
        );
    }

    .hero-content {
        width: 100%;
        padding: 1.5rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        padding: .35rem .6rem;
        color: var(--ui-accent, #22d3c5);
        background: rgba(34, 211, 197, .08);
        border: 1px solid rgba(34, 211, 197, .22);
        border-radius: .5rem;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .8px;
    }

    .hero-title {
        margin-bottom: .35rem;
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }

    .hero-subtitle {
        max-width: 600px;
        margin-bottom: 0;
        color: #cbd5e1;
        font-size: 13px;
        line-height: 1.5;
    }

    /* FEATURED */
    .featured-box {
        max-width: 380px;
        margin-top: 1.1rem;
        padding: .8rem .9rem;
        background: rgba(15, 23, 42, .72);
        border: 1px solid rgba(148, 163, 184, .18);
        border-left: 2px solid var(--ui-accent, #22d3c5);
        border-radius: .65rem;
        backdrop-filter: blur(10px);
    }

    .featured-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .featured-box h3 {
        margin: .25rem 0 0;
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
    }

    /* BUTTON */
    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: .45rem .8rem;
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border: 1px solid var(--ui-accent, #22d3c5);
        border-radius: .55rem;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .18s ease;
    }

    .btn-modern:hover {
        color: #061311;
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    /* SEARCH */
    .search-wrapper {
        position: relative;
        z-index: 10;
        margin-top: -1rem;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-height: 48px;
        padding: .35rem .45rem .35rem .75rem;
        background: linear-gradient(135deg, rgba(22, 32, 51, .97), rgba(15, 23, 42, .9));
        border: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        border-radius: .75rem;
        box-shadow: 0 10px 28px rgba(0, 0, 0, .28);
    }

    .search-box:focus-within {
        border-color: var(--ui-accent, #22d3c5);
        box-shadow: 0 0 0 2px rgba(34, 211, 197, .08), 0 10px 28px rgba(0, 0, 0, .28);
    }

    .search-icon {
        flex: 0 0 auto;
        color: var(--ui-accent, #22d3c5);
        font-size: 14px;
    }

    .search-input {
        flex: 1;
        min-width: 0;
        height: 40px;
        padding: 0;
        color: #e2e8f0;
        background: transparent;
        border: 0;
        outline: 0;
        font-size: 14px;
    }

    .search-input::placeholder {
        color: #64748b;
    }

    .search-btn {
        flex: 0 0 auto;
        min-height: 36px;
        padding: .4rem .75rem;
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border: 1px solid var(--ui-accent, #22d3c5);
        border-radius: .5rem;
        font-size: 13px;
        font-weight: 600;
        transition: all .18s ease;
    }

    .search-btn:hover {
        color: #061311;
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    /* SERIES CARD */
    .series-card {
        position: relative;
        overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(148, 163, 184, .14);
        border-radius: .7rem;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .28);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .series-card:hover {
        transform: translateY(-3px);
        border-color: rgba(34, 211, 197, .28);
        box-shadow: 0 16px 30px rgba(0, 0, 0, .36);
    }

    .series-card > a {
        display: block;
    }

    .series-poster {
        display: block;
        width: 100%;
        aspect-ratio: 2 / 3;
        object-fit: cover;
        transition: transform .25s ease, filter .25s ease;
    }

    .series-card:hover .series-poster {
        transform: scale(1.035);
        filter: brightness(.78);
    }

    /* OVERLAY */
    .series-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: .8rem;
        background: linear-gradient(to top, rgba(5, 12, 22, .96), rgba(5, 12, 22, .08));
        opacity: 0;
        transition: opacity .2s ease;
    }

    .series-card:hover .series-overlay {
        opacity: 1;
    }

    .series-title {
        margin: 0 0 .65rem;
        color: #f8fafc;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
    }

    .series-actions {
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
    }

    .series-actions .btn {
        min-height: 32px;
        padding: .35rem .6rem;
        border-radius: .45rem;
        font-size: 12px;
        font-weight: 600;
    }

    .btn-watch {
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border: 1px solid var(--ui-accent, #22d3c5);
    }

    .btn-watch:hover {
        color: #061311;
        filter: brightness(1.06);
    }

    .btn-delete {
        color: #fecaca;
        background: rgba(127, 29, 29, .72);
        border: 1px solid rgba(248, 113, 113, .25);
    }

    .btn-delete:hover {
        color: #fff;
        background: rgba(153, 27, 27, .85);
    }

    .series-card-title {
        display: -webkit-box;
        overflow: hidden;
        min-height: 35px;
        padding: .45rem .15rem 0;
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
        text-overflow: ellipsis;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .series-card:hover + .series-card-title {
        color: var(--ui-accent, #22d3c5);
    }

    /* EMPTY */
    .empty-state {
        position: relative;
        overflow: hidden;
        padding: 3rem 1.5rem;
        text-align: center;
        color: #94a3b8;
        background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
        border: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        border-radius: .85rem;
    }

    .empty-state::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--ui-accent, #22d3c5);
    }

    .empty-state i {
        display: block;
        margin-bottom: .65rem;
        color: var(--ui-accent, #22d3c5);
        font-size: 38px;
    }

    .empty-state h3 {
        margin: 0 0 .25rem;
        color: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
    }

    .empty-state p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
    }

    /* PAGINATION */
    .series-page .pagination {
        margin-bottom: 0;
    }

    .series-page .pagination .page-link {
        min-width: 34px;
        margin: 0 2px;
        padding: .4rem .6rem;
        color: #cbd5e1;
        background: rgba(22, 32, 51, .82);
        border: 1px solid var(--ui-border, rgba(148, 163, 184, .16));
        border-radius: .5rem;
        font-size: 13px;
        text-align: center;
        transition: all .18s ease;
    }

    .series-page .pagination .page-link:hover {
        color: var(--ui-accent, #22d3c5);
        background: rgba(34, 211, 197, .07);
        border-color: rgba(34, 211, 197, .3);
    }

    .series-page .pagination .page-item.active .page-link {
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border-color: var(--ui-accent, #22d3c5);
    }

    .series-page .pagination .page-item.disabled .page-link {
        color: #475569;
        background: rgba(15, 23, 42, .55);
        border-color: rgba(148, 163, 184, .1);
    }

    /* MOBILE */
    @media (max-width: 768px) {
        .series-page {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .series-hero {
            min-height: 340px;
            border-radius: .75rem;
        }

        .hero-content {
            padding: 1rem;
        }

        .search-wrapper {
            margin-top: -.65rem;
        }

        .search-box {
            gap: .45rem;
        }

        .search-btn {
            padding: .4rem .6rem;
        }
    }

    @media (max-width: 420px) {
        .hero-content > .d-flex {
            align-items: flex-start !important;
        }

        .btn-modern {
            width: 100%;
        }

        .search-btn {
            font-size: 12px;
        }
    }
</style>

@endsection
