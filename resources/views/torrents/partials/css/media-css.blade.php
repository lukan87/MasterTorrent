@once
<style>


/* =========================================================
   FILEIPLAY — CAST SECTION
   ========================================================= */

.cast-section {
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    margin-top: 24px;
}

/* Header */

.cast-title {
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    color: var(--theme-text, #fff);
    margin-bottom: 3px;
}

.cast-subtitle {
    color: var(--theme-muted, rgba(255,255,255,.52));
    font-size: var(--site-font-body, 13px);
}

.cast-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: .55rem;

    background: var(--theme-teal-soft, rgba(45,212,191,.06));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.18));

    color: var(--ui-accent);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.cast-count-badge i {
    font-size: 13px;
}

/* Horizontal row */

.cast-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;

    width: 100%;
    max-width: 100%;

    overflow-x: auto;
    overflow-y: hidden;

    padding: 3px 2px 12px;

    scroll-behavior: smooth;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;

    box-sizing: border-box;
}

.cast-row::-webkit-scrollbar {
    display: none;
}

/* Actor card */

.cast-card {
    flex: 0 0 140px;
    width: 140px;
    min-width: 140px;
    max-width: 140px;

    padding: 12px 9px;

    border-radius: .7rem;

    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.95)),
        var(--theme-surface, rgba(10,15,27,.84))
    );

    border: 1px solid var(--ui-border);

    box-shadow: 0 8px 22px var(--theme-shadow, rgba(0,0,0,.25));

    backdrop-filter: blur(10px);

    transition:
        transform .2s ease,
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease;

    overflow: hidden;
    box-sizing: border-box;
}

.cast-card:hover {
    transform: translateY(-3px);

    border-color: var(--theme-teal-border, rgba(45,212,191,.30));

    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(16,25,38,.97)),
        var(--theme-surface, rgba(10,15,27,.92))
    );

    box-shadow: 0 10px 26px var(--theme-shadow, rgba(0,0,0,.34));
}

/* Actor image */

.actor-image-wrapper {
    width: 112px;
    height: 112px;

    margin: auto;

    border-radius: 50%;
    overflow: hidden;

    border: 2px solid rgba(45,212,191,.14);

    background: #0a0f1b;

    box-shadow:
        0 6px 18px rgba(0,0,0,.38);
}

.actor-image {
    width: 100%;
    height: 100%;

    object-fit: cover;

    transition: transform .3s ease;
}

.cast-card:hover .actor-image {
    transform: scale(1.04);
}

/* Actor name */

.actor-name {
    font-size: var(--site-font-body, 13px);
    font-weight: 700;

    line-height: 1.35;

    margin-bottom: 4px;
}

.actor-name a {
    color: var(--theme-text, rgba(255,255,255,.92));
    text-decoration: none;

    transition: color .15s ease;
}

.actor-name a:hover {
    color: var(--ui-accent);
}

.actor-character {
    font-size: var(--site-font-small, 13px);
    line-height: 1.4;

    color: var(--theme-muted, rgba(255,255,255,.52));

    overflow: hidden;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Mobile */

@media (max-width: 768px) {

    .cast-section {
        margin-top: 20px;
    }

    .cast-title {
        font-size: var(--site-font-body, 13px);
    }

    .cast-subtitle {
        font-size: var(--site-font-body, 13px);
    }

    .cast-count-badge {
        margin-top: 8px;
        font-size: var(--site-font-small, 13px);
    }

    .cast-row {
        gap: 10px;
    }

    .cast-card {
        flex: 0 0 125px;
        width: 125px;
        min-width: 125px;
        max-width: 125px;

        padding: 10px 7px;
    }

    .actor-image-wrapper {
        width: 96px;
        height: 96px;
    }

    .actor-name {
        font-size: var(--site-font-small, 13px);
    }

    .actor-character {
        font-size: var(--site-font-small, 13px);
    }
}

html,
body {
    overflow-x: hidden;
}




html,
body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.content-overlay {
    position: relative;
    z-index: 1;
    background: none;
    padding: 20px;
}

/* Fanart background */

html::before {
    content: '';

    position: fixed;

    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background-image:
        linear-gradient(
            to bottom,
            rgba(5,10,18,.38),
            rgba(5,10,18,.94)
        ),
        url('{{ $fanartBackground ?? ($torrent->background ?: ($display['backdrop'] ?? '')) }}');

    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;

    opacity: .65;

    z-index: -2;
}

/* Darkening layer */

html::after {
    content: '';

    position: fixed;

    top: 55px;
    left: 0;
    right: 0;
    bottom: 0;

    background: linear-gradient(
        to bottom,
        rgba(3,6,12,.05) 0%,
        rgba(3,6,12,.28) 25%,
        rgba(3,6,12,.58) 55%,
        rgba(3,6,12,.86) 80%,
        rgba(3,6,12,1) 100%
    );

    pointer-events: none;

    z-index: -1;
}


html.has-torrent-backdrops::before {
    opacity: 0;
}

.torrent-backdrop-slideshow {
    position: fixed;
    inset: 55px 0 0;
    z-index: -2;
    pointer-events: none;
}

.torrent-backdrop-slide {
    position: absolute;
    inset: 0;
    background-position: center top;
    background-size: cover;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 900ms ease-in-out;
}

.torrent-backdrop-slide.is-visible {
    opacity: .65;
}

@media (prefers-reduced-motion: reduce) {
    .torrent-backdrop-slide {
        transition: none;
    }
}
</style>
@endonce
