@extends('layouts.app')

@section('content')

<div class="series-page">

    {{-- BACKDROP --}}
    <div class="backdrop-wrapper">
        <div class="backdrop-overlay"></div>

        <img src="{{ $series->backdrop_path
            ? 'https://image.tmdb.org/t/p/original'.$series->backdrop_path
            : '/images/nobackdrop.jpg' }}"
             class="backdrop-image">
    </div>

    <div class="container-fluid px-lg-5 px-3 position-relative">

        {{-- HERO --}}
        <div class="hero-card">

            <div class="row g-5 align-items-center">

                {{-- POSTER --}}
                <div class="col-12 col-lg-3">

                    <div class="poster-container">

                        <img src="https://image.tmdb.org/t/p/w500{{ $series->poster_path }}"
                             class="main-poster"
                             alt="{{ $seriesDetails['name'] }}">

                        <div class="poster-glow"></div>

                        {{-- ACTIONS --}}
                        <div class="poster-buttons">

                            @if($series->tmdb_id)
                                <a href="https://www.themoviedb.org/tv/{{ $series->tmdb_id }}"
                                   target="_blank"
                                   class="circle-btn tmdb-btn">

                                    <i class="bi bi-film"></i>
                                </a>
                            @endif

                            @if($series->imdb_id)
                                <a href="https://www.imdb.com/title/{{ $series->imdb_id }}"
                                   target="_blank"
                                   class="circle-btn imdb-btn">

                                    <i class="bi bi-star-fill"></i>
                                </a>
                            @endif

                        </div>

                    </div>

                </div>

                {{-- INFO --}}
                <div class="col-12 col-lg-9">

                    <div class="hero-content">

                        {{-- TITLE --}}
                        <div class="title-row">

                            <h1 class="series-title">
                                {{ $seriesDetails['name'] }}
                            </h1>

                            @if(isset($seriesDetails['first_air_date']))
                                <span class="year-pill">
                                    {{ \Carbon\Carbon::parse($seriesDetails['first_air_date'])->format('Y') }}
                                </span>
                            @endif

                        </div>

                        {{-- GENRES --}}
                        <div class="genres-row">

                            @foreach(array_slice($seriesDetails['genres'],0,6) as $genre)

                                <span class="genre-pill">
                                    {{ $genre['name'] }}
                                </span>

                            @endforeach

                        </div>

                        {{-- OVERVIEW --}}
                        @if(isset($seriesDetails['overview']))
                            <div class="overview-box">
                                <p>
                                    {{ $seriesDetails['overview'] }}
                                </p>
                            </div>
                        @endif

                        {{-- META --}}
                        <div class="meta-grid">

                            @if(isset($seriesDetails['number_of_seasons']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Seasons
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesDetails['number_of_seasons'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesDetails['number_of_episodes']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Episodes
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesDetails['number_of_episodes'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesOm['imdbRating']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        IMDb Rating
                                    </span>

                                    <span class="meta-value">
                                        ⭐ {{ $seriesOm['imdbRating'] }}
                                    </span>

                                </div>
                            @endif

                            @if(isset($seriesOm['imdbVotes']))
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Votes
                                    </span>

                                    <span class="meta-value">
                                        {{ $seriesOm['imdbVotes'] }}
                                    </span>

                                </div>
                            @endif

                        </div>

                        {{-- TRAILERS --}}
                        <div class="trailers-section">

                            <h5 class="section-title">
                                Trailers
                            </h5>

                            <div class="trailer-buttons">

                                @foreach(array_slice($seriesDetails['videos']['results'],0,3) as $video)

                                    <a href="https://www.youtube.com/watch?v={{ $video['key'] }}"
                                       data-lity
                                       class="trailer-btn">

                                        <i class="bi bi-play-circle-fill"></i>

                                        {{ $video['name'] }}

                                    </a>

                                @endforeach

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- CAST --}}
        <div class="cast-section">

            <div class="section-header">
                <h2>Top Cast</h2>
            </div>

            <div class="cast-slider">

                @foreach(array_slice($seriesDetails['credits']['cast'], 0, 12) as $castMember)

                    <div class="cast-card">

                        <div class="cast-image-wrapper">

                            <img src="{{ $castMember['profile_path']
                                ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                                : '/images/not-found.jpg' }}"
                                 class="cast-image">

                        </div>

                        <div class="cast-info">

                            <h6>
                                {{ $castMember['name'] }}
                            </h6>

                            <p>
                                {{ $castMember['character'] }}
                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        {{-- SEASONS --}}
        @include('series.seasons')

        {{-- ONLINE --}}
        @include('series.online')

    </div>

</div>

@endsection

<style>

/* PAGE */

.series-page {
    position: relative;
    min-height: 100vh;
    background: #070b14;
    overflow-x: hidden;
}

/* BACKDROP */

.backdrop-wrapper {
    position: fixed;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}

.backdrop-image {
    width: 100%;
    height: 100%;
    object-fit: cover;

    filter:
        blur(4px)
        brightness(0.35);

    transform: scale(1.05);
}

.backdrop-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(to bottom,
            rgba(7,11,20,0.3),
            rgba(7,11,20,0.95) 70%,
            #070b14 100%);

    z-index: 2;
}

/* HERO */

