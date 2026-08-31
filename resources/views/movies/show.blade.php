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
                                $userClass = auth()->user()->user_class;
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
                              placeholder="Write your thoughts about this movie...">
                    </textarea>

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

@endsection

<style>

/* PAGE */

.movie-page {

    position: relative;

    min-height: 100vh;

    background: #070b14;

    overflow-x: hidden;
}

/* BACKDROP */

.movie-backdrop-wrapper {

    position: fixed;

    inset: 0;

    z-index: 0;

    overflow: hidden;
}

.movie-backdrop-image {

    width: 100%;
    height: 100%;

    object-fit: cover;

    filter:
        blur(5px)
        brightness(0.3);

    transform: scale(1.05);
}

.movie-backdrop-overlay {

    position: absolute;

    inset: 0;

    background:
        linear-gradient(to bottom,
            rgba(7,11,20,0.2),
            rgba(7,11,20,0.95) 70%,
            #070b14 100%);

    z-index: 2;
}

/* HERO */

.movie-hero-card {

    position: relative;

    z-index: 10;

    margin-top: 110px;
    margin-bottom: 60px;

    padding: 40px;

    border-radius: 32px;

    background:
        rgba(15,20,32,0.72);

    backdrop-filter: blur(22px);

    border:
        1px solid rgba(255,255,255,0.08);

    box-shadow:
        0 20px 60px rgba(0,0,0,0.6);
}

/* POSTER */

.movie-poster-container {

    position: relative;

    max-width: 320px;

    margin: auto;
}

.movie-main-poster {

    width: 100%;

    border-radius: 24px;

    position: relative;

    z-index: 2;

    transition: 0.4s ease;

    box-shadow:
        0 25px 60px rgba(0,0,0,0.5);
}

.movie-main-poster:hover {

    transform:
        translateY(-8px)
        scale(1.02);
}

.movie-poster-glow {

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

.movie-poster-actions {

    position: absolute;

    bottom: 18px;
    left: 50%;

    transform: translateX(-50%);

    display: flex;

    gap: 14px;

    z-index: 3;
}

.movie-circle-btn {

    width: 52px;
    height: 52px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    color: white;

    font-size: 1.2rem;

    text-decoration: none;

    transition: 0.3s ease;
}

.movie-circle-btn:hover {

    transform:
        translateY(-5px)
        scale(1.08);

    color: white;
}

.tmdb-btn {

    background:
        linear-gradient(135deg,
            #01b4e4,
            #90cea1);
}

.imdb-btn {

    background:
        linear-gradient(135deg,
            #f5c518,
            #f39c12);

    color: #000;
}

/* TITLE */

.movie-title-row {

    display: flex;

    align-items: center;

    gap: 14px;

    flex-wrap: wrap;
}

.movie-title {

    font-size: 4rem;

    font-weight: 900;

    line-height: 1;

    color: white;

    margin: 0;
}

.movie-year-pill,
.movie-rating-pill {

    padding: 8px 14px;

    border-radius: 50px;

    font-weight: 700;
}

.movie-year-pill {

    background:
        rgba(255,255,255,0.08);

    color:
        rgba(255,255,255,0.75);
}

.movie-rating-pill {

    background:
        rgba(245,197,24,0.15);

    color: #f5c518;
}

/* GENRES */

.movie-genres-row {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 25px;
}

.movie-genre-pill {

    padding: 8px 14px;

    border-radius: 50px;

    background:
        linear-gradient(135deg,
            rgba(79,70,229,0.25),
            rgba(124,58,237,0.25));

    border:
        1px solid rgba(255,255,255,0.08);

    color: white;

    font-size: 0.9rem;
}

/* OVERVIEW */

.movie-overview-box {

    margin-top: 30px;

    max-width: 900px;
}

.movie-overview-box p {

    font-size: 1.08rem;

    line-height: 1.9;

    color:
        rgba(255,255,255,0.82);
}

/* META */

.movie-meta-grid {

    display: flex;

    flex-wrap: wrap;

    gap: 18px;

    margin-top: 35px;
}

.movie-meta-card {

    min-width: 160px;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.06);

    border-radius: 20px;

    padding: 18px;
}

.movie-meta-label {

    display: block;

    font-size: 0.8rem;

    color:
        rgba(255,255,255,0.55);

    margin-bottom: 6px;
}

.movie-meta-value {

    font-size: 1.05rem;

    font-weight: 700;

    color: white;
}

/* COLLECTION */

.collection-box {

    margin-top: 30px;

    padding: 18px 22px;

    border-radius: 22px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.06);

    max-width: fit-content;
}

.collection-label {

    display: block;

    font-size: 0.72rem;

    letter-spacing: 2px;

    color:
        rgba(255,255,255,0.5);

    margin-bottom: 6px;
}

.collection-link {

    color: white;

    font-weight: 700;

    text-decoration: none;
}

/* TRAILERS */

.movie-trailer-section {

    margin-top: 40px;
}

.movie-section-title {

    color: white;

    font-weight: 700;

    margin-bottom: 18px;
}

.movie-trailer-buttons {

    display: flex;

    flex-wrap: wrap;

    gap: 12px;
}

.movie-trailer-btn {

    display: inline-flex;

    align-items: center;

    gap: 10px;

    padding: 14px 20px;

    border-radius: 16px;

    background:
        rgba(255,255,255,0.08);

    border:
        1px solid rgba(255,255,255,0.08);

    color: white;

    text-decoration: none;

    transition: 0.3s ease;
}

.movie-trailer-btn:hover {

    background:
        rgba(239,68,68,0.25);

    transform: translateY(-3px);

    color: white;
}

/* WATCH */

.watch-actions {

    margin-top: 35px;
}

.watch-now-btn {

    display: inline-flex;

    align-items: center;

    gap: 10px;

    padding: 18px 28px;

    border-radius: 18px;

    background:
        linear-gradient(135deg,
            #4f46e5,
            #7c3aed);

    color: white;

    text-decoration: none;

    font-weight: 700;

    transition: 0.3s ease;
}

.watch-now-btn:hover {

    transform: translateY(-3px);

    box-shadow:
        0 14px 40px rgba(124,58,237,0.4);

    color: white;
}

.vip-required-box {

    display: inline-flex;

    align-items: center;

    gap: 10px;

    padding: 18px 24px;

    border-radius: 18px;

    background:
        rgba(239,68,68,0.15);

    color: #fca5a5;

    font-weight: 700;
}

/* CAST */

.movie-cast-section {

    position: relative;

    z-index: 5;

    margin-bottom: 60px;
}

.movie-section-header h2 {

    color: white;

    font-weight: 800;

    margin-bottom: 25px;
}

.movie-cast-slider {

    display: flex;

    gap: 20px;

    overflow-x: auto;

    scrollbar-width: none;

    padding-bottom: 10px;
}

.movie-cast-slider::-webkit-scrollbar {
    display: none;
}

.movie-cast-card {

    min-width: 180px;

    text-decoration: none;

    border-radius: 24px;

    overflow: hidden;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.06);

    transition: 0.35s ease;
}

.movie-cast-card:hover {

    transform: translateY(-8px);
}

.movie-cast-image-wrapper {

    height: 250px;

    overflow: hidden;
}

.movie-cast-image {

    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: 0.4s ease;
}

.movie-cast-card:hover .movie-cast-image {

    transform: scale(1.08);
}

.movie-cast-info {

    padding: 16px;
}

.movie-cast-info h6 {

    color: white;

    font-weight: 700;
}

.movie-cast-info p {

    color:
        rgba(255,255,255,0.6);

    margin-bottom: 0;
}

/* COMMENTS */

.comments-modern-wrapper {

    position: relative;

    z-index: 5;

    margin-bottom: 60px;
}

.comments-modern-card {

    background:
        rgba(15,20,32,0.72);

    backdrop-filter: blur(22px);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 30px;

    padding: 32px;
}

.comments-header h3 {

    color: white;

    font-weight: 800;

    margin-bottom: 25px;
}

/* FORM */

.comment-form-modern textarea {

    width: 100%;

    background:
        rgba(255,255,255,0.05);

    border:
        1px solid rgba(255,255,255,0.08);

    border-radius: 20px;

    padding: 20px;

    color: white;

    resize: none;

    outline: none;
}

.comment-form-modern textarea::placeholder {

    color:
        rgba(255,255,255,0.4);
}

.submit-comment-btn {

    margin-top: 18px;

    border: none;

    border-radius: 18px;

    padding: 14px 24px;

    font-weight: 700;

    color: white;

    background:
        linear-gradient(135deg,
            #4f46e5,
            #7c3aed);

    transition: 0.3s ease;
}

.submit-comment-btn:hover {

    transform: translateY(-2px);
}

/* COMMENT */

.single-comment-card {

    margin-top: 24px;

    padding: 24px;

    border-radius: 24px;

    background:
        rgba(255,255,255,0.04);

    border:
        1px solid rgba(255,255,255,0.05);
}

.comment-user-row {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 16px;
}

.comment-avatar {

    width: 48px;
    height: 48px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    background:
        linear-gradient(135deg,
            #4f46e5,
            #7c3aed);

    color: white;

    font-weight: 800;
}

.comment-user-row h6 {

    color: white;

    margin-bottom: 4px;
}

.comment-user-row small {

    color:
        rgba(255,255,255,0.5);
}

.comment-text {

    color:
        rgba(255,255,255,0.82);

    line-height: 1.8;

    margin-bottom: 0;
}

/* EMPTY */

.no-comments-box {

    padding: 40px;

    border-radius: 24px;

    text-align: center;

    background:
        rgba(255,255,255,0.04);

    color:
        rgba(255,255,255,0.6);
}

/* MOBILE */

@media(max-width:992px) {

    .movie-title {

        font-size: 2.6rem;
    }

    .movie-meta-card {

        min-width: calc(50% - 12px);
    }
}

@media(max-width:768px) {

    .movie-hero-card {

        padding: 22px;

        border-radius: 24px;

        margin-top: 90px;
    }

    .movie-title {

        font-size: 2rem;
    }

    .movie-meta-card {

        width: 100%;
    }

    .movie-cast-card {

        min-width: 150px;
    }

    .movie-cast-image-wrapper {

        height: 220px;
    }

    .comments-modern-card {

        padding: 20px;
    }
}

</style>