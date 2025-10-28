@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="row g-4">
        <!-- Torrent Summary Card -->
        <div class="col-md-9">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">{{ $torrent->name }}</h2>
                    <span class="badge bg-secondary">{{ strtoupper($torrent->info_hash) }}</span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Torrent Stats -->
                        <div class="col-md-4">
                            <h4 class="mb-3 text-primary">Torrent Stats</h4>
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Seeders</strong>
                                    <span class="badge bg-success rounded-pill">{{ $seedersCount }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Leechers</strong>
                                    <span class="badge bg-danger rounded-pill">{{ $leechersCount }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Times Completed</strong>
                                    <span class="badge bg-info text-dark rounded-pill">{{ $timesCompleted }}</span>
                                </li>
                            </ul>

                            <!-- Seeder / Leecher User Lists -->
                            <div class="mb-4">
                                <h5 class="text-success">Seeders</h5>
                                @if($seeders->isEmpty())
                                    <p class="text-muted">No active seeders</p>
                                @else
                                   <div class="{{ $seeders->count() > 15 ? 'scrollable-seeders' : '' }}">
    <ul class="list-group small mb-0">
        @foreach($seeders as $event)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    {{ $event->user->name ?? 'Unknown' }}
                    <br>
                    <small class="text-muted">
                        {{ $event->created_at->diffForHumans() }}
                    </small>
                </div>
                <div class="text-end">
                    <small class="d-block text-success">
                        ↑ {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}
                    </small>
                    <small class="d-block text-muted">
                        ↓ {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}
                    </small>
                </div>
            </li>
        @endforeach
    </ul>
</div>

                                @endif
                            </div>

                            <div>
                                <h5 class="text-danger">Leechers</h5>
                                @if($leechers->isEmpty())
                                    <p class="text-muted">No active leechers</p>
                                @else
                                    <ul class="list-group small">
                                        @foreach($leechers as $peer)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $peer->user->name ?? 'Unknown' }}
                                                <small class="text-muted">{{ \App\Helpers\FormatHelper::formatSize($peer->downloaded) }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <!-- Download History -->
                        <div class="col-md-8">
                            <h4 class="mb-3 text-primary">Download History</h4>
                            @if($history->isEmpty())
                                <div class="alert alert-warning">No download history available.</div>
                            @else
                                <div class="download-history-container">
                                    @foreach($history as $event)
                                        @php
                                            $user = $event->user;
                                            $isSeeder = $event->seeder;
                                        @endphp
                                        <div class="card mb-3 border-light shadow-sm">
                                            <div class="card-body">
                                                <h5 class="card-title mb-1">{{ $user->name ?? 'Unknown User' }}</h5>
                                                <small class="text-muted">{{ $event->created_at->format('Y-m-d H:i') }}</small>
                                                <div class="mt-2">
                                                    <span class="badge {{ $isSeeder ? 'bg-success' : 'bg-danger' }}">
                                                        Seeder: {{ $isSeeder ? 'Yes' : 'No' }}
                                                    </span>
                                                    <span class="badge {{ $event->completed_at ? 'bg-primary' : 'bg-secondary' }}">
                                                        Completed: {{ $event->completed_at ? 'Yes' : 'No' }}
                                                    </span>
                                                </div>
                                                <ul class="list-unstyled small mt-2">
                                                    <li><strong>Uploaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}</li>
                                                    <li><strong>Downloaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}</li>
                                                    <li><strong>Left:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->left) }}</li>
                                                    <li><strong>Seedtime:</strong> {{ \App\Helpers\FormatHelper::formatTime($event->seedtime) }}</li>
                                                    <li><strong>Agent:</strong> {{ $event->peer->agent ?? 'N/A' }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $history->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Torrent Info</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> {{ $torrent->id }}</li>
                    <li class="list-group-item"><strong>Name:</strong> {{ $torrent->name }}</li>
                    <li class="list-group-item"><strong>Slug:</strong> {{ $torrent->slug }}</li>
                    <li class="list-group-item"><strong>File Name:</strong> {{ $torrent->file_name }}</li>
                    <li class="list-group-item"><strong>Files:</strong> {{ $torrent->num_files }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.download-history-container {
    max-height: 900px;
    overflow-y: auto;
    scrollbar-width: thin;
}
body {
    background: radial-gradient(circle at center, #0d1117, #000);
    color: #e4e4e4;
}
.card {
    border-radius: 12px;
}
.card-header {
    border-top-left-radius: 12px !important;
    border-top-right-radius: 12px !important;
}
.scrollable-seeders {
    max-height: 300px;   /* adjust as you prefer */
    overflow-y: auto;
    scrollbar-width: thin; /* Firefox */
}
.scrollable-seeders::-webkit-scrollbar {
    width: 6px;
}
.scrollable-seeders::-webkit-scrollbar-thumb {
    background-color: #666;
    border-radius: 3px;
}

</style>
@endsection
