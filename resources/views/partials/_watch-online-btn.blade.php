{{-- =========================================================
    "Watch online" button — only rendered when a $watchUrl is provided.
    Used on the torrent detail page and both library show pages when
    the title exists in the movies / series (online) catalogue.
========================================================= --}}
@if(!empty($watchUrl ?? null))
    <a href="{{ $watchUrl }}"
       class="btn watch-online-btn"
       title="Watch this title in the online catalogue">
        <i class="bi bi-play-circle-fill me-1"></i> Available to watch online
    </a>
@endif