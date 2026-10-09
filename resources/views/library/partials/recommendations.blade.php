@if(!empty($recommendations))
<div class="tmdb-recs mt-4">
    <div class="tmdb-recs-header">
        <div><h5 class="tmdb-recs-title mb-0"><i class="bi bi-stars me-2"></i>You Might Also Like</h5>
            <div class="tmdb-recs-subtitle">Discover related titles, online playback, and torrents</div>
        </div>
    </div>
    <div class="tmdb-recs-body"><div class="tmdb-recs-row">
        @foreach($recommendations as $rec)
            <a href="{{ $rec['url'] }}" class="tmdb-recs-card tmdb-recs-card-available text-decoration-none" title="View title">
                <div class="tmdb-recs-poster-wrap">
                    <img src="{{ $rec['poster'] ?? '/images/not-found.jpg' }}" loading="lazy" class="tmdb-recs-poster" alt="{{ $rec['title'] }}">
                    @if($rec['in_database'])
                        <span class="tmdb-recs-status tmdb-recs-status-online" title="Online playback enabled"><i class="bi bi-play-circle-fill"></i></span>
                    @endif
                    @if(!empty($rec['rating']))
                        <span class="tmdb-recs-rating"><i class="bi bi-star-fill"></i> {{ number_format($rec['rating'], 1) }}</span>
                    @endif
                </div>
                <div class="tmdb-recs-info"><div class="tmdb-recs-name">{{ $rec['title'] }}</div>
                    @if(!empty($rec['year']))<div class="tmdb-recs-year">{{ $rec['year'] }}</div>@endif
                </div>
            </a>
        @endforeach
    </div></div>
</div>
@endif
