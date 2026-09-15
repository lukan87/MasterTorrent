@extends('layouts.app')

@section('content')

<div class="movie-page">

    {{-- BACKDROP --}}
    <div class="movie-backdrop-wrapper">

        <div class="movie-backdrop-overlay"></div>

        <img src="{{ $movie->backdrop_path
            ? 'https://image.tmdb.org/t/p/original'.$movie->backdrop_path
            : '/images/nobackdrop.jpg' }}"
             class="movie-backdrop-image">

    </div>

    <div class="container-fluid px-lg-5 px-3 position-relative">

        {{-- HERO --}}
        <div class="movie-hero-card">

            <div class="row g-5 align-items-center">

                {{-- POSTER --}}
                <div class="col-12 col-lg-3">

                    <div class="movie-poster-container">

                        <img src="https://image.tmdb.org/t/p/w600_and_h900_bestv2{{ $movie->poster_path }}"
                             class="movie-main-poster"
                             alt="{{ $movieDetails['title'] }}">

                        <div class="movie-poster-glow"></div>

                        {{-- ACTION BUTTONS --}}
                        <div class="movie-poster-actions">

                            @if($movie->tmdb_id)

                                <a href="https://www.themoviedb.org/movie/{{ $movie->tmdb_id }}"
                                   target="_blank"
                                   class="movie-circle-btn tmdb-btn">

                                    <i class="bi bi-film"></i>

                                </a>

                            @endif

                            @if($movie->imdb_id)

                                <a href="https://www.imdb.com/title/{{ $movie->imdb_id }}"
                                   target="_blank"
                                   class="movie-circle-btn imdb-btn">

                                    <i class="bi bi-star-fill"></i>

                                </a>

                            @endif

                        </div>

                    </div>

                </div>

                {{-- INFO --}}
                <div class="col-12 col-lg-9">

                    <div class="movie-hero-content">

                        {{-- TITLE --}}
                        <div class="movie-title-row">

                            <h1 class="movie-title">
                                {{ $movieDetails['title'] }}
                            </h1>

                            @if(isset($movieDetails['release_date']))

                                <span class="movie-year-pill">

                                    {{ \Carbon\Carbon::parse($movieDetails['release_date'])->format('Y') }}

                                </span>

                            @endif

                            @php
                                $rating = $movieOm['Rated'] ?? '';
                            @endphp

                            @if($rating)

                                <span class="movie-rating-pill">
                                    {{ $rating }}
                                </span>

                            @endif

                            @if(($movie->views ?? 0) > 0)

                                <span class="movie-rating-pill movie-views-pill">
                                    <i class="bi bi-eye"></i> {{ number_format($movie->views) }}
                                </span>

                            @endif

                        </div>

                        {{-- GENRES --}}
                        <div class="movie-genres-row">

                            @foreach(array_slice($movieDetails['genres'],0,8) as $genre)

                                <span class="movie-genre-pill">

                                    {{ $genre['name'] }}

                                </span>

                            @endforeach

                        </div>

                        {{-- OVERVIEW --}}
                        @if(isset($movieDetails['overview']))

                            <div class="movie-overview-box">

                                <p>
                                    {{ $movieDetails['overview'] }}
                                </p>

                            </div>

                        @endif

                        {{-- META --}}
                        <div class="movie-meta-grid">

                            @if(isset($movieDetails['runtime']))

                                @php
                                    $hours = intdiv($movieDetails['runtime'], 60);
                                    $minutes = $movieDetails['runtime'] % 60;
                                @endphp

                                <div class="movie-meta-card">

                                    <span class="movie-meta-label">
                                        Runtime
                                    </span>

                                    <span class="movie-meta-value">
                                        {{ $hours }}h {{ $minutes }}m
                                    </span>

                                </div>

                            @endif

                            @if(isset($movieOm['imdbRating']))

                                <div class="movie-meta-card">

                                    <span class="movie-meta-label">
                                        IMDb Rating
                                    </span>

                                    <span class="movie-meta-value">
                                        ⭐ {{ $movieOm['imdbRating'] }}
                                    </span>

                                </div>

                            @endif

                            @if(isset($movieOm['imdbVotes']))

                                <div class="movie-meta-card">

                                    <span class="movie-meta-label">
                                        Votes
                                    </span>

                                    <span class="movie-meta-value">
                                        {{ $movieOm['imdbVotes'] }}
                                    </span>

                                </div>

                            @endif

                            <div class="movie-meta-card">

                                <span class="movie-meta-label">
                                    Director
                                </span>

                                <span class="movie-meta-value">
                                    {{ $movieOm['Director'] ?? 'N/A' }}
                                </span>

                            </div>

                        </div>

                        {{-- COLLECTION --}}
                        @if(!empty($movie->collection_id))

                            <div class="collection-box">

                                <span class="collection-label">
                                    COLLECTION
                                </span>

                                <a href="{{ route('collections.show', $movie->collection_id) }}"
                                   class="collection-link">

                                    {{ $movie->collection_name }}

                                </a>

                            </div>

                        @endif

                        {{-- TRAILERS --}}
                        <div class="movie-trailer-section">

                            <h5 class="movie-section-title">
                                Trailers
                            </h5>

                            <div class="movie-trailer-buttons">

                                @foreach(array_slice($movieDetails['videos']['results'],0,3) as $video)

                                    <a href="https://www.youtube.com/watch?v={{ $video['key'] }}"
                                       data-lity
                                       class="movie-trailer-btn">

                                        <i class="bi bi-play-circle-fill"></i>

                                        {{ $video['name'] }}

                                    </a>

                                @endforeach

                            </div>

                        </div>

                        {{-- WATCH --}}
                        <div class="watch-actions">

                            @php
                                $userClass = optional(auth()->user())->user_class ?? 0;
                            @endphp

                            @if($userClass >= \App\Models\UserClass::USER)

                                <a href="https://vidsrc.me/embed/{{ $movie->imdb_id }}"
                                   data-lity
                                   class="watch-now-btn">

                                    <i class="bi bi-play-fill"></i>

                                    Watch Online

                                </a>

                            @else

                                <div class="vip-required-box">

                                    <i class="bi bi-gem"></i>

                                    VIP Required to Watch

                                </div>

                            @endif

                            @if($userClass >= \App\Models\UserClass::ADMIN)
                                <form action="{{ route('movies.delete', $movie->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete &quot;{{ addslashes($movie->name) }}&quot; permanently? This also removes its comments and torrents.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="watch-delete-btn">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- CAST --}}
        <div class="movie-cast-section">

            <div class="movie-section-header">

                <h2>
                    Top Cast
                </h2>

            </div>

            <div class="movie-cast-slider">

                @foreach(array_slice($movieDetails['credits']['cast'],0,10) as $castMember)

                    @php
                        $castImage = $castMember['profile_path']
                            ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                            : '/images/not-found.jpg';
                    @endphp

                    <a href="https://www.themoviedb.org/person/{{ $castMember['id'] }}"
                       target="_blank"
                       class="movie-cast-card">

                        <div class="movie-cast-image-wrapper">

                            <img src="{{ $castImage }}"
                                 class="movie-cast-image">

                        </div>

                        <div class="movie-cast-info">

                            <h6>
                                {{ $castMember['name'] }}
                            </h6>

                            <p>
                                {{ $castMember['character'] }}
                            </p>

                        </div>

                    </a>

                @endforeach

            </div>

        </div>

        {{-- SIMILAR MOVIES --}}
        @if(isset($similar) && $similar->isNotEmpty())
            <div class="movie-cast-section similar-section">

                <div class="movie-section-header">
                    <h2>You May Also Like</h2>
                </div>

                <div class="movie-cast-slider">
                    @foreach($similar as $sim)
                        <div class="movie-cast-card similar-card">
                            <a href="{{ $sim['in_library'] ? $sim['db_url'] : 'https://www.themoviedb.org/search?query=' . urlencode($sim['name']) . ($sim['year'] ? '&year='.$sim['year'] : '') }}"
                               {{ $sim['in_library'] ? '' : 'target="_blank"' }}
                               class="movie-cast-image-wrapper d-block">
                                <img src="{{ $sim['poster'] }}"
                                     class="movie-cast-image"
                                     loading="lazy"
                                     alt="{{ $sim['name'] }}">
                                @if($sim['in_library'])
                                    <span class="in-library-badge"><i class="bi bi-check2-circle"></i> Online</span>
                                @endif
                            </a>
                            <div class="movie-cast-info">
                                <h6>
                                    @if($sim['in_library'])
                                        <a href="{{ $sim['db_url'] }}" class="similar-title-link">{{ $sim['name'] }}</a>
                                    @else
                                        {{ $sim['name'] }}
                                    @endif
                                </h6>
                                <p>
                                    <i class="bi bi-star-fill text-warning"></i> {{ $sim['rating'] }}
                                    @if($sim['year']) · {{ $sim['year'] }} @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endif

        {{-- AVAILABLE TORRENTS --}}
        @if(isset($torrents) && $torrents->isNotEmpty())
            <div class="movie-cast-section torrent-section">

                <div class="movie-section-header d-flex align-items-center justify-content-between">
                    <h2>
                        Available Torrents
                        <span class="results-count">{{ $torrents->count() }}</span>
                    </h2>
                    <a href="{{ route('torrents.index') }}?tmdbid={{ $movie->tmdb_id }}"
                       class="btn btn-more-torrents">
                        View All <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="torrent-list">
                    @foreach($torrents as $torrent)
                        @php
                            $sz = $torrent->size ?? 0;
                            $fsize = $sz >= 1073741824
                                ? round($sz / 1073741824, 1) . ' GB'
                                : ($sz >= 1048576
                                    ? round($sz / 1048576, 1) . ' MB'
                                    : round($sz / 1024, 1) . ' KB');
                        @endphp
                        <a href="{{ route('torrents.show', $torrent->id) }}"
                           class="torrent-row">
                            <div class="torrent-row-main">
                                <strong>{{ $torrent->name }}</strong>
                                <small class="text-muted">
                                    @if($torrent->category_id) {{ $torrent->category->name ?? '' }} · @endif
                                    {{ $fsize }}
                                </small>
                            </div>
                            <div class="torrent-row-meta">
                                <span class="seeders"><i class="bi bi-arrow-up-circle"></i> {{ $torrent->seeders ?? 0 }}</span>
                                <span class="leechers"><i class="bi bi-arrow-down-circle"></i> {{ $torrent->leechers ?? 0 }}</span>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                    @endforeach
                </div>

            </div>
        @endif

        {{-- COMMENTS --}}
        <div class="comments-modern-wrapper">

            <div class="comments-modern-card">

                <div class="comments-header">

                    <h3>
                        Comments
                    </h3>

                </div>

                {{-- FORM --}}
                <form action="{{ route('comments.store') }}"
                      method="POST"
                      class="comment-form-modern">

                    @csrf

                    <input type="hidden"
                           name="commentable_id"
                           value="{{ $movie->id }}">

                    <input type="hidden"
                           name="commentable_type"
                           value="App\Models\Movie">

                    <textarea name="comment"
          rows="4"
          required
          placeholder="Write your thoughts about this movie..."></textarea>

                    <button type="submit"
                            class="submit-comment-btn">

                        Post Comment

                    </button>

                </form>

                {{-- COMMENTS LIST --}}
                @if($movie->comments->isEmpty())

                    <div class="no-comments-box">

                        No comments yet

                    </div>

                @else

                    @foreach($movie->comments()->paginate(5) as $comment)

                        <div class="single-comment-card">

                            <div class="comment-user-row">

                                <div class="comment-avatar">

                                    {{ strtoupper(substr($comment->user->name,0,1)) }}

                                </div>

                                <div>

                                    <h6>
                                        {{ $comment->user->name }}
                                    </h6>

                                    <small>
                                        {{ $comment->created_at }}
                                    </small>

                                </div>

                            </div>

                            <p class="comment-text">

                                {{ $comment->comment }}

                            </p>

                        </div>

                    @endforeach

                    <div class="mt-4">

                        {{ $movie->comments()->paginate(5)->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>



<style>
/* =========================================================
   FILEIPLAY MOVIE DETAILS — FORUM STYLE
   ========================================================= */

.movie-page{
    position:relative;
    min-height:100vh;
    background:#070b14;
    color:#e2e8f0;
    overflow-x:hidden;
}

.movie-backdrop-wrapper{
    position:fixed;
    inset:0;
    z-index:0;
    overflow:hidden;
    pointer-events:none;
}

.movie-backdrop-image{
    width:100%;
    height:100%;
    object-fit:cover;
    filter:blur(5px) brightness(.28) saturate(.72);
    transform:scale(1.04);
}

.movie-backdrop-overlay{
    position:absolute;
    inset:0;
    z-index:2;
    background:linear-gradient(
        to bottom,
        rgba(7,11,20,.38),
        rgba(7,11,20,.78) 48%,
        #070b14 90%,
        #070b14 100%
    );
}

.movie-page > .container-fluid{
    position:relative;
    z-index:3;
}

/* HERO */
.movie-hero-card{
    position:relative;
    z-index:5;
    margin-top:95px;
    margin-bottom:34px;
    padding:26px;
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.88)
    );
    border:1px solid rgba(255,255,255,.08);
    border-radius:.8rem;
    box-shadow:0 18px 50px rgba(0,0,0,.42);
    backdrop-filter:blur(16px);
}

