@extends('layouts.app')

@section('content')

{{-- =========================================================
    PREMIUM HEADER — reuses the exact torrent detail header
========================================================= --}}
@if(!empty($display) && $torrents->isNotEmpty())

    @include('torrents.partials.media-header', [
        'torrent' => $torrents->first(),
        'display' => $display,
    ])

    {{-- Subscribe control (preserved from the previous hero) --}}
    <div class="container px-xl-5 px-lg-4 px-3">
        <div class="library-subscribe-row">

            @if(Auth::check())

                @if($isSubscribed)

                    <form action="{{ route('library.movies.unsubscribe', $tmdbid) }}" method="POST">
                        @csrf

                        <button type="submit" class="btn subscribe-btn">
                            <i class="bi bi-bell-fill me-1"></i>
                            Unsubscribe
                        </button>
                    </form>

                @else

                    <form action="{{ route('library.movies.subscribe', $tmdbid) }}" method="POST">
                        @csrf

                        <button type="submit" class="btn subscribe-btn">
                            <i class="bi bi-bell me-1"></i>
                            Subscribe
                        </button>
                    </form>

                @endif

            @endif

            {{-- Subscribers (count + names) beside the subscribe button --}}
            @include('torrents.partials._subscribers-label', [
                'subscribers' => $subscribers ?? collect()
            ])

        </div>
    </div>

@else

    {{-- Fallback (no torrent / no display data) — rich hero from TMDB/library data --}}
    @php
        $fbPoster = !empty($movie['poster_path'])
            ? 'https://image.tmdb.org/t/p/w342' . $movie['poster_path']
            : (
                !empty($libraryEntry?->poster_path)
                    ? 'https://image.tmdb.org/t/p/w342' . $libraryEntry->poster_path
                    : '/images/noposter.jpg'
            );

        $fbBackdrop = !empty($movie['backdrop_path'])
            ? 'https://image.tmdb.org/t/p/w1280' . $movie['backdrop_path']
            : (
                !empty($libraryEntry?->backdrop_path)
                    ? 'https://image.tmdb.org/t/p/w1280' . $libraryEntry->backdrop_path
                    : null
            );

        $fbRating = !empty($movie['vote_average'])
            ? $movie['vote_average']
            : $libraryEntry?->rating;

        $fbYear = !empty($movie['release_date'])
            ? substr($movie['release_date'], 0, 4)
            : null;

        $fbGenres = collect($movie['genres'] ?? [])
            ->pluck('name')
            ->take(3)
            ->implode(' · ');
    @endphp

    <div class="hero">

        @if($fbBackdrop)
            <div
                class="hero-bg"
                style="background-image:url('{{ $fbBackdrop }}')"
            ></div>
        @endif

        <div class="hero-overlay"></div>

        <div class="container hero-content">

            <div class="row align-items-center g-4">

                <div class="col-md-3 col-sm-4 col-5 text-center">

                    <img
                        src="{{ $fbPoster }}"
                        class="poster shadow-lg"
                        alt="{{ $movie['title'] ?? 'Movie' }}"
                    >

                </div>

                <div class="col-md-9 col-sm-8 col-7 text-white">

                    <h1 class="mb-2">

                        {{ $movie['title'] ?? 'Movie' }}

                        @if($fbYear)
                            <span class="year">
                                ({{ $fbYear }})
                            </span>
                        @endif

                    </h1>

                    @if($fbRating || $fbGenres)

                        <div class="meta mb-3">

                            @if($fbRating)

                                <span class="badge-rating">
                                    <i class="bi bi-star-fill me-1"></i>
                                    {{ number_format($fbRating, 1) }}
                                </span>

                            @endif

                            @if($fbGenres)

                                <span class="badge-meta">
                                    {{ $fbGenres }}
                                </span>

                            @endif

                        </div>

                    @endif

                    <p class="overview">
                        {{ $movie['overview'] ?? 'No description available.' }}
                    </p>

                </div>

            </div>

        </div>

    </div>

@endif


