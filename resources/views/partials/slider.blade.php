
@if($recommendedTorrents->isEmpty())

@else
<div class="container mt-4">
    <div class="card shadow-lg border-0 rounded-4 bg-dark text-white">
        <!-- Card Title -->
        <div class="card-header bg-transparent border-0 text-center">
            <h4 class="mb-0">Recommended Torrents</h4>
        </div>

        <div class="card-body p-4">
          
                <div class="owl-carousel owl-theme hero-slide">
                    @foreach ($recommendedTorrents as $torrent)
                        <div class="hero-slide-item rounded-4 overflow-hidden position-relative">
                            <!-- Poster -->
                            <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" class="text-light text-decoration-none">
                                <img src="{{ $torrent->poster }}" class="rounded-4 img-fluid lazyload w-100" alt="{{ $torrent->name }}">
                            </a>

                            <!-- Hover Content (Initially Hidden) -->
                            <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}" class="hover-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center text-center bg-dark bg-opacity-75 rounded-4 text-decoration-none" data-bs-toggle="tooltip" title="{{ $torrent->name }}">
                                <div class="p-3 text-white">
                                    <p class="mb-2">
                                        @foreach($torrent->genres as $genre)
                                            <span class="badge bg-secondary">{{ $genre->name }}</span>
                                        @endforeach
                                    </p>
                                    <div class="movie-infos d-flex flex-wrap gap-2 justify-content-center">
                                        <span class="badge bg-success">Seeders: {{ $torrent->seeders }}</span>
                                        <span class="badge bg-danger">Leechers: {{ $torrent->leechers }}</span>
                                        <span class="badge bg-info">Completed: {{ $torrent->times_completed }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
          
        </div>
    </div>
</div>

@endif





<style>
/* Ensure Parent Container Handles Positioning */
.hero-slide-item {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    transition: transform 0.3s ease-in-out;
}

.hero-slide-item:hover {
    transform: scale(1.05); /* Slight Zoom Effect */
}

/* Hover Overlay (Initially Hidden) */
.hover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
}

/* Show Overlay on Hover */
.hero-slide-item:hover .hover-overlay {
    opacity: 1;
}
</style>
