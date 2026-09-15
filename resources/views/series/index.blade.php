@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 px-lg-4 px-3 series-page">

    {{-- HERO --}}
    @if(isset($featured) && $featured)
        <div class="series-hero mb-4" style="background-image:url('{{ $featured->backdrop_url }}')">
            <div class="hero-overlay"></div>

            <div class="hero-content position-relative z-2">
                <span class="hero-badge">FEATURED SERIES</span>

                <h1 class="hero-title mt-3">{{ $featured->name }}</h1>

                <p class="hero-subtitle">
                    <i class="bi bi-star-fill text-warning"></i> {{ number_format($featured->vote_average ?? 0, 1) }}
                    @if($featured->genres)
                        &nbsp;&bull;&nbsp; {{ collect($featured->genres)->take(3)->implode(' / ') }}
                    @endif
                    @if($featured->year)
                        &nbsp;&bull;&nbsp; {{ $featured->year }}
                    @endif
                    @if($featured->status)
                        &nbsp;&bull;&nbsp; {{ $featured->status === 'Ended' ? 'Completed' : $featured->status }}
                    @endif
                </p>

                <a href="{{ route('series.show', ['id'=>$featured->id,'slug'=>$featured->slug]) }}" class="btn btn-hero">
                    <i class="bi bi-play-circle me-1"></i> View Details
                </a>
            </div>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="series-header mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="series-page-title mb-1">
                    <i class="bi bi-tv me-2"></i>Series
                </h1>
                <p class="series-page-subtitle mb-0">Browse and discover your TV collection</p>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form action="{{ route('series.index') }}" method="GET" class="sort-form">
                    <div class="sort-wrapper">
                        <i class="bi bi-sort-down"></i>
                        <select name="sort" id="seriesSort" class="sort-select">
                            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Newest</option>
                            <option value="rating" @selected(($sort ?? '') === 'rating')>Top Rated</option>
                            <option value="views"  @selected(($sort ?? '') === 'views')>Most Viewed</option>
                        </select>
                    </div>
                </form>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <a href="{{ route('series.create') }}" class="btn btn-modern">
                        <i class="bi bi-plus-lg me-1"></i> Add Series
                    </a>
                @endif
            </div>
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
                            src="{{ $serie->poster_path ? 'https://image.tmdb.org/t/p/w600_and_h900_bestv2'.$serie->poster_path : '/images/noposter.jpg' }}"
                            loading="lazy"
                            alt="{{ $serie->name }}"
                            class="series-poster"
                        >
                    </a>

                    @if($serie->vote_average)
                        <span class="series-badge rating-badge">
                            <i class="bi bi-star-fill"></i> {{ number_format($serie->vote_average, 1) }}
                        </span>
                    @endif

                    @if($serie->year)
                        <span class="series-badge year-badge">{{ $serie->year }}</span>
                    @endif

                    <div class="series-overlay">
                        <div>
                            <h5 class="series-title">{{ $serie->name }}</h5>
                            @if($serie->status)
                                <span class="series-status-chip">{{ ucwords(str_replace('_', ' ', $serie->status)) }}</span>
                            @endif
                        </div>

                        <div class="series-actions">

                            <!-- <a
                                href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}"
                                class="btn btn-watch"
                            >
                                <i class="bi bi-eye me-1"></i> View
                            </a> -->

                            <!-- @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)

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

                            @endif -->

                        </div>
                    </div>

                </div>

                <div class="series-card-title">
                    {{ $serie->name }}
                </div>

                <div class="series-views">
                    <i class="bi bi-eye"></i> {{ number_format($serie->views ?? 0) }} views
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

<script>
    document.getElementById('seriesSort').addEventListener('change', function () {
        this.closest('form').submit();
    });
</script>

<style>
    /* =========================================
       FILEIPLAY SERIES PAGE
       Dark glass + restrained teal accents
    ========================================= */

    .series-page {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HERO */
    .series-hero {
        position: relative;
        min-height: 370px;
        margin-top: .35rem;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        background-size: cover;
        background-position: center top;
        background-color: #0f172a;
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
        padding: 1.1rem 1.5rem 1.5rem;
        max-width: 720px;
    }

    .hero-badge {
        display: inline-block;
        padding: .28rem .7rem;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 2px;
        color: #061311;
        background: var(--ui-accent, #22d3c5);
    }

    .hero-title {
        color: #f1f5f9;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: .35rem;
    }

    .hero-subtitle {
        max-width: 600px;
        margin-bottom: 1rem;
        color: #cbd5e1;
        font-size: 14px;
        line-height: 1.5;
    }

    .btn-hero {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .55rem 1.1rem;
        border-radius: .6rem;
        font-weight: 700;
        color: #061311;
        background: var(--ui-accent, #22d3c5);
        border: 0;
    }

    .btn-hero:hover {
        color: #061311;
        filter: brightness(1.08);
    }

    /* HEADER */
    .series-page-title {
        color: #f1f5f9;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.2;
    }

    .series-page-title i {
        color: var(--ui-accent, #22d3c5);
    }

    .series-page-subtitle {
        color: #94a3b8;
        font-size: 14px;
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
        pointer-events: none; /* let clicks reach the poster link underneath */
        transition: opacity .2s ease;
    }

    /* The overlay's own actions (View / Delete) stay clickable */
    .series-overlay a,
    .series-overlay button {
        pointer-events: auto;
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
        .hero-title {
            font-size: 1.5rem;
        }

        .btn-modern {
            width: 100%;
        }

        .search-btn {
            font-size: 12px;
        }
    }

    /* SORT */
    .sort-form {
        margin: 0;
    }

    .sort-wrapper {
        display: flex;
        align-items: center;
        gap: .5rem;
        height: 42px;
        padding: 0 .6rem;
        background: rgba(15, 23, 42, .72);
        border: 1px solid rgba(148, 163, 184, .17);
        border-radius: .6rem;
        color: var(--ui-accent, #22d3c5);
    }

    .sort-select {
        background: transparent;
        border: 0;
        outline: 0;
        color: #e2e8f0;
        font-size: 13px;
        cursor: pointer;
    }

    .sort-select option {
        background: #0f172a;
        color: #e2e8f0;
    }

    /* CARD BADGES */
    .series-badge {
        position: absolute;
        z-index: 3;
        padding: .22rem .5rem;
        border-radius: .4rem;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .35);
    }

    .series-badge.rating-badge {
        top: 8px;
        left: 8px;
        color: #061311;
        background: var(--ui-accent, #22d3c5);
    }

    .series-badge.rating-badge i {
        color: #061311;
    }

    .series-badge.year-badge {
        top: 8px;
        right: 8px;
        color: #f8fafc;
        background: rgba(5, 12, 22, .72);
        backdrop-filter: blur(4px);
    }

    .series-status-chip {
        display: inline-block;
        padding: .16rem .5rem;
        font-size: 10px;
        font-weight: 700;
        color: #f8fafc;
        background: rgba(34, 211, 197, .18);
        border: 1px solid rgba(34, 211, 197, .3);
        border-radius: 999px;
    }

    .series-views {
        margin-top: .3rem;
        color: #64748b;
        font-size: 11px;
    }

    .series-views i {
        color: var(--ui-accent, #22d3c5);
    }
</style>

@endsection