{{-- =========================================================
    TORRENTS SECTION
========================================================= --}}
<div class="container py-5">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">

        <h4 class="text-white mb-0">
            📥 Available Torrents
        </h4>

        @include('partials._watch-online-btn', [
            'watchUrl' => $watchUrl ?? null
        ])

    </div>


    @if($torrents->isNotEmpty())

        @php
            $resGroups = $torrents
                ->groupBy(fn ($t) => $t->resolution_label)
                ->map(fn ($g) => $g->sortByDesc('seeders')->values())
                ->sortBy(fn ($g) => $g->first()->resolution_order)
                ->values();
        @endphp


        <div class="res-panels">

            @foreach($resGroups as $group)

                @php
                    $panelId = 'movie-res-' . Str::slug(
                        $group->first()->resolution_label
                    );

                    $isFirst = $loop->first;
                @endphp


                <div class="res-panel">

                    <button
                        class="res-panel-header {{ $isFirst ? 'is-open' : '' }}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $panelId }}"
                        aria-expanded="{{ $isFirst ? 'true' : 'false' }}"
                    >

                        <span class="res-label">
                            {{ $group->first()->resolution_label }}
                        </span>

                        <span class="res-count">
                            {{ $group->count() }}
                        </span>

                        <i class="bi bi-chevron-down res-chevron"></i>

                    </button>


                    <div
                        id="{{ $panelId }}"
                        class="collapse {{ $isFirst ? 'show' : '' }}"
                    >

                        <div class="res-panel-body">

                            @foreach($group as $torrent)

                                <div class="torrent-item">

                                    <div class="torrent-main">

                                        <div class="torrent-name">

                                            <a
                                                href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}"
                                                class="torrent-link text-decoration-none"
                                            >
                                                {{ $torrent->name }}
                                            </a>

                                        </div>

                                        <div class="torrent-meta">

                                            <span class="torrent-size">
                                                💾 {{ number_format($torrent->size / 1073741824, 2) }} GB
                                            </span>

                                            <span class="seeders">
                                                🌱 {{ $torrent->seeders }}
                                            </span>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @else

        <div class="torrent-empty">

            <i class="bi bi-box-seam"></i>

            <h4>
                This title is not in our torrent database yet
            </h4>

            <p>
                This movie is in your library, but no torrent has been uploaded for it yet.
                If you would like to watch it, please submit a request and an uploader may fill it.
            </p>


            {{-- =================================================
                SAFE REQUEST DATA

                IMPORTANT:
                $libraryEntry can be NULL.
                Always use ?-> before reading its properties.
            ================================================== --}}
            @php
                $reqName = $movie['title']
                    ?? $libraryEntry?->title
                    ?? 'This movie';

                $reqTmdb = 'https://www.themoviedb.org/movie/' . $tmdbid;

                $reqImdb = !empty($movie['imdb_id'])
                    ? 'https://www.imdb.com/title/' . $movie['imdb_id'] . '/'
                    : null;

                if (!empty($movie['poster_path'])) {

                    $reqImage = 'https://image.tmdb.org/t/p/w500'
                        . $movie['poster_path'];

                } elseif (!empty($libraryEntry?->poster_path)) {

                    $reqImage = 'https://image.tmdb.org/t/p/w500'
                        . $libraryEntry->poster_path;

                } else {

                    $reqImage = null;

                }
            @endphp


            <a
                class="btn request-btn"
                href="{{ route('requests.create', array_filter([
                    'name'     => $reqName,
                    'tmdb_url' => $reqTmdb,
                    'imdb_url' => $reqImdb,
                    'image'    => $reqImage,
                ])) }}"
            >

                <i class="bi bi-megaphone me-1"></i>
                Make a Request

            </a>

        </div>

    @endif


    {{-- =========================================================
        YOU MIGHT ALSO LIKE
    ========================================================= --}}
    @if(!empty($recommendations))

        <div class="tmdb-recs mt-4">

            <div class="tmdb-recs-header">

                <div>

                    <h5 class="tmdb-recs-title mb-0">

                        <i class="bi bi-stars me-2"></i>

                        You Might Also Like

                    </h5>

                    <div class="tmdb-recs-subtitle">
                        Recommendations from The Movie Database That You Can Watch Online
                    </div>

                </div>


                <a
                    href="https://www.themoviedb.org/movie/{{ $tmdbid }}/recommendations"
                    target="_blank"
                    rel="noreferrer"
                    class="tmdb-recs-link"
                >

                    <i class="bi bi-box-arrow-up-right me-1"></i>

                    View All

                </a>

            </div>


            <div class="tmdb-recs-body">

                <div class="tmdb-recs-row">

                    @foreach($recommendations as $rec)

                        @if($rec['in_database'])

                            {{-- Movie is available on our website --}}
                            <a
                                href="{{ $rec['url'] }}"
                                class="tmdb-recs-card tmdb-recs-card-available text-decoration-none"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="View online"
                            >

                        @else

                            {{-- Movie is not in our database --}}
                            <div
                                class="tmdb-recs-card tmdb-recs-card-unavailable"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Not in database yet"
                            >

                        @endif


                        <div class="tmdb-recs-poster-wrap">

                            <img
                                src="{{ $rec['poster'] ?? '/images/not-found.jpg' }}"
                                loading="lazy"
                                class="tmdb-recs-poster {{ !$rec['in_database'] ? 'tmdb-recs-poster-unavailable' : '' }}"
                                alt="{{ $rec['title'] }}"
                            >


                            {{-- Database status --}}
                            <span class="tmdb-recs-status {{ $rec['in_database'] ? 'tmdb-recs-status-online' : 'tmdb-recs-status-missing' }}">

                                @if($rec['in_database'])

                                    <i class="bi bi-play-circle-fill"></i>

                                @else

                                    <i class="bi bi-database-x"></i>

                                @endif

                            </span>


                            @if(!empty($rec['rating']))

                                <span class="tmdb-recs-rating">

                                    <i class="bi bi-star-fill"></i>

                                    {{ number_format($rec['rating'], 1) }}

                                </span>

                            @endif

                        </div>


                        <div class="tmdb-recs-info">

                            <div class="tmdb-recs-name">
                                {{ $rec['title'] }}
                            </div>

                            @if(!empty($rec['year']))

                                <div class="tmdb-recs-year">
                                    {{ $rec['year'] }}
                                </div>

                            @endif

                        </div>


                        @if($rec['in_database'])

                            </a>

                        @else

                            </div>

                        @endif

                    @endforeach

                </div>

            </div>

        </div>

    @endif

</div>


@include('library.movies.partials.movies')

@endsection