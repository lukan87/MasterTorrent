<header class="py-4">
    <h1 class="h3 text-break">{{ $torrent->name }}</h1>
    @if($torrent->category)
        <p class="text-muted mb-0">{{ $torrent->category->name }}</p>
    @endif
</header>
