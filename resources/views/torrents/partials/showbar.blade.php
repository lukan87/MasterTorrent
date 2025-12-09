<div class="card torrent-card mb-4 mt-4">
    <!-- Header -->
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center p-3 gap-2 gap-md-0">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h5 class="card-title mb-0 fw-bold text-truncate torrent-name d-flex align-items-center gap-1">
                <i class="bi bi-collection-play-fill me-1 text-primary"></i> 
                {{ $torrent->name }}
            </h5>

            <!-- Torrent Tags (Free, Double, Seedbox, Sticky) -->
<div class="d-flex flex-wrap gap-1">
    @if($torrent->free)
        <span class="badge bg-success text-white" data-bs-toggle="tooltip" title="This torrent is freeleech — downloading won't reduce your ratio.">
            Free
        </span>
    @endif
    @if($torrent->double)
        <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="Double upload credit — seeding counts double toward your ratio.">
            Double
        </span>
    @endif
    @if($torrent->seedbox)
        <span class="badge bg-info text-white" data-bs-toggle="tooltip" title="This torrent is hosted on a seedbox — expect high seed speed.">
            Seedbox
        </span>
    @endif
    @if($torrent->sticky)
        <span class="badge bg-danger text-white" data-bs-toggle="tooltip" title="Sticky torrent — always pinned to the top of all torrents.">
            Sticky
        </span>
    @endif
</div>

        </div>

        <span class="badge bg-secondary-subtle shadow-sm d-flex flex-wrap justify-content-end gap-2 ms-md-auto">
            <i class="bi bi-calendar3 me-1"></i> {{ $torrent->created_at->format('M d, Y') }}
        </span>
    </div>

    <!-- Body -->
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <!-- Left: Buttons -->
        <div class="d-flex align-items-center flex-wrap gap-2">
           @if (Auth::user()->hit_and_run_count > 20)
    <div class="alert alert-danger py-1 px-2 mb-0 glass-alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Download restricted: more than 20 H&R.
    </div>
@else
    @if (Auth::check() && Auth::user()->slots >= 1)
        @php
            $userSeedboxes = \App\Models\Seedbox::where('user_id', auth()->id())->get();
        @endphp

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

                {{-- Optional Seedbox Integration --}}
                @if($userSeedboxes->isNotEmpty())
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-header text-secondary fw-bold">
                        <i class="bi bi-cloud-upload-fill me-1"></i> Send to Seedbox
                    </li>
                    @foreach($userSeedboxes as $seedbox)
                        <li>
                            <form action="{{ route('torrents.sendToSeedbox', $torrent) }}" method="POST" class="m-0 p-0">
                                @csrf
                                <input type="hidden" name="seedbox_id" value="{{ $seedbox->id }}">
                                <button type="submit" class="dropdown-item">
                                    {{ $seedbox->name }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                @endif
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
                        <i class="bi bi-arrow-up-circle"></i> Bump
                    </button>
                </form>
            @endif

            @if(!$hasThanked)
                <form action="{{ route('torrents.thank', $torrent->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm glass-btn" data-bs-toggle="tooltip" title="Thanked by: {{ implode(', ', $thankUserNames) }}">
                        {{ $torrent->thanksCount() }} <i class="bi bi-hand-thumbs-up-fill"></i>
                    </button>
                </form>
            @else
                <button class="btn btn-success btn-sm shadow-sm glass-btn" data-bs-toggle="tooltip" title="Thanked by: {{ implode(', ', $thankUserNames) }}">
                    {{ $torrent->thanksCount() }} <i class="bi bi-hand-thumbs-up-fill"></i>
                </button>
            @endif
        </div>

        <!-- Right: Stats as badges -->
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
                <span class="badge glass-badge bg-primary" data-bs-toggle="tooltip" title="Uploader">
                      <i class="bi bi-person-fill me-1"></i> {{ $torrent->uploader->name }}
                </span>
                <a href="{{ route('torrent.history', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
                   class="badge glass-badge bg-primary text-decoration-none" data-bs-toggle="tooltip" title="View Download History">
                    <i class="bi bi-download me-1"></i> {{ $torrent->times_completed }}
                </a>
            @else
                <span class="badge glass-badge bg-success" data-bs-toggle="tooltip" title="Seeders">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ $torrent->seeders }}
                </span>
                <span class="badge glass-badge bg-danger" data-bs-toggle="tooltip" title="Leechers">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> {{ $torrent->leechers }}
                </span>
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

<!-- Glassmorphism and Gradient Styles (keep your original) -->
<style>
.torrent-card {
    position: relative;
    z-index: 1;
    backdrop-filter: blur(1px);
    -webkit-backdrop-filter: blur(1px);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(20, 20, 20, 0.6);
}
.btn-gradient-primary { background: linear-gradient(45deg,#007bff,#0056b3); border: none; color: #fff; transition: all .3s ease-in-out; }
.btn-gradient-info { background: linear-gradient(45deg,#17a2b8,#138496); border: none; color: #fff; transition: all .3s ease-in-out; }
.btn-gradient-primary:hover,.btn-gradient-info:hover { filter: brightness(1.15); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
.glass-alert { backdrop-filter: blur(4px); background: rgba(220,53,69,.2); border: 1px solid rgba(220,53,69,.3); border-radius: 10px; }
.glass-badge { backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); border-radius: 12px; box-shadow: 0 3px 6px rgba(0,0,0,.25); }
.glass-btn { backdrop-filter: blur(3px); background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); transition: all .25s ease; }
.glass-btn:hover { background: rgba(255,255,255,.15); transform: translateY(-1px); }

.torrent-name {
    font-size: 1.1rem; /* default size */
    max-width: 100%; /* ensures it doesn't overflow container */
    white-space: normal; /* wrap by default on large screens */
    overflow: visible;
    text-overflow: unset;
}

@media (max-width: 992px) { /* md and below */
    .torrent-name {
        font-size: 0.95rem; /* slightly smaller */
        max-width: 400px; /* limit width for ellipsis */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis; /* add ... */
    }
}

@media (max-width: 576px) { /* sm and below */
    .torrent-name {
        max-width: 400px; /* fit smaller screens */
    }
}

</style>
