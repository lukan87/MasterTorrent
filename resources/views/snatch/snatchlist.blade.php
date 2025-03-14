@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Navigation Links as Buttons -->
        <div class="mb-4">
            <nav>
                <div class="btn-group" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId]) }}" class="btn btn-primary">Seeding</a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId]) }}" class="btn btn-warning">Leeching</a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId]) }}" class="btn btn-danger">Hit and Run</a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId]) }}" class="btn btn-success">Need to Seed</a>
                </div>
                
            </nav>
        </div>

        <!-- Snatchlist Section -->
        <h1 class="mb-4">
            Snatchlist for {{ $user->name ?? 'Unknown User' }}
        </h1>
        
        @if($snatchlist->isEmpty())
            <div class="alert alert-info" role="alert">
                No torrents in your snatchlist.
            </div>
        @else
            <ul class="list-group">
                @foreach($snatchlist as $snatch)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Torrent:</strong> {{ $snatch->torrent->name ?? 'Unknown' }}<br>
                            <small>Snatched on: {{ $snatch->created_at->format('Y-m-d') }}</small> /
                            <small>
                                Seeder: 
                                @if($snatch->seeder)
                                <span class="text-success">Yes</span>
                                @else
                                <span class="text-danger">No</span>
                                @endif
                            </small> /
                            <small>Seedtime: {{ \App\Helpers\FormatHelper::formatTime($snatch->seedtime) }}</small> / 
                            <small>
    Ratio: 
    @if($snatch->actual_downloaded > 0)
        <span style="color: {{ $snatch->uploaded / $snatch->actual_downloaded >= 1 ? 'green' : 'red' }}">
            {{ number_format($snatch->uploaded / $snatch->actual_downloaded, 2) }}
        </span>
    @else
        <span style="color: green">∞</span>
    @endif
</small><br>
<small>
{{-- Uploaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->uploaded) }} / Actual Uploaded: {{\App\Helpers\FormatHelper::formatSize( $snatch->actual_uploaded) }}<br>
Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->downloaded) }} / Actual Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_downloaded) }} --}}
Uploaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->uploaded) }}  /  Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_downloaded) }}
</small>                 



                        </div>
                    </li>
                @endforeach
            </ul>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center">
                {{ $snatchlist->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
