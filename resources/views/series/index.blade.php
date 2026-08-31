@extends('layouts.app')

@section('content')

@php
    $backdrop = $featured && $featured->backdrop_path
        ? 'https://image.tmdb.org/t/p/original' . $featured->backdrop_path
        : 'https://image.tmdb.org/t/p/original';
@endphp

<div class="container-fluid px-lg-5 px-3">

    {{-- HERO --}}
    <div class="series-hero mb-5"
         style="background-image:url('{{ $backdrop }}')">

        <div class="hero-overlay"></div>

        <div class="hero-content position-relative z-2">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div>
                    <span class="hero-badge">
                        STREAMING COLLECTION
                    </span>

                    <h1 class="hero-title mt-3">
                        Discover Amazing Series
                    </h1>

                    <p class="hero-subtitle">
                        Browse, search and explore your TV collection.
                    </p>
                </div>

                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                    <a href="{{ route('series.create') }}"
                       class="btn btn-modern">
                        ➕ Add Series
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
    <div class="search-wrapper mb-5">

        <form action="{{ route('series.search-series') }}"
              method="POST">
            @csrf

            <div class="search-box">

                <div class="search-icon">
                    🔍
                </div>

                <input type="text"
                       name="name"
                       id="name"
                       class="search-input"
                       placeholder="Search for a TV Series..."
                       required>

                <button type="submit" class="search-btn">
                    Search
                </button>

            </div>
        </form>

    </div>

    {{-- PAGINATION TOP --}}
    <div class="d-flex justify-content-center mb-4">
        {{ $series->links('pagination::bootstrap-5') }}
    </div>

    {{-- GRID --}}
    <div class="row g-4">

        @forelse ($series as $serie)

            <div class="col-6 col-md-4 col-lg-3 col-xl-2">

                <div class="series-card">

                    {{-- IMAGE --}}
                    <a href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}">

                        <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $serie->poster_path }}"
                             alt="{{ $serie->name }}"
                             class="series-poster">

                    </a>

                    {{-- OVERLAY --}}
                    <div class="series-overlay">

                        <div>
                            <h5 class="series-title">
                                {{ $serie->name }}
                            </h5>
                        </div>

                        <div class="series-actions">

                            <a href="{{ route('series.show', ['id' => $serie->id, 'slug' => $serie->slug]) }}"
                               class="btn btn-watch">
                                View
                            </a>

                            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::WEB_DEVELOPER)

                                <form action="{{ route('series.destroy', $serie->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete {{ $serie->name }}?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-delete">
                                        Delete
                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        @empty

            <div class="col-12">
                <div class="empty-state">
                    <h3>No Series Found</h3>
                    <p>Try searching for another title.</p>
                </div>
            </div>

        @endforelse

    </div>

    {{-- PAGINATION BOTTOM --}}
    <div class="d-flex justify-content-center mt-5">
        {{ $series->links('pagination::bootstrap-5') }}
    </div>

</div>

<style>

/* PAGE */
body {
    background: #0b0f19;
}

/* HERO */

.series-hero {
    position: relative;
    min-height: 500px;
    border-radius: 28px;
    overflow: hidden;

    background-size: cover;
    background-position: center top;

    display: flex;
    align-items: flex-end;

    box-shadow:
        0 20px 60px rgba(0,0,0,0.7),
        0 0 120px rgba(0,0,0,0.4);

    margin-top: 20px;
}

.hero-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(to top,
            rgba(4,7,15,0.98) 0%,
            rgba(4,7,15,0.85) 35%,
            rgba(4,7,15,0.4) 70%,
            rgba(4,7,15,0.15) 100%);
}

.hero-content {
    width: 100%;
    padding: 60px;
}

.hero-badge {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.15);

    padding: 8px 14px;
    border-radius: 50px;

    font-size: 0.8rem;
    letter-spacing: 1px;
    color: #fff;

    backdrop-filter: blur(10px);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    color: #fff;

    line-height: 1.1;
}

