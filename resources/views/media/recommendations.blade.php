@if(isset($similar) && $similar->isNotEmpty())
<section class="md-recommendations" aria-labelledby="media-recommendations-title">
    <header class="md-section-heading">
        <div><span class="md-eyebrow">Discover more</span><h2 id="media-recommendations-title">You may also like</h2></div>
        <span class="md-section-note">Similar genres · Rated by IMDb · In your library</span>
    </header>
    <div class="md-recommendation-grid">
        @foreach($similar as $recommendation)
            @php
                $available = !empty($recommendation['in_library']) && !empty($recommendation['db_url']);
                $link = $available ? $recommendation['db_url'] : (!empty($recommendation['tmdb_id']) ? 'https://www.themoviedb.org/'.($mediaType === 'movie' ? 'movie' : 'tv').'/'.$recommendation['tmdb_id'] : null);
                $name = $recommendation['name'] ?? 'Untitled';
            @endphp
            <article class="md-recommendation {{ $available ? 'is-available' : 'is-missing' }}">
                @if($link)<a class="md-recommendation-link" href="{{ $link }}" @if(!$available) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $available ? 'View '.$name : $name.' on TMDB (opens a new tab)' }}">@else<div class="md-recommendation-link">@endif
                    <div class="md-recommendation-poster">
                        <img class="md-image" src="{{ ($recommendation['poster'] ?? null) ?: asset('images/not-found.jpg') }}" data-fallback="{{ asset('images/not-found.jpg') }}" alt="" loading="lazy" decoding="async" width="240" height="360">
                        @if((float) ($recommendation['rating'] ?? 0) > 0)<span class="md-poster-rating" title="IMDb {{ $recommendation['rating'] }}/10 · {{ number_format((int) ($recommendation['votes'] ?? 0)) }} votes"><i class="bi bi-star-fill" aria-hidden="true"></i>{{ $recommendation['rating'] }}</span>@endif
                    </div>
                    <div class="md-recommendation-info">
                        <h3>{{ $name }}</h3>
                        <div class="md-recommendation-meta"><span>{{ ($recommendation['year'] ?? null) ?: 'Release date unknown' }}</span><span class="md-availability {{ $available ? 'md-available' : '' }}">{{ $available ? 'In library' : 'Not in library' }}</span></div>
                    </div>
                @if($link)</a>@else</div>@endif
            </article>
        @endforeach
    </div>
</section>
@endif
