<div style="display:block; height:50px"></div>

<div class="movie-header-container">
    <div class="movie-header-card">
        <div class="movie-header-content">
            {{-- <!-- Media Type Badge -->
            <div class="media-type-badge">
                <span class="badge-icon"><i class="bi bi-film"></i></span>
                <span class="badge-text">MOVIE</span>
            </div>
             --}}
            <div class="row">
                <!-- Poster Column -->
                <div class="col-12 col-sm-4 col-md-3 col-lg-2 poster-column">
                    <div class="poster-wrapper">
                        <img src="{{$torrent->poster}}" class="movie-poster" alt="{{$tmdbData['title'] ?? 'Movie Poster'}}">
                        
                        <!-- Action Buttons -->
                        <div class="poster-actions">
                            @if($torrent->tmdbid)
                                <a href="https://www.themoviedb.org/movie/{{ $torrent->tmdbid }}" 
                                   target="_blank" 
                                   class="action-btn tmdb-btn"
                                   data-tooltip="View on TMDB">
                                    <i class="bi bi-star-fill"></i>
                                </a>
                            @endif
                            
                            @if($torrent->imdbid)
                                <a href="https://www.imdb.com/title/{{ $torrent->imdbid }}" 
                                   target="_blank" 
                                   class="action-btn imdb-btn"
                                   data-tooltip="View on IMDb">
                                    <i class="bi bi-film"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Info Column -->
                <div class="col-12 col-sm-8 col-md-9 col-lg-10 info-column">
                    <div class="movie-info">
                        <!-- Title Section -->
                        <div class="title-section">
                            <h1 class="movie-title">
                                {{ $tmdbData['title'] ?? '<span class="text-muted">Title not available</span>' }}
                                @if(isset($tmdbData['release_date']))
                                    <span class="release-year">({{ \Carbon\Carbon::parse($tmdbData['release_date'])->format('Y') }})</span>
                                @endif
                            </h1>
                            
                            @if(isset($omdbData['Rated']) && $omdbData['Rated'] != 'N/A')
                                <div class="content-rating">
                                    {!! getRatingBadge($omdbData['Rated']) !!}
                                    @if(isset($tmdbData['runtime']))
                                        <span class="runtime">
                                            <i class="bi bi-clock"></i> 
                                            {{ intdiv($tmdbData['runtime'], 60) }}h {{ $tmdbData['runtime'] % 60 }}m
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            @if(isset($tmdbData['tagline']))
                                <p class="tagline">{{ $tmdbData['tagline'] }}</p>
                            @endif
                        </div>
                        
                        <!-- Genres -->
                        <div class="genres-section">
                            @foreach($torrent->genres as $genre)
                                <a href="{{ route('torrents.index', ['genre' => $genre->id]) }}" 
                                   class="genre-tag"
                                   data-tooltip="Browse {{ $genre->name }} movies">
                                    {{ $genre->name }}
                                </a>
                            @endforeach
                        </div>
                        
                        <!-- Ratings -->
                        <div class="ratings-section">
                            @if(isset($tmdbData['vote_average']))
                                <div class="rating-badge tmdb-rating">
                                    <div class="rating-value">{{ number_format($tmdbData['vote_average'], 1) }}</div>
                                    <div class="rating-source">TMDB</div>
                                </div>
                            @endif
                            
                            @if(isset($omdbData['imdbRating']))
                                <div class="rating-badge imdb-rating">
                                    <div class="rating-value">{{ $omdbData['imdbRating'] }}</div>
                                    <div class="rating-source">IMDb</div>
                                </div>
                            @endif
                            
                            @if(isset($omdbData['Ratings']))
                                @foreach($omdbData['Ratings'] as $rating)
                                    @if($rating['Source'] == 'Rotten Tomatoes')
                                        <div class="rating-badge rt-rating" data-bs-toggle="tooltip" title="Rotten Tomatoes">
                                            <div class="rating-value">{{ $rating['Value'] }}</div>
                                            
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        
                        <!-- Overview -->
                        <div class="overview-section">
                            <h3 class="section-heading"><i class="bi bi-info-circle"></i> Overview</h3>
                            <h5>{{ $tmdbData['overview'] ?? 'No overview available.' }}</h5>
                        </div>
                        
                        <!-- Quick Facts -->
                        <div class="quick-facts">
                            {{-- @if(isset($tmdbData['status']))
                                <div class="fact-item">
                                    <span class="fact-label">Status:</span>
                                    <span class="fact-value">{{ $tmdbData['status'] }}</span>
                                </div>
                            @endif --}}
                            
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
                        
                        <!-- Original Trailer Section -->
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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Movie Header Container */
    .movie-header-container {
        position: relative;
        margin-bottom: 30px;
    }
    
    .movie-header-card {
       
        backdrop-filter: blur(1px);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .movie-header-content {
        padding: 25px;
    }
    
    /* Media Type Badge */
    .media-type-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #6e48aa 0%, #9d50bb 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    
    .badge-icon {
        margin-right: 8px;
        font-size: 0.9rem;
    }
    
    /* Poster Column */
    .poster-column {
        margin-bottom: 20px;
    }
    
    .poster-wrapper {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
        transition: transform 0.3s ease;
    }
    
    .movie-poster {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }
    
    .poster-wrapper:hover {
        transform: translateY(-5px);
    }
    
    .poster-wrapper:hover .movie-poster {
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
    
    .movie-title {
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
    
    .rating-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
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
    
    /* Ratings */
    .ratings-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .rating-badge {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
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
    }
    
    .trailer-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 600;
        padding: 8px 16px;
        background: rgba(255, 0, 0, 0.7);
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .trailer-link:hover {
        background: rgba(255, 0, 0, 0.9);
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
    
    /* Tooltip */
    [data-tooltip] {
        position: relative;
    }
    
    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    [data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
        bottom: calc(100% + 5px);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .movie-title {
            font-size: 1.8rem;
        }
        
        .release-year {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 768px) {
        .movie-header-content {
            padding: 15px;
        }
        
        .movie-title {
            font-size: 1.6rem;
        }
        
        .rating-badge {
            width: 50px;
            height: 50px;
        }
        
        .rating-value {
            font-size: 1.1rem;
        }
    }
    
    @media (max-width: 576px) {
        .movie-title {
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
    }
</style>

<style>
    /* Movie Header Container */
    .movie-header-container {
        position: relative;
        margin-bottom: 30px;
    }
    
    .movie-header-card {
       
        backdrop-filter: blur(1px);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .movie-header-content {
        padding: 25px;
    }
    
    /* Media Type Badge */
    .media-type-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #6e48aa 0%, #9d50bb 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    
    .badge-icon {
        margin-right: 8px;
        font-size: 0.9rem;
    }
    
    /* Poster Column */
    .poster-column {
        margin-bottom: 20px;
    }
    
    .poster-wrapper {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
        transition: transform 0.3s ease;
    }
    
    .movie-poster {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }
    
    .poster-wrapper:hover {
        transform: translateY(-5px);
    }
    
    .poster-wrapper:hover .movie-poster {
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
    
    .trailer-btn {
        background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
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
    
    .movie-title {
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
    
    .rating-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
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
    
    /* Ratings */
    .ratings-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .rating-badge {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
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
    
    /* Quick Facts */
    .quick-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
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
    
    /* Tooltip */
    [data-tooltip] {
        position: relative;
    }
    
    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    [data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
        bottom: calc(100% + 5px);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .movie-title {
            font-size: 1.8rem;
        }
        
        .release-year {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 768px) {
        .movie-header-content {
            padding: 15px;
        }
        
        .movie-title {
            font-size: 1.6rem;
        }
        
        .rating-badge {
            width: 50px;
            height: 50px;
        }
        
        .rating-value {
            font-size: 1.1rem;
        }
    }
    
    @media (max-width: 576px) {
        .movie-title {
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
    }
</style>

@php
function getRatingBadge($rating) {
    switch ($rating) {
        case 'G':
            return "<span class='rating-badge g-rating' data-bs-toggle='tooltip' title='General Audiences: All ages admitted'><i class='bi bi-emoji-smile'></i> G</span>";
        case 'PG':
            return "<span class='rating-badge pg-rating' data-bs-toggle='tooltip' title='Parental Guidance Suggested: Some material may not be suitable for children'><i class='bi bi-emoji-neutral'></i> PG</span>";
        case 'PG-13':
            return "<span class='rating-badge pg13-rating' data-bs-toggle='tooltip' title='Parents Strongly Cautioned: Some material may be inappropriate for children under 13'><i class='bi bi-emoji-frown'></i> PG-13</span>";
        case 'R':
            return "<span class='rating-badge r-rating' data-bs-toggle='tooltip' title='Restricted: Under 17 requires adult guardian'><i class='bi bi-emoji-dizzy'></i> R</span>";
        case 'NC-17':
            return "<span class='rating-badge nc17-rating' data-bs-toggle='tooltip' title='Adults Only: No one 17 and under admitted'><i class='bi bi-emoji-angry'></i> NC-17</span>";
        default:
            return "<span class='rating-badge'>$rating</span>";
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


<div class="col-12 d-xxl-none">
                                         <h4>Cast</h4>
                                    </div>

<div class="row justify-content-center">
@php
        $i = 0; // Initialize counter for cast members
    @endphp

@php $i = 1; @endphp
@foreach (($tmdbData['credits']['cast'] ?? []) as $castMember)
    @if ($i > 6)
        @break
    @endif

    @php
        // Safely extract cast member data with fallbacks
        $castId = $castMember['id'] ?? 0;
        $castName = $castMember['name'] ?? 'Unknown Actor';
        $castPlayed = $castMember['character'] ?? 'Unknown Role';
        
        $castImage = isset($castMember['profile_path']) 
            ? "<img class='actor-image' src='https://www.themoviedb.org/t/p/w300_and_h450_bestv2{$castMember['profile_path']}'>"
            : "<img src='/images/not-found.jpg' class='actor-image'>";
    @endphp

    <div class="select col-12 col-sm-6 col-md-3 col-lg-2 text-center">
        {!! $castImage !!}
        <div class="mt-2">
            <h5>
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

    @php $i++; @endphp
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
background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 1)), url('{{$torrent->background}}');
background-position-x: center top;
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
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
    }

    .select {
        gap: 3px 20px;
        border-radius: 16px;
        padding: 6px 26px 6px 6px;
        overflow: hidden;
    }

    .select:hover {
        backdrop-filter: brightness(130%) blur(10px);
    }
</style>
