@extends('layouts.app')

@section('title', $seriesDetails['name'] ?? $series->name)

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

                            @if(($series->views ?? 0) > 0)
                                <div class="meta-card">

                                    <span class="meta-label">
                                        Views
                                    </span>

                                    <span class="meta-value">
                                        <i class="bi bi-eye"></i> {{ number_format($series->views) }}
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

                        {{-- ACTIONS (ADMIN DELETE) --}}
                        <div class="watch-actions">

                            @php
                                $userClass = optional(auth()->user())->user_class ?? 0;
                            @endphp

                            @if($userClass >= \App\Models\UserClass::ADMIN)
                                <form action="{{ route('series.delete', $series->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete &quot;{{ addslashes($series->name) }}&quot; permanently? This also removes its comments, torrents and torrent-library entry.')">
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
        <div class="cast-section">

            <div class="section-header">
                <h2>Top Cast</h2>
            </div>

            <div class="cast-slider">

                @foreach(array_slice($seriesDetails['credits']['cast'], 0, 12) as $castMember)
                @php
                        $castImage = $castMember['profile_path']
                            ? 'https://www.themoviedb.org/t/p/w300_and_h450_bestv2'.$castMember['profile_path']
                            : '/images/not-found.jpg';
                    @endphp

                    <div class="cast-card">

                    <a href="/actors/{{ $castMember['id'] }}"
                       target="_blank"
                       class="movie-cast-card">

                        <div class="cast-image-wrapper">

                            <img src="{{ $castImage }}"
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
                    </a>

                @endforeach

            </div>

        </div>

        {{-- SIMILAR SERIES --}}
        @if(isset($similar) && $similar->isNotEmpty())
            <div class="cast-section similar-section">

                <div class="section-header">
                    <h2>You May Also Like</h2>
                </div>

                <div class="cast-slider">
                    @foreach($similar as $sim)

    <div class="cast-card similar-series-card {{ $sim['in_library'] ? 'similar-series-available' : 'similar-series-unavailable' }}">

        {{-- POSTER --}}
        @if($sim['in_library'])

            <a href="{{ $sim['db_url'] }}"
               class="cast-image-wrapper similar-series-poster-link"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="View online">

        @else

            <div class="cast-image-wrapper similar-series-poster-link"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="Not in database yet">

        @endif

                <img src="{{ $sim['poster'] }}"
                     class="cast-image {{ !$sim['in_library'] ? 'similar-series-poster-unavailable' : '' }}"
                     loading="lazy"
                     alt="{{ $sim['name'] }}">

                {{-- STATUS ICON --}}
                <span class="similar-series-status {{ $sim['in_library'] ? 'similar-series-status-online' : 'similar-series-status-missing' }}">

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


        {{-- SERIES INFO --}}
        <div class="cast-info">

            <h6>
                @if($sim['in_library'])

                    <a href="{{ $sim['db_url'] }}"
                       class="similar-series-title-link"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="View online">
                        {{ $sim['name'] }}
                    </a>

                @else

                    <span class="similar-series-title-unavailable"
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

        {{-- SEASONS --}}
        @include('series.seasons')

        {{-- ONLINE --}}
        @include('series.online')


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
                           value="{{ $series->id }}">

                    <input type="hidden"
                           name="commentable_type"
                           value="App\Models\Series">

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
                @if($series->comments->isEmpty())

                    <div class="no-comments-box">

                        No comments yet

                    </div>

                @else

                    @foreach($series->comments()->paginate(5) as $comment)

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

                        {{ $series->comments()->paginate(5)->links() }}

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@include('series.partials.showstyle')

@endsection

