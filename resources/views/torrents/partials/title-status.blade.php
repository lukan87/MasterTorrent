{{-- TITLE + STATUS ICONS --}}
    <div class="torrent-title-line d-flex align-items-center gap-2">

        <a href="{{ route('torrents.show', [$torrent->id, urlencode($torrent->slug)]) }}"
           class="torrent-name-link text-decoration-none overflow-hidden {{ $torrent->is_seeding ? 'torrent-name-link--seeding' : '' }}"
           data-bs-toggle="tooltip"
           data-bs-html="true"
           data-bs-title="<img src='{{ $torrent->poster }}' class='img-fluid rounded' style='max-width:180px'>">

            <small class="torrent-title text-truncate d-block fw-bold">
                {{ $torrent->name }}
            </small>

        </a>

        {{-- Downloaded --}}
        @if($torrent->has_downloaded)

            <span
                class="torrent-completed-mark"
                tabindex="0"
                role="img"
                aria-label="You downloaded this torrent"
                data-bs-toggle="tooltip"
                title="You downloaded this torrent"
            >
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            </span>

        @endif

        {{-- Seeding --}}
        @if($torrent->is_seeding)

            <span
                class="torrent-seeding-label"
                tabindex="0"
                role="img"
                aria-label="You are currently seeding this torrent"
                data-bs-toggle="tooltip"
                title="You are currently seeding this torrent"
            >
                <i class="bi bi-arrow-up" aria-hidden="true"></i>
                <span aria-hidden="true">Seeding</span>
            </span>

        @endif

    </div>

@once
<style>
/* Keep personal status beside short titles and visible beside truncated ones. */
.torrent-title-line {
    min-width: 0;
    padding-block: 3px;
}

.torrent-name-link {
    flex: 0 1 auto;
    min-width: 0;
}

.torrent-title-line .torrent-name-link--seeding .torrent-title {
    color: #9aebc9;
    text-shadow: 0 0 12px rgba(52, 211, 153, .32);
}

.torrent-title-line .torrent-name-link--seeding:hover .torrent-title {
    color: #c5ffe7;
    text-shadow: 0 0 16px rgba(52, 211, 153, .48);
}

.torrent-name-link--seeding .torrent-title::after {
    background: linear-gradient(90deg, transparent, #6ee7b7);
}

.torrent-completed-mark,
.torrent-seeding-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    cursor: help;
}

.torrent-completed-mark {
    color: #6ee7b7;
    font-size: 14px;
    line-height: 1;
}

.torrent-seeding-label {
    gap: 3px;
    padding: 3px 7px;
    border: 1px solid rgba(110, 231, 183, .24);
    border-radius: 50rem;
    background: rgba(52, 211, 153, .09);
    color: #9aebc9;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}

.torrent-completed-mark:focus-visible,
.torrent-seeding-label:focus-visible {
    outline: 2px solid #6ee7b7;
    outline-offset: 2px;
    border-radius: 4px;
}
</style>
@endonce