.movie-poster-container{
    position:relative;
    max-width:280px;
    margin:auto;
}

.movie-main-poster{
    position:relative;
    z-index:2;
    display:block;
    width:100%;
    border-radius:.65rem;
    border:1px solid rgba(255,255,255,.09);
    box-shadow:0 18px 38px rgba(0,0,0,.45);
    transition:.25s ease;
}

.movie-main-poster:hover{
    transform:translateY(-3px);
}

.movie-poster-glow{
    position:absolute;
    inset:12% 8%;
    z-index:1;
    background:rgba(45,212,191,.10);
    filter:blur(35px);
    border-radius:50%;
}

.movie-poster-actions{
    position:absolute;
    z-index:4;
    left:50%;
    bottom:14px;
    transform:translateX(-50%);
    display:flex;
    gap:8px;
}

.movie-circle-btn{
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:.55rem;
    color:#fff;
    text-decoration:none;
    border:1px solid rgba(255,255,255,.14);
    box-shadow:0 8px 18px rgba(0,0,0,.30);
    transition:.2s ease;
}

.movie-circle-btn:hover{
    transform:translateY(-2px);
    color:#fff;
    border-color:rgba(255,255,255,.28);
}

.tmdb-btn{
    background:rgba(1,180,228,.82);
}

.imdb-btn{
    background:rgba(245,197,24,.88);
    color:#111;
}

