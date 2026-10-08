@if($movieOfTheDay)
    @php
        $featuredUrl = route('torrents.show', [$movieOfTheDay->id, urlencode($movieOfTheDay->slug)]);
        $featuredTitle = $movieOfTheDay->clean_name ?? str_replace('.', ' ', $movieOfTheDay->name);
    @endphp
    <section class="motd-feature" aria-labelledby="motd-title">
        <a class="motd-feature-poster" href="{{ $featuredUrl }}" tabindex="-1" aria-hidden="true">
            @if($movieOfTheDay->poster)
                <img src="{{ $movieOfTheDay->poster }}" width="96" height="144" alt="" loading="lazy" decoding="async">
            @else
                <i class="bi bi-film" aria-hidden="true"></i>
            @endif
        </a>
        <div class="motd-feature-info">
            <span class="motd-feature-label"><i class="bi bi-stars" aria-hidden="true"></i> Movie of the Day</span>
            <h2 id="motd-title"><a href="{{ $featuredUrl }}">{{ $featuredTitle }}</a></h2>
            <div class="motd-feature-meta">
                <span><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $movieOfTheDay->category->name ?? 'Movies' }}</span>
                @if($movieOfTheDay->created_at)
                    <span>Added <time datetime="{{ $movieOfTheDay->created_at->toDateString() }}">{{ $movieOfTheDay->created_at->format('M j, Y') }}</time></span>
                @endif
            </div>
            <div class="motd-feature-stats" aria-label="Torrent activity">
                <span class="motd-feature-seeders"><i class="bi bi-arrow-up" aria-hidden="true"></i> <strong>{{ number_format($movieOfTheDay->seeders) }}</strong> seeders</span>
                <span><i class="bi bi-arrow-down" aria-hidden="true"></i> <strong>{{ number_format($movieOfTheDay->leechers) }}</strong> leechers</span>
                <span><i class="bi bi-check2-circle" aria-hidden="true"></i> <strong>{{ number_format($movieOfTheDay->times_completed) }}</strong> completed</span>
            </div>
        </div>
        <a class="motd-feature-link" href="{{ $featuredUrl }}">View release <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </section>
@endif
