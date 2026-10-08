@extends('layouts.app')

@section('title', $movieDetails['title'] ?? $movie->name)

@section('content')

<div class="movie-page media-detail-page">

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
                Use <strong>uBlock Origin</strong> to help block ads and unwanted content while watching.
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

        @include('media.detail-hero', ['media' => $movie, 'mediaType' => 'movie', 'details' => $movieDetails, 'metadata' => $movieOm ?? []])

        @include('media.recommendations', ['mediaType' => 'movie'])

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

        @include('comments.discussion', ['commentTarget' => $movie, 'commentType' => \App\Models\Movie::class])

    </div>

</div>



@include('movies.partials.showstyles')
@if((optional(auth()->user())->user_class ?? 0) >= \App\Models\UserClass::USER && !empty($movie->imdb_id))
    @include('movies.partials.watch-modal')
@endif


@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/media-details.css') }}?v={{ filemtime(public_path('css/media-details.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/media-details.js') }}?v={{ filemtime(public_path('js/media-details.js')) }}" defer></script>
@endpush
