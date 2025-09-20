@extends('layouts.app')

@section('content')
<div class="container-fluid my-5" style="background-color: #4f4d515f; padding: 2rem; border-radius: 0.5rem;">

  
    <div class="mb-4 text-center">
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Torrent
        </a>
    </div>

    <h3 class="mb-5 text-center text-white">Users That Finished: 
        <span class="text-info">{{ $torrent->name }}</span>
    </h3>

    <div class="d-flex flex-column align-items-center w-100">

        @if($histories->isEmpty())
            <p class="text-light">No users have finished this torrent yet.</p>
        @else
            @foreach($histories as $history)
                @php
                    $gradient = $history->seeder
                        ? 'linear-gradient(90deg, rgba(40,167,69,0.1) 0%, rgba(255,255,255,0) 100%)'
                        : 'linear-gradient(90deg, rgba(220,53,69,0.1) 0%, rgba(255,255,255,0) 100%)';
                @endphp

                <div class="card shadow-sm mb-3 w-100 border-0 position-relative history-card"
                     style="border-left: 6px solid {{ $history->seeder ? '#28a745' : '#dc3545' }};
                            background: {{ $gradient }};
                            transition: transform 0.3s, box-shadow 0.3s;">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start flex-nowrap">

                       
                        <div class="me-3 text-white">
                            <strong>
                                @if($history->user)
                                    <a href="{{ route('profile.show', ['id' => $history->user->id, 'name' => $history->user->name]) }}" class="text-white">
                                        {{ $history->user->name }}
                                    </a>
                                    @if($history->user->id === $torrent->owner)
                                        <span class="badge bg-warning text-dark ms-1">Torrent Owner</span>
                                    @endif
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </strong>
                            <div class="text-muted small">
                                Started: {{ $history->created_at->format('Y-m-d H:i:s') }}<br>
                                Finished: {{ $history->completed_at ? $history->completed_at->format('Y-m-d H:i:s') : 'Incomplete' }}
                            </div>
                        </div>

                       
                        <div class="mb-2 mb-md-0">
                            <span class="badge bg-success me-1"><i class="bi bi-upload me-1"></i>{{ App\Helpers\FormatHelper::formatSize($history->uploaded) }}</span>
                            <span class="badge bg-primary me-1"><i class="bi bi-download me-1"></i>{{ App\Helpers\FormatHelper::formatSize($history->downloaded) }}</span>
                            <span class="badge bg-secondary"><i class="bi bi-clock-history me-1"></i>{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</span>
                        </div>

                       
                        <div class="mt-2 mt-md-0 text-md-end ms-auto">
                            <span class="badge {{ $history->seeder ? 'bg-success' : 'bg-danger' }}">
                                {{ $history->seeder ? 'Seeding' : 'Not Seeding' }}
                            </span>
                            <div class="small text-muted mt-1">
                                Last Active: {{ $history->updated_at->format('Y-m-d H:i:s') }}<br>
                                Time Leeching: 
                                @if ($history->completed_at)
                                    {{ $history->completed_at->diffForHumans($history->created_at, true) }}
                                @else
                                    Incomplete
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

           
            <div class="mt-4">
                {{ $histories->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>


<style>
    .history-card {
        position: relative;
        overflow: hidden;
    }

    /* Hover lift & shadow */
    .history-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.5);
    }

    /* Username link hover */
    .history-card a {
        text-decoration: none;
    }

    .history-card a:hover {
        text-decoration: underline;
    }

    /* Shimmer effect */
    .history-card::after {
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

    .history-card:hover::after {
        left: 125%;
        transition: all 0.6s ease-in-out;
    }
</style>
@endsection
