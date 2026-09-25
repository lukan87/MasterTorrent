<section class="actor-panel actor-credit-section" id="{{ $sectionId }}" aria-labelledby="{{ $sectionId }}-title">
    <header class="actor-section-heading">
        <div><h2 id="{{ $sectionId }}-title">{{ $heading }} <span class="actor-count">{{ count($credits) }}</span></h2>
            <p>Newest first{{ count($credits) > 10 ? ' · Scroll to explore all credits' : '' }}</p>
        </div>
    </header>
    @if($credits)
        <div class="actor-credit-list {{ count($credits) > 10 ? 'actor-credit-list-scroll' : '' }}" @if(count($credits) > 10) tabindex="0" role="region" aria-label="{{ $heading }} credits, scroll for more" @endif>
            @foreach($credits as $credit)
                <a class="actor-credit" href="{{ route($credit['type'] === 'movie' ? 'library.movies.show' : 'library.series.show', ['tmdbid' => $credit['id']]) }}">
                    <div class="actor-credit-poster">
                        @if($credit['poster'])<img src="{{ $credit['poster'] }}" alt="" loading="lazy" width="44" height="66">
                        @else<i class="bi bi-film" aria-hidden="true"></i>@endif
                    </div>
                    <div class="actor-credit-copy">
                        <h3 title="{{ $credit['title'] }}">{{ $credit['title'] }}</h3>
                        <p title="{{ implode(', ', $credit['roles']) }}">{{ $credit['roles'] ? implode(', ', $credit['roles']) : 'Role not listed' }}</p>
                        <span>{{ $credit['type'] === 'movie' ? 'Movie' : 'TV series' }}@if($credit['episodes']) · {{ $credit['episodes'] }} {{ $credit['episodes'] === 1 ? 'episode' : 'episodes' }}@endif</span>
                    </div>
                    <div class="actor-credit-year">{{ $credit['year'] }}
                        @if($credit['votes'])<small><i class="bi bi-star-fill" aria-hidden="true"></i> {{ number_format($credit['rating'], 1) }}</small>@endif
                    </div>
                    <i class="bi bi-chevron-right actor-credit-arrow" aria-hidden="true"></i>
                </a>
            @endforeach
        </div>
    @else
        <p class="actor-empty">No {{ strtolower($heading) }} credits are listed on TMDB yet.</p>
    @endif
</section>
