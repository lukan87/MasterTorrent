<article class="tvc-agenda-row {{ $episode['followed'] ? 'tvc-followed' : '' }}">
    <time class="tvc-agenda-time" @if($episode['airstamp']) datetime="{{ $episode['airstamp'] }}" @endif>{{ $episode['time'] ?: 'TBA' }}</time>
    <div class="tvc-poster">@if($episode['image'])<img src="{{ $episode['image'] }}" alt="" loading="lazy" width="210" height="295">@else<i class="bi bi-tv" aria-hidden="true"></i>@endif</div>
    <div class="tvc-agenda-info">
        @if($episode['number'] === 1)<span class="tvc-agenda-premiere">{{ $episode['season'] === 1 ? 'Series premiere' : 'Season premiere' }}</span>@endif
        <h4><a href="{{ $episode['url'] }}" target="_blank" rel="noopener noreferrer">{{ $episode['title'] }}</a></h4>
        <p class="tvc-agenda-meta"><b>{{ $episode['episode_code'] }}</b> · {{ $episode['episode'] }} · {{ $episode['network'] }} @if($episode['runtime']) · {{ $episode['runtime'] }} min @endif</p>
        <div class="tvc-availability">
            @if($episode['available'])<a href="{{ $episode['torrent_url'] }}" class="tvc-uploaded"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Episode uploaded <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            @elseif($episode['upload_count'])<a href="{{ $episode['series_url'] ?: $episode['online_url'] }}"><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $episode['upload_count'] }} series uploads · episode pending</a>
            @elseif($episode['online_url'])<a href="{{ $episode['online_url'] }}"><i class="bi bi-play-circle" aria-hidden="true"></i> Series in online library</a>
            @else<span><i class="bi bi-clock" aria-hidden="true"></i> {{ ($episode['airstamp'] ? \Carbon\CarbonImmutable::parse($episode['airstamp'])->isFuture() : $episode['date'] > $today->toDateString()) ? 'Upcoming' : 'Aired · waiting for upload' }}</span>@endif
        </div>
        @if($episode['summary'])<details class="tvc-summary"><summary>Episode details</summary><p>{{ $episode['summary'] }}</p></details>@endif
    </div>
    @include('tv-calendar.actions')
</article>
