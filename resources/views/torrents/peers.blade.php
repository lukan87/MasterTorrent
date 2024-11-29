@extends('layouts.app')

@section('content')
<div class="my-4">
<h1 class="mb-4">Peers for Torrent:
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="text-info">
            {{ $torrent->name }}
        </a>
    </h1>

    <div class="row">
        <!-- Seeders Section -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Seeders</h5>
                </div>
                <div class="card-body">
                    @if($seeders->isEmpty())
                        <p>No seeders available for this torrent.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($seeders as $seeder)
                                <li class="list-group-item">
                                    <strong>{{ $seeder->user->name }}</strong> -
                                    <span class="text-muted">{{ $seeder->agent }}</span>
                                    <br>
                                    <small>IP: {{ $seeder->ip }} |
                                    <span class="text-secondary">{{ $seeder->created_at->diffForHumans() }}</span></small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="pagination-container d-flex justify-content-center mt-4">
        {{ $seeders->links('pagination::bootstrap-5') }}
    </div>
        </div>

        <!-- Leechers Section -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Leechers</h5>
                </div>
                <div class="card-body">
                    @if($leechers->isEmpty())
                        <p>No leechers available for this torrent.</p>
                    @else
                        <ul class="list-group">
                            @foreach ($leechers as $leecher)
                                <li class="list-group-item">
                                    <strong>{{ $leecher->user->name }}</strong> -
                                    <span class="text-muted">{{ $leecher->agent }}</span>
                                    <br>
                                    <span class="text-muted">Left to Download: {{ \App\Helpers\FormatHelper::formatSize($leecher->left) }}</span>
                                    <br>
                                    <small>IP: {{ $leecher->ip }} |
                                    <span class="text-secondary">{{ $leecher->created_at->diffForHumans() }}</span></small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

                    <div class="pagination-container d-flex justify-content-center mt-4">
                       {{ $leechers->links('pagination::bootstrap-5') }}
                    </div>
        </div>
    </div>
</div>
@endsection
