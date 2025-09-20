@extends('layouts.app')

@section('content')
<div class="container my-5" style="background-color: #1e1e2f; padding: 2rem; border-radius: 0.5rem;">

    
    <div class="mb-4 text-center">
        <nav>
            <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                <a href="{{ route('snatch.seeding', ['userId' => $userId]) }}" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-cloud-upload"></i> Seeding
                </a>
                <a href="{{ route('snatch.leeching', ['userId' => $userId]) }}" class="btn btn-outline-warning fw-bold">
                    <i class="bi bi-arrow-down-circle"></i> Leeching
                </a>
                <a href="{{ route('snatch.hitAndRun', ['userId' => $userId]) }}" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-exclamation-triangle"></i> Hit and Run
                </a>
                <a href="{{ route('snatch.needToSeed', ['userId' => $userId]) }}" class="btn btn-outline-success fw-bold">
                    <i class="bi bi-hourglass-bottom"></i> Need to Seed
                </a>
            </div>
        </nav>
    </div>

    
    <h2 class="text-center text-white mb-4">
        <i class="bi bi-collection"></i> Snatchlist for {{ $user->name ?? 'Unknown User' }}
    </h2>

    <div class="d-flex flex-column align-items-center w-100">
        @if($snatchlist->isEmpty())
            <div class="alert alert-info text-center fw-bold w-100" role="alert">
                No torrents in the snatchlist.
            </div>
        @else
            @foreach($snatchlist as $snatch)
                @php
                    $gradient = $snatch->seeder
                        ? 'linear-gradient(90deg, rgba(40,167,69,0.1) 0%, rgba(255,255,255,0) 100%)'
                        : 'linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                    $ratio = $snatch->actual_downloaded > 0 ? number_format($snatch->uploaded / $snatch->actual_downloaded, 2) : '∞';
                    $ratioColor = $snatch->actual_downloaded > 0
                        ? ($snatch->uploaded / $snatch->actual_downloaded >= 1 ? 'text-success' : 'text-danger')
                        : 'text-success';
                @endphp

                <div class="card mb-3 w-100 border-0 position-relative snatch-card"
                     style="border-left: 6px solid {{ $snatch->seeder ? '#28a745' : '#dc3545' }};
                            background: {{ $gradient }};
                            transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap text-center text-md-start">

                       
                        <div class="mb-2 mb-md-0">
    <strong>
        <a href="{{ route('torrents.show', ['id' => $snatch->torrent->id]) }}" class="text-white text-decoration-none">
            <i class="bi bi-file-earmark-arrow-down"></i> {{ $snatch->torrent->name ?? 'Unknown' }}
        </a>
        @if(isset($snatch->torrent->owner) && $snatch->torrent->owner == $snatch->user_id)
            <span class="badge bg-warning text-dark ms-1">Torrent Owner</span>
        @endif
    </strong>
    <div class="text-muted small">
        Snatched On: {{ $snatch->created_at->format('Y-m-d') }}<br>
        Seeder: <span class="{{ $snatch->seeder ? 'text-success' : 'text-danger' }}">{{ $snatch->seeder ? 'Yes' : 'No' }}</span>
    </div>
</div>


                        
                        <div class="text-md-end ms-auto">
                            <span class="{{ $ratioColor }} fw-bold">Ratio: {{ $ratio }}</span><br>
                            <span class="badge bg-success me-1" data-bs-toggle="tooltip" title="Actual Uploaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_uploaded) }}">
                                Uploaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->uploaded) }}
                            </span>
                            <span class="badge bg-primary me-1" data-bs-toggle="tooltip" title="Actual Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_downloaded) }}">
                                Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->downloaded) }}
                            </span>
                            <span class="badge bg-secondary">
                                Seedtime: {{ \App\Helpers\FormatHelper::formatTime($snatch->seedtime) }}
                            </span>
                        </div>

                    </div>
                </div>
            @endforeach

           
            <div class="d-flex justify-content-center mt-4">
                {{ $snatchlist->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>


<style>
    .snatch-card {
        position: relative;
        overflow: hidden;
    }

    .snatch-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    .snatch-card a {
        text-decoration: none;
    }

    .snatch-card a:hover {
        text-decoration: underline;
    }

    .snatch-card::after {
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

    .snatch-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
