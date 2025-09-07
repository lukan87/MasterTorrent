<div class="mt-3 mb-3">
    <div class="card shadow-sm rounded-2">
        <div class="card-header">
            <h5 class="mb-0">Top Torrents</h5>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs nav-fill" id="torrentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active py-3 text-center" id="last-day-tab" data-bs-toggle="tab" href="#last-day" role="tab">Last Day</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link py-3 text-center" id="last-week-tab" data-bs-toggle="tab" href="#last-week" role="tab">Last Week</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link py-3 text-center" id="last-month-tab" data-bs-toggle="tab" href="#last-month" role="tab">Last Month</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link py-3 text-center" id="movies-tab" data-bs-toggle="tab" href="#movies" role="tab">Movies</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link py-3 text-center" id="series-tab" data-bs-toggle="tab" href="#series" role="tab">Series</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content p-3" id="torrentTabsContent">
            @foreach (['last-day' => $topLastDay, 'last-week' => $topLastWeek, 'last-month' => $topLastMonth, 'movies' => $topMovies, 'series' => $topSeries] as $tabId => $torrents)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tabId }}" role="tabpanel">
                    @if($torrents->isEmpty())
                        <div class="text-center text-muted py-4">
                            No top {{ str_replace('-', ' ', $tabId) }} found.
                        </div>
                    @else
                       <div class="row g-3">
    @foreach ($torrents as $torrent)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="torrent-info-card card h-100 shadow-sm rounded-3 p-3 position-relative">
                
                <!-- Ranking Number -->
                <div class="ranking-badge">
                    #{{ $loop->iteration }}
                </div>

                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" class="text-decoration-none">
                    <h6 class="fw-bold mb-2">{{ $torrent->name }}</h6>
                </a>

                <!-- Genres -->
                @if(isset($torrent->genres) && $torrent->genres->isNotEmpty())
                    <div class="mb-2">
                        @foreach($torrent->genres as $genre)
                            <span class="badge bg-secondary">{{ $genre->name }}</span>
                        @endforeach
                    </div>
                @endif

                <!-- Stats -->
                <div class="torrent-stats d-flex flex-wrap gap-2">
                    <span class="badge bg-success">Seeders: {{ $torrent->seeders }}</span>
                    <span class="badge bg-danger">Leechers: {{ $torrent->leechers }}</span>
                    <span class="badge bg-info">Completed: {{ $torrent->times_completed }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* Torrent Info Card */
.torrent-info-card {
    transition: transform 0.2s ease-in-out;
}
.torrent-info-card:hover {
    transform: translateY(-4px);
}

/* Tabs */
.nav-tabs .nav-link {
    font-weight: 500;
    border: none;
    color: #fff;
    font-size: 0.95rem;
}
.nav-tabs .nav-link.active {
    border-bottom: 3px solid #0d6efd;
}
.nav-tabs .nav-link {
    transition: background 0.2s, transform 0.2s;
}
.nav-tabs .nav-link:hover {
    background: rgba(255,255,255,0.1);
    transform: scale(1.05);
}
/* Ranking Badge */
.ranking-badge {
    position: absolute;
    top: 80px;
    right: 10px;
    background: #6a6b6bff;
    color: #fff;
    font-weight: bold;
    font-size: 0.85rem;
    padding: 4px 10px;
    border-radius: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

</style>
