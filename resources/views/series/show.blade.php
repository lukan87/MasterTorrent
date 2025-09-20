@extends('layouts.app')

@section('content')

<div class="series-header-container container-fluid py-5">
    <div class="series-header-card row g-4 align-items-start mx-auto">
        <!-- Poster Column -->
        <div class="col-12 col-md-4 text-center poster-column">
            <div class="poster-wrapper">
                <img src="https://image.tmdb.org/t/p/w500{{ $series->poster_path }}" 
                     class="series-poster" alt="{{ $seriesDetails['name'] }}">
                <div class="poster-actions">
                    @if($series->tmdb_id)
                        <a href="https://www.themoviedb.org/tv/{{ $series->tmdb_id }}" target="_blank" class="action-btn tmdb-btn" title="View on TMDB">
                            <i class="bi bi-film"></i>
                        </a>
                    @endif
                    @if($series->imdb_id)
                        <a href="https://www.imdb.com/title/{{ $series->imdb_id }}" target="_blank" class="action-btn imdb-btn" title="View on IMDb">
                            <i class="bi bi-star"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Column -->
        <div class="col-12 col-md-8 info-column">
            <div class="title-section">
                <h1 class="series-title">{{ $seriesDetails['name'] }}</h1>
                @if(isset($seriesDetails['first_air_date']))
                    <span class="release-year">({{ \Carbon\Carbon::parse($seriesDetails['first_air_date'])->format('Y') }})</span>
                @endif
            </div>

            <div class="genres-section">
                @foreach(array_slice($seriesDetails['genres'],0,8) as $genre)
                    <span class="genre-tag">{{ $genre['name'] }}</span>
                @endforeach
            </div>

            @if(isset($seriesDetails['overview']))
                <div class="overview-section">
                    <p>{{ $seriesDetails['overview'] }}</p>
                </div>
            @endif

            <div class="series-meta">
                @if(isset($seriesDetails['runtime']))
                    @php
                        $hours = intdiv($seriesDetails['runtime'], 60);
                        $minutes = $seriesDetails['runtime'] % 60;
                    @endphp
                    <span class="meta-badge"><i class="bi bi-clock"></i> {{ $hours }}h {{ $minutes }}m</span>
                @endif

                @if(isset($seriesDetails['number_of_seasons']))
                    <span class="meta-badge"><i class="bi bi-collection-play"></i> {{ $seriesDetails['number_of_seasons'] }} Seasons / {{ $seriesDetails['number_of_episodes'] }} Episodes</span>
                @endif

                @if(isset($seriesOm['imdbVotes']))
                    <span class="meta-badge"><i class="bi bi-star-fill"></i> {{ $seriesOm['imdbVotes'] }} Votes</span>
                @endif
            </div>

            <div class="trailer-section mt-3">
                @foreach(array_slice($seriesDetails['videos']['results'],0,3) as $video)
                    <a href="#" class="trailer-link" 
                       onmouseover="this.href='https://www.youtube.com/watch?v={{ $video['key'] }}'" 
                       onmouseout="this.href='#'" data-lity>
                        <i class="bi bi-play-circle-fill"></i> {{ $video['name'] }}
                    </a>
                @endforeach
                @if(empty($seriesDetails['videos']['results']))
                    <span class="no-trailer">No trailers available</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Cast Section -->
<div class="cast-row container-fluid text-center my-5">
    @foreach(array_slice($seriesDetails['credits']['cast'], 0, 8) as $castMember)
        <div class="select d-inline-block mx-2">
            <img src="{{ $castMember['profile_path'] ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path'] : '/images/not-found.jpg' }}" class="actor-image">
            <div class="mt-2">
                <h5><b>{{ $castMember['name'] }}</b></h5>
                <p class="small text-muted">{{ $castMember['character'] }}</p>
            </div>
        </div>
    @endforeach
</div>


@include('series.seasons')

@include('series.online')

@endsection

<style>
/* Background Overlay */
body::before {
    content: '';
    position: fixed;
    top: 55px; left:0; right:0; bottom:0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.9)), 
                url('{{ $series->backdrop_path ? 'https://image.tmdb.org/t/p/original'.$series->backdrop_path : '/images/nobackdrop.jpg' }}') center/cover no-repeat;
    z-index: -1;
    filter: blur(0.8px);
    transition: transform 0.2s ease-out;
}

/* Card styling */
.series-header-card {
    background: rgba(30,30,40,0.6);
    backdrop-filter: blur(3px);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.7);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff;
    max-width: 1500px;
    margin: 0 auto;
}

/* Poster */
.poster-wrapper {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    max-width: 220px;
    margin: 0 auto;
    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
}

.series-poster {
    width: 100%;
    border-radius: 12px;
    transition: transform 0.4s ease;
}

.poster-wrapper:hover .series-poster {
    transform: scale(1.05);
}

/* Poster Actions */
.poster-actions {
    position: absolute;
    bottom: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 10px;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.poster-wrapper:hover .poster-actions {
    opacity: 1;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: scale(1.2) translateY(-5px);
}

/* Info Section */
.series-title {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
}

.release-year {
    font-size: 1.2rem;
    color: #ccc;
}

.genres-section {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 15px 0;
}

.genre-tag {
    background: rgba(110,72,170,0.3);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    border: 1px solid rgba(110,72,170,0.5);
    transition: all 0.3s ease;
}

.genre-tag:hover {
    background: rgba(110,72,170,0.5);
}

.series-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 15px;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.1);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Trailers */
.trailer-section a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(125, 123, 123, 0.6);
    border-radius: 8px;
    font-weight: 600;
    color: #fff;
    margin-right: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.trailer-section a:hover {
    background: rgba(60, 59, 59, 0.9);
    transform: translateY(-2px);
}

/* Cast Section */
.cast-row {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    justify-content: flex-start;
    gap: 20px;
    padding: 15px 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.select {
    gap: 3px 20px;
    border-radius: 16px;
    padding: 6px 26px 6px 6px;
    overflow: hidden;
    flex: 0 0 auto; /* prevent shrinking in scroll */
}

.select:hover {
    backdrop-filter: brightness(130%) blur(10px);
}

.actor-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 50%;
}

/* Desktop: center cast in grid */
@media (min-width: 768px) {
    .cast-row {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
