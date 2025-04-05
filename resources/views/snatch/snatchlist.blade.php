@extends('layouts.app')

@section('content')
    <div class="mt-4">
        <!-- Navigation Links -->
        <div class="mb-4 text-center">
            <nav>
                <div class="btn-group shadow-sm" role="group" aria-label="Snatch Sections">
                    <a href="{{ route('snatch.seeding', ['userId' => $userId]) }}" class="btn btn-outline-primary fw-bold">
                        <i class="bi bi-cloud-upload"></i> Seeding
                    </a>
                    <a href="{{ route('snatch.leeching', ['userId' => $userId]) }}" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-arrow-down-circle"></i> Leeching
                    </a>
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId]) }}" class="btn btn-outline-danger fw-bold">
                        <i class="bi bi-exclamation-triangle"></i> Hit and Run
                    </a>
                    <a href="{{ route('snatch.needToSeed', ['userId' => $userId]) }}" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-hourglass-bottom"></i> Need to Seed
                    </a>
                </div>
            </nav>
        </div>

        <!-- Snatchlist Section -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white text-center">
                <h2 class="fw-bold"><i class="bi bi-collection"></i> Snatchlist for {{ $user->name ?? 'Unknown User' }}</h2>
            </div>
            <div class="card-body">
                @if($snatchlist->isEmpty())
                    <div class="alert alert-info text-center fw-bold" role="alert">
                        No torrents in your snatchlist.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Torrent</th>
                                    <th>Snatched On</th>
                                    <th>Seeder</th>
                                    <th>Seedtime</th>
                                    <th>Ratio</th>
                                    <th>Uploaded / Downloaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($snatchlist as $snatch)
                                    @php
                                        $ratio = $snatch->actual_downloaded > 0 ? number_format($snatch->uploaded / $snatch->actual_downloaded, 2) : '∞';
                                        $ratioColor = $snatch->actual_downloaded > 0 ? ($snatch->uploaded / $snatch->actual_downloaded >= 1 ? 'text-success' : 'text-danger') : 'text-success';
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('torrents.show', ['id' => $snatch->torrent->id]) }}" class="text-decoration-none fw-bold">
                                                <i class="bi bi-file-earmark-arrow-down"></i> {{ $snatch->torrent->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ $snatch->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="{{ $snatch->seeder ? 'text-success' : 'text-danger' }}">
                                                {{ $snatch->seeder ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td>{{ \App\Helpers\FormatHelper::formatTime($snatch->seedtime) }}</td>
                                        <td>
                                            <span class="{{ $ratioColor }}">
                                                {{ $ratio }}
                                            </span>
                                        </td>
                                        <td>
                                            Uploaded:
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Actual Uploaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_uploaded) }}">
                                                                 {{ \App\Helpers\FormatHelper::formatSize($snatch->uploaded) }}
                                        </span> <br>
                                        Downloaded: 
                                       <span data-bs-toggle="tooltip" data-bs-placement="top" title="Actual Downloaded: {{ \App\Helpers\FormatHelper::formatSize($snatch->actual_downloaded) }}">
                                                                 {{ \App\Helpers\FormatHelper::formatSize($snatch->downloaded) }}
                                       </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $snatchlist->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
