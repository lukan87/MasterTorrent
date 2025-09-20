@extends('layouts.app')

@section('content')
<div class="container my-5" style="background-color: #1e1e2f; padding: 2rem; border-radius: 0.5rem;">

    
    <div class="mb-4 text-center">
        <nav>
            <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-cloud-upload"></i> Seeding
                </a>
                <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                    <i class="bi bi-arrow-down-circle"></i> Leeching
                </a>
                <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-collection"></i> Snatch List
                </a>
                <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                    <i class="bi bi-hourglass-split"></i> Need to Seed
                </a>
            </div>
        </nav>
    </div>

    <h2 class="text-center text-white mb-4">
        <i class="bi bi-exclamation-triangle"></i> Hit-and-Run Torrents
        @if($user->hit_and_run_count > 0)
            <span class="badge bg-dark ms-2">{{ $user->hit_and_run_count }}</span>
        @endif
    </h2>

    <div class="d-flex flex-column align-items-center w-100">
        @if($hitAndRun->isEmpty())
            <div class="alert alert-info text-center fw-bold w-100" role="alert">
                🎉 You have no hit-and-run torrents!
            </div>
        @else
            @foreach($hitAndRun as $history)
                @php
                    $ratio = number_format(($history->uploaded / max($history->downloaded, 1)), 2);
                    $gradient = 'linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                @endphp

                <div class="card mb-3 w-100 border-0 position-relative hnr-card"
                     style="border-left: 6px solid #dc3545; background: {{ $gradient }}; transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start">

                        
                        <div class="mb-2 mb-md-0">
                            <strong>
                                <a href="{{ route('torrents.show', ['id' => $history->torrent->id]) }}" class="text-white text-decoration-none">
                                    <i class="bi bi-file-earmark-arrow-down"></i> {{ $history->torrent->name ?? 'Unknown' }}
                                </a>
                            </strong>
                            <div class="text-muted small">
                                Uploaded: {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}<br>
                                Downloaded: {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}
                            </div>
                        </div>

                       
                        <div class="ms-auto text-md-end">
                            <div class="mb-1">
                                <span class="text-muted fw-bold">Seedtime:</span>
                                <span class="badge bg-danger">{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</span>
                            </div>

                            <div class="mb-1">
                                <span class="text-muted fw-bold">Ratio:</span>
                                <span class="{{ $ratio < 1 ? 'text-danger' : 'text-success' }}">{{ $ratio }}</span>
                            </div>

                            <span class="badge bg-danger">Hit-and-Run</span>

                            <div class="mt-2 d-flex justify-content-center justify-content-md-end gap-2 flex-wrap">
                                @if(auth()->id() === $history->user_id)
                                    <form action="{{ route('bonus.removeHNR') }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="5000 seedbonus points">
                                            <i class="bi bi-cash-coin"></i> Remove HNR
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->id == 3 && auth()->id() !== $history->user_id)
                                    <form action="{{ route('snatch.deleteHNR', ['userId' => $history->user_id, 'torrentId' => $history->torrent->id]) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to remove this torrent from the user\'s history?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Remove from user's history"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

           
            <div class="d-flex justify-content-center mt-4">
                {{ $hitAndRun->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>


<style>
    .hnr-card {
        position: relative;
        overflow: hidden;
    }

    .hnr-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    .hnr-card a {
        text-decoration: none;
    }

    .hnr-card a:hover {
        text-decoration: underline;
    }

    .hnr-card::after {
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

    .hnr-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
