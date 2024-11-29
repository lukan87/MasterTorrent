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
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="torrentTabsContent">
            <!-- Last Day Tab -->
            <div class="tab-pane fade show active" id="last-day" role="tabpanel" aria-labelledby="last-day-tab">
                <div class="list-group">
                    @foreach ($topLastDay as $torrent)
                        <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap">
                            <div class="torrent-info w-100">
                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a>
                                @include('torrents.partials.tags')
                                <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>


                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Last Week Tab -->
            <div class="tab-pane fade" id="last-week" role="tabpanel" aria-labelledby="last-week-tab">
                <div class="list-group">
                    @foreach ($topLastWeek as $torrent)
                        <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap">
                            <div class="torrent-info w-100">
                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a>
                                @include('torrents.partials.tags')
                                <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Last Month Tab -->
            <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="last-month-tab">
                <div class="list-group">
                    @foreach ($topLastMonth as $torrent)
                        <div class="list-group-item d-flex justify-content-between align-items-start flex-wrap">
                            <div class="torrent-info w-100">
                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" class="fw-bold">{{ $torrent->name }}</a>
                                @include('torrents.partials.tags')
                                <div class="small text-muted">{{ $torrent->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="torrent-stats d-flex flex-wrap w-100 mt-2">
                                <div class="badge bg-primary me-2 mb-2">{{ $torrent->seeders }} Seeder(s)</div>
                                <div class="badge bg-info me-2 mb-2">{{ $torrent->leechers }} Leecher(s)</div>
                                <div class="badge bg-success mb-2">{{ $torrent->times_completed }} Completed</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

