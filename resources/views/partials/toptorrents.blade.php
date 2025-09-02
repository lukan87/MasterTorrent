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
                       <div class="row g-5"> <!-- increased gutter -->
    @foreach ($torrents as $torrent)
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="torrent-card position-relative overflow-hidden rounded-4 mb-3"> <!-- extra margin bottom -->
                <!-- Poster -->
                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" class="text-decoration-none">
                    <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/150x225' }}" 
                         class="rounded-4 img-fluid w-100" 
                         alt="{{ $torrent->name }}">
                </a>

                <!-- Hover Overlay -->
                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" 
                   class="hover-overlay d-flex flex-column justify-content-center align-items-center text-center bg-dark bg-opacity-75 text-decoration-none" 
                   data-bs-toggle="tooltip" 
                   title="{{ $torrent->name }}">
                    <div class="p-2 text-white">
                        <!-- Genres -->
                        <p class="mb-2">
                            @if(isset($torrent->genres))
                                @foreach($torrent->genres as $genre)
                                    <span class="badge bg-secondary">{{ $genre->name }}</span>
                                @endforeach
                            @endif
                        </p>
                        <!-- Stats -->
                        <div class="torrent-infos d-flex flex-wrap gap-2 justify-content-center">
                            <span class="badge bg-success">Seeders: {{ $torrent->seeders }}</span>
                            <span class="badge bg-danger">Leechers: {{ $torrent->leechers }}</span>
                            <span class="badge bg-info">Completed: {{ $torrent->times_completed }}</span>
                        </div>
                    </div>
                </a>
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
/* Poster Card */
.torrent-card {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    transition: transform 0.3s ease-in-out;
}
.torrent-card img {
    display: block;
    width: 100%;
    height: auto;
    object-fit: contain;
}
.torrent-card:hover {
    transform: scale(1.05);
}

/* Hover Overlay */
.hover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
}
.torrent-card:hover .hover-overlay {
    opacity: 1;
}

/* Tabs */
.nav-tabs .nav-link {
    font-weight: 500;
    border: none;        /* removes the default border */
    color: #fff;
    font-size: 0.95rem;
}
.nav-tabs .nav-link.active {
    border-bottom: 3px solid #0d6efd; /* highlight active tab */
}
.nav-tabs .nav-link {
    transition: background 0.2s, transform 0.2s;
}
.nav-tabs .nav-link:hover {
    background: rgba(255,255,255,0.1);
    transform: scale(1.05);
}
</style>
