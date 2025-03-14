@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Navigation Links as Buttons -->
        <div class="mb-4">
            <nav>
                <div class="btn-group" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-primary">Seeding</a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-warning">Snatch List</a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-danger">Hit and Run</a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-success">Need to Seed</a>
                </div>
                
            </nav>
        </div>

        <!-- Leeching Section -->
        <h1 class="mb-4">Leeching Torrents</h1>
        
        @if($leeching->isEmpty())
            <div class="alert alert-info" role="alert">
                You are not currently leeching any torrents.
            </div>
        @else
            <ul class="list-group">
                @foreach($leeching as $peer)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Torrent:</strong> {{ $peer->torrent->name ?? 'Unknown' }}<br>
                            {{-- <strong>Uploaded:</strong> <a data-bs-toggle="tooltip" title="Actual Upload: {{ \App\Helpers\FormatHelper::formatSize($peer->history->actual_uploaded)}}">{{ \App\Helpers\FormatHelper::formatSize($peer->history->uploaded) ?? '0' }}</a><br> --}}
                            {{-- <strong>Downloaded:</strong> <a data-bs-toggle="tooltip" title="Actual Download: {{ \App\Helpers\FormatHelper::formatSize($peer->history->actual_downloaded)}}">{{ \App\Helpers\FormatHelper::formatSize($peer->history->downloaded) ?? '0' }}</a><br> --}}
                            {{-- <strong>Leeching Time:</strong> {{ \App\Helpers\FormatHelper::formatTime($peer->seedtime) ?? '0' }}<br> --}}
                            <strong>Leeching Since:</strong> {{ $peer->created_at->format('Y-m-d H:i') }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
