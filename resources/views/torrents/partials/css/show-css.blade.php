<style>
/* =========================================================
   FILEIPLAY TORRENT DETAILS — FORUM STYLE
   ========================================================= */

.modern-tabs-card {
    background: linear-gradient(135deg, rgba(14,21,33,.95), rgba(10,15,27,.84));
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
    overflow: hidden;
    box-shadow: 0 10px 28px rgba(0,0,0,.24);
    backdrop-filter: blur(12px);
}

.modern-tabs-header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--ui-border);
}

.modern-tabs-nav {
    gap: 8px;
    flex-wrap: wrap;
}

.modern-tabs-nav .nav-link {
    border: 1px solid transparent;
    background: rgba(255,255,255,0.0245);
    color: rgba(255,255,255,.72);
    border-radius: .6rem;
    padding: 9px 13px;
    font-size: 14px;
    font-weight: 700;
    transition: .2s ease;
}

.modern-tabs-nav .nav-link:hover {
    color: #fff;
    background: rgba(255,255,255,0.042);
    border-color: var(--ui-border);
}

.modern-tabs-nav .nav-link.active {
    background: rgba(20, 184, 166, .14);
    color: var(--ui-accent);
    border-color: rgba(20, 184, 166, .35);
    box-shadow: none;
}

.modern-tabs-body {
    padding: 18px;
}

/* Shared inner cards */
.modern-description-card,
.modern-subtitles-card,
.modern-snatched-card {
    background: linear-gradient(135deg, rgba(14,21,33,.92), rgba(10,15,27,.78));
    border: 1px solid var(--ui-border);
    border-radius: .8rem;
    overflow: hidden;
    position: relative;
}

.modern-description-card::before,
.modern-subtitles-card::before,
.modern-snatched-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, var(--ui-accent), var(--ui-accent-strong));
    opacity: .9;
}

.modern-description-header,
.modern-subtitles-header,
.modern-snatched-header {
    padding: 15px 18px;
    border-bottom: 1px solid var(--ui-border);
}

.description-icon-box,
.subtitle-icon-box,
.snatch-icon {
    width: 42px;
    height: 42px;
    border-radius: .65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(20,184,166,.12);
    border: 1px solid rgba(20,184,166,.28);
    color: var(--ui-accent);
    font-size: 18px;
    flex: 0 0 auto;
}

.modern-description-title,
.modern-subtitle-title,
.modern-snatched-title {
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    margin: 0;
}

.modern-description-subtitle,
.modern-subtitle-subtitle,
.modern-snatched-subtitle {
    color: rgba(255,255,255,.58);
    font-size: 13px;
}

.modern-description-body,
.modern-subtitles-body,
.modern-snatched-body {
    padding: 18px;
}

/* Description */
.modern-scrollable-content {
    background: rgba(255,255,255,0.0175);
    border: 1px solid var(--ui-border);
    border-radius: .65rem;
    padding: 17px;
    color: rgba(255,255,255,.84);
    font-size: 14px;
    line-height: 1.65;
    word-break: break-word;
    overflow-wrap: anywhere;
}

/* Subtitles */
.subtitle-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 14px;
    border-radius: .7rem;
    background: rgba(255,255,255,0.0175);
    border: 1px solid var(--ui-border);
    margin-bottom: 10px;
    transition: border-color .2s ease, background .2s ease;
}

.subtitle-item:hover {
    background: rgba(255,255,255,0.028);
    border-color: rgba(20,184,166,.28);
}

.subtitle-item:last-child {
    margin-bottom: 0;
}

.subtitle-left {
    flex: 1 1 280px;
    min-width: 0;
}

.subtitle-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.modern-source-badge {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .03em;
}

.local-badge {
    background: rgba(34,197,94,.12);
    color: #4ade80;
    border: 1px solid rgba(34,197,94,.22);
}

.external-badge {
    background: rgba(20,184,166,.12);
    color: var(--ui-accent);
    border: 1px solid rgba(20,184,166,.24);
}

.subtitle-file-name {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 5px;
    overflow-wrap: anywhere;
}

.subtitle-meta {
    color: rgba(255,255,255,.55);
    font-size: 13px;
}

