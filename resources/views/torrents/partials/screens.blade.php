@if ($torrent->images->isNotEmpty())
<section class="torrent-screens mb-3" data-screenshot-gallery aria-label="Torrent screenshots">
    <div class="torrent-screens-header">
        <h5><i class="bi bi-images me-2" aria-hidden="true"></i>Screenshots <span>{{ $torrent->images->count() }}</span></h5>
        <div class="torrent-screens-controls">
            <button type="button" data-screenshot-step="-1" aria-label="Previous screenshots" aria-controls="screenshots-{{ $torrent->id }}" disabled><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
            <button type="button" data-screenshot-step="1" aria-label="Next screenshots" aria-controls="screenshots-{{ $torrent->id }}" disabled><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="torrent-screens-track" id="screenshots-{{ $torrent->id }}" tabindex="0" role="region" aria-label="Screenshot previews">
        @foreach ($torrent->images as $image)
            <button type="button" data-image-src="{{ asset('storage/' . $image->path) }}" class="torrent-screen" data-image-lightbox
               @if($image->fallback) data-image-fallback="{{ asset('storage/' . $image->fallback) }}" @endif
               aria-label="Open screenshot {{ $loop->iteration }} of {{ $loop->count }}">
                <img src="{{ asset('storage/' . $image->path) }}" loading="lazy" decoding="async" alt="Screenshot {{ $loop->iteration }}" width="160" height="90">
                <span class="torrent-screen-number">{{ $loop->iteration }}</span>
                <span class="torrent-screen-expand" aria-hidden="true"><i class="bi bi-arrows-fullscreen"></i></span>
            </button>
        @endforeach
    </div>
</section>
@endif
