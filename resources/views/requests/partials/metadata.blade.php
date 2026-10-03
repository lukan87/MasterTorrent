@if($media = $metadata['media'] ?? null)
    <section class="rq-feature rq-panel mb-4" aria-label="Movie or series information">
        @if($media['backdrop'])<img class="rq-backdrop" src="{{ $media['backdrop'] }}" alt="" loading="lazy" referrerpolicy="no-referrer">@endif
        <div class="rq-feature-content">
            @if($media['poster'])<img class="rq-media-poster" src="{{ $media['poster'] }}" alt="{{ $media['title'] }} poster" loading="lazy" referrerpolicy="no-referrer">@endif
            <div class="rq-media-copy">
                <div class="rq-kicker">{{ $media['type'] }} · TMDB</div>
                <h2>{{ $media['title'] }}</h2>
                @if($media['tagline'])<p class="rq-tagline">{{ $media['tagline'] }}</p>@endif
                <div class="rq-facts">
                    @if($media['rating'] !== null)<span><i class="bi bi-star-fill" aria-hidden="true"></i> {{ $media['rating'] }}/10 TMDB</span>@endif
                    @if($media['imdb_rating'] && $media['imdb_rating'] !== 'N/A')<span>{{ $media['imdb_rating'] }}/10 IMDb</span>@endif
                    @if($media['release'])<span>{{ $media['release'] }}</span>@endif
                    @if($media['runtime'])<span>{{ $media['runtime'] }} min</span>@endif
                    @if($media['seasons'])<span>{{ $media['seasons'] }} {{ Str::plural('season', $media['seasons']) }}</span>@endif
                </div>
                <div class="rq-genres">@foreach($media['genres'] as $genre)<span>{{ $genre }}</span>@endforeach</div>
                @if($media['overview'])<p class="rq-overview">{{ $media['overview'] }}</p>@endif
                @if($media['cast'])<p class="rq-muted small mb-0"><strong>Starring</strong> {{ implode(' · ', $media['cast']) }}</p>@endif
                <p class="rq-attribution">Metadata provided by TMDB. This product uses the TMDB API but is not endorsed or certified by TMDB.</p>
            </div>
        </div>
    </section>
@endif
@if($game = $metadata['game'] ?? null)
    <section class="rq-panel rq-game mb-4" aria-label="Game information">
        <div class="rq-game-art">
            @if($game['poster'])<img src="{{ $game['poster'] }}" alt="{{ $game['title'] }}" loading="lazy" referrerpolicy="no-referrer">@endif
            <div class="rq-facts mt-3">@foreach($game['platforms'] as $platform)<span><i class="bi bi-controller" aria-hidden="true"></i> {{ ucfirst($platform) }}</span>@endforeach</div>
        </div>
        <div class="rq-media-copy">
            <div class="rq-kicker">Game spotlight · Steam</div><h2>{{ $game['title'] }}</h2>
            <div class="rq-facts">@if($game['release'])<span>{{ $game['coming_soon'] ? 'Coming' : 'Released' }} {{ $game['release'] }}</span>@endif @if($game['score'])<span>{{ $game['score'] }}/100 Metacritic</span>@endif</div>
            <div class="rq-genres">@foreach($game['genres'] as $genre)<span>{{ $genre }}</span>@endforeach</div>
            <p class="rq-overview">{{ $game['overview'] }}</p>
            @if($game['developers'])<p class="rq-muted small mb-1"><strong>Developer</strong> {{ implode(', ', $game['developers']) }}</p>@endif
            @if($game['publishers'])<p class="rq-muted small mb-0"><strong>Publisher</strong> {{ implode(', ', $game['publishers']) }}</p>@endif
        </div>
    </section>
@endif
@if(($request->tmdb_url || $request->steam_url) && empty($metadata['media']) && empty($metadata['game']))
    <p class="rq-muted small mb-4"><i class="bi bi-info-circle" aria-hidden="true"></i> Preview unavailable. You can still use the reference links below.</p>
@endif
