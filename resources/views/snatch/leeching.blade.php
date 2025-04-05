@extends('layouts.app')

@section('content')
    <div class="mt-4">
        <!-- Navigation Links -->
        <div class="mb-4 text-center">
            <nav>
                <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-primary fw-bold">
                        <i class="bi bi-cloud-upload"></i> Seeding
                    </a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-collection"></i> Snatch List
                    </a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                        <i class="bi bi-exclamation-triangle"></i> Hit and Run
                    </a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-hourglass-split"></i> Need to Seed
                    </a>
                </div>
            </nav>
        </div>

        <!-- Leeching Section -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-dark text-center">
                <h2 class="fw-bold"><i class="bi bi-arrow-down-circle"></i> Leeching Torrents</h2>
            </div>
            <div class="card-body">
                @if($leeching->isEmpty())
                    <div class="alert alert-info text-center fw-bold" role="alert">
                        🎉 You are not currently leeching any torrents.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Torrent</th>
                                    <th>Leeching Since</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leeching as $peer)
                                    <tr>
                                        <td>
                                            <a href="{{ route('torrents.show', ['id' => $peer->torrent->id]) }}" class="text-decoration-none fw-bold">
                                                <i class="bi bi-file-earmark-arrow-down"></i> {{ $peer->torrent->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ $peer->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $leeching->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
