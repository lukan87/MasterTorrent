@extends('layouts.app')

@section('content')
<div class="container-fluid my-5" style="background-color: #4f4d515f; padding: 2rem; border-radius: 0.5rem;">
    <h3 class="mb-4 text-center">Peers for Torrent:
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-info">
            {{ $torrent->name }}
        </a>
    </h3>

    <div class="d-flex flex-column align-items-center w-100">

       
        @if($seeders)
            <div class="col-md-12 mb-4 w-100">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Seeders</h5>
                    </div>
                    <div class="card-body">
                        @if($seeders->isEmpty())
                            <p class="text-center text-muted">No seeders available for this torrent.</p>
                        @else
                            @foreach ($seeders as $seeder)
                                @php
                                    $gradient = 'linear-gradient(90deg, rgba(40,167,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                                @endphp
                                <div class="card mb-3 w-100 border-0 position-relative peer-card"
                                     style="border-left: 6px solid #28a745; background: {{ $gradient }}; transition: transform 0.3s, box-shadow 0.3s;">
                                    <div class="card-body d-flex justify-content-between align-items-center flex-nowrap text-center text-md-start">

                                        
                                        <div class="me-3">
                                            <strong>
                                                <a href="{{ route('profile.show', ['id' => $seeder->user->id, 'name' => $seeder->user->name ?? 'Unknown']) }}">
                                                    {{ $seeder->user->name }}
                                                </a>
                                            </strong>
                                            @if($seeder->user->id === $torrent->owner)
                                                <span class="badge bg-warning text-dark ms-1">Torrent Owner</span>
                                            @endif
                                            - <span class="text-muted">{{ $seeder->agent }}</span>
                                        </div>

                                       
                                        <div class="text-end ms-auto">
                                            <small>
                                                IP: <span class="text-nowrap">{{ $seeder->ip }}</span> |
                                                <span class="text-secondary">{{ $seeder->created_at->diffForHumans() }}</span>
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $seeders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif

       
        @if($leechers)
            <div class="col-md-12 mb-4 w-100">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Leechers</h5>
                    </div>
                    <div class="card-body">
                        @if($leechers->isEmpty())
                            <p class="text-center text-muted">No leechers available for this torrent.</p>
                        @else
                            @foreach ($leechers as $leecher)
                                @php
                                    $gradient = 'linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                                @endphp
                                <div class="card mb-3 w-100 border-0 position-relative peer-card"
                                     style="border-left: 6px solid #dc3545; background: {{ $gradient }}; transition: transform 0.3s, box-shadow 0.3s;">
                                    <div class="card-body d-flex justify-content-between align-items-center flex-nowrap text-center text-md-start">

                                        
                                        <div class="me-3">
                                            <strong>
                                                <a href="{{ route('profile.show', ['id' => $leecher->user->id, 'name' => $leecher->user->name ?? 'Unknown']) }}">
                                                    {{ $leecher->user->name }}
                                                </a>
                                            </strong>
                                            @if($leecher->user->id === $torrent->owner)
                                                <span class="badge bg-warning text-dark ms-1">Torrent Owner</span>
                                            @endif
                                            - <span class="text-muted">{{ $leecher->agent }}</span>
                                        </div>

                                        
                                        <div class="text-end ms-auto">
                                            <small>
                                                Left to Download: <span class="text-nowrap">{{ \App\Helpers\FormatHelper::formatSize($leecher->left) }}</span><br>
                                                IP: <span class="text-nowrap">{{ $leecher->ip }}</span> |
                                                <span class="text-secondary">{{ $leecher->created_at->diffForHumans() }}</span>
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $leechers->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>


<style>
    .peer-card {
        position: relative;
        overflow: hidden;
    }

    /* Hover lift & shadow */
    .peer-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.25);
    }

    /* Username link hover */
    .peer-card a {
        text-decoration: none;
    }

    .peer-card a:hover {
        text-decoration: underline;
    }

    /* Shimmer effect */
    .peer-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-20deg);
        transition: all 0.3s ease-in-out;
    }

    .peer-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
