<div style="display:block; height:50px"></div>

<div class="series-header-container">
    <div class="series-header-card mt-3">
        <div class="series-header-content">
            <div class="row">
                <!-- Poster Column -->
                <div class="col-12 col-sm-4 col-md-3 col-lg-2 poster-column">
                    <div class="poster-wrapper">
                        <img src="{{$torrent->poster}}" lazy="loading" class="series-poster" alt="{{$tmdbData['name'] ?? 'TV Series Poster'}}" loading="lazy">
                        
                        <!-- Action Buttons -->
                        <div class="poster-actions">
                            @if($torrent->tmdbid)
                                <a href="https://www.themoviedb.org/tv/{{ $torrent->tmdbid }}" 
                                   target="_blank" 
                                   class="action-btn tmdb-btn"
                                   data-bs-toggle="tooltip" title="View on TMDB">
                                    <i class="bi bi-star-fill"></i>
                                </a>
                            @endif
                            
                            @if($torrent->imdbid)
                                <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}" 
                                   target="_blank" 
                                   class="action-btn imdb-btn"
                                   data-bs-toggle="tooltip" title="View on IMDb">
                                    <i class="bi bi-film"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Trailer Section -->
                        <div class="trailer-section">
                            @if(!empty($torrent->trailer))
                                @php
                                    $dbTrailerUrl = htmlspecialchars($torrent->trailer);
                                    $embedUrl = str_replace("watch?v=", "embed/", $dbTrailerUrl);
                                @endphp
                                <a href="{{ $embedUrl }}?version=3&amp;autohide=3&amp;hl=ro_RO&amp;showinfo=0&amp;autoplay=1&amp;disablekb=0&amp;hd=1&amp;theme=dark" 
                                   data-lity
                                   class="trailer-link">
                                    <i class="bi bi-play-circle-fill"></i> Play Trailer
                                </a>
                            @elseif(isset($tmdbData['videos']['results']) && count($tmdbData['videos']['results']) > 0)
                                @php
                                    $tmdbTrailer = collect($tmdbData['videos']['results'])->firstWhere('type', 'Trailer');
                                @endphp
                                @if($tmdbTrailer)
                                    <a href="https://www.youtube.com/embed/{{ $tmdbTrailer['key'] }}?version=3&amp;autohide=3&amp;hl=ro_RO&amp;showinfo=0&amp;autoplay=1&amp;disablekb=0&amp;hd=1&amp;theme=dark" 
                                       data-lity
                                       class="trailer-link">
                                        <i class="bi bi-play-circle-fill"></i> Play Trailer
                                    </a>
                                @else
                                    <p class="no-trailer">No trailer available.</p>
                                @endif
                            @else
                                <p class="no-trailer">No trailer available.</p>
                            @endif
                        </div>
                </div>
                
                <!-- Info Column -->
                <div class="col-12 col-sm-8 col-md-9 col-lg-10 info-column">
                    <div class="series-info">
                        <!-- Title Section -->
                        <div class="title-section">
                            <h1 class="series-title">
                                {{ $tmdbData['name'] ?? '<span class="text-muted">Title not available</span>' }}
                                @if(isset($tmdbData['first_air_date']))
                                    <span class="release-year">({{ \Carbon\Carbon::parse($tmdbData['first_air_date'])->format('Y') }})</span>
                                @endif
                                @if(isset($tmdbData['tagline']))
                                <p class="tagline">{{ $tmdbData['tagline'] }}</p>
                            @endif
                            </h1>
                            
                            @if(isset($omdbData['Rated']) && $omdbData['Rated'] != 'N/A')
                                <div class="content-rating">
                                    {!! getTVRatingBadge($omdbData['Rated']) !!}
                                    @if(isset($tmdbData['episode_run_time']))
                                        <span class="runtime">
                                            <i class="bi bi-clock"></i> 
                                            {{ $tmdbData['episode_run_time'][0] ?? 'N/A' }}m/episode
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Genres -->
                        <div class="genres-section">
                            @foreach($torrent->genres as $genre)
                                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" 
                                   class="genre-tag"
                                   data-bs-toggle="tooltip" title="Browse {{ $genre->name }} series">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                        
                        <!-- Series Info -->
                        <div class="series-meta">
                            @if(isset($tmdbData['number_of_seasons']))
                                <div class="meta-badge seasons">
                                    <i class="bi bi-collection-play"></i>
                                    {{ $tmdbData['number_of_seasons'] }} Season{{ $tmdbData['number_of_seasons'] > 1 ? 's' : '' }}
                                </div>
                            @endif
                            
                            @if(isset($tmdbData['number_of_episodes']))
                                <div class="meta-badge episodes">
                                    <i class="bi bi-tv"></i>
                                    {{ $tmdbData['number_of_episodes'] }} Episode{{ $tmdbData['number_of_episodes'] > 1 ? 's' : '' }}
                                </div>
                            @endif
                            
                            @if(isset($tmdbData['status']))
                                <div class="meta-badge status">
                                    <i class="bi bi-info-circle"></i>
                                    {{ $tmdbData['status'] }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Ratings -->
                        <div class="ratings-section">
                            @if(isset($tmdbData['vote_average']))
                                <div class="rating-badge tmdb-rating" data-bs-toggle="tooltip" title="TMDB Rating">
                                    <div class="rating-value">{{ number_format($tmdbData['vote_average'], 1) }}</div>
                                    <div class="rating-source">TMDB</div>
                                </div>
                            @endif
                            
                            @if(isset($omdbData['imdbRating']))
                                <div class="rating-badge imdb-rating" data-bs-toggle="tooltip" title="IMDb Rating">
                                    <div class="rating-value">{{ $omdbData['imdbRating'] }}</div>
                                    <div class="rating-source">IMDb</div>
                                </div>
                            @endif
                            
                            @if(isset($omdbData['Ratings']))
                                @foreach($omdbData['Ratings'] as $rating)
                                    @if($rating['Source'] == 'Rotten Tomatoes')
                                        <div class="rating-badge rt-rating" data-bs-toggle="tooltip" title="Rotten Tomatoes">
                                            <div class="rating-value">{{ $rating['Value'] }}</div>
                                            <div class="rating-source">RT</div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        
                        <!-- Overview -->
                        <div class="overview-section">
                            <h5>{{ $tmdbData['overview'] ?? 'No overview available.' }}</h5>
                        </div>
                        
                        <!-- Network/Production Info -->
                        @if(isset($tmdbData['networks']) && count($tmdbData['networks']) > 0)
                            <div class="network-section">
                                <h3 class="section-heading"><i class="bi bi-broadcast"></i> Network</h3>
                                <div class="networks">
                                    @foreach($tmdbData['networks'] as $network)
                                        @if(isset($network['logo_path']))
                                            <img src="https://www.themoviedb.org/t/p/w154{{ $network['logo_path'] }}" 
                                                 alt="{{ $network['name'] }}" 
                                                 class="network-logo"
                                                 data-bs-toggle="tooltip" 
                                                 title="{{ $network['name'] }}">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Quick Facts -->
                        <div class="quick-facts">
                            @if(isset($tmdbData['original_language']))
                                <div class="fact-item">
                                    <span class="fact-label">Language:</span>
                                    <span class="fact-value">{{ getLanguageName($tmdbData['original_language']) }}</span>
                                </div>
                            @endif
                            
                            @if(isset($omdbData['imdbVotes']))
                                <div class="fact-item">
                                    <span class="fact-label">IMDb Votes:</span>
                                    <span class="fact-value">{{ $omdbData['imdbVotes'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Seasons Section -->
{{-- @if(isset($tmdbData['seasons']) && count($tmdbData['seasons']) > 0)
<div class="seasons-section">
    <h2 class="section-title"><i class="bi bi-collection"></i> Seasons</h2>
    <div class="seasons-grid">
        @foreach($tmdbData['seasons'] as $season)
            @if($season['season_number'] > 0) <!-- Skip special seasons -->
                <div class="season-card">
                    <div class="season-poster">
                        @if(isset($season['poster_path']))
                            <img src="https://www.themoviedb.org/t/p/w300_and_h450_bestv2{{ $season['poster_path'] }}" 
                                 alt="Season {{ $season['season_number'] }}" 
                                 loading="lazy">
                        @else
                            <div class="no-poster">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="season-info">
                        <h4>Season {{ $season['season_number'] }}</h4>
                        @if(isset($season['air_date']))
                            <p class="air-date">{{ \Carbon\Carbon::parse($season['air_date'])->format('Y') }}</p>
                        @endif
                        @if(isset($season['episode_count']))
                            <p class="episode-count">{{ $season['episode_count'] }} episodes</p>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endif --}}

<div class="col-12 d-xxl-none">
                                         <h4>Cast</h4>
                                    </div>

<div class="cast-row">
    @php $i = 0; @endphp
    @foreach ($tmdbData['credits']['cast'] as $castMember)
        @if ($i++ > 5)
            @break
        @endif

        @php
            $castId = $castMember['id'] ?? 0;
            $castName = $castMember['name'] ?? 'Unknown Actor';
            $castPlayed = $castMember['character'] ?? 'Unknown Role';
            $castPlayed = Str::limit($castPlayed, 30); // Limit to 30 chars
            $castImage = $castMember['profile_path']
                ? "<img class='actor-image' lazy='loading' src='https://www.themoviedb.org/t/p/w185/{$castMember['profile_path']}'>"
                : "<img src='/images/not-found.jpg' class='actor-image'>";
        @endphp

        <div class="select text-center">
            {!! $castImage !!}
            <div class="mt-2">
                <h6>
                    @if($castId)
                        <a href="https://www.themoviedb.org/person/{{ $castId }}" rel="noreferrer" target="_blank">
                            <b>{{ $castName }}</b>
                            <div class="small text-muted">{{ $castPlayed }}</div>
                        </a>
                    @else
                        <b>{{ $castName }}</b>
                        <div class="small text-muted">{{ $castPlayed }}</div>
                    @endif
                </h5>
            </div>
        </div>

    @endforeach
</div>


<style>
    /* Style adjustments for background display */
    .content-overlay {
        background: none; /* Remove card background */
        padding: 20px;
    }

    body::before {
        content: '';
        position: fixed;
        /* position: absolute; */
        top: 55px;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('{{$torrent->background}}');
        background-position-x: center;
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0.7;
    }

    .videoWrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 */
        height: 0;
    }

    .videoWrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

   .actor-image {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 50%;
}

.select {
    gap: 3px 20px;
    border-radius: 16px;
    padding: 6px 26px 6px 6px;
    overflow: hidden;
    flex: 0 0 auto; /* prevent shrinking in scroll */
}

.select:hover {
    backdrop-filter: brightness(130%) blur(10px);
}

/* Wrapper for horizontal scroll on md and below */
.cast-row {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 50px;
    padding: 10px 0;
    -webkit-overflow-scrolling: touch; /* smooth scroll on mobile */
}

/* Use Bootstrap grid layout on large screens */
@media (min-width: 992px) {
    .cast-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        overflow-x: visible;
    }
    .select {
        width: auto; /* Let col-* classes control width */
    }
}

/* Optional: scrollbar styling */
.cast-row::-webkit-scrollbar {
    height: 6px;
}
.cast-row::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.3);
    border-radius: 3px;
}

     /* Series Header Container */
    .series-header-container {
        position: relative;
        margin-bottom: 30px;
    }
    
    .series-header-card {
        /* background: rgba(20, 20, 30, 0.85); */
        backdrop-filter: blur(1px);
        border-radius: 16px;
        overflow: visible;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .series-header-content {
        padding: 25px;
    }
    
    /* Poster Column */
    .poster-column {
        margin-bottom: 20px;
    }
    
    .poster-wrapper {
        position: relative;
        margin-top: -75px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
        transition: transform 0.3s ease;
    }
    
    .series-poster {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }
    
    .poster-wrapper:hover {
        transform: translateY(-5px);
    }
    
    .poster-wrapper:hover .series-poster {
        transform: scale(1.03);
    }
    
    .poster-actions {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        padding: 15px;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .poster-wrapper:hover .poster-actions {
        opacity: 1;
    }
    
    .action-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 5px;
        color: white;
        font-size: 1.2rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .tmdb-btn {
        background: linear-gradient(135deg, #01b4e4 0%, #1a73e8 100%);
    }
    
    .imdb-btn {
        background: linear-gradient(135deg, #f5c518 0%, #e2b616 100%);
        color: #000;
    }
    
    .action-btn:hover {
        transform: scale(1.1) translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    /* Info Column */
    .info-column {
        color: #fff;
    }
    
    /* Title Section */
    .title-section {
        margin-bottom: 15px;
    }
    
    .series-title {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 5px;
        color: #fff;
        line-height: 1.2;
    }
    
    .release-year {
        font-size: 1.5rem;
        color: #aaa;
        font-weight: 400;
    }
    
    .content-rating {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
    }
    
    .tagline {
        font-size: 1.1rem;
        color: #ddd;
        font-style: italic;
        margin-bottom: 0;
    }
    
    /* Genres */
    .genres-section {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .genre-tag {
        display: inline-block;
        background: rgba(110, 72, 170, 0.3);
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(110, 72, 170, 0.5);
    }
    
    .genre-tag:hover {
        background: rgba(110, 72, 170, 0.5);
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    /* Series Meta */
    .series-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.1);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
    .seasons {
        background: rgba(30, 170, 120, 0.2);
        border: 1px solid rgba(30, 170, 120, 0.5);
    }
    
    .episodes {
        background: rgba(100, 120, 200, 0.2);
        border: 1px solid rgba(100, 120, 200, 0.5);
    }
    
    .status {
        background: rgba(200, 100, 100, 0.2);
        border: 1px solid rgba(200, 100, 100, 0.5);
    }
    
    /* Ratings */
    .ratings-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
}

/* Color-coded by content level */
.tv-y-rating { background: #4CAF50; }        /* Green – all ages */
.tv-y7-rating { background: #7BC043; }       /* Lime – kids 7+ */
.tv-g-rating { background: #2196F3; }        /* Blue – general audience */
.tv-pg-rating { background: #FFC107; color: #000; } /* Yellow – parental guidance */
.tv-14-rating { background: #FF9800; }       /* Orange – teens */
.tv-ma-rating { background: #F44336; }       /* Red – mature */

    
    .tmdb-rating {
        background: linear-gradient(135deg, #01b4e4 0%, #1a73e8 100%);
    }
    
    .imdb-rating {
        background: linear-gradient(135deg, #f5c518 0%, #e2b616 100%);
        color: #000;
    }
    
    .rt-rating {
        background: linear-gradient(135deg, #fa320a 0%, #e00909 100%);
    }
    
    .rating-value {
        font-size: 1.3rem;
        line-height: 1;
    }
    
    .rating-source {
        font-size: 0.7rem;
        opacity: 0.9;
    }
    
    /* Overview */
    .overview-section {
        margin-bottom: 20px;
    }
    
    .section-heading {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: #fff;
    }
    
    .overview-section p {
        font-size: 1rem;
        line-height: 1.6;
        color: #ddd;
    }
    
    /* Network Section */
    .network-section {
        margin-bottom: 15px;
    }
    
    .networks {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .network-logo {
        height: 30px;
        width: auto;
        filter: brightness(0) invert(1);
        opacity: 0.8;
        transition: all 0.3s ease;
    }
    
    .network-logo:hover {
        opacity: 1;
        transform: scale(1.1);
    }
    
    /* Quick Facts */
    .quick-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .fact-item {
        display: flex;
        align-items: center;
    }
    
    .fact-label {
        font-weight: 600;
        color: #aaa;
        margin-right: 5px;
    }
    
    .fact-value {
        color: #fff;
    }
    
    /* Trailer Section */
    .trailer-section {
    margin-top: 15px;
    text-align: center; 
}
    
    .trailer-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 600;
        padding: 8px 16px;
        background: rgba(80, 77, 77, 0.7);
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .trailer-link:hover {
        background: rgba(48, 46, 46, 0.9);
        text-decoration: none;
        transform: translateY(-2px);
    }
    
    .trailer-link i {
        font-size: 1.2rem;
    }
    
    .no-trailer {
        color: #aaa;
        font-style: italic;
    }
    
    /* Seasons Section */
    .seasons-section {
        background: rgba(20, 20, 30, 0.85);
        backdrop-filter: blur(12px);
        border-radius: 16px;
        padding: 25px;
        margin-top: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
    
    .seasons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 25px;
    }
    
    .season-card {
        background: rgba(30, 30, 40, 0.7);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .season-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }
    
    .season-poster {
        position: relative;
        width: 100%;
        padding-top: 150%;
        overflow: hidden;
    }
    
    .season-poster img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .no-poster {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(50, 50, 70, 0.5);
        color: #aaa;
        font-size: 3rem;
    }
    
    .season-info {
        padding: 15px;
        text-align: center;
    }
    
    .season-info h4 {
        font-size: 1rem;
        margin-bottom: 5px;
        color: #fff;
    }
    
    .air-date, .episode-count {
        font-size: 0.85rem;
        color: #aaa;
        margin-bottom: 0;
    }

     /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .seasons-grid,
        .cast-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 20px;
        }
    }
    
    @media (max-width: 992px) {
        .series-title {
            font-size: 1.8rem;
        }
        
        .release-year {
            font-size: 1.2rem;
        }
        
        .seasons-grid,
        .cast-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 18px;
        }
    }
    
    @media (max-width: 768px) {
        .series-header-content {
            padding: 15px;
        }
        
        .series-title {
            font-size: 1.6rem;
        }
        
        .rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
}

/* Color-coded by content level */
.tv-y-rating { background: #4CAF50; }        /* Green – all ages */
.tv-y7-rating { background: #7BC043; }       /* Lime – kids 7+ */
.tv-g-rating { background: #2196F3; }        /* Blue – general audience */
.tv-pg-rating { background: #FFC107; color: #000; } /* Yellow – parental guidance */
.tv-14-rating { background: #FF9800; }       /* Orange – teens */
.tv-ma-rating { background: #F44336; }       /* Red – mature */

        
        .rating-value {
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 1.6rem;
        }
    }
    
    @media (max-width: 576px) {
        .series-title {
            font-size: 1.4rem;
        }
        
        .poster-actions {
            opacity: 1;
            padding: 10px;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
        
        .seasons-grid,
        .cast-grid {
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
        }
        
        .seasons-section,
        .cast-section {
            padding: 20px;
        }
    }


    
</style>

@php

function getTVRatingBadge($rating) {
    switch (strtoupper($rating)) {
        case 'TV-Y':
            return "<span class='rating-badge tv-y-rating' data-bs-toggle='tooltip' 
                title='TV-Y — All Children. Suitable for all ages, including young children. Contains no violence, language, or sexual content.'>
                <i class='bi bi-balloon-heart'></i> TV-Y
            </span>";

        case 'TV-Y7':
            return "<span class='rating-badge tv-y7-rating' data-bs-toggle='tooltip' 
                title='TV-Y7 — Older Children. Recommended for ages 7 and up. May contain mild fantasy violence or comedic action.'>
                <i class='bi bi-emoji-sunglasses'></i> TV-Y7
            </span>";

        case 'TV-G':
            return "<span class='rating-badge tv-g-rating' data-bs-toggle='tooltip' 
                title='TV-G — General Audience. Suitable for all ages. Contains little or no violence, no strong language, and minimal sexual content.'>
                <i class='bi bi-people'></i> TV-G
            </span>";

        case 'TV-PG':
            return "<span class='rating-badge tv-pg-rating' data-bs-toggle='tooltip' 
                title='TV-PG — Parental Guidance Suggested. May contain some mild violence, suggestive themes, or infrequent coarse language.'>
                <i class='bi bi-exclamation-circle'></i> TV-PG
            </span>";

        case 'TV-14':
            return "<span class='rating-badge tv-14-rating' data-bs-toggle='tooltip' 
                title='TV-14 — Parents Strongly Cautioned. May be unsuitable for children under 14 due to stronger language, violence, or sexual content.'>
                <i class='bi bi-shield-exclamation'></i> TV-14
            </span>";

        case 'TV-MA':
            return "<span class='rating-badge tv-ma-rating' data-bs-toggle='tooltip' 
                title='TV-MA — Mature Audience Only. Intended for adults and may contain explicit sexual content, strong language, or graphic violence.'>
                <i class='bi bi-explicit'></i> TV-MA
            </span>";

        default:
            return "<span class='rating-badge' data-bs-toggle='tooltip' title='Unrated or Unknown Rating'>
                <i class='bi bi-question-circle'></i> $rating
            </span>";
    }
}


function getLanguageName($code) {
    $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'ru' => 'Russian',
        'hi' => 'Hindi'
    ];
    
    return $languages[$code] ?? strtoupper($code);
}
@endphp
