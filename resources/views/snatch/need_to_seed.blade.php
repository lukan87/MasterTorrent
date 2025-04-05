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
                    <a href="{{ route('snatch.hitAndRun', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-danger fw-bold">
                        <i class="bi bi-exclamation-triangle"></i> Hit and Run
                    </a>
                    <a href="{{ route('snatch.snatchlist', ['userId' => $userId ?? Auth::id()]) }}" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-collection"></i> Snatch List
                    </a>
                </div>
            </nav>
        </div>

        <!-- Need to Seed Section -->
        <div class="card shadow-lg border-0">
            <div class="card-header bg-danger text-white text-center">
                <h2 class="fw-bold"><i class="bi bi-hourglass-bottom"></i> Torrents That Need Seeding</h2>
            </div>
            <div class="card-body">
                @if($needToSeed->isEmpty())
                    <div class="alert alert-info text-center fw-bold" role="alert">
                        ✅ You have no torrents that need seeding.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Torrent</th>
                                    <th>Created At</th>
                                    <th>Uploaded</th>
                                    <th>Downloaded</th>
                                    <th>Seedtime</th>
                                    <th>Ratio</th>
                                    <th>Remaining Time</th>
                                    <th>Status</th>
                                       @if(auth()->check() && $needToSeed->first()->user_id === auth()->id())
                                    <th>Actions</th>
                                       @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($needToSeed as $torrent)
                                    @php
                                        $ratio = number_format(($torrent->uploaded / max($torrent->actual_downloaded, 1)), 2);
                                        $remainingSeedtime = max(0, 86400 - $torrent->seedtime);
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('torrents.show', ['id' => $torrent->torrent->id]) }}" class="text-decoration-none fw-bold">
                                                <i class="bi bi-file-earmark-arrow-down"></i> {{ $torrent->torrent->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ $torrent->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::formatSize($torrent->uploaded) ?? '0' }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::formatSize($torrent->actual_downloaded) ?? '0' }}</td>
                                        <td>{{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}</td>
                                        <td>
                                            <span class="{{ $ratio >= 1.00 ? 'text-success' : 'text-danger' }}">
                                                {{ $ratio }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($remainingSeedtime > 0)
                                                <span class="text-danger">{{ \App\Helpers\FormatHelper::formatTime($remainingSeedtime) }}</span>
                                            @else
                                                <span class="text-success">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($torrent->seedtime >= 86400 || $ratio >= 1.00)
                                                <span class="badge bg-success">Done</span>
                                            @else
                                                <span class="badge bg-danger">Needs Seeding</span>
                                            @endif
                                        </td>
                                        @if (auth()->id() === $torrent->user_id)
                                        <td class="d-flex gap-2">
                                           
                                            <!-- Download Button -->
                                            <a href="{{ route('torrents.download', ['id' => $torrent->torrent->id, 'slug' => $torrent->torrent->slug]) }}" 
                                               class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Download to continue seeding">
                                                <i class="bi bi-download"></i>
                                            </a>

                                            <!-- Buy Seedtime Button -->
                                            
                                                <form action="{{ route('bonus.buySeedtime') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}">
                                                    <button type="submit" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="5000 seedbonus points">
                                                        <i class="bi bi-coin"></i>
                                                    </button>
                                                </form>
                                            
                                        </td>
                                        @endif
                                        @if (auth()->user()->id == 3 && auth()->id() !== $torrent->user_id)
                                        <td class="d-flex gap-2">
                                                 <!-- Delete History Button -->
                                            <form action="{{ route('snatch.deleteNeedToSeed', ['userId' => $torrent->user_id, 'torrentId' => $torrent->torrent->id]) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to remove this torrent from the user\'s history?');">
                                                       @csrf
                                                       @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Remove from user's history"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $needToSeed->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
