@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Navigation Links as Buttons -->
        <div class="mb-4">
            <nav>
                <div class="btn-group" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-primary">Seeding</a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-warning">Leeching</a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-danger">Hit and Run</a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-success">Snatch List</a>
                </div>
                
            </nav>
        </div>

        <!-- Torrents That Need Seeding Section -->
        <h1 class="mb-4">Torrents That Need Seeding</h1>

        @if($needToSeed->isEmpty())
            <div class="alert alert-info" role="alert">
                You have no torrents that need seeding.
            </div>
        @else
            <ul class="list-group">
                @foreach($needToSeed as $torrent)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Torrent:</strong> {{ $torrent->torrent->name ?? 'Unknown' }}<br>
                            <strong>Created At:</strong> {{ $torrent->created_at }}<br>
                            <strong>Uploaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($torrent->uploaded) ?? '0' }}<br>
                            <strong>Downloaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($torrent->actual_downloaded) ?? '0' }}<br>
                            <strong>Seedtime:</strong> {{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}<br>

                            <strong>Ratio:</strong> 
                            @php
                                $ratio = number_format(($torrent->uploaded / max($torrent->actual_downloaded, 1)), 2);
                            @endphp
                            <span class="{{ $ratio >= 1.00 ? 'text-success' : 'text-danger' }}">
                                {{ $ratio }}
                            </span><br>

                            <!-- Calculate remaining seedtime (how long more to seed to 24 hours) -->
                            @if($torrent->seedtime < 86400)
                                @php
                                    $remainingSeedtime = 86400 - $torrent->seedtime;
                                @endphp
                                <strong>Remaining Seedtime:</strong> 
                                <span class="{{ $remainingSeedtime <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ \App\Helpers\FormatHelper::formatTime($remainingSeedtime) }}
                                </span>
                            @endif
                            <br>
                            <!-- Display 'Done' status with color -->
                            @if($torrent->seedtime >= 86400 || $ratio >= 1.00)
                                <strong>Status:</strong> <span class="text-success">Done. No need to seed, but it would be nice to help others!</span>
                            @else
                                <strong>Status:</strong> <span class="text-danger">Needs Seeding</span>
                            @endif
                        </div>

                        <div class="d-flex flex-column align-items-start">
                            <!-- Download Button -->
                            <a href="{{ route('torrents.download', ['id' => $torrent->torrent->id, 'slug' => $torrent->torrent->slug]) }}" 
                               class="btn btn-success btn-sm mb-2" data-bs-toggle="tooltip" title="Download the torrent so you can either complete the seedtime or have a ratio of 1:1">
                                <i class="bi bi-download"></i> Download
                            </a>

                            <!-- Buy Seedtime Button -->
                            @if (auth()->id() === $torrent->user_id)
                                <form action="{{ route('bonus.buySeedtime') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}">
                                    <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="5000 seedbonus points">
                                        Buy Seedtime
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $needToSeed->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
