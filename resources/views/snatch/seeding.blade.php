@extends('layouts.app')

@section('content')
<div class="container my-5" style="background-color: #1e1e2f; padding: 2rem; border-radius: 0.5rem;">

    
    <div class="mb-4 text-center">
        <nav>
            <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-collection"></i> Snatch List
                </a>
                <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                    <i class="bi bi-arrow-down-circle"></i> Leeching
                </a>
                <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-exclamation-triangle"></i> Hit and Run
                </a>
                <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-info fw-bold">
                    <i class="bi bi-hourglass-bottom"></i> Need to Seed
                </a>
            </div>
        </nav>
    </div>

    
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white text-center">
            <h2 class="fw-bold"><i class="bi bi-cloud-upload"></i> Seeding Torrents</h2>
        </div>
        <div class="card-body d-flex flex-column align-items-center w-100">

            @if($seeding->isEmpty())
                <div class="alert alert-info text-center fw-bold w-100" role="alert">
                    No torrents currently being seeded.
                </div>
            @else
                @foreach($seeding as $history)
                    @php
                        $gradient = 'linear-gradient(90deg, rgba(40,167,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                    @endphp
                    <div class="card mb-3 w-100 border-0 position-relative seeding-card"
                         style="border-left: 6px solid #28a745; background: {{ $gradient }}; transition: transform 0.3s, box-shadow 0.3s;">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap text-center text-md-start">

                           
                            <div class="mb-2 mb-md-0">
                                <strong>
                                    <a href="{{ route('torrents.show', ['id' => $history->torrent->id]) }}" class="text-white text-decoration-none">
                                        <i class="bi bi-file-earmark-arrow-down"></i> {{ $history->torrent->name ?? 'Unknown' }}
                                    </a>
                                </strong>
                            </div>

                            
                            <div class="text-md-end ms-auto">
                                <span class="badge bg-success me-1" data-bs-toggle="tooltip" title="Actual Upload: {{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded) }}">
                                    {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}
                                </span>
                                <span class="badge bg-primary me-1" data-bs-toggle="tooltip" title="Actual Download: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? '0') }}">
                                    {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}
                                </span>
                                <span class="badge bg-secondary">{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) ?? '0' }}</span>
                            </div>

                        </div>
                    </div>
                @endforeach

                
                <div class="d-flex justify-content-center mt-4">
                    {{ $seeding->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>
    </div>
</div>


<style>
    .seeding-card {
        position: relative;
        overflow: hidden;
    }

    .seeding-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    .seeding-card a {
        text-decoration: none;
    }

    .seeding-card a:hover {
        text-decoration: underline;
    }

    .seeding-card::after {
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

    .seeding-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
