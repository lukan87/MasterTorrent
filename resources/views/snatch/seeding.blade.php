@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Navigation Links as Buttons -->
        <div class="mb-4">
            <nav>
                <div class="btn-group" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-primary">Snatch List</a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-warning">Leeching</a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-danger">Hit and Run</a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-info">Need to Seed</a>
                </div>
            </nav>
        </div>

        <!-- Seeding Section -->
        <h1 class="mb-4">Seeding Torrents</h1>
        
        @if($seeding->isEmpty())
            <div class="alert alert-info" role="alert">
                No torrents currently being seeded.
            </div>
        @else
            <ul class="list-group">
                @foreach($seeding as $history)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Torrent:</strong> {{ $history->torrent->name ?? 'Unknown' }}<br>
                            <strong>Uploaded:</strong> <a data-bs-toggle="tooltip" title="Actual Upload: {{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded) ?? '0'}}">{{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}</a><br>
                            <strong>Downloaded:</strong> <a data-bs-toggle="tooltip" title="Actual Download: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? '0')}}">{{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}</a><br>
                            <strong>Seedtime:</strong> {{ \App\Helpers\FormatHelper::formatTime($history->seedtime) ?? '0' }}<br>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $seeding->links('pagination::bootstrap-5') }}
        @endif
    </div>
@endsection
