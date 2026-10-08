@php
    $guideEpisodes = $episodes->flatten(1);
    $myToday = $guideEpisodes->filter(fn ($episode) => $episode['followed'] && $episode['date'] === $today->toDateString());
    $uploaded = $guideEpisodes->where('available', true)->unique('show_id');
    $waiting = $guideEpisodes->filter(fn ($episode) => $episode['followed'] && !$episode['available'] && ($episode['airstamp'] ? \Carbon\CarbonImmutable::parse($episode['airstamp'])->isPast() : $episode['date'] < $today->toDateString()))->unique('show_id');
    $premieres = $guideEpisodes->where('number', 1)->take(8);
@endphp
<aside class="tvc-guide-sidebar" aria-label="Guide highlights">
    <section class="tvc-rail-panel"><header><h2><i class="bi bi-moon-stars" aria-hidden="true"></i> Today</h2><span>from your shows</span></header>
        @forelse($myToday->take(5) as $episode)<a class="tvc-rail-row" href="{{ $episode['torrent_url'] ?: $episode['url'] }}"><span><strong>{{ $episode['title'] }}</strong><small>{{ $episode['episode_code'] }} · {{ $episode['network'] }}</small></span><b>{{ $episode['time'] ?: 'TBA' }}</b></a>@empty<p>No followed shows in today’s selected schedule.</p>@endforelse
        <a class="tvc-rail-link" href="{{ $calendarUrl(['tab' => 'my', 'q' => '', 'genre' => '', 'network' => '', 'scope' => 'all']) }}#calendar-tabs">Open my shows <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </section>
    <section class="tvc-rail-panel"><header><h2><i class="bi bi-check-circle" aria-hidden="true"></i> Ready on FileIplay</h2><b class="tvc-count-success">{{ $uploaded->count() }}</b></header><p>Matching episodes already uploaded.</p>
        @forelse($uploaded->take(5) as $episode)<a class="tvc-rail-row" href="{{ $episode['torrent_url'] }}"><span><strong>{{ $episode['title'] }}</strong><small>{{ $episode['episode_code'] }}</small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>@empty<div class="tvc-rail-empty">No matching episode uploads in this schedule yet.</div>@endforelse
    </section>
    <section class="tvc-rail-panel"><header><h2><i class="bi bi-hourglass-split" aria-hidden="true"></i> Waiting for upload</h2><b class="tvc-count-waiting">{{ $waiting->count() }}</b></header>
        @forelse($waiting->take(4) as $episode)<a class="tvc-rail-row" href="{{ $episode['series_url'] ?: $episode['url'] }}"><span><strong>{{ $episode['title'] }}</strong><small>{{ $episode['episode_code'] }} · Scheduled {{ \Carbon\CarbonImmutable::parse($episode['date'])->format('j M') }}</small></span><i class="bi bi-clock" aria-hidden="true"></i></a>@empty<p>No followed episodes waiting in this selection.</p>@endforelse
    </section>
    <section class="tvc-rail-panel"><header><h2><i class="bi bi-stars" aria-hidden="true"></i> Premieres</h2><span>this {{ $filters['view'] === 'day' ? 'day' : 'week' }}</span></header>
        @forelse($premieres as $episode)<a class="tvc-rail-row tvc-rail-premiere" href="{{ $episode['url'] }}"><time datetime="{{ $episode['date'] }}">{{ \Carbon\CarbonImmutable::parse($episode['date'])->format('j D') }}</time><span><strong>{{ $episode['title'] }}</strong><small>{{ $episode['episode_code'] }}</small></span><b>Premiere</b></a>@empty<p>No premieres in the selected schedule.</p>@endforelse
    </section>
</aside>
