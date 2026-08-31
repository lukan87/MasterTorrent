<div class="mt-4 mb-4">
    <div class="row g-4">

        @foreach ([
            'Last Day'   => $topLastDay,
            'Last Week'  => $topLastWeek,
            'Last Month' => $topLastMonth
        ] as $title => $torrents)

        @php
            $sortedTorrents = $torrents->sortByDesc('seeders')->take(5);
        @endphp

        <div class="col-12 col-md-6 col-xxl-4">
            {{-- <div class="card top-section-card glass h-100 rounded-5 shadow-lg"> --}}
                <div class="card glass h-100 rounded-5 shadow-lg">

                {{-- Header --}}
                <div class="section-header px-4 py-3 rounded-top-5 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-white">
                        {{ $title }}
                    </span>
                    <i class="bi bi-fire text-warning"></i>
                </div>

                {{-- Body --}}
                <div class="card-body d-flex flex-column gap-3 px-4 pb-4">

                    @forelse ($sortedTorrents as $index => $torrent)
                        <div class="torrent-info-card rounded-4 p-3 glow-hover">

                            {{-- Rank --}}
                            <div class="rank-badge">
                                #{{ $index + 1 }}
                            </div>

                            <div class="d-flex flex-column gap-2">

                                {{-- Name --}}
                                <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => urlencode($torrent->slug)]) }}"
                                   class="torrent-link">
                                    <span class="torrent-title">
                                       {{ \Illuminate\Support\Str::limit($torrent->name, 70, '...') }}
                                    </span>
                                </a>

                                {{-- Genres + Stats Inline --}}
    <div class="d-flex align-items-center flex-wrap gap-2 small">

        {{-- Genres --}}
        @if(!empty($torrent->genres) && $torrent->genres->isNotEmpty())
            @foreach($torrent->genres as $genre)
                <span class="genre-pill">{{ $genre->name }}</span>
            @endforeach
        @endif

        {{-- Stats --}}
        <span class="stat-inline seeders">
            <i class="bi bi-arrow-up"></i> {{ $torrent->seeders }}
        </span>

        <span class="stat-inline leechers">
            <i class="bi bi-arrow-down"></i> {{ $torrent->leechers }}
        </span>

        <span class="stat-inline completed">
            <i class="bi bi-check-circle"></i> {{ $torrent->times_completed }}
        </span>

    </div>

                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            No top {{ strtolower($title) }} found.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

        @endforeach
    </div>
</div>


<style>
/* Section card */
.top-section-card {
    background: linear-gradient(160deg, rgba(30,30,45,.9), rgba(15,15,25,.95));
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,.08);
}

/* Header */
.section-header {
    background: linear-gradient(90deg, #2d2e2f, #17122987);
    letter-spacing: .3px;
}

/* Torrent card */
.torrent-info-card {
    position: relative;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    transition: all .25s ease;
}

/* Glow hover */
.glow-hover:hover {
    transform: translateY(-4px);
    box-shadow:
        0 0 0 1px rgba(140,150,255,.35),
        0 15px 40px rgba(140,150,255,.35);
}

/* Rank */
.rank-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #ffb347, #ff7b00);
    color: #000;
    font-size: .7rem;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 999px;
    box-shadow: 0 5px 15px rgba(255,150,50,.6);
}

/* Title */
.torrent-link {
    text-decoration: none;
}

.torrent-title {
    font-size: .9rem;
    font-weight: 600;
    color: #fff;
    line-height: 1.3;
    transition: color .2s ease;
}

.torrent-link:hover .torrent-title {
    color: #aab1ff;
}

/* Genre pills */
.genre-pill {
    font-size: .75rem;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
    color: #ddd;
}

/* Inline Stats */
.stat-inline {
    font-size: .85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.stat-inline.seeders {
    color: #4dff88;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
}

.stat-inline.leechers {
    color: #ff6b6b;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
}

.stat-inline.completed {
    color: #5ee7ff;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
}

</style>
