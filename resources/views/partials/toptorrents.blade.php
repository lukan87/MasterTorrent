<div class="mt-3 mb-3">
    <!-- Card for Top Torrents -->
    <div class="card">
        <div class="card-header">
            <h5>Top Torrents</h5>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="torrentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="last-day-tab" data-bs-toggle="tab" href="#last-day" role="tab" aria-controls="last-day" aria-selected="true">Top Last Day</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="last-week-tab" data-bs-toggle="tab" href="#last-week" role="tab" aria-controls="last-week" aria-selected="false">Top Last Week</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="last-month-tab" data-bs-toggle="tab" href="#last-month" role="tab" aria-controls="last-month" aria-selected="false">Top Last Month</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="movies-tab" data-bs-toggle="tab" href="#movies" role="tab" aria-controls="movies" aria-selected="false">Top Movies</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="series-tab" data-bs-toggle="tab" href="#series" role="tab" aria-controls="series" aria-selected="false">Top Series</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="torrentTabsContent">
            <!-- Last Day Tab -->
            <div class="tab-pane fade show active" id="last-day" role="tabpanel" aria-labelledby="last-day-tab">
                <div class="list-group">
                    @foreach ($topLastDay as $torrent)
                        <div class="list-group-item d-flex align-items-start flex-wrap">
                            <!-- Poster Column -->
                            <div class="flex-shrink-0 me-3" style="width: 90px;">
                                <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/60x90' }}" 
                                     alt="Poster" 
                                     class="img-fluid rounded" 
                                     style="width: 90px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="torrent-info w-100">
                                    <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a><br>
                                    @include('torrents.partials.tags')
                                    <div class="small text-muted">Uploaded: {{ $torrent->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                    <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                    <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                    <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Last Week Tab -->
            <div class="tab-pane fade" id="last-week" role="tabpanel" aria-labelledby="last-week-tab">
                <div class="list-group">
                    @foreach ($topLastWeek as $torrent)
                        <div class="list-group-item d-flex align-items-start flex-wrap">
                            <!-- Poster Column -->
                            <div class="flex-shrink-0 me-3" style="width: 90px;">
                                <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/60x90' }}" 
                                     alt="Poster" 
                                     class="img-fluid rounded" 
                                     style="width: 90px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="torrent-info w-100">
                                    <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a><br>
                                    @include('torrents.partials.tags')
                                    <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                    <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                    <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                    <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Last Month Tab -->
            <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="last-month-tab">
                <div class="list-group">
                    @foreach ($topLastMonth as $torrent)
                        <div class="list-group-item d-flex align-items-start flex-wrap">
                            <!-- Poster Column -->
                            <div class="flex-shrink-0 me-3" style="width: 90px;">
                                <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/60x90' }}" 
                                     alt="Poster" 
                                     class="img-fluid rounded" 
                                     style="width: 90px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="torrent-info w-100">
                                    <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a><br>
                                    @include('torrents.partials.tags')
                                    <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                    <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                    <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                    <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Movies Tab -->
            <div class="tab-pane fade" id="movies" role="tabpanel" aria-labelledby="movies-tab">
                <div class="list-group">
                    @forelse ($topMovies as $torrent)
                        <div class="list-group-item d-flex align-items-start flex-wrap">
                            <!-- Poster Column -->
                            <div class="flex-shrink-0 me-3" style="width: 90px;">
                                <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/60x90' }}" 
                                     alt="Poster" 
                                     class="img-fluid rounded" 
                                     style="width: 90px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="torrent-info w-100">
                                    <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a><br>
                                    @include('torrents.partials.tags')
                                    <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                    <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                    <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                    <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item">
                            <p class="text-muted mb-0">No top movies found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Series Tab -->
            <div class="tab-pane fade" id="series" role="tabpanel" aria-labelledby="series-tab">
                <div class="list-group">
                    @forelse ($topSeries as $torrent)
                        <div class="list-group-item d-flex align-items-start flex-wrap">
                            <!-- Poster Column -->
                            <div class="flex-shrink-0 me-3" style="width: 90px;">
                                <img src="{{ $torrent->poster ?: 'https://via.placeholder.com/60x90' }}" 
                                     alt="Poster" 
                                     class="img-fluid rounded" 
                                     style="width: 90px; height: 120px; object-fit: cover;">
                            </div>
                            <!-- Content Column -->
                            <div class="flex-grow-1">
                                <div class="torrent-info w-100">
                                    <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a><br>
                                    @include('torrents.partials.tags')
                                    <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                    <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                    <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                    <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item">
                            <p class="text-muted mb-0">No top series found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>