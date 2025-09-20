@extends('layouts.app')

@section('content')
<div class="container my-5" style="background-color: #1e1e2f; padding: 2rem; border-radius: 0.5rem;">

    
    <div class="mb-4 text-center">
        <nav>
            <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-cloud-upload"></i> Seeding
                </a>
                <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                    <i class="bi bi-collection"></i> Snatch List
                </a>
                <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-exclamation-triangle"></i> Hit and Run
                </a>
                <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                    <i class="bi bi-hourglass-split"></i> Need to Seed
                </a>
            </div>
        </nav>
    </div>

    
    <h2 class="text-center text-white mb-4">
        <i class="bi bi-arrow-down-circle"></i> Leeching Torrents
    </h2>

    <div class="d-flex flex-column align-items-center w-100">
        @if($leeching->isEmpty())
            <div class="alert alert-info text-center fw-bold w-100" role="alert">
                🎉 You are not currently leeching any torrents.
            </div>
        @else
            @foreach($leeching as $peer)
                <div class="card mb-3 w-100 border-0 position-relative leech-card"
                     style="border-left: 6px solid #ffc107;
                            background: linear-gradient(90deg, rgba(255,193,7,0.1) 0%, rgba(255,255,255,0) 100%);
                            transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start">

                        
                        <div class="mb-2 mb-md-0">
                            <strong>
                                <a href="{{ route('torrents.show', ['id' => $peer->torrent->id]) }}" class="text-white text-decoration-none">
                                    <i class="bi bi-file-earmark-arrow-down"></i> {{ $peer->torrent->name ?? 'Unknown' }}
                                </a>
                            </strong>
                        </div>

                        
                        <div class="ms-auto text-md-end">
                            <span class="text-muted fw-bold">Leeching Since:</span><br>
                            <span class="badge bg-warning text-dark">
                                {{ $peer->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>

                    </div>
                </div>
            @endforeach

            
            <div class="d-flex justify-content-center mt-4">
                {{ $leeching->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>


<style>
    .leech-card {
        position: relative;
        overflow: hidden;
    }

    .leech-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    .leech-card a {
        text-decoration: none;
    }

    .leech-card a:hover {
        text-decoration: underline;
    }

    .leech-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-20deg);
        transition: all 0.3s ease-in-out;
    }

    .leech-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
