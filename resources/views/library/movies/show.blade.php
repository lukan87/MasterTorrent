@extends('layouts.app')

@section('content')

{{-- 🎬 HERO BANNER --}}
<div class="hero">

    {{-- Background --}}
    <div class="hero-bg"
        style="background-image: url('{{ $movie['backdrop_path'] 
            ? 'https://image.tmdb.org/t/p/original/' . $movie['backdrop_path'] 
            : '' }}')">
    </div>

    {{-- Overlay --}}
    <div class="hero-overlay"></div>

    {{-- Content --}}
    <div class="container hero-content">

        <div class="row align-items-center">

            {{-- Poster --}}
            <div class="col-md-3 text-center mb-4 mb-md-0">
                <img class="poster shadow"
                     src="{{ $movie['poster_path'] 
                        ? 'https://image.tmdb.org/t/p/w500/' . $movie['poster_path'] 
                        : asset('images/no-poster.png') }}">
            </div>

            {{-- Info --}}
            <div class="col-md-9 text-white">

                <h1 class="mb-2">
                    {{ $movie['title'] }}
                    @if(!empty($movie['release_date']))
                        <span class="year">
                            ({{ substr($movie['release_date'], 0, 4) }})
                        </span>
                    @endif
                </h1>

                <div class="meta mb-3">
                    @if(!empty($movie['vote_average']))
                        <span class="badge-rating">
                            ⭐ {{ number_format($movie['vote_average'], 1) }}
                        </span>
                    @endif

                    @if(!empty($movie['release_date']))
                        <span class="badge-meta">
                            {{ $movie['release_date'] }}
                        </span>
                    @endif
                </div>

                <p class="overview">
                    {{ $movie['overview'] ?? 'No description available.' }}
                </p>

            </div>

        </div>

    </div>
</div>

{{-- 📦 TORRENTS SECTION --}}
<div class="container py-5">

    <h4 class="text-white mb-4">📥 Available Torrents</h4>

    <div class="torrent-list">

        @forelse($torrents as $torrent)
            <div class="torrent-item">

                <div class="torrent-main">
                    <div class="torrent-name">
                        <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}" 
   class="torrent-item text-decoration-none">
                        {{ $torrent->name }}
                        </a>
                    </div>

                    <div class="torrent-meta">
                        <span>💾 {{ number_format($torrent->size / 1073741824, 2) }} GB</span>
                        <span class="seeders">🌱 {{ $torrent->seeders }}</span>
                    </div>
                </div>

            </div>
        @empty
            <div class="text-muted">No torrents available.</div>
        @endforelse

    </div>

</div>

<style>

/* HERO */
.hero {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;

    /* 🔥 KEY CHANGE */
    background-position: top center;

    filter: blur(0.15px) brightness(0.5);
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, #0b0b0b 10%, transparent 60%);
}

.hero-content {
    position: relative;
    z-index: 2;
    padding-top: 100px;
}

/* POSTER */
.poster {
    width: 200px;
    border-radius: 10px;
}

/* TITLE */
.year {
    font-weight: 400;
    opacity: 0.7;
    font-size: 1.5rem;
}

/* META */
.meta {
    display: flex;
    gap: 10px;
    align-items: center;
}

.badge-rating {
background: rgba(255,255,255,0.15);
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 1.25rem
}

.badge-meta {
    background: rgba(255,255,255,0.15);
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 1.25rem
}

/* OVERVIEW */
.overview {
    max-width: 700px;
    opacity: 0.9;
}

/* TORRENTS */
.torrent-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.torrent-item {
    background: #111;
    border-radius: 10px;
    padding: 15px;
    transition: 0.2s ease;
}

.torrent-item:hover {
    background: #1a1a1a;
}

.torrent-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.torrent-name {
    font-weight: 600;
    color: #fff;
}

.torrent-meta {
    display: flex;
    gap: 15px;
    color: #aaa;
}

.seeders {
    color: #4caf50;
    font-weight: 600;
}

</style>

@endsection