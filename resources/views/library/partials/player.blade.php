@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/movie-player.css') }}?v={{ filemtime(public_path('css/movie-player.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('js/library-player.js') }}?v={{ filemtime(public_path('js/library-player.js')) }}" defer></script>
@endpush
<div class="modal fade movie-watch-modal" id="libraryPlayer" tabindex="-1" aria-labelledby="libraryPlayerTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="libraryPlayerTitle">Watch online</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close player"></button>
            </div>
            <div class="modal-body">
                <iframe title="Online player" src="about:blank" data-library-frame allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen></iframe>
            </div>
            <div class="modal-footer">
                <span>Choose subtitles in the player’s settings.</span>
                <a data-library-external href="#" target="_blank" rel="noopener noreferrer">Open player</a>
            </div>
        </div>
    </div>
</div>
@endonce