.subtitle-meta a {
    color: var(--ui-accent);
    text-decoration: none;
}

.subtitle-meta a:hover {
    color: #fff;
}

.subtitle-year {
    color: rgba(255,255,255,.55);
    font-size: 12px;
}

.external-description {
    margin-top: 8px;
    color: rgba(255,255,255,.72);
    font-size: 13px;
    line-height: 1.5;
}

.modern-subtitle-btn {
    border: 1px solid rgba(20,184,166,.28);
    border-radius: .55rem;
    background: rgba(20,184,166,.11);
    color: var(--ui-accent);
    padding: 7px 11px;
    font-size: 13px;
    font-weight: 700;
    transition: .2s ease;
}

.modern-subtitle-btn:hover {
    background: rgba(20,184,166,.18);
    color: #fff;
    border-color: rgba(20,184,166,.45);
}

.modern-delete-btn {
    border: 1px solid rgba(239,68,68,.25);
    border-radius: .55rem;
    background: rgba(239,68,68,.1);
    color: #f87171;
    padding: 7px 10px;
    font-size: 13px;
}

.modern-delete-btn:hover {
    background: rgba(239,68,68,.17);
    color: #fff;
}

/* Snatched */
.snatched-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
    padding: 0;
}

.snatched-user-card {
    background: rgba(255,255,255,0.0175);
    border: 1px solid var(--ui-border);
    border-radius: .7rem;
    padding: 15px;
    transition: .2s ease;
}

.snatched-user-card:hover {
    transform: translateY(-2px);
    border-color: rgba(20,184,166,.28);
    box-shadow: 0 8px 20px rgba(0,0,0,.18);
}

.snatched-user-link {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
}

.snatched-user-link:hover {
    color: var(--ui-accent);
}

