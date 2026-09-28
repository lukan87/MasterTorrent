@extends('layouts.app')

@section('title', $movieDetails['title'] ?? $movie->name)

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

    {{-- UBLOCK ORIGIN NOTICE --}}
    <div class="ublock-notice" role="alert">

        <div class="ublock-notice-icon">
            <i class="bi bi-shield-check"></i>
        </div>

        <div class="ublock-notice-content">

            <div class="ublock-notice-title">
                Watch without ads
            </div>

            <div class="ublock-notice-text">
                We recommend installing
                <strong>uBlock Origin</strong>.

                uBlock Origin is not just an "ad blocker" — it's a
                wide-spectrum content blocker designed with CPU and
                memory efficiency as a primary feature.

                It can help block ads and other unwanted content while browsing.
            </div>

        </div>

        <a href="https://ublockorigin.com/"
           target="_blank"
           rel="noopener noreferrer"
           class="ublock-notice-button">

            <i class="bi bi-box-arrow-up-right"></i>
            Get uBlock Origin

        </a>

    </div>

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

            

            <div class="movie-cast-slider">

                @foreach(array_slice($movieDetails['credits']['cast'],0,10) as $castMember)

                    @php
                        $castImage = $castMember['profile_path']
                            ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                            : '/images/not-found.jpg';
                    @endphp

                    <a href="/actors/{{ $castMember['id'] }}"
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

    <div class="movie-cast-card similar-card {{ $sim['in_library'] ? 'similar-card-available' : 'similar-card-unavailable' }}">

        {{-- POSTER --}}
        @if($sim['in_library'])

            <a href="{{ $sim['db_url'] }}"
               class="movie-cast-image-wrapper d-block similar-poster-link"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="View online">

        @else

            <div class="movie-cast-image-wrapper d-block similar-poster-link"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="Not in database yet">

        @endif

                <img src="{{ $sim['poster'] }}"
                     class="movie-cast-image {{ !$sim['in_library'] ? 'similar-poster-unavailable' : '' }}"
                     loading="lazy"
                     alt="{{ $sim['name'] }}">

                {{-- STATUS --}}
                <span class="similar-status-badge {{ $sim['in_library'] ? 'similar-status-online' : 'similar-status-missing' }}">

                    @if($sim['in_library'])
                        <i class="bi bi-play-circle-fill"></i>
                    @else
                        <i class="bi bi-database-x"></i>
                    @endif

                </span>

                {{-- ONLINE BADGE --}}
                @if($sim['in_library'])
                    <span class="in-library-badge">
                        <i class="bi bi-check2-circle"></i>
                        Online
                    </span>
                @endif

        @if($sim['in_library'])
            </a>
        @else
            </div>
        @endif


        {{-- MOVIE INFO --}}
        <div class="movie-cast-info">

            <h6>
                @if($sim['in_library'])

                    <a href="{{ $sim['db_url'] }}"
                       class="similar-title-link"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="View online">
                        {{ $sim['name'] }}
                    </a>

                @else

                    <span class="similar-title-unavailable"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Not in database yet">
                        {{ $sim['name'] }}
                    </span>

                @endif
            </h6>

            <p>
                <i class="bi bi-star-fill text-warning"></i>
                {{ $sim['rating'] }}

                @if($sim['year'])
                    · {{ $sim['year'] }}
                @endif
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



@include('movies.partials.showstyles')


@endsection
