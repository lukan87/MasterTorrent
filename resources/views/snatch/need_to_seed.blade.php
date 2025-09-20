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
                <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                    <i class="bi bi-exclamation-triangle"></i> Hit and Run
                </a>
                <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                    <i class="bi bi-collection"></i> Snatch List
                </a>
            </div>
        </nav>
    </div>

    <h2 class="text-center text-white mb-4"><i class="bi bi-hourglass-bottom"></i> Torrents That Need Seeding</h2>

    <div class="d-flex flex-column align-items-center w-100">
        @if($needToSeed->isEmpty())
            <div class="alert alert-info text-center fw-bold w-100" role="alert">
                ✅ You have no torrents that need seeding.
            </div>
        @else
            @foreach($needToSeed as $torrent)
                @php
                    $ratio = number_format(($torrent->uploaded / max($torrent->actual_downloaded, 1)), 2);
                    $remainingSeedtime = max(0, 43200 - $torrent->seedtime);
                    $gradient = 'linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                @endphp

                <div class="card mb-3 w-100 border-0 position-relative seed-card"
                     style="border-left: 6px solid #dc3545; background: {{ $gradient }}; transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start">

                       
                        <div class="mb-2 mb-md-0">
                            <strong>
                                <a href="{{ route('torrents.show', ['id' => $torrent->torrent->id, 'slug' => $torrent->torrent->slug]) }}" class="text-white text-decoration-none">
                                    <i class="bi bi-file-earmark-arrow-down"></i> {{ $torrent->torrent->name ?? 'Unknown' }}
                                </a>
                            </strong>
                            <div class="text-muted small">
                                Created At: {{ $torrent->created_at->format('Y-m-d H:i') }}<br>
                                Uploaded: {{ \App\Helpers\FormatHelper::formatSize($torrent->uploaded) }} |
                                Downloaded: {{ \App\Helpers\FormatHelper::formatSize($torrent->actual_downloaded) }}
                            </div>
                        </div>

                        
                        <div class="ms-auto text-md-end">
                            <span class="text-muted fw-bold">Seedtime:</span>
                            <span class="badge {{ $torrent->seedtime >= 86400 || $ratio >= 1.00 ? 'bg-success' : 'bg-danger' }}">
                                {{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}
                            </span><br>

                            <span class="text-muted fw-bold">Ratio:</span>
                            <span class="{{ $ratio >= 1.00 ? 'text-success' : 'text-danger' }}">
                                {{ $ratio }}
                            </span><br>

                            <span class="text-muted fw-bold">Remaining:</span>
                            <span class="{{ $remainingSeedtime > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $remainingSeedtime > 0 ? \App\Helpers\FormatHelper::formatTime($remainingSeedtime) : 'Completed' }}
                            </span><br>

                            <div class="mt-2 d-flex justify-content-center justify-content-md-end gap-2 flex-wrap">
                                @if(auth()->id() === $torrent->user_id)
                                    <a href="{{ route('torrents.download', ['id' => $torrent->torrent->id, 'slug' => $torrent->torrent->slug]) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Download to continue seeding">
                                        <i class="bi bi-download"></i>
                                    </a>

                                    <form action="{{ route('bonus.buySeedtime') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}">
                                        <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="1000 seedbonus points">
                                            <i class="bi bi-coin"></i>
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->id == 3 && auth()->id() !== $torrent->user_id)
                                    <form action="{{ route('snatch.deleteNeedToSeed', ['userId' => $torrent->user_id, 'torrentId' => $torrent->torrent->id]) }}" method="POST"
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
                {{ $needToSeed->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>


<style>
    .seed-card {
        position: relative;
        overflow: hidden;
    }

    .seed-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    .seed-card a {
        text-decoration: none;
    }

    .seed-card a:hover {
        text-decoration: underline;
    }

    .seed-card::after {
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

    .seed-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
