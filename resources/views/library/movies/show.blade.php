@extends('layouts.app')

@section('content')

@include('library.partials.hero-styles')

@include('library.partials.detail-hero')

<div data-library-subscription>
@include('library.movies.subscription')
</div>


{{-- =========================================================
    TORRENTS SECTION
========================================================= --}}
<div class="container py-5">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">

        <h4 class="theme-text mb-0">
            📥 Available Torrents
        </h4>

        @if($canWatchOnline)
            <a class="watch-online-btn" data-library-play href="https://v2.vidsrc.me/embed/{{ $onlineMedia->imdb_id }}" data-play-title="{{ $display['title'] }}" aria-haspopup="dialog">
                <i class="bi bi-play-circle"></i> Watch Online
            </a>
        @endif

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
    @if(request()->boolean('full_details'))
        @include('library.partials.recommendations')
    @else
        <div data-library-recommendations><p class="text-muted">Recommendations load as you scroll.</p><noscript><a href="{{ request()->fullUrlWithQuery(['full_details' => 1]) }}">View recommendations</a></noscript></div>
    @endif

</div>


@include('library.movies.partials.movies')

@include('library.partials.discussion')
@if($canWatchOnline)
    @include('library.partials.player')
@endif

@vite('resources/js/library-detail.js')
@endsection