.imdb-btn:hover{
    color:#111;
}

/* INFO */
.movie-title-row{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
}

.movie-title{
    margin:0;
    color:#f1f5f9;
    font-size:2.45rem;
    font-weight:700;
    line-height:1.15;
    letter-spacing:-.02em;
}

.movie-year-pill,
.movie-rating-pill{
    display:inline-flex;
    align-items:center;
    padding:.28rem .55rem;
    border-radius:.4rem;
    font-size:.72rem;
    font-weight:600;
}

.movie-year-pill{
    background:rgba(45,212,191,.08);
    border:1px solid rgba(45,212,191,.20);
    color:#8ff5e6;
}

.movie-rating-pill{
    background:rgba(245,197,24,.10);
    border:1px solid rgba(245,197,24,.18);
    color:#f5c518;
}

.movie-views-pill i{
    margin-right:4px;
}

.movie-genres-row{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:14px;
}

.movie-genre-pill{
    padding:.28rem .55rem;
    border-radius:.4rem;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.08);
    color:rgba(226,232,240,.72);
    font-size:.72rem;
}

.movie-overview-box{
    max-width:900px;
    margin-top:18px;
    padding:.8rem .95rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    border-left:3px solid rgba(45,212,191,.42);
    border-radius:.5rem;
}

