@if(!$similarTorrents->isEmpty())

<div class="similar-torrents mt-3">

    <div class="similar-torrents-header">
        <div>
            <h5 class="similar-torrents-title mb-0">
                <i class="bi bi-collection-play me-2"></i>
                Similar Torrents
            </h5>

            <div class="similar-torrents-subtitle">
                Torrents related to this release
            </div>
        </div>
    </div>

    <div class="similar-torrents-body">

        <div class="similar-torrent-list">

            @foreach($similarTorrents as $similar)

                <div class="similar-torrent-item">

                    <div class="similar-torrent-info">

                        <a href="{{ route('torrents.show', ['id' => $similar->id, 'slug' => $similar->slug]) }}"
                           class="similar-torrent-name">

                            {{ $similar->name }}

                        </a>

                        <div class="similar-torrent-stats">

                            <span class="similar-seeders">
                                <i class="bi bi-arrow-up-circle-fill"></i>
                                Seeders:
                                <strong>{{ $similar->seeders }}</strong>
                            </span>

                            <span class="similar-leechers">
                                <i class="bi bi-arrow-down-circle-fill"></i>
                                Leechers:
                                <strong>{{ $similar->leechers }}</strong>
                            </span>

                            <span class="similar-completed">
                                <i class="bi bi-check-circle-fill"></i>
                                Completed:
                                <strong>{{ $similar->times_completed }}</strong>
                            </span>

                        </div>

                    </div>

                    <div class="similar-torrent-size">

                        <i class="bi bi-hdd me-1"></i>

                        {{ \App\Helpers\FormatHelper::formatSize($similar->size) }}

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>

<style>
/* =========================================================
   FILEIPLAY — SIMILAR TORRENTS
   ========================================================= */

.similar-torrents {
    overflow: hidden;

    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.95)),
        var(--theme-surface, rgba(10,15,27,.84))
    );

    border: 1px solid var(--ui-border);
    border-radius: .85rem;

    box-shadow: 0 14px 36px var(--theme-shadow, rgba(0, 0, 0, .28));

    backdrop-filter: blur(14px);
}

/* Header */

.similar-torrents-header {
    display: flex;
    align-items: center;

    padding: 14px 16px;

    background: var(--theme-teal-soft, rgba(45, 212, 191, .045));

    border-bottom: 1px solid var(--ui-border);
}

.similar-torrents-title {
    color: var(--theme-text, #fff);

    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.similar-torrents-title i {
    color: var(--ui-accent);
}

.similar-torrents-subtitle {
    margin-top: 3px;

    color: var(--theme-muted, rgba(255, 255, 255, .42));

    font-size: var(--site-font-small, 13px);
}

/* Body */

.similar-torrents-body {
    padding: 10px;
}

/* List */

.similar-torrent-list {
    display: flex;
    flex-direction: column;

    gap: 6px;
}

/* Torrent row */

.similar-torrent-item {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 16px;

    padding: 11px 13px;

    background: var(--theme-surface, rgba(6,10,19,.48));

    border: 1px solid var(--theme-border, rgba(255, 255, 255, .055));

    border-radius: .6rem;

    transition:
        background .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.similar-torrent-item:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .045));

    border-color: var(--theme-teal-border, rgba(45, 212, 191, .22));

    transform: translateX(2px);
}

/* Information */

.similar-torrent-info {
    min-width: 0;

    flex: 1;
}

/* Torrent name */

.similar-torrent-name {
    display: block;

    overflow: hidden;

    color: var(--theme-text, rgba(255, 255, 255, .86));

    font-size: var(--site-font-body, 13px);
    font-weight: 600;

    line-height: 1.4;

    text-decoration: none;

    white-space: nowrap;
    text-overflow: ellipsis;

    transition: color .15s ease;
}

.similar-torrent-name:hover {
    color: var(--ui-accent);
}

/* Stats */

.similar-torrent-stats {
    display: flex;
    flex-wrap: wrap;

    gap: 12px;

    margin-top: 5px;

    color: var(--theme-muted, rgba(255, 255, 255, .43));

    font-size: var(--site-font-small, 13px);
}

.similar-torrent-stats strong {
    color: var(--theme-muted, rgba(255, 255, 255, .72));
}

.similar-torrent-stats i {
    margin-right: 3px;

    font-size: 10px;
}

.similar-seeders {
    color: var(--theme-teal-text, #6ee7b7);
}

.similar-leechers {
    color: var(--theme-red-text, #fca5a5);
}

.similar-completed {
    color: var(--ui-accent);
}

/* Size */

.similar-torrent-size {
    flex: 0 0 auto;

    padding: 5px 9px;

    border-radius: .45rem;

    background: var(--theme-surface-alt, rgba(255,255,255,0.0245));

    border: 1px solid var(--theme-border, rgba(255, 255, 255, .07));

    color: var(--theme-muted, rgba(255, 255, 255, .62));

    font-size: var(--site-font-small, 13px);
    font-weight: 600;

    white-space: nowrap;
}

.similar-torrent-size i {
    color: var(--ui-accent);
}

/* Mobile */

@media (max-width: 768px) {

    .similar-torrents-header {
        padding: 12px 13px;
    }

    .similar-torrents-body {
        padding: 8px;
    }

    .similar-torrent-item {
        align-items: flex-start;

        flex-direction: column;

        gap: 8px;

        padding: 10px;
    }

    .similar-torrent-name {
        font-size: var(--site-font-body, 13px);

        white-space: normal;
    }

    .similar-torrent-stats {
        gap: 8px;

        font-size: var(--site-font-small, 13px);
    }

    .similar-torrent-size {
        font-size: var(--site-font-small, 13px);
    }
}
</style>

@endif