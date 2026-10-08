<style>

/* =========================================================
   ACTOR PAGE
========================================================= */

.actor-page {
    --actor-bg: var(--theme-surface, #0b1019);
    --actor-panel: var(--theme-surface, #101622);
    --actor-panel-hover: var(--theme-surface, #131b2a);
    --actor-border: var(--theme-border, rgba(148, 163, 184, .16));
    --actor-border-hover: var(--theme-teal-border, rgba(34, 211, 238, .35));
    --actor-accent: var(--theme-teal-text, #67e8f9);
    --actor-accent-dark: var(--theme-teal-text, #22d3ee);
    --actor-text: var(--theme-text, #e2e8f0);
    --actor-muted: var(--theme-muted, #94a3b8);

    width: 100%;
    max-width: 1500px;

    margin: 0 auto;

    padding:
        24px
        clamp(12px, 2vw, 28px)
        40px;

    color: var(--actor-text);

    box-sizing: border-box;
}

.actor-page *,
.actor-page *::before,
.actor-page *::after {
    box-sizing: border-box;
}

.actor-page a {
    text-decoration: none;
}

.actor-page :focus-visible {
    outline: 2px solid var(--actor-accent);
    outline-offset: 3px;
}


/* =========================================================
   BOOTSTRAP ROW SAFETY

   Important:
   Prevent horizontal overflow inside AdminLTE.
========================================================= */

.actor-page > .row {
    margin-left: calc(-.5 * var(--bs-gutter-x));
    margin-right: calc(-.5 * var(--bs-gutter-x));

    max-width: none;
}

.actor-page .row > * {
    min-width: 0;
}


/* =========================================================
   BREADCRUMB
========================================================= */

.actor-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .55rem;

    margin: 0 0 14px;

    font-size: var(--site-font-body, 13px);

    color: var(--actor-muted);
}

.actor-breadcrumb a {
    display: inline-flex;
    align-items: center;

    color: var(--actor-accent);
}

.actor-breadcrumb a:hover {
    color: var(--theme-text, #ffffff);
}

.actor-breadcrumb > i {
    font-size: .6rem;

    opacity: .55;
}


/* =========================================================
   HERO
========================================================= */

.actor-hero {
    position: relative;

    width: 100%;

    overflow: hidden;

    margin-bottom: 24px;

    padding:
        clamp(22px, 3vw, 38px);

    border:
        1px solid var(--actor-border);

    border-top:
        3px solid var(--actor-accent-dark);

    border-radius: 16px;

    background:
        radial-gradient(
            circle at 90% 10%,
            var(--theme-teal-soft, rgba(34, 211, 238, .15)),
            transparent 32%
        ),
        radial-gradient(
            circle at 5% 100%,
            var(--theme-blue-soft, rgba(59, 130, 246, .09)),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            var(--theme-surface, #101622) 0%,
            var(--theme-surface, #0b111c) 100%
        );

    box-shadow:
        0 10px 30px var(--theme-shadow, rgba(0, 0, 0, .16));
}

.actor-hero::after {
    content: "";

    position: absolute;

    width: 280px;
    height: 280px;

    right: -120px;
    top: -150px;

    border-radius: 50%;

    background:
        var(--theme-teal-soft, rgba(103, 232, 249, .05));

    pointer-events: none;
}

.actor-hero-content {
    position: relative;

    z-index: 1;
}

.actor-eyebrow {
    display: block;

    margin-bottom: 6px;

    color: var(--actor-accent);

    font-size: var(--site-font-small, 13px);
    font-weight: 800;

    letter-spacing: .15em;
}

.actor-hero h1 {
    margin: 0 0 20px;

    color: var(--theme-text, #f8fafc);

    font-size:
        clamp(2rem, 4vw, 3.2rem);

    font-weight: 800;

    line-height: 1.05;

    overflow-wrap: anywhere;
}


/* =========================================================
   HERO STATS
========================================================= */

.actor-stats {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 12px;
}

.actor-stat {
    display: flex;
    flex-direction: column;

    min-width: 100px;

    padding: 10px 15px;

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .12));

    border-radius: 10px;

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.0175));

    transition:
        background .2s ease,
        border-color .2s ease,
        transform .2s ease;
}

.actor-stat:hover {
    transform: translateY(-2px);

    background:
        var(--theme-teal-soft, rgba(34, 211, 238, .06));

    border-color:
        var(--theme-teal-border, rgba(34, 211, 238, .25));
}

.actor-stat strong {
    color: var(--theme-text, #ffffff);

    font-size: 1.25rem;

    line-height: 1.1;
}

.actor-stat span {
    margin-top: 4px;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}


/* =========================================================
   SIDEBAR
========================================================= */

.actor-sidebar {
    display: flex;
    flex-direction: column;

    width: 100%;

    gap: 18px;
}


/* =========================================================
   PORTRAIT
========================================================= */

.actor-portrait {
    position: relative;

    width: 100%;

    aspect-ratio: 2 / 3;

    overflow: hidden;

    border:
        1px solid var(--actor-border);

    border-radius: 15px;

    background: var(--theme-surface, #0e1521);

    box-shadow:
        0 10px 30px var(--theme-shadow, rgba(0, 0, 0, .2));
}

.actor-portrait img {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
}

.actor-no-photo {
    width: 100%;
    height: 100%;

    display: flex;
    flex-direction: column;

    align-items: center;
    justify-content: center;

    gap: 10px;

    color: var(--actor-muted);
}

.actor-no-photo i {
    font-size: 5rem;

    opacity: .5;
}

.actor-no-photo span {
    font-size: var(--site-font-body, 13px);
}


/* =========================================================
   MAIN CONTENT
========================================================= */

.actor-main {
    display: flex;
    flex-direction: column;

    width: 100%;
    min-width: 0;

    gap: 18px;
}


/* =========================================================
   PANELS
========================================================= */

.actor-panel {
    width: 100%;
    min-width: 0;

    padding:
        clamp(16px, 2vw, 22px);

    overflow: hidden;

    border:
        1px solid var(--actor-border);

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            var(--theme-surface, #101724) 0%,
            var(--theme-surface, #0e1420) 100%
        );

    box-shadow:
        0 8px 24px var(--theme-shadow, rgba(0, 0, 0, .08));
}

.actor-panel-title {
    display: flex;
    align-items: center;

    gap: 9px;
}

.actor-panel-title > i {
    color: var(--actor-accent);

    font-size: .9rem;
}

.actor-panel-title h2 {
    margin: 0;

    color: var(--theme-text, #f8fafc);

    font-size: 1rem;

    font-weight: 700;
}


/* =========================================================
   BIOGRAPHY
========================================================= */

.actor-biography {
    min-height: 140px;
}

.actor-bio-text {
    margin-top: 15px;

    max-width: 100%;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-body, 13px);

    line-height: 1.8;

    white-space: pre-line;

    overflow-wrap: break-word;
    word-break: normal;
}


/* =========================================================
   PERSONAL INFORMATION
========================================================= */

.actor-details dl {
    margin: 16px 0 0;
}

.actor-detail {
    padding: 11px 0;

    border-bottom:
        1px solid var(--theme-border, rgba(148, 163, 184, .08));
}

.actor-detail:first-child {
    padding-top: 0;
}

.actor-detail:last-child {
    padding-bottom: 0;

    border-bottom: 0;
}

.actor-detail dt {
    margin: 0 0 4px;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: .04em;
}

.actor-detail dd {
    margin: 0;

    color: var(--theme-text, #f1f5f9);

    font-size: var(--site-font-body, 13px);

    line-height: 1.55;

    overflow-wrap: anywhere;
}

.actor-muted {
    color: var(--actor-muted);
}


/* =========================================================
   TAGS
========================================================= */

.actor-tags {
    display: flex;
    flex-wrap: wrap;

    gap: 5px;
}

.actor-tags span {
    display: inline-flex;

    padding: 3px 7px;

    color: var(--actor-accent);

    font-size: var(--site-font-small, 13px);

    border:
        1px solid var(--theme-teal-border, rgba(34, 211, 238, .18));

    border-radius: 5px;

    background:
        var(--theme-teal-soft, rgba(34, 211, 238, .07));
}


/* =========================================================
   ALIASES
========================================================= */

.actor-aliases {
    list-style: none;

    padding: 0;
    margin: 0;
}

.actor-aliases li {
    padding: 2px 0;
}


/* =========================================================
   SOCIAL LINKS
========================================================= */

.actor-socials {
    display: flex;
    flex-direction: column;

    gap: 7px;

    margin-top: 15px;
}

.actor-socials a {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    padding: 9px 11px;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-body, 13px);

    border:
        1px solid var(--theme-border, rgba(148, 163, 184, .12));

    border-radius: 7px;

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.0105));

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease;
}

.actor-socials a:hover {
    color: var(--actor-accent);

    background:
        var(--theme-teal-soft, rgba(34, 211, 238, .05));

    border-color:
        var(--theme-teal-border, rgba(34, 211, 238, .25));
}


/* =========================================================
   SECTION HEADINGS
========================================================= */

.actor-section-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 16px;
}

.actor-section-heading p {
    margin:
        6px 0 0 25px;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}

.actor-scroll-hint {
    display: flex;
    align-items: center;

    gap: 5px;

    flex-shrink: 0;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}

.actor-scroll-hint i {
    color: var(--actor-accent);
}


/* =========================================================
   COUNT
========================================================= */

.actor-count {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    margin-left: 5px;

    padding: 2px 6px;

    color: var(--actor-accent);

    font-size: var(--site-font-small, 13px);

    border-radius: 5px;

    background:
        var(--theme-teal-soft, rgba(34, 211, 238, .09));
}


/* =========================================================
   KNOWN FOR
========================================================= */

.actor-known-list {
    display: flex;

    width: 100%;
    max-width: 100%;

    gap: 12px;

    padding:
        0 0 10px;

    overflow-x: auto;
    overflow-y: hidden;

    scroll-snap-type:
        x proximity;

    overscroll-behavior-inline:
        contain;
}

.actor-known-card {
    flex:
        0 0 clamp(115px, 14vw, 150px);

    min-width: 0;

    color: var(--actor-text);

    scroll-snap-align: start;
}

.actor-known-poster {
    position: relative;

    width: 100%;

    aspect-ratio: 2 / 3;

    overflow: hidden;

    border:
        1px solid var(--actor-border);

    border-radius: 8px;

    background: #141b26;

    transition:
        transform .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}

.actor-known-card:hover .actor-known-poster {
    transform:
        translateY(-3px);

    border-color:
        rgba(34, 211, 238, .35);

    box-shadow:
        0 8px 20px rgba(0, 0, 0, .25);
}

.actor-known-poster img {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
}

.actor-poster-placeholder {
    display: flex;

    width: 100%;
    height: 100%;

    align-items: center;
    justify-content: center;

    color: var(--actor-muted);
}

.actor-poster-placeholder i {
    font-size: 2rem;
}

.actor-known-info {
    padding-top: 8px;
}

.actor-known-info h3 {
    margin: 0 0 4px;

    color: var(--theme-text, #e2e8f0);

    font-size: var(--site-font-body, 13px);

    font-weight: 600;

    line-height: 1.35;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.actor-known-card:hover h3 {
    color: var(--actor-accent);
}

.actor-known-info > span {
    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}

.actor-dot {
    margin: 0 2px;

    opacity: .6;
}


/* =========================================================
   CREDIT LIST
========================================================= */

.actor-credit-list {
    display: flex;
    flex-direction: column;

    width: 100%;

    gap: 8px;
}


/*
 * Keeps approximately 10 credits visible.
 */
.actor-credit-list-scroll {
    width: 100%;
    max-width: 100%;

    max-height: 872px;

    overflow-y: auto;
    overflow-x: hidden;

    padding-right: 6px;

    scrollbar-gutter: stable;
}

.actor-credit {
    display: flex;
    align-items: center;

    width: 100%;
    min-width: 0;

    height: 80px;

    gap: 11px;

    padding: 6px 10px;

    color: var(--actor-text);

    border:
        1px solid var(--actor-border);

    border-radius: 8px;

    background:
        var(--theme-surface-alt, rgba(255,255,255,0.0154));

    transition:
        background .2s ease,
        border-color .2s ease;
}

.actor-credit:hover {
    color:  var(--theme-text, #f8fafc);

    background:
        var(--theme-teal-soft, rgba(34, 211, 238, .05));

    border-color:
        var(--theme-teal-border, rgba(34, 211, 238, .28));
}


/* =========================================================
   CREDIT POSTER
========================================================= */

.actor-credit-poster {
    display: grid;

    place-items: center;

    width: 44px;
    height: 66px;

    flex: 0 0 44px;

    overflow: hidden;

    border-radius: 5px;

    background: #141b26;

    color: var(--actor-muted);
}

.actor-credit-poster img {
    display: block;

    width: 100%;
    height: 100%;

    object-fit: cover;
}


/* =========================================================
   CREDIT COPY
========================================================= */

.actor-credit-copy {
    flex: 1 1 auto;

    width: 0;
    min-width: 0;
}

.actor-credit-copy h3 {
    margin: 0 0 3px;

    color: var(--theme-text, #e2e8f0);

    font-size: var(--site-font-body, 13px);

    font-weight: 600;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.actor-credit:hover .actor-credit-copy h3 {
    color: var(--actor-accent);
}

.actor-credit-copy p {
    margin: 0;

    color: var(--theme-text, #b6c3d4);

    font-size: var(--site-font-small, 13px);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.actor-credit-copy span {
    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}


/* =========================================================
   CREDIT YEAR
========================================================= */

.actor-credit-year {
    flex: 0 0 auto;

    min-width: 55px;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-small, 13px);

    text-align: right;
}

.actor-credit-year small {
    display: block;

    margin-top: 4px;

    color: var(--theme-amber-text, #fbbf24);

    font-size: var(--site-font-small, 13px);
}

.actor-credit-arrow {
    flex: 0 0 auto;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);
}


/* =========================================================
   PHOTOS
========================================================= */

.actor-photo-list {
    display: flex;

    width: 100%;
    max-width: 100%;

    gap: 10px;

    overflow-x: auto;
    overflow-y: hidden;

    padding-bottom: 10px;

    scroll-snap-type:
        x proximity;
}

.actor-photo-list a {
    flex: 0 0 110px;

    scroll-snap-align: start;
}

.actor-photo-list img {
    display: block;

    width: 110px;
    height: 165px;

    object-fit: cover;

    border:
        1px solid var(--actor-border);

    border-radius: 8px;

    transition:
        transform .2s ease,
        border-color .2s ease;
}

.actor-photo-list a:hover img {
    transform:
        translateY(-3px);

    border-color:
        var(--theme-teal-border, rgba(34, 211, 238, .35));
}


/* =========================================================
   EMPTY
========================================================= */

.actor-empty {
    margin: 0;

    padding: 20px 5px;

    color: var(--actor-muted);

    font-size: var(--site-font-body, 13px);
}


/* =========================================================
   ATTRIBUTION
========================================================= */

.actor-attribution {
    margin: 0;

    color: var(--actor-muted);

    font-size: var(--site-font-small, 13px);

    line-height: 1.6;
}

.actor-attribution a {
    color: var(--actor-accent);
}


/* =========================================================
   SCROLLBARS
========================================================= */

.actor-known-list,
.actor-photo-list,
.actor-credit-list-scroll {
    scrollbar-width: thin;

    scrollbar-color:
        var(--theme-teal-border, #3197a7)
        var(--theme-border, rgba(20, 32, 51, .7));
}

.actor-page ::-webkit-scrollbar {
    width: 7px;
    height: 7px;
}

.actor-page ::-webkit-scrollbar-track {
    background:
        var(--theme-surface, rgba(13,21,33,.7));

    border-radius: 10px;
}

.actor-page ::-webkit-scrollbar-thumb {
    background: var(--theme-teal-soft, #3197a7);

    border-radius: 10px;
}

.actor-page ::-webkit-scrollbar-thumb:hover {
    background: var(--theme-teal-soft, #42b7ca);
}


/* =========================================================
   LARGE DESKTOP
========================================================= */

@media (min-width: 1600px) {

    .actor-page {
        max-width: 1550px;
    }

}


/* =========================================================
   MEDIUM DESKTOP
========================================================= */

@media (max-width: 1199.98px) {

    .actor-page {
        padding-left: 15px;
        padding-right: 15px;
    }

    .actor-known-card {
        flex-basis: 130px;
    }

}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 991.98px) {

    .actor-page {
        padding-top: 18px;
    }

    .actor-hero {
        margin-bottom: 18px;
    }

    .actor-panel {
        padding: 16px;
    }

    .actor-stat {
        min-width: 90px;
    }

    .actor-known-card {
        flex-basis: 120px;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767.98px) {

    .actor-page {
        padding:
            14px 10px 30px;
    }

    .actor-hero {
        padding: 20px 16px;

        border-radius: 12px;
    }

    .actor-hero h1 {
        font-size: 2rem;
    }

    .actor-stats {
        gap: 7px;
    }

    .actor-stat {
        flex: 1 1 30%;

        min-width: 85px;

        padding: 9px 10px;
    }

    .actor-stat strong {
        font-size: 1.1rem;
    }

    .actor-sidebar {
        max-width: 420px;

        margin:
            0 auto;
    }

    .actor-portrait {
        width: min(280px, 100%);

        margin:
            0 auto;
    }

    .actor-main {
        margin-top: 2px;
    }

    .actor-known-card {
        flex-basis: 120px;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 575.98px) {

    .actor-page {
        padding-left: 6px;
        padding-right: 6px;
    }

    .actor-breadcrumb {
        padding-left: 4px;
        padding-right: 4px;
    }

    .actor-panel {
        padding: 14px;

        border-radius: 10px;
    }

    .actor-section-heading {
        gap: 8px;
    }

    .actor-scroll-hint {
        display: none;
    }

    .actor-section-heading p {
        margin-left: 0;
    }

    .actor-known-card {
        flex-basis: 110px;
    }

    .actor-credit {
        gap: 8px;

        padding:
            6px 7px;
    }

    .actor-credit-year {
        min-width: 42px;

        font-size: var(--site-font-small, 13px);
    }

    .actor-credit-arrow {
        display: none;
    }

}


/* =========================================================
   VERY SMALL MOBILE
========================================================= */

@media (max-width: 420px) {

    .actor-stats {
        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));
    }

    .actor-stat {
        min-width: 0;
    }

    .actor-credit-year {
        display: none;
    }

}

</style>