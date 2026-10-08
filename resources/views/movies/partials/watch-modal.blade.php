<link rel="stylesheet" href="{{ asset('css/movie-player.css') }}?v={{ filemtime(public_path('css/movie-player.css')) }}">
<script src="{{ asset('js/movie-player.js') }}?v={{ filemtime(public_path('js/movie-player.js')) }}" defer></script>

<div class="modal fade movie-watch-modal" id="movieWatchModal" tabindex="-1" aria-labelledby="movieWatchTitle" aria-describedby="movieWatchHelp" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content" data-movie-watch-container>
            <div class="modal-header">
                <span class="movie-watch-icon"><i class="bi bi-play-fill" aria-hidden="true"></i></span>
                <div class="movie-watch-heading">
                    <span class="movie-watch-eyebrow">Now watching</span>
                    <h2 class="modal-title" id="movieWatchTitle">{{ $movie->name }}</h2>
                </div>
                <button type="button" class="movie-watch-fullscreen" data-movie-watch-fullscreen aria-label="Enter fullscreen" aria-pressed="false" title="Enter fullscreen">
                    <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                </button>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close movie player"></button>
            </div>
            <div class="modal-body">
                <iframe data-movie-watch-frame title="Watch {{ $movie->name }}" src="about:blank"
                        allow="autoplay; fullscreen *; picture-in-picture; encrypted-media" allowfullscreen webkitallowfullscreen></iframe>
            </div>
            <div class="modal-footer">
                <span id="movieWatchHelp"><i class="bi bi-badge-cc" aria-hidden="true"></i> Choose subtitles in the player’s settings.</span>
                <a href="https://v2.vidsrc.me/embed/{{ $movie->imdb_id }}" target="_blank" rel="noopener noreferrer">
                    Open player <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>