.movie-overview-box p{
    margin:0;
    color:rgba(226,232,240,.78);
    font-size:.86rem;
    line-height:1.65;
}

/* META */
.movie-meta-grid{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:18px;
}

.movie-meta-card{
    min-width:115px;
    padding:.65rem .75rem;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.065);
    border-radius:.5rem;
}

.movie-meta-label{
    display:block;
    margin-bottom:3px;
    color:rgba(203,213,225,.48);
    font-size:.66rem;
    font-weight:600;
    text-transform:uppercase;
}

.movie-meta-value{
    color:#e8f0f7;
    font-size:.82rem;
    font-weight:600;
}

/* COLLECTION */
.collection-box{
    display:inline-block;
    max-width:100%;
    margin-top:18px;
    padding:.65rem .8rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    border-radius:.5rem;
}

.collection-label{
    display:block;
    margin-bottom:3px;
    color:rgba(203,213,225,.48);
    font-size:.64rem;
    font-weight:600;
    letter-spacing:1.2px;
}

.collection-link{
    color:#8ff5e6;
    font-size:.82rem;
    font-weight:600;
    text-decoration:none;
}

.collection-link:hover{
    color:#fff;
}

/* TRAILERS / WATCH */
.movie-trailer-section{
    margin-top:20px;
}

