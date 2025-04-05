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
                    <a href="{{ route('snatch.leeching', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-arrow-down-circle"></i> Leeching
                    </a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                        <i class="bi bi-collection"></i> Snatch List
                    </a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-hourglass-split"></i> Need to Seed
                    </a>
                </div>
            </nav>
        </div>

        <!-- Hit-and-Run Section -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-danger text-white text-center">
                <h2 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Hit-and-Run Torrents</h2>
            </div>
            <div class="card-body">
                @if($hitAndRun->isEmpty())
                    <div class="alert alert-info text-center fw-bold" role="alert">
                        🎉 You have no hit-and-run torrents!
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
                                    <th>Ratio</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hitAndRun as $history)
                                    <tr>
                                        <td>
                                            <a href="{{ route('torrents.show', ['id' => $history->torrent->id]) }}" class="text-decoration-none fw-bold">
                                                <i class="bi bi-file-earmark-arrow-down"></i> {{ $history->torrent->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ \App\Helpers\FormatHelper::formatSize($history->uploaded) ?? '0' }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::formatSize($history->downloaded) ?? '0' }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</td>
                                        <td class="fw-bold {{ ($history->uploaded / max($history->downloaded, 1)) < 1 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(($history->uploaded / max($history->downloaded, 1)), 2) }}
                                        </td>
                                        <td><span class="badge bg-danger">Hit-and-Run</span></td>
                                        <td>
                                            @if (auth()->id() === $history->user_id)
                                                <form action="{{ route('bonus.removeHNR') }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <input type="hidden" name="torrent_id" value="{{ $history->torrent_id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="5000 seedbonus points">
                                                        <i class="bi bi-cash-coin"></i> Remove HNR
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $hitAndRun->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
