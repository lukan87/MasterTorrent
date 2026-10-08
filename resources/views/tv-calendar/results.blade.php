@php
    $calendarUrl = fn (array $changes = []) => route('tv-calendar.index', array_replace($filters, $changes));
    $step = $filters['view'] !== 'day' ? 7 : 1;
@endphp
<div class="tv-calendar {{ $filters['view'] === 'week' ? 'tvc-week-view' : 'tvc-agenda-view' }} {{ $filters['view'] === 'day' ? 'tvc-daily-view' : '' }}">
    <header class="tvc-guide-header">
        <div class="tvc-guide-title"><span class="tvc-guide-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span><div><h1>TV Guide</h1><p>Your next episode, all in one place.</p></div></div>
        <div class="tvc-guide-period">{{ $start->format('j M') }}{{ $start->equalTo($end) ? '' : ' – '.$end->format('j M') }} <span>{{ $end->format('Y') }}</span></div>
        <nav class="tvc-date-nav" aria-label="Quick schedule navigation">
            <a class="tvc-button" href="{{ $calendarUrl(['date' => $start->subDays($step)->toDateString()]) }}" aria-label="Previous {{ $filters['view'] }}"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
            <a class="tvc-button" href="{{ $calendarUrl(['date' => $today->toDateString(), 'view' => 'week']) }}">This week</a>
            <a class="tvc-button" href="{{ $calendarUrl(['date' => $start->addDays($step)->toDateString()]) }}" aria-label="Next {{ $filters['view'] }}"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            <a class="tvc-button" href="{{ $calendarUrl(['date' => $today->toDateString(), 'view' => 'day']) }}">Today</a>
        </nav>
        <div class="tvc-guide-views"><a class="tvc-button {{ $filters['view'] === 'week' ? 'is-active' : '' }}" href="{{ $calendarUrl(['view' => 'week']) }}">Week</a><a class="tvc-button {{ $filters['view'] !== 'week' ? 'is-active' : '' }}" href="{{ $calendarUrl(['view' => 'agenda']) }}">Agenda</a></div>
    </header>
    <nav class="tvc-tabs" id="calendar-tabs" aria-label="TV calendar sections">
        @foreach(['my' => ['bi-bookmark-heart', 'My shows', 'Your follows & upload alerts'], 'popular' => ['bi-fire', 'Popular on FileIplay', 'Most downloaded series airing'], 'all' => ['bi-globe2', 'All shows (worldwide)', 'Discover TV & streaming schedules']] as $tab => $item)
            <a href="{{ $calendarUrl(['tab' => $tab, 'scope' => 'all', 'q' => '', 'genre' => '', 'network' => '']) }}#calendar-tabs" class="tvc-tab {{ $filters['tab'] === $tab ? 'is-active' : '' }}" @if($filters['tab'] === $tab) aria-current="page" @endif>
                <i class="bi {{ $item[0] }}" aria-hidden="true"></i><span><strong>{{ $item[1] }}</strong><small>{{ $item[2] }}</small></span><b>{{ $tabCounts[$tab] }}</b>
            </a>
        @endforeach
    </nav>
    @if($filters['tab'] === 'my')
    <section id="watchlist" class="tvc-watchlist tvc-watchlist-top" aria-labelledby="watchlist-title">
        <div class="tvc-toolbar"><div><span class="tvc-eyebrow">YOUR PERSONAL LINE-UP</span><h2 id="watchlist-title">My shows <span>{{ $watchlist->count() }}</span></h2></div><a class="tvc-button" href="{{ $calendarUrl(['tab' => 'my', 'scope' => 'followed', 'view' => 'week', 'q' => '', 'genre' => '', 'network' => '', 'source' => 'all']) }}#airing-schedule">See airing episodes <i class="bi bi-arrow-down" aria-hidden="true"></i></a></div>
        <p class="tvc-note">All your followed series appear here, even when they aren’t airing this week. Upload alerts arrive as private messages in your site inbox. Use “Manage title alerts” for existing site subscriptions; removing a calendar follow keeps its existing title subscription.</p>
        <div class="tvc-watchlist-grid">
            @forelse($watchlist as $follow)
                <div class="tvc-watchlist-item"><div><a href="{{ $follow->url }}" target="_blank" rel="noopener noreferrer">{{ $follow->title }} <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a><small>{{ $follow->legacy_notify ? 'Site subscription · upload alerts on' : ($follow->notify_upload ? 'Upload alerts on' : 'Following · upload alerts off') }}</small></div>
                    @if($follow->legacy_notify)
                        <a class="tvc-button" href="{{ $follow->manage_url }}">Manage title alerts</a>
                    @else
                    <form action="{{ route('tv-calendar.follow', $follow->tvmaze_id) }}" method="POST">@csrf<input type="hidden" name="notify_upload" value="{{ $follow->notify_upload ? '0' : '1' }}"><button class="tvc-icon-button" type="submit" @disabled(!$follow->imdbid && !$follow->tmdbid) aria-label="{{ $follow->notify_upload ? 'Disable' : 'Enable' }} upload alerts for {{ $follow->title }}" title="{{ $follow->notify_upload ? 'Disable' : 'Enable' }} upload alerts"><i class="bi {{ $follow->notify_upload ? 'bi-bell-fill' : 'bi-bell' }}" aria-hidden="true"></i></button></form>
                    @endif
                    @if($follow->tvmaze_id)
                    <form action="{{ route('tv-calendar.unfollow', $follow->tvmaze_id) }}" method="POST">@csrf @method('DELETE')<button class="tvc-icon-button" type="submit" aria-label="Unfollow {{ $follow->title }}" title="Unfollow"><i class="bi bi-x-lg" aria-hidden="true"></i></button></form>
                    @endif
                </div>
            @empty
                <div class="tvc-empty"><i class="bi bi-bookmark-heart" aria-hidden="true"></i><div><strong>Your next favourite belongs here.</strong><p>Follow a show in the schedule to save it, even before its first upload.</p></div></div>
            @endforelse
        </div>
    </section>
    @elseif($filters['tab'] === 'popular')
        <p class="tvc-tab-description">Series airing in this date range with uploads on FileIplay, ranked by completed downloads on the site.</p>
    @else
        <p class="tvc-tab-description">Explore broadcast schedules by country and streaming releases from global platforms. Choose a country below to browse its networks.</p>
    @endif
    <div class="tvc-stats" aria-label="Filtered schedule totals">
        <div><i class="bi bi-tv" aria-hidden="true"></i><strong>{{ $stats['shows'] }}</strong><span>Series on the radar</span></div>
        <div><i class="bi bi-collection-play" aria-hidden="true"></i><strong>{{ $stats['episodes'] }}</strong><span>Scheduled episodes</span></div>
        <div><i class="bi bi-bookmark-heart" aria-hidden="true"></i><strong>{{ $stats['followed'] }}</strong><span>Followed series airing</span></div>
        <div><i class="bi bi-check-circle" aria-hidden="true"></i><strong>{{ $stats['available'] }}</strong><span>Episodes uploaded</span></div>
    </div>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    @if(count($warnings))
        <div class="tvc-warning" role="status"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> TVmaze could not refresh part of this schedule. Some results may be missing or out of date.
            <details><summary>Schedule details</summary>@foreach($warnings as $warning)<p>{{ $warning }}</p>@endforeach</details>
        </div>
    @endif
    <form method="GET" action="{{ route('tv-calendar.index') }}" class="tvc-filters" aria-label="Filter the TV schedule">
        <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
        <input type="hidden" name="hide_daily" value="{{ (int) $filters['hide_daily'] }}">
        <input type="hidden" name="only_followed" value="{{ (int) $filters['only_followed'] }}">
        <div class="tvc-filter-main">
            <label class="tvc-search">Search shows or episodes<input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Find your next favourite…"></label>
            <label>Date<input type="date" name="date" value="{{ $filters['date'] }}" min="2000-01-01" max="2100-12-31" required></label>
            <label>View<select name="view"><option value="week" @selected($filters['view'] === 'week')>Week grid</option><option value="agenda" @selected($filters['view'] === 'agenda')>Weekly agenda</option><option value="day" @selected($filters['view'] === 'day')>Daily schedule</option></select></label>
            <label>Country<select name="country">@foreach($countries as $code => $name)<option value="{{ $code }}" @selected($filters['country'] === $code)>{{ $name }}</option>@endforeach</select></label>
            <button class="tvc-button tvc-primary" type="submit"><i class="bi bi-sliders" aria-hidden="true"></i> Apply filters</button>
            <a class="tvc-reset" href="{{ route('tv-calendar.index', ['tab' => $filters['tab']]) }}#calendar-tabs">Reset</a>
        </div>
        <details class="tvc-filter-options" @if($filters['genre'] || $filters['network'] || $filters['scope'] !== 'all' || $filters['source'] !== 'all') open @endif>
            <summary><i class="bi bi-sliders2" aria-hidden="true"></i> Genre, network & availability filters <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
        <div class="tvc-filter-extra">
            <label>Genre<select name="genre"><option value="">All genres</option>@foreach($genres->merge([$filters['genre']])->filter()->unique()->sort() as $genre)<option @selected($filters['genre'] === $genre)>{{ $genre }}</option>@endforeach</select></label>
            <label>Network / platform<select name="network"><option value="">All networks & platforms</option>@foreach($networks->merge([$filters['network']])->filter()->unique()->sort() as $network)<option @selected($filters['network'] === $network)>{{ $network }}</option>@endforeach</select></label>
            <label>Schedule<select name="source"><option value="all" @selected($filters['source'] === 'all')>TV + streaming</option><option value="broadcast" @selected($filters['source'] === 'broadcast')>TV broadcasts</option><option value="streaming" @selected($filters['source'] === 'streaming')>Streaming platforms</option></select></label>
            <label>Show me<select name="scope"><option value="all" @selected($filters['scope'] === 'all')>Everything</option><option value="followed" @selected($filters['scope'] === 'followed')>My followed shows</option><option value="available" @selected($filters['scope'] === 'available')>Episode uploaded</option><option value="on-site" @selected($filters['scope'] === 'on-site')>Series on this site</option></select></label>
        </div>
        </details>
    </form>
    <section class="tvc-discovery-controls" aria-label="Networks and show preferences">
        <nav class="tvc-network-chips" aria-label="Quick network filters">
            <span><i class="bi bi-broadcast" aria-hidden="true"></i> Networks</span>
            <a href="{{ $calendarUrl(['network' => '']) }}" class="{{ !$filters['network'] ? 'is-active' : '' }}" @if(!$filters['network']) aria-current="true" @endif>All networks</a>
            @foreach($networkCounts as $network => $count)
                <a href="{{ $calendarUrl(['network' => $network]) }}" class="{{ $filters['network'] === $network ? 'is-active' : '' }}" @if($filters['network'] === $network) aria-current="true" @endif>{{ $network }} <small>{{ $count }}</small></a>
            @endforeach
        </nav>
        <form action="{{ route('tv-calendar.index') }}" method="GET" class="tvc-quick-options">
            @foreach($filters as $key => $value)
                @if(!in_array($key, ['hide_daily', 'only_followed']))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <input type="hidden" name="hide_daily" value="0">
            <label class="tvc-check-option"><input type="checkbox" name="hide_daily" value="1" @checked($filters['hide_daily'])><span>Hide daily, talk &amp; reality</span></label>
            <input type="hidden" name="only_followed" value="0">
            <label class="tvc-check-option"><input type="checkbox" name="only_followed" value="1" @checked($filters['only_followed'])><span>Only followed shows</span></label>
            <noscript><button type="submit" class="tvc-button">Apply preferences</button></noscript>
        </form>
    </section>
    <aside class="tvc-info-alert" role="note" aria-labelledby="tvc-info-title">
        <span class="tvc-info-icon"><i class="bi bi-info-circle-fill" aria-hidden="true"></i></span>
        <div>
            <h2 id="tvc-info-title">About this schedule</h2>
            <p><strong>Airing does not guarantee an upload.</strong> Episode badges match individual releases; season packs appear under series uploads.</p>
            <p>Schedule, show information and artwork provided by <a href="https://www.tvmaze.com/" target="_blank" rel="noopener noreferrer">TVmaze <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a> · <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA</a> · Schedule refreshed every 30 minutes. Networks can change air dates.</p>
        </div>
    </aside>
    <div class="tvc-guide-layout">
    <section id="airing-schedule" aria-label="Episode schedule" class="tvc-schedule">
        <div class="tvc-schedule-caption"><span><i class="bi bi-clock" aria-hidden="true"></i> Original network / platform times</span><span>TV + streaming · {{ $stats['episodes'] }} episodes</span></div>
        <div class="tvc-board {{ $dates->count() <= 1 ? 'tvc-single-date' : '' }}" style="--tvc-days: {{ max(1, $dates->count()) }}" tabindex="0" aria-label="{{ $filters['view'] === 'week' ? 'Weekly guide, scroll horizontally to see more days' : ($filters['view'] === 'agenda' ? 'Weekly episode agenda' : 'Daily episode agenda') }}">
        @forelse($dates as $date)
        @php
            $dayEpisodes = $episodes->get($date->toDateString(), collect());
            $visibleCount = $filters['view'] === 'week' ? 10 : 24;
        @endphp
        <section class="tvc-day {{ $date->isSameDay($today) ? 'is-today' : '' }}" id="day-{{ $date->toDateString() }}" aria-labelledby="heading-{{ $date->toDateString() }}">
            <div class="tvc-day-heading"><div><h3 id="heading-{{ $date->toDateString() }}">{{ $filters['view'] === 'week' ? ($date->isSameDay($today) ? 'Today' : $date->format('D')) : $date->format('l, j F') }} @if($filters['view'] !== 'week' && $date->isSameDay($today))<b class="tvc-today-label">Today</b>@endif</h3><small>{{ $dayEpisodes->count() }} episodes</small></div><time datetime="{{ $date->toDateString() }}">{{ $date->format('j') }}<span>{{ $date->format('M') }}</span></time></div>
            <div class="tvc-cards">
                @forelse($dayEpisodes->take($visibleCount) as $episode)
                    @include($filters['view'] === 'week' ? 'tv-calendar.card' : 'tv-calendar.agenda-row')
                @empty
                    <div class="tvc-empty"><i class="bi bi-moon-stars" aria-hidden="true"></i><div><strong>No episodes to show</strong><p>{{ count($warnings) ? 'Schedule data may be unavailable. Try again shortly or change your filters.' : 'Try another date, country, or filter to discover more shows.' }}</p></div></div>
                @endforelse
            </div>
            @if($dayEpisodes->count() > $visibleCount)
                <details class="tvc-more-episodes">
                    <summary>Explore {{ $dayEpisodes->count() - $visibleCount }} more episodes on {{ $date->format('l') }} <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="tvc-cards">@foreach($dayEpisodes->skip($visibleCount) as $episode)@include($filters['view'] === 'week' ? 'tv-calendar.card' : 'tv-calendar.agenda-row')@endforeach</div>
                </details>
            @endif
        </section>
        @empty
            <div class="tvc-empty"><i class="bi bi-search" aria-hidden="true"></i><div><strong>No matching episodes this week</strong><p>{{ count($warnings) ? 'Schedule data may be unavailable. Try again shortly or change your filters.' : 'Try another search, filter, or week to find scheduled episodes.' }}</p></div></div>
        @endforelse
        </div>
    </section>
    @include('tv-calendar.sidebar')
    </div>
</div>
