<div class="card torrent-card mb-4 mt-4">
    <!-- Header -->
    <div class="card-header d-flex justify-content-between align-items-center p-3">
        <h5 class="card-title mb-0 fw-bold text-truncate">
            <i class="bi bi-collection-play-fill me-2 text-primary"></i> 
            {{ $torrent->name }}
        </h5>
        <span class="badge bg-secondary-subtle shadow-sm d-flex flex-wrap justify-content-end gap-2 ms-md-auto">
            <i class="bi bi-calendar3 me-1"></i> {{ $torrent->created_at->format('M d, Y') }}
        </span>
    </div>

    <!-- Body -->
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        
        <!-- Left: Buttons -->
        <div class="d-flex align-items-center flex-wrap gap-2">
            @if (Auth::user()->hit_and_run_count > '20')
                <div class="alert alert-danger py-1 px-2 mb-0 glass-alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Download restricted: more than 20 H&R.
                </div>
            @else
                @if (Auth::check() && Auth::user()->slots >= 1)
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                           class="btn btn-gradient-primary btn-sm d-flex align-items-center">
                            <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-gradient-primary btn-sm dropdown-toggle dropdown-toggle-split text-white" data-bs-toggle="dropdown"></button>
                        <ul class="dropdown-menu dropdown-menu-dark shadow-lg">
                            <li>
                                <a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?free=1">
                                    <i class="bi bi-0-circle-fill me-2"></i> Free
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}?double=1">
                                    <i class="bi bi-chevron-double-down me-2"></i> Double
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('torrents.download', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                       class="btn btn-gradient-info btn-sm shadow-sm d-flex align-items-center">
                        <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Download
                    </a>
                @endif
            @endif

            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR || Auth::id() === $torrent->owner))
                <a href="{{ route('torrents.edit', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                   class="btn btn-outline-info btn-sm shadow-sm glass-btn" data-bs-toggle="tooltip" title="Edit Torrent">
                    <i class="bi bi-pencil-square"></i>
                </a>
            @endif

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN && !$torrent->bumped)
                <form action="{{ route('torrents.bump', $torrent->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm shadow-sm glass-btn" data-bs-toggle="tooltip" title="Bump Torrent">
                        <i class="bi bi-arrow-up-circle"></i>
                    </button>
                </form>
            @endif

            @if(!$hasThanked)
                <form action="{{ route('torrents.thank', $torrent->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm glass-btn">
                        {{ $torrent->thanksCount() }} <i class="bi bi-hand-thumbs-up-fill"></i>
                    </button>
                </form>
            @else
                <button class="btn btn-success btn-sm shadow-sm glass-btn" data-bs-toggle="tooltip" title="Thanked by: {{ implode(', ', $thankUserNames) }}">
                    {{ $torrent->thanksCount() }} <i class="bi bi-hand-thumbs-up-fill"></i>
                </button>
            @endif
        </div>

        <!-- Right: Stats as badges with tooltips -->
        <div class="d-flex flex-wrap justify-content-end gap-2 ms-md-auto">
            <span class="badge glass-badge bg-secondary" data-bs-toggle="tooltip" title="Category">
                <i class="bi bi-tags me-1"></i> {{ $torrent->category->name }}
            </span>

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?seeders" 
                   class="badge glass-badge bg-success text-decoration-none" data-bs-toggle="tooltip" title="View Seeders List">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ $torrent->seeders }}
                </a>
                <a href="{{ route('torrent.peers', ['torrent' => $torrent->id]) }}?leechers" 
                   class="badge glass-badge bg-danger text-decoration-none" data-bs-toggle="tooltip" title="View Leechers List">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> {{ $torrent->leechers }}
                </a>
            @else
                <span class="badge glass-badge bg-success" data-bs-toggle="tooltip" title="Seeders">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ $torrent->seeders }}
                </span>
                <span class="badge glass-badge bg-danger" data-bs-toggle="tooltip" title="Leechers">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> {{ $torrent->leechers }}
                </span>
            @endif

            @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                <a href="{{ route('torrent.history', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                   class="badge glass-badge bg-primary text-decoration-none" data-bs-toggle="tooltip" title="View Download History">
                    <i class="bi bi-download me-1"></i> {{ $torrent->times_completed }}
                </a>
            @else
                <span class="badge glass-badge bg-primary" data-bs-toggle="tooltip" title="Times Completed">
                    <i class="bi bi-download me-1"></i> {{ $torrent->times_completed }}
                </span>
            @endif

            <span class="badge glass-badge bg-purple text-white" data-bs-toggle="tooltip" title="Torrent Size">
                <i class="bi bi-pie-chart-fill me-1"></i> {{ \App\Helpers\FormatHelper::formatSize($torrent->size) }}
            </span>
        </div>

    </div>
</div>

<!-- Custom Glassmorphism Styling -->
<style>
.torrent-card {
    position: relative; /* already probably set */
    z-index: 1;
    backdrop-filter: blur(1px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 16px;
    /* overflow: hidden; */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(20, 20, 20, 0.6); /* subtle transparency */
}

/* Gradient buttons */
.btn-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    color: #fff;
    transition: all 0.3s ease-in-out;
}
.btn-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #138496);
    border: none;
    color: #fff;
    transition: all 0.3s ease-in-out;
}
.btn-gradient-primary:hover, .btn-gradient-info:hover {
    filter: brightness(1.15);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

/* Glass effect for alerts & badges */
.glass-alert {
    backdrop-filter: blur(4px);
    background: rgba(220, 53, 69, 0.2);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 10px;
}
.glass-badge {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    border-radius: 12px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.25);
}

/* Glass buttons */
.glass-btn {
    backdrop-filter: blur(3px);
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    transition: all 0.25s ease;
}
.glass-btn:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-1px);
}
</style>


