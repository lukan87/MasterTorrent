@extends('layouts.app')

@section('content')

<div class="series-header-container container-fluid py-5">
    <div class="series-header-card row g-4 align-items-start mx-auto">
        <!-- Poster Column -->
        <div class="col-12 col-md-4 text-center poster-column">
            <div class="poster-wrapper">
                <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}" 
                     class="series-poster" alt="{{ $movieDetails['title'] }}">
                <div class="poster-actions">
                    @if($movie->tmdb_id)
                        <a href="https://www.themoviedb.org/movie/{{ $movie->tmdb_id }}" target="_blank" class="action-btn tmdb-btn" title="View on TMDB">
                            <i class="bi bi-film"></i>
                        </a>
                    @endif
                    @if($movie->imdb_id)
                        <a href="https://www.imdb.com/title/{{ $movie->imdb_id }}" target="_blank" class="action-btn imdb-btn" title="View on IMDb">
                            <i class="bi bi-star"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Column -->
        <div class="col-12 col-md-8 info-column">
            <div class="title-section">
                <h1 class="series-title">{{ $movieDetails['title'] }}</h1>
                @if(isset($movieDetails['release_date']))
                    <span class="release-year">({{ \Carbon\Carbon::parse($movieDetails['release_date'])->format('Y') }})</span>
                @endif
                @php
                    $rating = $movieOm['Rated'] ?? '';
                    $PG = '';
                    switch ($rating) {
                        case 'G': $PG = "<i class='bi bi-stars' title='G - All ages admitted'><b>$rating</b></i>"; break;
                        case 'PG': $PG = "<i class='bi bi-stars' title='PG - Parental Guidance Suggested'><b>$rating</b></i>"; break;
                        case 'PG-13': $PG = "<i class='bi bi-stars' title='PG-13 - Some material may be inappropriate for children under 13'><b>$rating</b></i>"; break;
                        case 'R': $PG = "<i class='bi bi-stars' title='R - Restricted'><b>$rating</b></i>"; break;
                        case 'NC-17': $PG = "<i class='bi bi-stars' title='NC-17 - Adults Only'><b>$rating</b></i>"; break;
                        default: $PG = $rating ? "<i class='bi bi-stars'><b>$rating</b></i>" : ""; break;
                    }
                @endphp
                {!! $PG !!}
            </div>

            <div class="genres-section">
                @foreach(array_slice($movieDetails['genres'],0,8) as $genre)
                    <span class="genre-tag">{{ $genre['name'] }}</span>
                @endforeach
            </div>

            @if(isset($movieDetails['overview']))
                <div class="overview-section">
                    <p>{{ $movieDetails['overview'] }}</p>
                </div>
            @endif

            <div class="series-meta">
                @if(isset($movieDetails['runtime']))
                    @php
                        $hours = intdiv($movieDetails['runtime'], 60);
                        $minutes = $movieDetails['runtime'] % 60;
                    @endphp
                    <span class="meta-badge"><i class="bi bi-clock"></i> {{ $hours }}h {{ $minutes }}m</span>
                @endif

                @if(isset($movieOm['imdbVotes']))
                    <span class="meta-badge"><i class="bi bi-star-fill"></i> {{ $movieOm['imdbVotes'] }} Votes</span>
                @endif
            </div>

            <div class="trailer-section mt-3">
                @foreach(array_slice($movieDetails['videos']['results'],0,3) as $video)
                    <a href="#" class="trailer-link" 
                       onmouseover="this.href='https://www.youtube.com/watch?v={{ $video['key'] }}'" 
                       onmouseout="this.href='#'" data-lity>
                        <i class="bi bi-play-circle-fill"></i> {{ $video['name'] }}
                    </a>
                @endforeach
                @if(empty($movieDetails['videos']['results']))
                    <span class="no-trailer">No trailers available</span>
                @endif
            </div>

            <div class="mt-3">
                <b>Director:</b> {{ $movieOm['Director'] ?? 'N/A' }}
            </div>

            @if(!empty($movie->collection_id))
                <div class="mt-2">
                    <b>Belongs to Collection:</b> 
                    <a href="{{ route('collections.show', $movie->collection_id) }}">{{ $movie->collection_name }}</a>
                </div>
            @endif

            <div class="mt-3">
                @php $userClass = auth()->user()->user_class; @endphp
                @if($userClass >= \App\Models\UserClass::VIP)
                    <a href="#" id="embedLink" class="btn btn-primary btn-xl" data-lity>Watch Online</a>
                    <script>
                        const embedLink = document.getElementById('embedLink');
                        const imdbId = '{{ $movie->imdb_id }}';
                        embedLink.addEventListener('mouseover', function() {
                            embedLink.href = `https://vidsrc.me/embed/${imdbId}`;
                        });
                        embedLink.addEventListener('mouseout', function() {
                            embedLink.href = '#';
                        });
                    </script>
                @else
                    <p class="text-danger">You need to be a VIP to watch the movie.</p>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Cast Section -->
