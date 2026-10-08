<style>

/* HERO */
.hero {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;

    /* 🔥 KEY CHANGE */
    background-position: top center;

    filter: blur(0.15px) brightness(0.5);
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, #070707 10%, transparent 60%);
}

.hero-content {
    position: relative;
    z-index: 2;
    padding-top: 100px;
}

/* POSTER */
.poster {
    width: 200px;
    border-radius: 10px;
}

/* TITLE */
.year {
    font-weight: 400;
    opacity: 0.7;
    font-size: 1.5rem;
}

/* META */
.meta {
    display: flex;
    gap: 10px;
    align-items: center;
}

.badge-rating {
background: var(--theme-surface-alt, rgba(255,255,255,0.105));
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 1.25rem
}

.badge-meta {
    background: var(--theme-surface-alt, rgba(255,255,255,0.105));
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 1.25rem
}

/* SUBSCRIBE */
.subscribe-wrap form {
    display: inline-block;
}

.subscribe-btn {
    background: var(--theme-blue-soft, rgba(59,130,246,.18));
    border: 1px solid var(--theme-blue-border, rgba(59,130,246,.35));
    color: var(--theme-blue-text, #9cc7ff);
    font-weight: 600;
    border-radius: 8px;
    padding: 6px 14px;
    transition: 0.15s ease;
}

.subscribe-btn:hover {
    background: var(--theme-blue-soft, rgba(59,130,246,.32));
    border-color: var(--theme-blue-border, rgba(59,130,246,.55));
    color: var(--theme-text, #fff);
}

/* OVERVIEW */
.overview {
    max-width: 700px;
    opacity: 0.9;
}

/* TORRENTS */
.torrent-item {
    background: var(--theme-surface-alt, #0b0b0b);
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .05));
    border-radius: 10px;
    padding: 13px 15px;
    transition: 0.2s ease;
}

.torrent-item:hover {
    background: var(--theme-surface-alt, #111111);
    border-color: var(--theme-blue-border, rgba(59, 130, 246, .25));
}

.torrent-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.torrent-name {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.torrent-link {
    font-weight: 600;
    color: var(--theme-text, #fff);
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.torrent-link:hover {
    color: var(--theme-blue-text, #60a5fa);
}

.torrent-meta {
    display: flex;
    gap: 15px;
    color: var(--theme-muted, #aaa);
    flex-shrink: 0;
}

.seeders {
    color: var(--theme-green-text, #4caf50);
    font-weight: 600;
}

/* Resolution panels */
.res-panels {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.res-panel {
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .07));
    border-radius: 12px;
    overflow: hidden;
    background: var(--theme-surface, rgba(6,10,19,.45));
}

.res-panel-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    background: transparent;
    border: none;
    padding: 14px 18px;
    cursor: pointer;
    text-align: left;
    transition: background .15s ease;
}

.res-panel-header:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .05));
}

.res-panel-header .res-label {
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    color: var(--theme-text, #f1f5f9);
    letter-spacing: .2px;
}

.res-panel-header .res-count {
    display: inline-flex;
    align-items: center;
    padding: 2px 9px;
    border-radius: 999px;
    background: var(--theme-teal-soft, rgba(45, 212, 191, .12));
    color: var(--ui-accent);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.res-panel-header .res-chevron {
    margin-left: auto;
    color: var(--theme-muted, #64748b);
    transition: transform .2s ease;
}

.res-panel-header.is-open .res-chevron {
    transform: rotate(180deg);
}

.res-panel-body {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 4px 14px 14px;
}

@media (max-width: 575px) {
    .torrent-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .torrent-meta {
        width: 100%;
        justify-content: space-between;
    }
    .torrent-link {
        white-space: normal;
    }
}

/* ── TORRENT EMPTY STATE (no uploads yet) ────────────────────── */
.torrent-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: var(--theme-surface, rgba(10,15,27,.55));
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .06));
    border-radius: .85rem;
}
.torrent-empty i {
    font-size: 42px;
    color: var(--ui-accent);
    opacity: .55;
    display: block;
    margin-bottom: .75rem;
}
.torrent-empty h4 {
    color: var(--theme-text, #f1f5f9);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    margin: 0 0 .5rem;
}
.torrent-empty p {
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-body, 13px);
    max-width: 480px;
    margin: 0 auto 1.1rem;
}
.torrent-empty .request-btn {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .10));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .25));
    color: var(--ui-accent);
    font-weight: 600;
    border-radius: .55rem;
    padding: .45rem 1.1rem;
    font-size: var(--site-font-body, 13px);
    transition: background .15s, border-color .15s;
}
.torrent-empty .request-btn:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .20));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .45));
    color: var(--ui-accent);
}

/* =========================
   YOU MIGHT ALSO LIKE (TMDB recs)
========================= */
.tmdb-recs {
    overflow: hidden;
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.95)), var(--theme-surface, rgba(10,15,27,.84)));
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    box-shadow: 0 14px 36px var(--theme-shadow, rgba(0, 0, 0, .28));
}

.tmdb-recs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 16px;
    background: var(--theme-teal-soft, rgba(45, 212, 191, .045));
    border-bottom: 1px solid var(--ui-border);
}

.tmdb-recs-title {
    color: var(--theme-text, #fff);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.tmdb-recs-title i {
    color: var(--ui-accent);
}

.tmdb-recs-subtitle {
    margin-top: 3px;
    color: var(--theme-muted, rgba(255, 255, 255, .42));
    font-size: var(--site-font-small, 13px);
}

.tmdb-recs-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 10px;
    border-radius: .5rem;
    background: var(--theme-teal-soft, rgba(45, 212, 191, .07));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .18));
    color: var(--ui-accent);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease;
}