.hero-subtitle {
    color: rgba(255,255,255,0.75);
    font-size: 1.1rem;
    max-width: 600px;
}

/* FEATURED */

.featured-box {
    margin-top: 40px;

    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);

    border: 1px solid rgba(255,255,255,0.08);

    padding: 20px;
    border-radius: 20px;

    max-width: 380px;
}

.featured-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 2px;

    color: rgba(255,255,255,0.6);
}

.featured-box h3 {
    color: #fff;
    margin-top: 10px;
    font-weight: 700;
}

/* BUTTONS */

.btn-modern {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff;

    border-radius: 14px;
    padding: 12px 22px;

    border: none;
    font-weight: 600;

    transition: 0.3s ease;
}

.btn-modern:hover {
    transform: translateY(-2px);
    color: #fff;

    box-shadow: 0 12px 30px rgba(124,58,237,0.4);
}

/* SEARCH */

.search-wrapper {
    margin-top: -35px;
    position: relative;
    z-index: 10;
}

.search-box {
    display: flex;
    align-items: center;

    background: rgba(18,24,38,0.85);

    border: 1px solid rgba(255,255,255,0.08);

    border-radius: 22px;

    padding: 12px 14px;

    backdrop-filter: blur(18px);

    box-shadow:
        0 10px 40px rgba(0,0,0,0.35);
}

.search-icon {
    font-size: 1.2rem;
    margin-right: 12px;
}

.search-input {
    flex: 1;

    background: transparent;
    border: none;
    outline: none;

    color: #fff;
    font-size: 1.05rem;
}

.search-input::placeholder {
    color: rgba(255,255,255,0.45);
}

.search-btn {
    background: linear-gradient(135deg, #2563eb, #7c3aed);

    border: none;
    color: #fff;

    border-radius: 14px;

    padding: 12px 24px;
    font-weight: 600;

    transition: 0.3s ease;
}

.search-btn:hover {
    transform: scale(1.03);
}

/* SERIES CARDS */

.series-card {
    position: relative;

    border-radius: 22px;
    overflow: hidden;

    transition: all 0.35s ease;

    background: #111827;
}

.series-card:hover {
    transform: translateY(-10px) scale(1.03);

    box-shadow:
        0 25px 50px rgba(0,0,0,0.5);
}

.series-poster {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform 0.4s ease;
}

.series-card:hover .series-poster {
    transform: scale(1.08);
}

/* OVERLAY */

.series-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(to top,
            rgba(0,0,0,0.98),
            rgba(0,0,0,0.15));

    display: flex;
    flex-direction: column;
    justify-content: flex-end;

    padding: 18px;

    opacity: 0;

    transition: opacity 0.3s ease;
}

.series-card:hover .series-overlay {
    opacity: 1;
}

.series-title {
    color: #fff;
    font-size: 1rem;
    font-weight: 700;

    margin-bottom: 15px;
}

/* ACTIONS */

.series-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-watch {
    background: #fff;
    color: #111827;

    border-radius: 12px;

    font-weight: 600;
    border: none;
}

.btn-delete {
    background: rgba(239,68,68,0.95);
    color: white;

    border: none;
    border-radius: 12px;

    font-weight: 600;
}

/* EMPTY */

.empty-state {
    background: rgba(255,255,255,0.04);

    border: 1px solid rgba(255,255,255,0.08);

    padding: 60px 20px;

    border-radius: 24px;

    text-align: center;
    color: #fff;
}

/* MOBILE */

@media(max-width:768px) {

    .series-hero {
        min-height: 380px;
        border-radius: 20px;
    }

    .hero-content {
        padding: 30px;
    }

    .hero-title {
        font-size: 2.3rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .search-box {
        flex-direction: column;
        gap: 12px;
    }

    .search-btn {
        width: 100%;
    }

    .featured-box {
        max-width: 100%;
    }
}

</style>

@endsection