@php($preview = $torrent->featured_preview ?? [])
<div class="torrent-hover-preview{{ ($browsePreview ?? false) ? ' torrent-hover-preview--browse' : '' }}">
    <img class="torrent-hover-poster" src="{{ $preview['poster'] ?? asset('images/noposter.jpg') }}" data-poster-fallback="{{ asset('images/noposter.jpg') }}" width="{{ ($browsePreview ?? false) ? 140 : 96 }}" height="{{ ($browsePreview ?? false) ? 210 : 144 }}" alt="Poster" decoding="async">
    <div class="torrent-hover-info">
        @if(!($browsePreview ?? false) || !empty($preview['title']))
            <strong class="torrent-hover-title">{{ $preview['title'] ?? $torrent->name }}</strong>
        @endif
        <div class="torrent-hover-meta">{{ $preview['year'] ?? '' }}@if(!empty($preview['year'])) · @endif{{ $torrent->category?->name ?? 'Torrent' }}</div>
        @if(!empty($preview['imdb']) || !empty($preview['tmdb']))
            <div class="torrent-hover-ratings">
                @if(!empty($preview['imdb']))<span><b>IMDb</b> {{ $preview['imdb'] }}/10</span>@endif
                @if(!empty($preview['tmdb']))<span><b>TMDB</b> {{ $preview['tmdb'] }}/10</span>@endif
            </div>
        @endif
        @if(!empty($preview['overview']))<p class="torrent-hover-overview">{{ $preview['overview'] }}</p>@endif
        @if(!empty($preview['cast']))<p class="torrent-hover-cast"><b>Cast</b> {{ implode(', ', $preview['cast']) }}</p>@endif
        @unless($browsePreview ?? false)
        <div class="torrent-hover-release">{{ $torrent->name }}</div>
        <div class="torrent-hover-stats">{{ App\Helpers\FormatHelper::formatSize($torrent->size) }} · {{ number_format($torrent->seeders) }} seeders · {{ number_format($torrent->leechers) }} leechers</div>
        @endunless
    </div>
</div>
