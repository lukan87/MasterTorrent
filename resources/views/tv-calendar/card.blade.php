<article class="tvc-card {{ $episode['followed'] ? 'tvc-followed' : '' }}">
    <div class="tvc-poster">@if($episode['image'])<img src="{{ $episode['image'] }}" alt="" loading="lazy" width="210" height="295">@else<i class="bi bi-tv" aria-hidden="true"></i>@endif
        @if($episode['rating'])<span class="tvc-rating"><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $episode['rating'] }}</span>@endif
    </div>
    <div class="tvc-card-details">
    <div class="tvc-card-heading">
        <div class="tvc-airline"><span><i class="bi {{ $episode['streaming'] ? 'bi-play-btn' : 'bi-clock' }}" aria-hidden="true"></i> {{ $episode['time'] ?: 'Time TBA' }}</span></div>
        <h4><a href="{{ $episode['url'] }}" target="_blank" rel="noopener noreferrer">{{ $episode['title'] }}</a></h4>
        <p class="tvc-episode-code">{{ $episode['episode_code'] }}</p>
    </div>
    <div class="tvc-card-body">
        <p class="tvc-network">{{ $episode['network'] }} @if($episode['runtime'])<span>· {{ $episode['runtime'] }} min</span>@endif</p>
        <p class="tvc-episode">{{ $episode['episode'] }}</p>
        <div class="tvc-tags">@foreach($episode['genres'] as $genre)<a href="{{ $calendarUrl(['genre' => $genre]) }}">{{ $genre }}</a>@endforeach @if($episode['number'] === 1)<span class="tvc-premiere">{{ $episode['season'] === 1 ? 'Series premiere' : 'Season premiere' }}</span>@endif</div>
        <div class="tvc-availability">
            @if($episode['available'])<a href="{{ $episode['torrent_url'] }}" class="tvc-uploaded"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Episode uploaded <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            @elseif($episode['upload_count'])<a href="{{ $episode['series_url'] ?: $episode['online_url'] }}"><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $episode['upload_count'] }} series uploads · episode pending</a>
            @elseif($episode['online_url'])<a href="{{ $episode['online_url'] }}"><i class="bi bi-play-circle" aria-hidden="true"></i> Series in online library</a>
            @else<span><i class="bi bi-hourglass-split" aria-hidden="true"></i> {{ ($episode['airstamp'] ? \Carbon\CarbonImmutable::parse($episode['airstamp'])->isFuture() : $episode['date'] > $today->toDateString()) ? 'Upcoming' : 'Awaiting site upload' }}</span>@endif
        </div>
        @if($episode['summary'])<details class="tvc-summary"><summary>Episode details</summary><p>{{ $episode['summary'] }}</p></details>@endif
        @include('tv-calendar.actions')
    </div>
    </div>
</article>
