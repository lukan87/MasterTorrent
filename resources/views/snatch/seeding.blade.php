@extends('layouts.app')

@section('content')
    <div class="mt-4">
        <!-- Navigation Links -->
        <div class="mb-4 text-center">
            <nav>
                <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-primary fw-bold">
                        <i class="bi bi-collection"></i> Snatch List
                    </a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-arrow-down-circle"></i> Leeching
                    </a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                        <i class="bi bi-exclamation-triangle"></i> Hit and Run
                    </a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-info fw-bold">
                        <i class="bi bi-hourglass-bottom"></i> Need to Seed
                    </a>
                </div>
            </nav>
        </div>

        <!-- Seeding Section -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="fw-bold"><i class="bi bi-cloud-upload"></i> Seeding Torrents</h2>
            </div>
            <div class="card-body">
                @if($seeding->isEmpty())
                    <div class="alert alert-info text-center fw-bold" role="alert">
                        No torrents currently being seeded.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Torrent</th>
                                    <th>Uploaded</th>
                                    <th>Downloaded</th>
                                    <th>Seedtime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($seeding as $history)
                                    <tr>
                                        <td>
                                            <a href="{{ route('torrents.show', ['id' => $history->torrent->id]) }}" class="text-decoration-none fw-bold">
                                                <i class="bi bi-file-earmark-arrow-down"></i> {{ $history->torrent->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a data-bs-toggle="tooltip" title="Actual Upload: {{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded) }}">
                                                {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a data-bs-toggle="tooltip" title="Actual Download: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? '0') }}">
                                                {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}
                                            </a>
                                        </td>
                                        <td>{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) ?? '0' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $seeding->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
