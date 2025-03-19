@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Navigation Links as Buttons -->
        <div class="mb-4">
            <nav>
                <div class="btn-group" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-primary">Seeding</a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-warning">Leeching</a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-danger">Snatch List</a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-success">Need to Seed</a>
                </div>
            </nav>
        </div>

        <!-- Hit-and-Run Section -->
        <h1 class="mb-4">Hit-and-Run Torrents</h1>
        
        @if($hitAndRun->isEmpty())
            <div class="alert alert-info" role="alert">
                You have no hit-and-run torrents.
            </div>
        @else
            <ul class="list-group">
                @foreach($hitAndRun as $history)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Torrent:</strong> {{ $history->torrent->name ?? 'Unknown' }}<br>
                            <strong>Uploaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}<br>
                            <strong>Downloaded:</strong> {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}<br>
                            <strong>Seedtime:</strong> {{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}<br>
                            <strong>Ratio:</strong> {{ number_format(($history->uploaded / max($history->downloaded, 1)), 2) }}<br>
                            <strong>Status:</strong> <span class="text-danger">Hit-and-Run</span>
                        </div>

                        <!-- Buy Seedtime Button and Remove HNR Button -->
                        @if (auth()->id() === $history->user_id)  <!-- Make sure the user is the one who uploaded the torrent -->
                            <form action="{{ route('bonus.removeHNR') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
                                <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="5000 seedbonus points">
                                    Remove Hit-and-Run
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
