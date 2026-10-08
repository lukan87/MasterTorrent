@php
    $isMovie = $mediaType === 'movie';
    $mediaTitle = $details[$isMovie ? 'title' : 'name'] ?? $media->name;
    $date = $details[$isMovie ? 'release_date' : 'first_air_date'] ?? null;
    $year = $date ? substr($date, 0, 4) : null;
    $poster = $media->poster_path ? 'https://image.tmdb.org/t/p/w500'.$media->poster_path : asset('images/not-found.jpg');
    $cast = array_slice($details['credits']['cast'] ?? [], 0, 12);
    $trailers = collect($details['videos']['results'] ?? [])->filter(fn ($video) => !empty($video['key']) && ($video['site'] ?? 'YouTube') === 'YouTube')->take(3);
    $userClass = optional(auth()->user())->user_class ?? 0;
    $imdbRating = $metadata['imdbRating'] ?? null;
@endphp
<section class="{{ $isMovie ? 'movie-hero-card' : 'hero-card' }} media-detail-hero {{ $media->backdrop_path ? 'md-has-backdrop' : '' }}" aria-labelledby="media-detail-title">
    @if($media->backdrop_path)
        <img class="md-hero-backdrop" src="https://image.tmdb.org/t/p/w1280{{ $media->backdrop_path }}" alt="" aria-hidden="true" decoding="async">
    @endif
    <div class="md-hero-layout {{ !empty($cast) ? 'md-hero-with-cast' : '' }}">
        <div class="md-poster-column">
            <img class="md-poster md-image" src="{{ $poster }}" data-fallback="{{ asset('images/not-found.jpg') }}" width="240" height="360" alt="{{ $mediaTitle }} poster" decoding="async">
            <div class="md-provider-links">
                @if($media->tmdb_id)
                    <a href="https://www.themoviedb.org/{{ $isMovie ? 'movie' : 'tv' }}/{{ $media->tmdb_id }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $mediaTitle }} on TMDB (opens a new tab)">TMDB <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                @endif
                @if($media->imdb_id)
                    <a href="https://www.imdb.com/title/{{ $media->imdb_id }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $mediaTitle }} on IMDb (opens a new tab)">IMDb <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                @endif
            </div>
        </div>
        <div class="md-hero-info">
            <div class="md-eyebrow">{{ $isMovie ? 'Movie' : 'TV series' }} @if($year)<span>· {{ $year }}</span>@endif @if(!empty($metadata['Rated']) && $metadata['Rated'] !== 'N/A')<span>· {{ $metadata['Rated'] }}</span>@endif</div>
            <h1 id="media-detail-title" class="md-title">{{ $mediaTitle }}</h1>
            @if(!empty($details['tagline']))<p class="md-tagline">{{ $details['tagline'] }}</p>@endif
            @if(!empty($details['genres']))
                <div class="md-genres" aria-label="Genres">
                    @foreach(array_slice($details['genres'], 0, 8) as $genre)<span>{{ $genre['name'] }}</span>@endforeach
                </div>
            @endif
            <dl class="md-facts">
                @if($imdbRating && $imdbRating !== 'N/A')<div class="md-fact-rating"><dt><i class="bi bi-star-fill" aria-hidden="true"></i> IMDb</dt><dd>{{ $imdbRating }}<span> / 10</span></dd></div>@endif
                @if(!empty($metadata['imdbVotes']) && $metadata['imdbVotes'] !== 'N/A')<div class="md-fact-votes"><dt>Votes</dt><dd>{{ $metadata['imdbVotes'] }}</dd></div>@endif
                @if($isMovie && !empty($details['runtime']))<div class="md-fact-runtime"><dt>Runtime</dt><dd>{{ intdiv((int) $details['runtime'], 60) }}h {{ (int) $details['runtime'] % 60 }}m</dd></div>@endif
                @if(!$isMovie && isset($details['number_of_seasons']))<div class="md-fact-seasons"><dt>Seasons</dt><dd>{{ $details['number_of_seasons'] }}</dd></div>@endif
                @if(!$isMovie && isset($details['number_of_episodes']))<div class="md-fact-episodes"><dt>Episodes</dt><dd>{{ $details['number_of_episodes'] }}</dd></div>@endif
                @if(($media->views ?? 0) > 0)<div class="md-fact-views"><dt>Views</dt><dd>{{ number_format($media->views) }}</dd></div>@endif
                @if($isMovie && !empty($metadata['Director']) && $metadata['Director'] !== 'N/A')<div class="md-fact-director"><dt>Director</dt><dd>{{ $metadata['Director'] }}</dd></div>@endif
            </dl>
            @if(!empty($details['overview']))<p class="md-overview">{{ $details['overview'] }}</p>@endif
            @if($isMovie && !empty($media->collection_id))
                <a class="md-collection" href="{{ route('collections.show', $media->collection_id) }}"><i class="bi bi-collection" aria-hidden="true"></i> {{ $media->collection_name }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            @endif
            <div class="md-actions">
                @if($isMovie && $userClass >= \App\Models\UserClass::USER && $media->imdb_id)
                    <a href="https://v2.vidsrc.me/embed/{{ $media->imdb_id }}" data-movie-watch aria-haspopup="dialog" aria-controls="movieWatchModal" class="md-button md-button-primary"><i class="bi bi-play-fill" aria-hidden="true"></i>Watch Online</a>
                @elseif(!$isMovie)
                    <a href="#series-seasons" class="md-button md-button-primary"><i class="bi bi-collection-play" aria-hidden="true"></i>Browse episodes</a>
                @endif
                @foreach($trailers as $video)
                    <a href="https://www.youtube.com/watch?v={{ $video['key'] }}" data-lity class="md-button" title="{{ $video['name'] ?? 'Trailer' }}"><i class="bi bi-play-circle" aria-hidden="true"></i>{{ $loop->first ? 'Watch trailer' : 'Trailer '.$loop->iteration }}</a>
                @endforeach
                @if($userClass >= \App\Models\UserClass::ADMIN)
                    <form action="{{ route($isMovie ? 'movies.delete' : 'series.delete', $media->id) }}" method="POST" onsubmit="return confirm('Delete this title permanently? Its comments and related torrents will also be removed.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="md-button md-button-danger"><i class="bi bi-trash3" aria-hidden="true"></i>Delete</button>
                    </form>
                @endif
            </div>
        </div>
        @include('media.hero-cast')
    </div>
</section>
