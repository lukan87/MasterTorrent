<header class="py-4 d-flex gap-3 align-items-start">
    @if($torrent->poster)
        <img src="{{ $torrent->poster }}" alt="" width="120" class="rounded" style="max-height:180px;object-fit:contain" decoding="async">
    @endif
    <div>
        <h1 class="h3 text-break">{{ $torrent->name }}</h1>
        @if($torrent->category)
            <p class="text-muted mb-0">{{ $torrent->category->name }}</p>
        @endif
    </div>
</header>
