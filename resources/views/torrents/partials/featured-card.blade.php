<section class="torrent-featured-card torrent-featured-card--{{ $kind }}" aria-label="{{ $kind === 'hot' ? 'Hot torrents' : 'Sticky torrents' }}">
    <header class="torrent-featured-header">
        <span class="torrent-featured-icon"><i class="bi {{ $kind === 'hot' ? 'bi-fire' : 'bi-pin-angle-fill' }}" aria-hidden="true"></i></span>
        <div class="torrent-featured-heading">
            <h2>{{ $kind === 'hot' ? 'Hot torrents' : 'Sticky torrents' }} <span>{{ number_format($items->total()) }}</span></h2>
            <p>{{ $kind === 'hot' ? 'Fresh activity. Rediscovered favourites.' : 'Pinned picks from the team.' }}</p>
        </div>
        @if($items->lastPage() > 1)
            <nav class="torrent-featured-nav" aria-label="{{ ucfirst($kind) }} torrent pages">
                @foreach(['previous' => [$items->previousPageUrl(), 'bi-chevron-left'], 'next' => [$items->nextPageUrl(), 'bi-chevron-right']] as $direction => [$url, $icon])
                    @if($url)
                        <a href="{{ $url }}" data-featured-nav="{{ $direction }}" aria-label="{{ ucfirst($direction) }} {{ $kind }} torrents"><i class="bi {{ $icon }}" aria-hidden="true"></i></a>
                    @else
                        <span aria-disabled="true"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                    @endif
                @endforeach
            </nav>
        @endif
    </header>
    <div class="torrent-featured-toolbar">
        <span>{{ $kind === 'hot' ? 'Community favourites' : 'Staff selection' }}</span>
        <span>7 per page</span>
    </div>
    <ol class="torrent-featured-list" start="{{ $items->firstItem() ?? 1 }}">
        @forelse($items as $torrent)
            <li>
                <span class="torrent-featured-rank">{{ ($items->firstItem() ?? 1) + $loop->index }}</span>
                <div class="torrent-featured-title">
                    <a href="{{ route('torrents.show', [$torrent->id, urlencode($torrent->slug)]) }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-container="body"
                       data-bs-custom-class="torrent-featured-tooltip torrent-featured-tooltip--{{ $kind }}"
                       data-torrent-preview="{{ $kind }}-{{ $torrent->id }}" data-bs-title="{{ $torrent->name }}">{{ $torrent->name }}</a>
                </div>
                <div class="torrent-featured-peers">
                    <span class="torrent-featured-seeders" aria-label="{{ $torrent->seeders }} seeders" tabindex="0" data-bs-toggle="tooltip" data-bs-container="body" data-bs-title="{{ number_format($torrent->seeders) }} users sharing the complete torrent" data-bs-custom-class="torrent-featured-tooltip torrent-featured-tooltip--{{ $kind }}"><i class="bi bi-arrow-up" aria-hidden="true"></i>{{ number_format($torrent->seeders) }}</span>
                    <span class="torrent-featured-leechers" aria-label="{{ $torrent->leechers }} leechers" tabindex="0" data-bs-toggle="tooltip" data-bs-container="body" data-bs-title="{{ number_format($torrent->leechers) }} users downloading this torrent" data-bs-custom-class="torrent-featured-tooltip torrent-featured-tooltip--{{ $kind }}"><i class="bi bi-arrow-down" aria-hidden="true"></i>{{ number_format($torrent->leechers) }}</span>
                </div>
                <template data-preview-content="{{ $kind }}-{{ $torrent->id }}">@include('torrents.partials.featured-preview')</template>
            </li>
        @empty
            <li class="torrent-featured-empty"><i class="bi {{ $kind === 'hot' ? 'bi-fire' : 'bi-pin-angle' }}" aria-hidden="true"></i><strong>{{ $kind === 'hot' ? 'Waiting for the next spark' : 'No pinned torrents yet' }}</strong><span>{{ $kind === 'hot' ? 'Active downloads bring torrents into this list.' : 'Staff picks will appear here.' }}</span></li>
        @endforelse
    </ol>
    <footer class="torrent-featured-footer">
        <span>{{ $kind === 'hot' ? 'Ranked by swarm activity · refreshed every 15 minutes' : 'Selected by staff' }}</span>
        @if($items->total())<span>Page {{ $items->currentPage() }} of {{ $items->lastPage() }}</span>@endif
    </footer>
</section>