.movie-section-title{
    margin-bottom:10px;
    color:#e8f0f7;
    font-size:.92rem;
    font-weight:600;
}

.movie-trailer-buttons{
    display:flex;
    flex-wrap:wrap;
    gap:7px;
}

.movie-trailer-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    max-width:100%;
    padding:.42rem .65rem;
    border-radius:.45rem;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.08);
    color:rgba(226,232,240,.78);
    font-size:.73rem;
    text-decoration:none;
    transition:.2s ease;
}

.movie-trailer-btn i{
    color:var(--ui-accent,#2dd4bf);
}

.movie-trailer-btn:hover{
    color:#fff;
    background:rgba(45,212,191,.07);
    border-color:rgba(45,212,191,.25);
    transform:translateY(-1px);
}

.watch-actions{
    margin-top:18px;
}

.watch-now-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .85rem;
    border-radius:.5rem;
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.25);
    color:#8ff5e6;
    font-size:.78rem;
    font-weight:600;
    text-decoration:none;
    transition:.2s ease;
}

.watch-now-btn:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
    transform:translateY(-1px);
}

.watch-delete-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .85rem;
    border-radius:.5rem;
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.30);
    color:#fca5a5;
    font-size:.78rem;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    transition:.2s ease;
    font-family:inherit;
}

.watch-delete-btn:hover{
    color:#fff;
    background:#ef4444;
    border-color:#ef4444;
    transform:translateY(-1px);
}

.vip-required-box{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .8rem;
    border-radius:.5rem;
    background:rgba(239,68,68,.08);
    border:1px solid rgba(239,68,68,.18);
    color:#fca5a5;
    font-size:.76rem;
    font-weight:600;
}

/* CAST */
.movie-cast-section{
    position:relative;
    z-index:5;
    width:100%;
    margin:0 auto 34px;
    text-align:center;
}

.movie-section-header{
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
}

.movie-section-header h2{
    margin:0 0 12px;
    color:#e8f0f7;
    font-size:1.1rem;
    font-weight:650;
    text-align:center;
}

.movie-cast-slider{
    display:flex;
    align-items:flex-start;
    justify-content:center;
    gap:12px;
    width:100%;
    overflow-x:auto;
    padding:2px 2px 10px;
    scrollbar-width:thin;
    scrollbar-color:rgba(45,212,191,.25) transparent;
}

.movie-cast-slider::-webkit-scrollbar{
    height:5px;
}

.movie-cast-slider::-webkit-scrollbar-thumb{
    background:rgba(45,212,191,.22);
    border-radius:10px;
}

