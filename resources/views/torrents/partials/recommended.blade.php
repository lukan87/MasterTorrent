@if(!$recommendedTorrents->isEmpty())
    <div class="recommended-torrents mb-5 mt-3">

        <div class="recommended-header">
            <div>
                <h6 class="recommended-title mb-0">
                    <i class="bi bi-stars me-2"></i>
                    Recommended Torrents
                </h6>
                <div class="recommended-subtitle">
                    You may also be interested in
                </div>
            </div>
        </div>

        <div class="recommended-body">
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-6 g-3">

                @foreach($recommendedTorrents as $recommended)

                    <div class="col">

                        <a href="{{ route('torrents.show', ['id' => $recommended->id, 'slug' => $recommended->slug]) }}"
                           class="recommended-link text-decoration-none"
                           data-bs-toggle="tooltip"
                           title="{{ $recommended->name }}">

                            <div class="recommended-item">

                                <div class="recommended-poster-wrap">

                                    <img src="{{ $recommended->poster ?? '/images/noposter.jpg' }}"
                                         alt="{{ $recommended->name }}"
                                         class="recommended-poster">

                                    <div class="recommended-overlay">
                                        <i class="bi bi-play-circle-fill"></i>
                                    </div>

                                </div>

                                <div class="recommended-info">

                                    <div class="recommended-name">
                                        {{ $recommended->name }}
                                    </div>

                                    <div class="recommended-stats">

                                        <span class="recommended-seeders">
                                            <i class="bi bi-arrow-up-circle-fill"></i>
                                            {{ $recommended->seeders }}
                                        </span>

                                        <span class="recommended-leechers">
                                            <i class="bi bi-arrow-down-circle-fill"></i>
                                            {{ $recommended->leechers }}
                                        </span>

                                        <span class="recommended-completed">
                                            <i class="bi bi-check-circle-fill"></i>
                                            {{ $recommended->times_completed }}
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </a>

                    </div>

                @endforeach

            </div>
        </div>

    </div>
@endif

<style>
/* =========================================================
   FILEIPLAY — RECOMMENDED TORRENTS
   ========================================================= */

.recommended-torrents {
    overflow: hidden;

    background: linear-gradient(
        135deg,
        rgba(22, 32, 51, .95),
        rgba(15, 23, 42, .84)
    );

    border: 1px solid var(--ui-border);
    border-radius: .85rem;

    box-shadow: 0 14px 36px rgba(0, 0, 0, .28);

    backdrop-filter: blur(14px);
}

/* Header */

.recommended-header {
    padding: 14px 16px;

    background: rgba(45, 212, 191, .045);

    border-bottom: 1px solid var(--ui-border);
}

.recommended-title {
    color: #fff;

    font-size: 14px;
    font-weight: 700;
}

.recommended-title i {
    color: var(--ui-accent);
}

.recommended-subtitle {
    margin-top: 3px;

    color: rgba(255, 255, 255, .42);

    font-size: 12px;
}

/* Body */

.recommended-body {
    padding: 12px;
}

/* Item */

.recommended-link {
    display: block;
    height: 100%;
}

.recommended-item {
    height: 100%;
    overflow: hidden;

    background: rgba(9, 16, 29, .55);

    border: 1px solid var(--ui-border);
    border-radius: .65rem;

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.recommended-link:hover .recommended-item {
    transform: translateY(-3px);

    background: rgba(12, 25, 39, .72);

    border-color: rgba(45, 212, 191, .32);

    box-shadow: 0 10px 24px rgba(0, 0, 0, .28);
}

/* Poster */

.recommended-poster-wrap {
    position: relative;

    overflow: hidden;

    aspect-ratio: 2 / 3;

    background: #0b1220;
}

.recommended-poster {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform .25s ease;
}

.recommended-link:hover .recommended-poster {
    transform: scale(1.035);
}

/* Hover overlay */

.recommended-overlay {
    position: absolute;
    inset: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background: rgba(4, 10, 20, .42);

    opacity: 0;

    transition: opacity .18s ease;
}

.recommended-overlay i {
    color: var(--ui-accent);

    font-size: 28px;

    filter: drop-shadow(0 3px 8px rgba(0, 0, 0, .5));
}

.recommended-link:hover .recommended-overlay {
    opacity: 1;
}

/* Info */

.recommended-info {
    padding: 9px;
}

.recommended-name {
    overflow: hidden;

    color: rgba(255, 255, 255, .82);

    font-size: 13px;
    font-weight: 600;

    line-height: 1.35;

    white-space: nowrap;
    text-overflow: ellipsis;
}

.recommended-stats {
    display: flex;
    align-items: center;

    gap: 8px;

    margin-top: 7px;

    font-size: 11px;
    font-weight: 600;
}

.recommended-seeders {
    color: #6ee7b7;
}

.recommended-leechers {
    color: #fca5a5;
}

.recommended-completed {
    color: var(--ui-accent);
}

.recommended-stats i {
    margin-right: 2px;

    font-size: 10px;
}

/* Mobile */

@media (max-width: 576px) {

    .recommended-header {
        padding: 12px 13px;
    }

    .recommended-body {
        padding: 9px;
    }

    .recommended-name {
        font-size: 12px;
    }

    .recommended-stats {
        gap: 5px;
        font-size: 10px;
    }

    .recommended-stats i {
        font-size: 9px;
    }
}
</style>