<div class="cast-row container-fluid my-5">
    @foreach(array_slice($movieDetails['credits']['cast'],0,7) as $castMember)
        @php
            $castName = $castMember['name'] ?? '';
            $castPlayed = $castMember['character'] ?? '';
            $castImage = $castMember['profile_path']
                ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                : '/images/not-found.jpg';
        @endphp
        <div class="select text-center">
            <img src="{{ $castImage }}" class="actor-image">
            <div class="mt-2">
                <h5>
                    <a href="https://www.themoviedb.org/person/{{ $castMember['id'] }}" target="_blank">
                        <b>{{ $castName }}</b>
                        <div class="small text-muted">{{ $castPlayed }}</div>
                    </a>
                </h5>
            </div>
        </div>
    @endforeach
</div>

<hr>

<!-- Comments Section -->
<div class="card mt-3">
    <div class="card-body">
        <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
            @csrf
            <input type="hidden" name="commentable_id" value="{{ $movie->id }}">
            <input type="hidden" name="commentable_type" value="App\Models\Movie">
            <div class="mb-3">
                <textarea name="comment" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Comment</button>
        </form>

        @if($movie->comments->isEmpty())
            <p>No comments yet</p>
        @else
            @foreach($movie->comments()->paginate(5) as $comment)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-subtitle mb-2 text-muted">{{ $comment->user->name }} <b>@ {{ $comment->created_at }}</b></h5>
                        <p class="card-text"><b>{{ $comment->comment }}</b></p>
                    </div>
                </div>
            @endforeach
            {{ $movie->comments()->paginate(5)->links() }}
        @endif
    </div>
</div>

@endsection

<style>
/* Backdrop */
body::before {
    content: '';
    position: fixed;
    top: 55px;
    right: 0;
    bottom: 0;
    left: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.9)), 
                url('{{ $movie->backdrop_path ? 'https://image.tmdb.org/t/p/original'.$movie->backdrop_path : '/images/nobackdrop.jpg' }}') center/cover no-repeat;
    filter: blur(0.7px);
    z-index: -1;
}

/* Poster */
.poster-wrapper {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    max-width: 280px;
    margin: 0 auto;
    box-shadow: 0 14px 28px rgba(0,0,0,0.25),0 10px 10px rgba(0,0,0,0.22);
}

.series-poster {
    width: 100%;
    border-radius: 12px;
    transition: transform 0.3s ease;
}

.poster-wrapper:hover .series-poster {
    transform: scale(1.05);
}

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
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.2rem;
    color: white;
}

.action-btn.tmdb-btn {
    background: linear-gradient(135deg,#01b4e4,#90cea1);
}

.action-btn.imdb-btn {
    background: linear-gradient(135deg,#f5de50,#f39c12);
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
    margin-left: 10px;
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
    background: rgba(255,0,0,0.6);
    border-radius: 8px;
    font-weight: 600;
    color: #fff;
    margin-right: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.trailer-section a:hover {
    background: rgba(255,0,0,0.9);
    transform: translateY(-2px);
}

/* Cast */
.cast-row {
    display: flex;
    flex-wrap: nowrap;           /* horizontal scroll on mobile */
    overflow-x: auto;
    justify-content: flex-start; /* aligns items to the left */
    gap: 50px;                   /* consistent distance between cast members */
    padding: 15px 10px;
    scroll-behavior: smooth;
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

/* Desktop: center the cast */
@media(min-width:768px){
    .cast-row {
        flex-wrap: wrap;
        justify-content: center;  /* center horizontally on desktop */
    }
}

</style>