.movie-cast-card{
    flex:0 0 145px;
    width:145px;
    min-width:145px;
    overflow:hidden;
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.94),
        rgba(15,23,42,.90)
    );
    border:1px solid rgba(255,255,255,.07);
    border-radius:.65rem;
    box-shadow:0 8px 20px rgba(0,0,0,.22);
    text-decoration:none;
    transition:.2s ease;
}

.movie-cast-card:hover{
    transform:translateY(-3px);
    border-color:rgba(45,212,191,.22);
    box-shadow:0 12px 26px rgba(0,0,0,.30);
}

.movie-cast-image-wrapper{
    width:100%;
    height:205px;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    background:rgba(7,14,27,.72);
}

.movie-cast-image{
    display:block;
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:center center;
    margin:0 auto;
    transition:.25s ease;
}

.movie-cast-card:hover .movie-cast-image{
    transform:scale(1.035);
}

.movie-cast-info{
    padding:.65rem .7rem;
    text-align:center;
}

.movie-cast-info h6{
    margin:0 0 .2rem;
    overflow:hidden;
    color:#e8f0f7;
    font-size:.78rem;
    font-weight:600;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.movie-cast-info p{
    margin:0;
    overflow:hidden;
    color:rgba(203,213,225,.52);
    font-size:.68rem;
    line-height:1.35;
    text-overflow:ellipsis;
    white-space:nowrap;
}

/* SIMILAR */
.similar-section{
    margin-top:4px;
}

.similar-card{
    display:block;
}

.similar-card .movie-cast-image-wrapper{
    height:215px;
}

/* TORRENTS */
.torrent-section{
    text-align:left;
}

.torrent-section .movie-section-header{
    justify-content:space-between;
}

.torrent-section .movie-section-header h2{
    text-align:left;
}

.results-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:24px;
    height:19px;
    padding:0 6px;
    margin-left:6px;
    border-radius:999px;
    background:#22d3c5;
    color:#061311;
    font-size:11px;
    font-weight:800;
    vertical-align:middle;
}

.btn-more-torrents{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:.4rem .65rem;
    border:1px solid rgba(255,255,255,.10);
    border-radius:.45rem;
    background:rgba(255,255,255,.035);
    color:rgba(226,232,240,.78);
    font-size:.72rem;
    font-weight:600;
    text-decoration:none;
    transition:.2s ease;
}

.btn-more-torrents:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
}

.torrent-list{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.torrent-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:.65rem .75rem;
    border-radius:.5rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    color:#e8f0f7;
    text-decoration:none;
    transition:.2s ease;
}

.torrent-row:hover{
    background:rgba(45,212,191,.055);
    border-color:rgba(45,212,191,.20);
}

.torrent-row-main{
    display:flex;
    flex-direction:column;
    min-width:0;
}

.torrent-row-main strong{
    overflow:hidden;
    color:#e8f0f7;
    font-size:.78rem;
    font-weight:600;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.torrent-row-main small{
    font-size:.68rem;
}

.torrent-row-meta{
    display:flex;
    align-items:center;
    gap:10px;
    flex:0 0 auto;
    font-size:.72rem;
    font-weight:600;
}

.torrent-row-meta .seeders{
    color:#4ade80;
}

.torrent-row-meta .leechers{
    color:#f87171;
}

.torrent-row-meta i.bi-chevron-right{
    color:rgba(255,255,255,.42);
}

/* COMMENTS */
.comments-modern-wrapper{
    position:relative;
    z-index:5;
    margin-bottom:34px;
}

.comments-modern-card{
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.94),
        rgba(15,23,42,.88)
    );
    border:1px solid rgba(255,255,255,.08);
    border-radius:.8rem;
    padding:18px;
    box-shadow:0 14px 36px rgba(0,0,0,.30);
    backdrop-filter:blur(14px);
}

.comments-header{
    border-bottom:1px solid rgba(255,255,255,.06);
    margin-bottom:14px;
    padding-bottom:10px;
}

.comments-header h3{
    margin:0;
    color:#e8f0f7;
    font-size:1rem;
    font-weight:650;
}

