@extends('layouts.app')

@section('content')
<div class="mt-4">
    <div class="row">

        <!-- Main Content Area -->
        <div class="col-md-9">
            <!-- Torrent Card -->
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0">{{ $torrent->name }}</h1>
                </div>
                <div class="card-body">
                    <div class="row">

                        <!-- Torrent Details Column -->
                        <div class="col-md-3">
                            <h3 class="mb-3">Torrent Details</h3>
                            <ul class="list-group mb-4">
                                <li class="list-group-item"><strong>Seeders:</strong> {{ $seeders }}</li>
                                <li class="list-group-item"><strong>Leechers:</strong> {{ $leechers }}</li>
                                <li class="list-group-item"><strong>Times Completed:</strong> {{ $times_completed }}</li>
                            </ul>
                        </div>

                        <!-- Download History Column -->
                        <div class="col-md-9">
                            <h3 class="mb-3">Download History</h3>
                            @if($history->isEmpty())
                                <div class="alert alert-warning">
                                    No download history available for this torrent.
                                </div>
                            @else
                                <!-- Scrollable download history section -->
                                <div class="download-history-container">
                                    <ul class="list-group mb-4">
                                        @foreach($history as $event)
                                            @php
                                                $user = $event->user;
                                                $userPeer = \App\Models\Peer::where('user_id', $user->id)->first();
                                                $seeder = $event->seeder ? 'Yes' : 'No';
                                                $seederClass = $event->seeder ? 'badge bg-success' : 'badge bg-danger';
                                                $finished = $event->completed_at ? 'Yes' : 'No';
                                                $finishedClass = $event->completed_at ? 'badge bg-success' : 'badge bg-danger';
                                            @endphp
                                            <li class="list-group-item">
                                                <div class="fw-bold">{{ $user->name ?? 'Unknown User' }}</div>
                                                <ul class="list-unstyled ms-3 mt-2">
                                                    <li><strong>Downloaded at:</strong> {{ $event->created_at->format('Y-m-d H:i:s') }}</li>
                                                    <li><strong>Seeder:</strong>
                                                      <span class="{{ $seederClass }}">{{ $seeder }}</span>
                                                    </li>
                                                    <li><strong>Uploaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->uploaded) }}</li>
                                                    <li><strong>Downloaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->downloaded) }}</li>
                                                    <li><strong>Left to download:</strong> {{ \App\Helpers\FormatHelper::formatSize($event->left) }}</li>
                                                    <li><strong>Seedtime:</strong> {{ \App\Helpers\FormatHelper::formatTime($event->seedtime) }}</li>
                                                    <li><strong>Agent:</strong>
                                                        @if($userPeer && $userPeer->agent)
                                                            @foreach(explode(',', $userPeer->agent) as $agent)
                                                                {{ trim($agent) }}@if (!$loop->last), @endif
                                                            @endforeach
                                                        @else
                                                            N/A
                                                        @endif
                                                    </li>
                                                    <li><strong>Finished downloading full torrent:</strong>
                                                        <span class="{{ $finishedClass }}">{{ $finished }}</span>
                                                    </li>
                                                </ul>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <!-- Pagination links -->
                                <div class="d-flex justify-content-center">
                                    {{ $history->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with Torrent Info -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Info</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Torrent ID:</strong> {{ $torrent->id }}</li>
                        <li class="list-group-item"><strong>Name:</strong> {{ $torrent->name }}</li>
                        <li class="list-group-item"><strong>Slug:</strong> {{ $torrent->slug }}</li>
                        <li class="list-group-item"><strong>Info Hash:</strong> {{ $torrent->info_hash }}</li>
                        <li class="list-group-item"><strong>File Name:</strong> {{ $torrent->file_name }}</li>
                        <li class="list-group-item"><strong>Number of Files:</strong> {{ $torrent->num_files }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styling -->
<style>
    .download-history-container {
        max-height: 650px;
        overflow-y: auto;
    }
    /* Background overlay */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('{{ $torrent->background }}');
        background-size: cover;
        opacity: 0.7;
    }
</style>
@endsection
