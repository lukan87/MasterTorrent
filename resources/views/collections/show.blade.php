@extends('layouts.app')

@section('title', 'Coleection: ' . $collection['name'])

@section('content')


<div class="container mt-4">

    {{-- =========================
       COLLECTION HEADER
    ========================== --}}
    <div class="movie-header-container mb-5">
        <div class="movie-header-card">
            <div class="movie-header-content row">

                {{-- POSTER --}}
                <div class="col-12 col-md-3 poster-column">
                    <div class="poster-wrapper">
                        <img src="{{ $collection['poster'] ?? asset('images/noposter.jpg') }}"
     class="movie-poster"
     alt="{{ $collection['name'] }}">

                    </div>
                </div>

                {{-- INFO --}}
                <div class="col-12 col-md-9 info-column">
                    <h1 class="movie-title">
                        {{ $collection['name'] }}
                    </h1>

                    <p class="text-muted mt-2 fs-5">
                        {{ $collection['overview'] }}
                    </p>

                    <div class="mt-3">
                        <span class="badge bg-success">
                            {{ $collection['uploaded'] }} / {{ $collection['total'] }} Uploaded
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- =========================
       MOVIES IN COLLECTION
    ========================== --}}
    @foreach($movies as $movie)

        @php
            $torrents = collect($movie['torrents'] ?? []);
            $hasTorrents = $torrents->isNotEmpty();
            $primaryTorrent = $torrents->first();
            $isOnline    = $movie['is_online'];
        @endphp

        <div class="movie-header-container mb-4">
            <div class="movie-header-card {{ !$hasTorrents ? 'opacity-75' : '' }}">
                <div class="movie-header-content row">

                    {{-- POSTER --}}
                    <div class="col-12 col-md-3 poster-column">
                        <div class="poster-wrapper">
                            <img src="{{ $movie['poster'] }}"
                                 class="movie-poster {{ !$hasTorrents ? 'grayscale' : '' }}"
                                 alt="{{ $movie['title'] }}">
                        </div>
                        @if($isOnline && !$hasTorrents)
    <div class="text-center text-info fs-6 mt-1">
        <i class="bi bi-info-circle"></i> Online entry exists — upload torrent
    </div>
@endif


                        {{-- ACTION --}}
<div class="trailer-section text-center mt-3">

    {{-- UPLOAD TORRENT (independent of online status) --}}
    @if(
        Auth::check() &&
        !$hasTorrents &&
        (
            Auth::user()->user_class >= \App\Models\UserClass::UPLOADER ||
            Auth::user()->uploadpos === 'yes'
        )
    )
        <a href="{{ route('torrents.create', ['tmdb' => $movie['tmdb_id']]) }}"
           class="btn btn-sm btn-warning me-1">
            <i class="bi bi-upload"></i> Upload
        </a>
    @endif


    {{-- ONLINE STATUS (admin / staff only) --}}
    @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

        @if($isOnline)
            <a href="{{ route('movies.show', $movie['movie_id']) }}"
               class="btn btn-sm btn-outline-success">
                <i class="bi bi-cloud-check"></i> Uploaded Online
            </a>
        @else
            <a href="{{ route('movies.create', [
                'tmdb'  => $movie['tmdb_id'],
                'title' => $movie['title']
            ]) }}"
               class="btn btn-sm btn-outline-info">
                <i class="bi bi-cloud-plus"></i> Add Online
            </a>
        @endif

    @endif

</div>



                    </div>

                    {{-- INFO --}}
                    <div class="col-12 col-md-9 info-column">

                        {{-- TITLE --}}
                        <h2 class="movie-title">
                            {{ $movie['title'] }}
                            <span class="release-year">({{ $movie['year'] }})</span>
                        </h2>

                        {{-- OVERVIEW --}}
                        <p class="text-muted mt-2 fs-5">
                            {{ $movie['overview'] }}
                        </p>

                        {{-- TORRENTS --}}
                        @if($hasTorrents)
                            <div class="mt-3">

                                <h6 class="text-uppercase text-muted mb-2">
                                    Available Torrents
                                </h6>

                                @foreach($torrents as $torrent)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                        <div>
                                            <a href="{{ route('torrents.show', $torrent->id) }}"
                                               class="fw-semibold text-decoration-none">
                                                {{ $torrent->name }}
                                            </a>

                                            <div class="small text-muted">
                                                {{ App\Helpers\FormatHelper::formatSize($torrent->size) }}
                                                • {{ $torrent->created_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        <div class="text-end fw-bold">
                                            <span class="text-success">{{ $torrent->seeders }}</span>
                                            /
                                            <span class="text-danger">{{ $torrent->leechers }}</span>
                                        </div>

                                    </div>
                                @endforeach

                            </div>

                            @else
                            <p class="text-muted mt-2 fs-5">
                            No Torrents Available Yet!!
                        </p>
                        @endif

                    </div>

                </div>
            </div>
        </div>

    @endforeach

</div>





<style>

.collection-card,
.collection-movie-card {
    background: rgba(20, 22, 30, 0.85);
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
}

.grayscale {
    filter: grayscale(100%) brightness(0.7);
}

.upload-badge {
    margin-top: 5px;
    font-size: 0.75rem;
    background: #ff4d4d;
    color: #fff;
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
}


 /* Style adjustments for background display */
   html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

html::before {
    content: '';
    position: fixed;
    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,1)), url('{{ $collection['backdrop_path'] }}');
    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;
    opacity: 0.7;
    z-index: -1;
}
</style>


@endsection