.hero-card {
    position: relative;
    z-index: 10;

    margin-top: 120px;
    margin-bottom: 60px;

    padding: 40px;

    border-radius: 32px;

    background:
        rgba(15,20,32,0.72);

    backdrop-filter: blur(22px);

    border: 1px solid rgba(255,255,255,0.08);

    box-shadow:
        0 20px 60px rgba(0,0,0,0.6);
}

/* POSTER */

.poster-container {
    position: relative;
    max-width: 320px;
    margin: auto;
}

.main-poster {
    width: 100%;
    border-radius: 24px;

    position: relative;
    z-index: 2;

    transition: 0.4s ease;

    box-shadow:
        0 25px 60px rgba(0,0,0,0.5);
}

.poster-container:hover .main-poster {
    transform: translateY(-8px) scale(1.02);
}

.poster-glow {
    position: absolute;
    inset: 0;

    background:
        radial-gradient(circle,
            rgba(124,58,237,0.35),
            transparent 70%);

    filter: blur(35px);

    z-index: 1;
}

/* ACTIONS */

.poster-buttons {
    position: absolute;
    bottom: 18px;
    left: 50%;

    transform: translateX(-50%);

    display: flex;
    gap: 14px;

    z-index: 3;
}

.circle-btn {
    width: 52px;
    height: 52px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #fff;
    font-size: 1.2rem;

    text-decoration: none;

    backdrop-filter: blur(10px);

    transition: 0.3s ease;
}

.tmdb-btn {
    background: rgba(1,180,228,0.8);
}

.imdb-btn {
    background: rgba(245,197,24,0.85);
    color: #000;
}

.circle-btn:hover {
    transform: translateY(-5px) scale(1.08);
}

/* CONTENT */

.hero-content {
    color: white;
}

.title-row {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.series-title {
    font-size: 3.8rem;
    font-weight: 900;
    line-height: 1;
    margin: 0;
}

.year-pill {
    background: rgba(255,255,255,0.08);

    padding: 8px 14px;
    border-radius: 50px;

    font-weight: 600;
    color: rgba(255,255,255,0.75);
}

/* GENRES */

.genres-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;

    margin-top: 25px;
}

.genre-pill {
    background:
        linear-gradient(135deg,
            rgba(79,70,229,0.25),
            rgba(124,58,237,0.25));

    border: 1px solid rgba(255,255,255,0.08);

    padding: 8px 14px;
    border-radius: 50px;

    color: white;
    font-size: 0.9rem;

    backdrop-filter: blur(10px);
}

/* OVERVIEW */

.overview-box {
    margin-top: 28px;

    max-width: 900px;
}

.overview-box p {
    font-size: 1.08rem;
    line-height: 1.9;
    color: rgba(255,255,255,0.82);
}

/* META */

.meta-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;

    margin-top: 35px;
}

.meta-card {
    min-width: 150px;

    background: rgba(255,255,255,0.05);

    border: 1px solid rgba(255,255,255,0.06);

    border-radius: 20px;

    padding: 18px;
}

.meta-label {
    display: block;

    font-size: 0.82rem;
    color: rgba(255,255,255,0.55);

    margin-bottom: 6px;
}

.meta-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: white;
}

/* TRAILERS */

.trailers-section {
    margin-top: 40px;
}

.section-title {
    color: white;
    font-weight: 700;
    margin-bottom: 18px;
}

.trailer-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.trailer-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    padding: 14px 20px;

    border-radius: 16px;

    background:
        rgba(255,255,255,0.08);

    color: white;
    text-decoration: none;

    border: 1px solid rgba(255,255,255,0.08);

    transition: 0.3s ease;
}

.trailer-btn:hover {
    transform: translateY(-3px);

    background:
        rgba(124,58,237,0.35);

    color: white;
}

/* CAST */

.cast-section {
    position: relative;
    z-index: 5;

    margin-bottom: 60px;
}

.section-header h2 {
    color: white;
    font-weight: 800;

    margin-bottom: 25px;
}

.cast-slider {
    display: flex;
    gap: 20px;

    overflow-x: auto;

    padding-bottom: 10px;

    scrollbar-width: none;
}

.cast-slider::-webkit-scrollbar {
    display: none;
}

.cast-card {
    min-width: 180px;

    background:
        rgba(255,255,255,0.05);

    border-radius: 24px;

    overflow: hidden;

    border: 1px solid rgba(255,255,255,0.06);

    transition: 0.35s ease;

    backdrop-filter: blur(10px);
}

.cast-card:hover {
    transform: translateY(-8px);
}

.cast-image-wrapper {
    height: 250px;
    overflow: hidden;
}

.cast-image {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: 0.4s ease;
}

.cast-card:hover .cast-image {
    transform: scale(1.08);
}

.cast-info {
    padding: 16px;
}

.cast-info h6 {
    color: white;
    font-weight: 700;
}

.cast-info p {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* MOBILE */

@media(max-width: 992px) {

    .hero-card {
        padding: 25px;
    }

    .series-title {
        font-size: 2.5rem;
    }

    .meta-grid {
        gap: 12px;
    }

    .meta-card {
        min-width: calc(50% - 12px);
    }
}

@media(max-width: 768px) {

    .hero-card {
        margin-top: 90px;
        border-radius: 24px;
    }

    .series-title {
        font-size: 2rem;
    }

    .overview-box p {
        font-size: 1rem;
    }

    .meta-card {
        width: 100%;
    }

    .cast-card {
        min-width: 150px;
    }

    .cast-image-wrapper {
        height: 220px;
    }
}

</style>