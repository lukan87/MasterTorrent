<div id="recommended-slider" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner bg-dark p-3 rounded shadow">
        @foreach ($recommendedTorrents as $index => $torrent)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <div class="d-flex align-items-center">
                    <!-- Poster -->
                    <img src="{{ $torrent->poster ?? asset('images/default-poster.jpg') }}" 
                         alt="{{ $torrent->name }}" 
                         class="d-block w-25 me-4 rounded" 
                         style="object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    
                    <!-- Torrent Info -->
                    <div>
                        <h5 class="mb-2 text-primary">{{ $torrent->name }}</h5>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Seeders:</strong> {{ $torrent->seeders }}</li>
                            <li><strong>Leechers:</strong> {{ $torrent->leechers }}</li>
                            <li><strong>Completed:</strong> {{ $torrent->times_completed }} times</li>
                        </ul>
                        <a href="{{ route('torrents.show', $torrent->id) }}" class="btn btn-sm btn-success mt-3">
                            View Torrent
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#recommended-slider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon bg-primary rounded-circle p-2" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#recommended-slider" data-bs-slide="next">
        <span class="carousel-control-next-icon bg-primary rounded-circle p-2" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>