@extends('layouts.app')

@section('title', 'Collection: ' . $collection['name'])

@push('styles')
<link rel="stylesheet" href="{{ asset('css/collection-details.css') }}?v={{ filemtime(public_path('css/collection-details.css')) }}">
@endpush

@section('content')
@php
    $films = collect($movies);
    $totalFilms = $films->count();
    $availableFilms = $films->filter(fn ($film) => collect($film['torrents'] ?? [])->isNotEmpty())->count();
    $onlineFilms = $films->where('is_online', true)->count();
    $releaseCount = $films->sum(fn ($film) => collect($film['torrents'] ?? [])->count());
    $completion = $totalFilms ? (int) round($availableFilms / $totalFilms * 100) : 0;
    $years = $films->pluck('year')->filter()->sort()->values();
    $viewer = Auth::user();
    $canUpload = $viewer && ($viewer->user_class >= \App\Models\UserClass::UPLOADER || $viewer->uploadpos === 'yes');
    $isModerator = $viewer && $viewer->user_class >= \App\Models\UserClass::MODERATOR;
@endphp

<div class="collection-page">
    <a href="{{ route('collections.index') }}" class="cx-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> All collections</a>

    <header class="cx-hero">
        @if(!empty($collection['backdrop_path']))
            <img src="{{ $collection['backdrop_path'] }}" class="cx-backdrop" alt="" aria-hidden="true" decoding="async">
        @endif
        <div class="cx-hero-content">
            <img class="cx-hero-poster" src="{{ $collection['poster'] ?: asset('images/not-found.jpg') }}" alt="{{ $collection['name'] }} poster" width="200" height="300" decoding="async">
            <div class="cx-hero-info">
                <span class="cx-eyebrow"><i class="bi bi-collection-play" aria-hidden="true"></i> The collection</span>
                <h1>{{ $collection['name'] }}</h1>
                <div class="cx-hero-meta">
                    <span>{{ $totalFilms }} {{ $totalFilms === 1 ? 'film' : 'films' }}</span>
                    @if($years->isNotEmpty())
                        <span>{{ $years->first() }}@if($years->last() !== $years->first())–{{ $years->last() }}@endif</span>
                    @endif
                    <span>In release order</span>
                </div>
                @if(!empty($collection['overview']))
                    <p class="cx-hero-overview">{{ $collection['overview'] }}</p>
                @endif
                <div class="cx-hero-actions">
                    <a href="#collection-films" class="cx-button cx-button-primary"><i class="bi bi-film" aria-hidden="true"></i> Explore the films <i class="bi bi-arrow-down" aria-hidden="true"></i></a>
                    <span class="cx-hero-note">{{ number_format($releaseCount) }} torrent {{ $releaseCount === 1 ? 'release' : 'releases' }} available</span>
                </div>
            </div>
        </div>
    </header>

    <section class="cx-dashboard" aria-label="Collection availability">
        <div class="cx-completion">
            <div class="cx-completion-label"><span><i class="bi bi-stack" aria-hidden="true"></i> Collection coverage</span><strong>{{ $completion }}%</strong></div>
            <progress value="{{ $availableFilms }}" max="{{ max(1, $totalFilms) }}" aria-label="{{ $availableFilms }} of {{ $totalFilms }} films have torrents">{{ $completion }}%</progress>
            <span class="cx-caption">{{ $availableFilms }} of {{ $totalFilms }} films have torrents</span>
        </div>
        <div class="cx-stat cx-stat-available"><i class="bi bi-check-circle" aria-hidden="true"></i><div><strong>{{ $availableFilms }}</strong><span>Available</span></div></div>
        <div class="cx-stat cx-stat-missing"><i class="bi bi-hourglass-split" aria-hidden="true"></i><div><strong>{{ $totalFilms - $availableFilms }}</strong><span>Awaiting torrents</span></div></div>
        <div class="cx-stat cx-stat-online"><i class="bi bi-play-circle" aria-hidden="true"></i><div><strong>{{ $onlineFilms }}</strong><span>Online entries</span></div></div>
    </section>

    <section id="collection-films" class="cx-films" aria-labelledby="collection-films-title">
        <div class="cx-section-heading"><div><span class="cx-eyebrow">The complete story</span><h2 id="collection-films-title">Films in this collection</h2></div><span class="cx-film-count">{{ $totalFilms }} {{ $totalFilms === 1 ? 'film' : 'films' }}</span></div>
        @forelse($films as $movie)
            @php
                $torrents = collect($movie['torrents'] ?? []);
                $hasTorrents = $torrents->isNotEmpty();
                $isOnline = (bool) ($movie['is_online'] ?? false);
                $libraryUrl = route('library.movies.show', [$movie['tmdb_id'], \Illuminate\Support\Str::slug($movie['title'])]);
            @endphp
            <article class="cx-film {{ $hasTorrents ? 'cx-film-available' : 'cx-film-missing' }}">
                <div class="cx-film-art">
                    <a href="{{ $libraryUrl }}" aria-label="View {{ $movie['title'] }}">
                        <img src="{{ $movie['poster'] ?: asset('images/not-found.jpg') }}" alt="{{ $movie['title'] }} poster" width="120" height="180" loading="lazy" decoding="async">
                    </a>
                    <span class="cx-film-number" aria-label="Film {{ $loop->iteration }}">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="cx-film-body">
                    <div class="cx-film-heading">
                        <div>
                            <div class="cx-film-kicker">Film {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} @if(!empty($movie['year']))<span> / {{ $movie['year'] }}</span>@endif</div>
                            <h3><a href="{{ $libraryUrl }}">{{ $movie['title'] }}</a></h3>
                        </div>
                        <span class="cx-status {{ $hasTorrents ? 'cx-status-available' : 'cx-status-missing' }}"><i class="bi {{ $hasTorrents ? 'bi-check-circle-fill' : 'bi-hourglass' }}" aria-hidden="true"></i>{{ $hasTorrents ? 'Available' : 'Awaiting torrents' }}</span>
                    </div>
                    @if(!empty($movie['overview']))<p class="cx-film-overview">{{ $movie['overview'] }}</p>@endif
                    <div class="cx-film-actions">
                        <a href="{{ $libraryUrl }}" class="cx-film-link">Movie details <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        @if($canUpload && ! $hasTorrents)
                            <a href="{{ route('torrents.create', ['tmdb' => $movie['tmdb_id']]) }}" class="cx-button cx-button-small"><i class="bi bi-upload" aria-hidden="true"></i> Upload torrent</a>
                        @endif
                        @if($isModerator)
                            @if($isOnline)
                                <a href="{{ route('movies.show', $movie['movie_id']) }}" class="cx-button cx-button-small cx-button-online"><i class="bi bi-cloud-check" aria-hidden="true"></i> Online entry</a>
                            @else
                                <a href="{{ route('movies.create', ['tmdb' => $movie['tmdb_id'], 'title' => $movie['title']]) }}" class="cx-button cx-button-small"><i class="bi bi-cloud-plus" aria-hidden="true"></i> Add online</a>
                            @endif
                        @elseif($isOnline)
                            <span class="cx-online-note"><i class="bi bi-cloud-check" aria-hidden="true"></i> Online entry exists</span>
                        @endif
                    </div>
                    @if($hasTorrents)
                        <details class="cx-releases" @if($loop->first) open @endif>
                            <summary><span><i class="bi bi-download" aria-hidden="true"></i> Available torrents <span class="cx-release-count">{{ $torrents->count() }}</span></span><i class="bi bi-chevron-down cx-chevron" aria-hidden="true"></i></summary>
                            <div class="cx-release-list">
                                @foreach($torrents as $torrent)
                                    <div class="cx-release">
                                        <div class="cx-release-info">
                                            <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}" class="cx-release-title">{{ $torrent->name }}</a>
                                            <div class="cx-release-meta"><span><i class="bi bi-hdd" aria-hidden="true"></i> {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}</span><time datetime="{{ $torrent->created_at->toIso8601String() }}" title="{{ $torrent->created_at->format('j F Y, H:i') }}"><i class="bi bi-clock" aria-hidden="true"></i> {{ $torrent->created_at->diffForHumans() }}</time></div>
                                        </div>
                                        <div class="cx-peers"><span class="cx-seeders" aria-label="{{ number_format($torrent->seeders) }} seeders" title="Seeders"><i class="bi bi-arrow-up" aria-hidden="true"></i>{{ number_format($torrent->seeders) }}</span><span class="cx-leechers" aria-label="{{ number_format($torrent->leechers) }} leechers" title="Leechers"><i class="bi bi-arrow-down" aria-hidden="true"></i>{{ number_format($torrent->leechers) }}</span></div>
                                        <a href="{{ route('torrents.show', [$torrent->id, $torrent->slug]) }}" class="cx-release-open" aria-label="View torrent {{ $torrent->name }}"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <div class="cx-no-releases"><i class="bi bi-inbox" aria-hidden="true"></i><span>No torrents yet. @if($canUpload)Help complete the collection with an upload.@else Check back for new releases.@endif</span></div>
                    @endif
                </div>
            </article>
        @empty
            <div class="cx-empty"><i class="bi bi-film" aria-hidden="true"></i><h3>No films listed yet</h3><p>This collection does not have any films to display.</p></div>
        @endforelse
    </section>
</div>
@endsection