.comment-form-modern textarea{
    width:100%;
    min-height:105px;
    padding:.7rem .8rem;
    resize:vertical;
    outline:none;
    border:1px solid rgba(255,255,255,.08);
    border-radius:.5rem;
    background:rgba(255,255,255,.025);
    color:#e8f0f7;
    font-size:.8rem;
}

.comment-form-modern textarea:focus{
    border-color:rgba(45,212,191,.30);
    box-shadow:0 0 0 2px rgba(45,212,191,.06);
}

.comment-form-modern textarea::placeholder{
    color:rgba(203,213,225,.38);
}

.submit-comment-btn{
    margin-top:8px;
    padding:.45rem .75rem;
    border:1px solid rgba(45,212,191,.22);
    border-radius:.45rem;
    background:rgba(45,212,191,.09);
    color:#8ff5e6;
    font-size:.74rem;
    font-weight:600;
    transition:.2s ease;
}

.submit-comment-btn:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
}

.single-comment-card{
    margin-top:10px;
    padding:.75rem;
    border-radius:.55rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.06);
}

.comment-user-row{
    display:flex;
    align-items:center;
    gap:9px;
    margin-bottom:8px;
}

.comment-avatar{
    width:34px;
    height:34px;
    flex:0 0 34px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.20);
    color:#8ff5e6;
    font-size:.72rem;
    font-weight:700;
}

.comment-user-row h6{
    margin:0 0 2px;
    color:#e8f0f7;
    font-size:.76rem;
    font-weight:600;
}

.comment-user-row small{
    color:rgba(203,213,225,.45);
    font-size:.65rem;
}

.comment-text{
    margin:0;
    color:rgba(226,232,240,.76);
    font-size:.87rem;
    line-height:1.55;
}

.no-comments-box{
    padding:22px;
    border:1px dashed rgba(255,255,255,.08);
    border-radius:.55rem;
    background:rgba(255,255,255,.02);
    color:rgba(203,213,225,.48);
    font-size:.75rem;
    text-align:center;
}

/* RESPONSIVE */
@media(max-width:992px){
    .movie-hero-card{
        margin-top:82px;
        padding:22px;
    }

    .movie-title{
        font-size:2rem;
    }

    .movie-poster-container{
        max-width:245px;
    }
}

@media(max-width:768px){
    .movie-hero-card{
        margin-top:70px;
        margin-bottom:26px;
        padding:16px;
        border-radius:.65rem;
    }

    .movie-title{
        font-size:1.65rem;
    }

    .movie-overview-box p{
        font-size:.82rem;
    }

    .movie-meta-grid{
        gap:6px;
    }

    .movie-meta-card{
        min-width:calc(50% - 3px);
        padding:.55rem .6rem;
    }

    .movie-trailer-btn{
        width:100%;
    }

    .movie-cast-section{
        margin-bottom:28px;
    }

    .movie-section-header h2{
        font-size:1rem;
    }

    .movie-cast-slider{
        justify-content:flex-start;
    }

    .movie-cast-card{
        flex-basis:125px;
        min-width:125px;
        width:125px;
    }

    .movie-cast-image-wrapper{
        height:175px;
    }

    .similar-card .movie-cast-image-wrapper{
        height:185px;
    }

    .comments-modern-card{
        padding:14px;
    }

    .torrent-row{
        align-items:flex-start;
        flex-direction:column;
    }

    .torrent-row-meta{
        width:100%;
        justify-content:space-between;
    }
}
.movie-cast-image-wrapper{position:relative}

.in-library-badge{
    position:absolute;
    top:8px;
    left:8px;
    z-index:3;
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:.22rem .5rem;
    border-radius:999px;
    background:rgba(45,212,191,.92);
    color:#06291f;
    font-size:.62rem;
    font-weight:700;
    line-height:1;
    box-shadow:0 3px 8px rgba(0,0,0,.35);
    pointer-events:none;
}

.similar-title-link{
    color:#8ff5e6;
    text-decoration:none;
}
.similar-title-link:hover{
    color:#fff;
    text-decoration:underline;
}
</style>


@endsection
