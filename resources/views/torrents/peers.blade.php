@extends('layouts.app')

@section('content')
<div class="container-fluid my-5">
    <h1 class="mb-4">Peers for Torrent:
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-info">
            {{ $torrent->name }}
        </a>
    </h1>

    <div class="row">
        <!-- Seeders Section -->
        @if($seeders)
        <div class="col-md-12 mb-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Seeders</span></h5>
        </div>
        <div class="card-body">
            @if($seeders->isEmpty())
                <p class="text-center">No seeders available for this torrent.</p>
            @else
                <ul class="list-group">
                    @foreach ($seeders as $seeder)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><a href="{{ route('profile.show', ['id' => $seeder->user->id, 'name' => $seeder->user->name ?? 'Unknown']) }}">{{ $seeder->user->name }}</a></strong> - 
                                    <span class="text-muted">{{ $seeder->agent }}</span>
                                </div>
                                <div class="text-end">
                                    <small>
                                        IP: <span class="text-nowrap">{{ $seeder->ip }}</span> |
                                        <span class="text-secondary">{{ $seeder->created_at->diffForHumans() }}</span>
                                    </small>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $seeders->links('pagination::bootstrap-5') }}
    </div>
</div>

        @endif

        <!-- Leechers Section -->
        @if($leechers)
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Leechers</h5>
            </div>
            <div class="card-body">
                @if($leechers->isEmpty())
                    <p class="text-center">No leechers available for this torrent.</p>
                @else
                    <ul class="list-group">
                        @foreach ($leechers as $leecher)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><a href="{{ route('profile.show', ['id' => $leecher->user->id, 'name' => $leecher->user->name ?? 'Unknown']) }}">{{ $leecher->user->name }}</a></strong> - 
                                        <span class="text-muted">{{ $leecher->agent }}</span>
                                    </div>
                                    <div class="text-end">
                                        <small>
                                            Left to Download: <span class="text-nowrap">{{ \App\Helpers\FormatHelper::formatSize($leecher->left) }}</span><br>
                                            IP: <span class="text-nowrap">{{ $leecher->ip }}</span> |
                                            <span class="text-secondary">{{ $leecher->created_at->diffForHumans() }}</span>
                                        </small>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $leechers->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif

    </div>
</div>
@endsection
