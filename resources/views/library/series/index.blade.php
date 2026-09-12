@extends('layouts.app')

@section('content')

<div class="container-fluid py-4 px-3 px-md-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h4 class="fw-bold text-white mb-0">📺 Series Library</h4>

        <form method="GET" action="{{ route('library.series.index') }}" class="search-bar">
            <input 
                type="text" 
                name="q" 
                value="{{ $query ?? '' }}"
                placeholder="Search series..."
            >
            <button>🔍</button>
        </form>
    </div>

    {{-- SEARCH RESULT --}}
    @if(!empty($query))
        <p class="text-muted mb-3 small">
            Results for <strong class="text-white">{{ $query }}</strong>
        </p>
    @endif

    {{-- GRID --}}
    <div class="row gx-3 gy-4">

        @forelse($series as $item)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">

                <a href="{{ route('library.series.show', [
                    'tmdbid' => $item->tmdbid,
                    'slug' => $item->slug
                ]) }}" class="text-decoration-none">

                    <div class="movie-card">

                        {{-- POSTER --}}
                        <img 
                            src="{{ $item->poster 
                                ? 'https://image.tmdb.org/t/p/w500/' . $item->poster 
                                : asset('images/no-poster.png') }}"
                            alt="{{ $item->title }}"
                            loading="lazy"
                        >

                        {{-- HOVER OVERLAY --}}
                        <div class="movie-hover">

                            <div class="movie-hover-content">

                                <div class="movie-hover-title">
                                    {{ Str::limit($item->title, 40) }}
                                </div>

                                <div class="movie-hover-meta">
                                    @if($item->year)
                                        <span>{{ $item->year }}</span>
                                    @endif

                                    @if($item->rating)
                                        <span>⭐ {{ number_format($item->rating, 1) }}</span>
                                    @endif
                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        @empty
            <div class="col-12 text-center text-muted py-5">
                <h5>No series found 📺</h5>
            </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $series->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection


<style>

/* ===== BACKGROUND ===== */
body {
    background: #0b0b0b;
}

/* ===== CARD ===== */
.movie-card {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    background: #111;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

/* Poster */
.movie-card img {
    width: 100%;
    height: 360px;
    object-fit: cover;
    display: block;
    transition: transform 0.35s ease;
}

/* Hover lift */
.movie-card:hover {
    transform: scale(1.08);
    z-index: 5;
    box-shadow: 0 20px 40px rgba(0,0,0,0.7);
}

.movie-card:hover img {
    transform: scale(1.1);
}

/* ===== HOVER OVERLAY ===== */
.movie-hover {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: flex-end;

    background: linear-gradient(
        to top,
        rgba(0,0,0,0.95),
        rgba(0,0,0,0.2),
        transparent
    );

    opacity: 0;
    transition: opacity 0.25s ease;
}

.movie-card:hover .movie-hover {
    opacity: 1;
}

/* ===== HOVER CONTENT ===== */
.movie-hover-content {
    padding: 10px;
    width: 100%;
}

.movie-hover-title {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
}

.movie-hover-meta {
    font-size: 0.7rem;
    color: #ccc;
    display: flex;
    justify-content: space-between;
    margin-top: 4px;
}

/* ===== SEARCH ===== */
.search-bar {
    display: flex;
    background: #1a1a1a;
    border-radius: 20px;
    overflow: hidden;
}

.search-bar input {
    border: none;
    padding: 8px 12px;
    background: transparent;
    color: #fff;
}

.search-bar input:focus {
    outline: none;
}

.search-bar button {
    background: #ffc107;
    border: none;
    padding: 0 12px;
}

/* ===== MOBILE ===== */
@media (max-width: 576px) {
    .movie-card img {
        height: 240px;
    }
}

</style>