.owner-pill {
    background: rgba(250,204,21,.1);
    border: 1px solid rgba(250,204,21,.2);
    color: #fde047;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.seed-status {
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.seeded {
    background: rgba(34,197,94,.1);
    border: 1px solid rgba(34,197,94,.2);
    color: #4ade80;
}

.not-seeded {
    background: rgba(239,68,68,.1);
    border: 1px solid rgba(239,68,68,.2);
    color: #f87171;
}

.snatch-stats {
    display: flex;
    flex-direction: column;
    gap: 11px;
}

.snatch-stat {
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon {
    width: 36px;
    height: 36px;
    border-radius: .55rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    flex: 0 0 auto;
}

.snatch-stat small {
    display: block;
    color: rgba(255,255,255,.52);
    font-size: 12px;
}

.snatch-stat strong {
    color: #fff;
    font-size: 13px;
}

.empty-snatched {
    text-align: center;
    padding: 35px 18px;
    color: rgba(255,255,255,.58);
    font-size: 14px;
}

.empty-snatched i {
    font-size: 2rem;
    margin-bottom: 10px;
    display: block;
    color: var(--ui-accent);
}

/* Keep Bootstrap utility stat colors, but soften them to fit the theme */
.snatch-stat .bg-primary {
    background: rgba(20,184,166,.16) !important;
}

.snatch-stat .bg-success {
    background: rgba(34,197,94,.16) !important;
}

.snatch-stat .bg-secondary {
    background: rgba(148,163,184,0.098) !important;
}

/* Mobile */
@media (max-width: 768px) {
    .modern-tabs-header,
    .modern-tabs-body,
    .modern-description-body,
    .modern-subtitles-body,
    .modern-snatched-body {
        padding: 14px;
    }

    .modern-tabs-nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .modern-tabs-nav::-webkit-scrollbar {
        display: none;
    }

    .modern-tabs-nav .nav-link {
        white-space: nowrap;
        flex: 0 0 auto;
        font-size: 14px;
    }

    .modern-description-header,
    .modern-subtitles-header,
    .modern-snatched-header {
        padding: 13px 15px;
    }

    .description-icon-box,
    .subtitle-icon-box,
    .snatch-icon {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    .subtitle-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .subtitle-actions {
        width: 100%;
    }

    .snatched-grid {
        grid-template-columns: 1fr;
    }
}



/* =========================================================
   DESCRIPTION EXPAND SYSTEM
   ========================================================= */

.description-expand-wrapper {
    position: relative;
}


/* =========================================================
   DESCRIPTION CONTENT
   ========================================================= */

.description-expand-content {
    position: relative;

    max-height: none;

    overflow: visible;
}


/* =========================================================
   COLLAPSED DESCRIPTION
   ========================================================= */

.description-expand-wrapper.is-collapsed
.description-expand-content {

    max-height: 500px;

    overflow: hidden;
}


/* =========================================================
   DESCRIPTION FADE
   ========================================================= */

.description-fade {

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 170px;

    z-index: 10;

    pointer-events: none;

    background:
        linear-gradient(
            to bottom,

            rgba(10,15,27,0) 0%,

            rgba(10,15,27,.08) 15%,

            rgba(10,15,27,.25) 35%,

            rgba(10,15,27,.60) 65%,

            rgba(10,15,27,.93) 88%,

            rgba(10,15,27,.99) 100%
        );

    backdrop-filter: blur(1.5px);

    -webkit-backdrop-filter: blur(1.5px);

    opacity: 1;

    visibility: visible;

    transition:
        opacity .25s ease,
        visibility .25s ease;
}


/* Hide fade when open */

.description-expand-wrapper:not(.is-collapsed)
.description-fade {

    opacity: 0;

    visibility: hidden;
}



/* =========================================================
   DOWN ARROW
   ========================================================= */

.description-open-btn {

    position: absolute;

    left: 50%;
    bottom: 16px;

    transform: translateX(-50%);

    z-index: 20;

    width: 48px;
    height: 48px;

    padding: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    border:
        1px solid
        rgba(20, 184, 166, .55);

    background:
        rgba(10,15,27,.96);

    color:
        var(--ui-accent);

    font-size: 20px;

    cursor: pointer;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .50),
        0 0 0 5px rgba(20, 184, 166, .07);

    transition:
        transform .2s ease,
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}


.description-open-btn:hover {

    transform:
        translateX(-50%)
        translateY(3px);

    background:
        rgba(20, 184, 166, .20);

    color: #fff;

    border-color:
        rgba(20, 184, 166, .85);

    box-shadow:
        0 12px 35px rgba(0, 0, 0, .55),
        0 0 0 6px rgba(20, 184, 166, .10);
}


/* Hide down arrow when open */

.description-expand-wrapper:not(.is-collapsed)
.description-open-btn {

    display: none;
}



/* =========================================================
   HEADER UP ARROW
   ========================================================= */

.description-header-close {

    display: none;

    flex: 0 0 auto;

    width: 42px;
    height: 42px;

    padding: 0;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    border:
        1px solid
        rgba(20, 184, 166, .50);

    background:
        rgba(20, 184, 166, .10);

    color:
        var(--ui-accent);

    font-size: 18px;

    cursor: pointer;

    box-shadow:
        0 5px 15px rgba(0, 0, 0, .20);

    transition:
        transform .2s ease,
        background .2s ease,
        border-color .2s ease,
        color .2s ease,
        box-shadow .2s ease;
}


/* JS shows this after opening */

.description-header-close.show {

    display: flex;

    animation:
        descriptionHeaderButtonIn
        .25s ease;
}


.description-header-close:hover {

    transform:
        translateY(-2px);

    background:
        rgba(20, 184, 166, .20);

    border-color:
        rgba(20, 184, 166, .85);

    color: #fff;

    box-shadow:
        0 8px 20px rgba(0, 0, 0, .30);
}



/* =========================================================
   HEADER ARROW ANIMATION
   ========================================================= */

@keyframes descriptionHeaderButtonIn {

    from {

        opacity: 0;

        transform:
            translateY(5px)
            scale(.85);
    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }

}



/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {


    .description-expand-wrapper.is-collapsed
    .description-expand-content {

        max-height: 420px;
    }


    .description-fade {

        height: 140px;
    }


    .description-open-btn {

        width: 44px;
        height: 44px;

        font-size: 18px;

        bottom: 14px;
    }


    .description-header-close {

        width: 38px;
        height: 38px;

        font-size: 16px;
    }


    .modern-description-subtitle {

        font-size: 12px;
    }

}
</style>