.tmdb-recs-link:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .12));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .32));
    color: var(--ui-accent);
}

.tmdb-recs-body {
    padding: 12px;
}

.tmdb-recs-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 12px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 3px 2px 8px;
    scroll-behavior: smooth;
    scrollbar-width: none;
}

.tmdb-recs-row::-webkit-scrollbar {
    display: none;
}

.tmdb-recs-card {
    flex: 0 0 140px;
    width: 140px;
    min-width: 140px;
    max-width: 140px;
    padding: 8px;
    background: var(--theme-surface, rgba(6,10,19,.48));
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .055));
    border-radius: .6rem;
    transition: background .15s ease, border-color .15s ease, transform .15s ease;
}

.tmdb-recs-card:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .05));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .24));
    transform: translateY(-2px);
}

.tmdb-recs-poster-wrap {
    position: relative;
    aspect-ratio: 2 / 3;
    border-radius: .45rem;
    overflow: hidden;
    background: #0a0f1b;
}

.tmdb-recs-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .3s ease;
}

.tmdb-recs-card:hover .tmdb-recs-poster {
    transform: scale(1.04);
}

.tmdb-recs-rating {
    position: absolute;
    top: 6px;
    right: 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 6px;
    border-radius: .4rem;
    background: var(--theme-surface-alt, rgba(3,6,12,.82));
    color: var(--theme-amber-text, #facc15);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.tmdb-recs-info {
    padding: 7px 2px 2px;
}

.tmdb-recs-name {
    overflow: hidden;
    color: var(--theme-muted, rgba(255, 255, 255, .82));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.tmdb-recs-year {
    margin-top: 3px;
    color: var(--theme-muted, rgba(255, 255, 255, .42));
    font-size: var(--site-font-small, 13px);
}

@media (max-width: 768px) {
    .tmdb-recs-card {
        flex: 0 0 118px;
        width: 118px;
        min-width: 118px;
        max-width: 118px;
    }
}
/* =========================================================
   LIBRARY SUBSCRIBE ROW
========================================================= */
.library-subscribe-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    margin-top: 14px;
    border-radius: .8rem;
    background: var(--theme-surface, rgba(6,10,19,.55));
    border: 1px solid var(--ui-border);
}
.library-subscribe-row .subscribe-btn {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .08));
    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .22));
    color: var(--ui-accent);
    font-weight: 600;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}
.library-subscribe-row .subscribe-btn:hover {
    background: var(--theme-teal-soft, rgba(45, 212, 191, .16));
    border-color: var(--theme-teal-border, rgba(45, 212, 191, .38));
    color: var(--ui-accent);
}

/* Subscriber count + names next to subscribe button */
.subscribers-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    font-size: var(--site-font-body, 13px);
    color: var(--ui-text-muted);
    line-height: 1.3;
}
.subscribers-label i { color: var(--ui-accent); }
.subscribers-label .subscribers-count { font-weight: 700; color: var(--ui-accent-strong); white-space: nowrap; }
.subscribers-label .subscribers-names { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 260px; }
.subscribers-label .subscribers-more { color: var(--ui-accent); font-weight: 700; }
/* "Watch online" button (shown when the title is in the online catalogue) */
.watch-online-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--theme-green-soft, rgba(34,197,94,.10));
    border: 1px solid var(--theme-green-border, rgba(34,197,94,.30));
    color: var(--theme-green-text, #4ade80);
    padding: 8px 14px;
    border-radius: .6rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
}
.watch-online-btn:hover {
    background: var(--theme-green-soft, rgba(34,197,94,.18));
    border-color: var(--theme-green-border, rgba(34,197,94,.44));
    color: var(--theme-text, #bbf7d0);
    transform: translateY(-1px);
}
.watch-online-btn i { font-size: 15px; }

/* =========================================================
   RECOMMENDATION DATABASE STATUS
   ========================================================= */

.tmdb-recs-card-available {
    cursor: pointer;
}

.tmdb-recs-card-unavailable {
    cursor: default;
    opacity: .72;
}

/* Black & white poster when movie isn't in database */
.tmdb-recs-poster-unavailable {
    filter: grayscale(100%);
    opacity: .62;
    transition:
        filter .25s ease,
        opacity .25s ease,
        transform .3s ease;
}

.tmdb-recs-card-unavailable:hover {
    background: var(--theme-surface, rgba(6,10,19,.48));
    border-color: var(--theme-border, rgba(255, 255, 255, .055));
    transform: none;
}

.tmdb-recs-card-unavailable:hover .tmdb-recs-poster-unavailable {
    filter: grayscale(100%);
    opacity: .72;
    transform: scale(1.02);
}

/* Database status icon */
.tmdb-recs-status {
    position: absolute;
    left: 6px;
    top: 6px;

    width: 26px;
    height: 26px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: var(--theme-surface-alt, rgba(3,6,12,.86));
    backdrop-filter: blur(5px);

    font-size: 12px;
    z-index: 3;
}

.tmdb-recs-status-online {
    color: var(--theme-green-text, #4ade80);
    border: 1px solid var(--theme-green-border, rgba(74, 222, 128, .3));
}

.tmdb-recs-status-missing {
    color: var(--theme-muted, rgba(255, 255, 255, .45));
    border: 1px solid var(--theme-border, rgba(255, 255, 255, .12));
}
</style>