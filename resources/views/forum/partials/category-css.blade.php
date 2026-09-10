<style>
/* =========================================================
   FILEIPLAY — FORUM CATEGORY
   Bootstrap 5 / Dark Glass / Teal UI
   ========================================================= */

/* ---------------------------------------------------------
   BREADCRUMB
   --------------------------------------------------------- */

.forum-breadcrumb {
    display: flex;
    align-items: center;
}

.forum-breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: .5rem;

    color: var(--ui-text-muted);
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;

    transition: color 160ms ease, transform 160ms ease;
}

.forum-breadcrumb-link i {
    color: var(--ui-accent);
    font-size: .9rem;
}

.forum-breadcrumb-link:hover {
    color: #f1f5f9;
    transform: translateX(-2px);
}

.forum-breadcrumb-link:focus-visible {
    outline: 0;
    color: var(--ui-accent);
}


/* ---------------------------------------------------------
   CATEGORY HEADER
   --------------------------------------------------------- */

.forum-category-header {
    position: relative;

    display: flex;
    align-items: center;
    gap: 1.15rem;

    padding: 1.35rem 1.5rem;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .94),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 12px 30px rgba(0, 0, 0, .18),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    overflow: hidden;
}

.forum-category-header::before {
    content: "";

    position: absolute;
    top: 0;
    left: 8%;
    right: 8%;

    height: 1px;

    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, .08),
        transparent
    );
}

.forum-category-header::after {
    content: "";

    position: absolute;
    top: 18px;
    bottom: 18px;
    left: 0;

    width: 3px;

    background: linear-gradient(
        180deg,
        var(--ui-accent),
        var(--ui-accent-strong)
    );

    border-radius: 0 4px 4px 0;
}


/* ---------------------------------------------------------
   CATEGORY ICON
   --------------------------------------------------------- */

.forum-category-header > div:first-child {
    flex: 0 0 auto;
}

.forum-category-header .forum-category-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 58px;
    height: 58px;

    color: var(--ui-accent);

    background:
        linear-gradient(
            135deg,
            rgba(99, 210, 198, .16),
            rgba(61, 179, 167, .05)
        );

    border: 1px solid rgba(99, 210, 198, .17);
    border-radius: .9rem;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .04),
        0 8px 20px rgba(0, 0, 0, .14);
}

.forum-category-header .forum-category-icon i {
    font-size: 1.45rem;
}


/* ---------------------------------------------------------
   CATEGORY TITLE
   --------------------------------------------------------- */

.forum-category-header .forum-category-title {
    margin: 0;

    color: #f8fafc;

    font-size: clamp(1.35rem, 3vw, 1.8rem);
    font-weight: 800;
    line-height: 1.15;

    letter-spacing: -.025em;
}

.forum-category-header .forum-category-description {
    max-width: 850px;

    margin-top: .4rem;

    color: var(--ui-text-muted);

    font-size: .86rem;
    line-height: 1.55;
}


/* ---------------------------------------------------------
   CATEGORY ACTIONS
   --------------------------------------------------------- */

.forum-category-header > .flex-grow-1 + .d-flex {
    flex: 0 0 auto;
}

.forum-category-header .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 38px;

    border-radius: .65rem;

    font-size: .78rem;
    font-weight: 700;

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        border-color 160ms ease,
        background 160ms ease;
}

.forum-category-header .btn:hover {
    transform: translateY(-2px);
}


/* New Topic */

.forum-new-topic-btn {
    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border: 1px solid rgba(99, 210, 198, .35);

    box-shadow:
        0 7px 18px rgba(0, 0, 0, .18);
}

.forum-new-topic-btn:hover {
    color: #031716;

    filter: brightness(1.06);

    box-shadow:
        0 10px 24px rgba(0, 0, 0, .25);
}


/* Edit */

.forum-edit-category-btn {
    color: #cbd5e1;

    background: rgba(148, 163, 184, .07);

    border: 1px solid rgba(148, 163, 184, .17);
}

.forum-edit-category-btn:hover {
    color: #fff;

    background: rgba(148, 163, 184, .13);

    border-color: rgba(148, 163, 184, .28);
}


/* Delete */

.forum-delete-category-btn {
    color: #ff9ca5;

    background: rgba(220, 53, 69, .07);

    border: 1px solid rgba(220, 53, 69, .18);
}

.forum-delete-category-btn:hover {
    color: #ffd5d9;

    background: rgba(220, 53, 69, .13);

    border-color: rgba(220, 53, 69, .3);
}


/* ---------------------------------------------------------
   TOPIC LIST
   --------------------------------------------------------- */

.forum-topic-list {
    display: flex;
    flex-direction: column;
    gap: .65rem;
}


/* ---------------------------------------------------------
   TOPIC ROW
   --------------------------------------------------------- */

.forum-topic-row {
    position: relative;

    display: grid;

    grid-template-columns: 48px minmax(0, 1fr) auto 205px 24px;

    align-items: center;

    gap: 1rem;

    min-height: 88px;

    padding: .9rem 1rem;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .9),
            rgba(15, 23, 42, .8)
        );

    border: 1px solid var(--ui-border);
    border-radius: .9rem;

    box-shadow:
        0 7px 20px rgba(0, 0, 0, .13),
        inset 0 1px 0 rgba(255, 255, 255, .02);

    transition:
        transform 170ms ease,
        border-color 170ms ease,
        background 170ms ease,
        box-shadow 170ms ease;
}

.forum-topic-row:hover {
    transform: translateY(-2px);

    border-color: rgba(99, 210, 198, .2);

    background:
        linear-gradient(
            135deg,
            rgba(27, 43, 61, .94),
            rgba(16, 27, 46, .88)
        );

    box-shadow:
        0 12px 27px rgba(0, 0, 0, .2);
}


/* ---------------------------------------------------------
   TOPIC ICON
   --------------------------------------------------------- */

.forum-topic-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 42px;
    height: 42px;

    color: #8298ad;

    background: rgba(148, 163, 184, .06);

    border: 1px solid rgba(148, 163, 184, .1);
    border-radius: .7rem;

    transition:
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.forum-topic-row:hover .forum-topic-icon {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .08);

    border-color: rgba(99, 210, 198, .16);
}

.forum-topic-icon i {
    font-size: 1rem;
}


/* ---------------------------------------------------------
   PINNED TOPIC
   --------------------------------------------------------- */

.forum-topic-row.topic-pinned {
    border-color: rgba(99, 210, 198, .15);

    background:
        linear-gradient(
            135deg,
            rgba(24, 44, 58, .94),
            rgba(15, 28, 45, .84)
        );
}

.forum-topic-row.topic-pinned .forum-topic-icon {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .1);

    border-color: rgba(99, 210, 198, .18);
}


/* ---------------------------------------------------------
   LOCKED TOPIC
   --------------------------------------------------------- */

.forum-topic-row.topic-locked .forum-topic-icon {
    color: #aeb9c6;

    background: rgba(148, 163, 184, .055);
}


/* ---------------------------------------------------------
   TOPIC MAIN
   --------------------------------------------------------- */

.forum-topic-main {
    min-width: 0;
}

.forum-topic-title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .45rem .65rem;
}

.forum-topic-link {
    min-width: 0;

    color: #e8f1f8;

    font-size: 1.08rem;
    font-weight: 700;
    line-height: 1.35;

    text-decoration: none;

    transition: color 160ms ease;
}

.forum-topic-link:hover {
    color: var(--ui-accent);
}

.forum-topic-link:focus-visible {
    outline: 0;
    color: var(--ui-accent);
}


/* ---------------------------------------------------------
   TOPIC BADGES
   --------------------------------------------------------- */

.forum-topic-badges {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .3rem;
}

.forum-topic-badge {
    display: inline-flex;
    align-items: center;

    padding: .22rem .48rem;

    border-radius: .4rem;

    font-size: .62rem;
    font-weight: 700;
    line-height: 1.2;

    white-space: nowrap;
}

.forum-topic-badge i {
    font-size: .6rem;
}


/* Pinned */

.forum-topic-badge.pinned {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .09);

    border: 1px solid rgba(99, 210, 198, .15);
}


/* New replies */

.forum-topic-badge.new {
    color: #8ee7ff;

    background: rgba(56, 189, 248, .08);

    border: 1px solid rgba(56, 189, 248, .14);
}


/* Locked */

.forum-topic-badge.locked {
    color: #c5ced9;

    background: rgba(148, 163, 184, .07);

    border: 1px solid rgba(148, 163, 184, .13);
}


/* ---------------------------------------------------------
   STARTED BY
   --------------------------------------------------------- */

.forum-topic-started {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    margin-top: .35rem;

    color: #71859d;

    font-size: .85rem;
    line-height: 1.4;
}

.forum-topic-started i {
    color: #718ca3;
}

.forum-topic-started strong {
    margin-left: .2rem;

    color: #aebfd0;

    font-weight: 600;
}


/* ---------------------------------------------------------
   STATS
   --------------------------------------------------------- */

.forum-topic-stats {
    display: flex;
    align-items: stretch;
    gap: .75rem;
}

.forum-topic-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    min-width: 58px;
    padding: .3rem .5rem;

    border-left: 1px solid var(--ui-border);
}

.forum-topic-stat strong {
    color: #e1ebf4;

    font-size: 1rem;
    font-weight: 700;
    line-height: 1.2;
}

.forum-topic-stat span {
    margin-top: .18rem;

    color: #6f849b;

    font-size: .59rem;
    font-weight: 700;

    letter-spacing: .06em;
    text-transform: uppercase;

    white-space: nowrap;
}

.forum-topic-stat span i {
    font-size: .8rem;
}


/* ---------------------------------------------------------
   LAST POST
   --------------------------------------------------------- */

.forum-last-post {
    min-width: 0;

    padding-left: 1rem;

    border-left: 1px solid var(--ui-border);
}

.forum-last-post-link {
    display: block;

    color: inherit;

    text-decoration: none;

    transition: color 160ms ease;
}

.forum-last-post-label {
    margin-bottom: .2rem;

    color: #71859d;

    font-size: .8rem;
    font-weight: 700;

    letter-spacing: .07em;
    text-transform: uppercase;
}

.forum-last-post-label i {
    color: var(--ui-accent);
}

.forum-last-post-user {
    overflow: hidden;

    color: #b9c9d8;

    font-size: .89rem;
    font-weight: 600;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.forum-last-post-user i {
    color: #718ca3;
}

.forum-last-post-time {
    margin-top: .15rem;

    color: #687d94;

    font-size: .83rem;

    white-space: nowrap;
}

.forum-last-post-time i {
    color: #718ca3;
}

.forum-last-post-link:hover .forum-last-post-user {
    color: var(--ui-accent);
}


/* ---------------------------------------------------------
   ARROW
   --------------------------------------------------------- */

.forum-topic-arrow {
    display: flex;
    align-items: center;
    justify-content: center;

    color: #52677d;

    transition:
        color 160ms ease,
        transform 160ms ease;
}

.forum-topic-arrow i {
    font-size: .75rem;
}

.forum-topic-row:hover .forum-topic-arrow {
    color: var(--ui-accent);
    transform: translateX(3px);
}


/* ---------------------------------------------------------
   EMPTY STATE
   --------------------------------------------------------- */

.forum-empty-state {
    margin-top: .25rem;
    padding: 4rem 1.5rem;

    text-align: center;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .82),
            rgba(15, 23, 42, .72)
        );

    border: 1px dashed rgba(148, 163, 184, .2);
    border-radius: 1rem;
}

.forum-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 64px;
    height: 64px;

    margin: 0 auto 1rem;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .08);

    border: 1px solid rgba(99, 210, 198, .14);
    border-radius: 50%;
}

.forum-empty-icon i {
    font-size: 1.5rem;
}

.forum-empty-state h4 {
    margin-bottom: .4rem;

    color: #e8f1f8;

    font-size: 1.05rem;
    font-weight: 700;
}

.forum-empty-state p {
    margin-bottom: 1.2rem;

    color: var(--ui-text-muted);

    font-size: .84rem;
}


/* ---------------------------------------------------------
   PAGINATION
   --------------------------------------------------------- */

.forum-pagination {
    display: flex;
    justify-content: center;

    padding-top: .5rem;
}

.forum-pagination .pagination {
    gap: .3rem;
    margin-bottom: 0;
}

.forum-pagination .page-link {
    min-width: 36px;
    min-height: 36px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    color: #aebfd0;

    background: rgba(22, 32, 51, .8);

    border: 1px solid var(--ui-border);

    border-radius: .55rem;

    font-size: .76rem;
    font-weight: 600;

    transition:
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease,
        transform 160ms ease;
}

.forum-pagination .page-link:hover {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .07);

    border-color: rgba(99, 210, 198, .2);

    transform: translateY(-1px);
}

.forum-pagination .page-item.active .page-link {
    color: #062523;

    background: var(--ui-accent);

    border-color: var(--ui-accent);

    font-weight: 700;
}

.forum-pagination .page-item.disabled .page-link {
    color: #4d6075;

    background: rgba(15, 23, 42, .55);

    border-color: rgba(148, 163, 184, .07);
}


/* ---------------------------------------------------------
   RESPONSIVE — TABLET
   --------------------------------------------------------- */

@media (max-width: 991.98px) {

    .forum-topic-row {
        grid-template-columns:
            44px
            minmax(0, 1fr)
            auto
            175px
            18px;

        gap: .8rem;
    }

    .forum-topic-stats {
        gap: .35rem;
    }

    .forum-topic-stat {
        min-width: 48px;
        padding-inline: .35rem;
    }

    .forum-last-post {
        padding-left: .75rem;
    }
}


/* ---------------------------------------------------------
   RESPONSIVE — MOBILE
   --------------------------------------------------------- */

@media (max-width: 767.98px) {

    .forum-category-header {
        align-items: flex-start;
        flex-wrap: wrap;

        padding: 1.15rem;
    }

    .forum-category-header .forum-category-icon {
        width: 50px;
        height: 50px;
    }

    .forum-category-header .forum-category-title {
        font-size: 1.35rem;
    }

    .forum-category-header > .flex-grow-1 {
        flex: 1 1 calc(100% - 70px);
    }

    .forum-category-header > .flex-grow-1 + .d-flex {
        flex: 1 1 100%;

        padding-top: .2rem;
    }

    .forum-category-header > .flex-grow-1 + .d-flex .btn {
        flex: 1 1 auto;
    }


    /* Topic becomes compact two-column layout */

    .forum-topic-row {
        grid-template-columns: 42px minmax(0, 1fr) 18px;

        gap: .75rem;

        padding: .85rem;
    }

    .forum-topic-icon {
        width: 38px;
        height: 38px;
    }

    .forum-topic-title-row {
        gap: .35rem;
    }

    .forum-topic-link {
        font-size: .88rem;
    }

    .forum-topic-started {
        font-size: .65rem;
    }

    .forum-topic-stats,
    .forum-last-post {
        grid-column: 2 / -1;
    }

    .forum-topic-stats {
        justify-content: flex-start;

        gap: .4rem;

        padding-top: .55rem;

        border-top: 1px solid var(--ui-border);
    }

    .forum-topic-stat {
        flex-direction: row;

        gap: .35rem;

        min-width: auto;

        padding: .25rem .5rem;

        border-left: 0;
    }

    .forum-topic-stat strong {
        font-size: .75rem;
    }

    .forum-topic-stat span {
        margin-top: 0;

        font-size: .57rem;
    }

    .forum-last-post {
        padding-top: .55rem;
        padding-left: 0;

        border-top: 1px solid var(--ui-border);
        border-left: 0;
    }

    .forum-topic-arrow {
        grid-column: 3;
        grid-row: 1;
    }
}


/* ---------------------------------------------------------
   RESPONSIVE — SMALL MOBILE
   --------------------------------------------------------- */

@media (max-width: 575.98px) {

    .forum-category-header {
        gap: .85rem;
    }

    .forum-category-header > .flex-grow-1 {
        flex-basis: calc(100% - 62px);
    }

    .forum-category-header > .flex-grow-1 + .d-flex {
        flex-direction: column;
    }

    .forum-category-header > .flex-grow-1 + .d-flex .btn {
        width: 100%;
    }

    .forum-topic-badges {
        width: 100%;
    }

    .forum-topic-badge {
        font-size: .58rem;
    }

    .forum-last-post-user {
        font-size: .68rem;
    }
}


/* ---------------------------------------------------------
   VERY SMALL DEVICES
   --------------------------------------------------------- */

@media (max-width: 419.98px) {

    .forum-category-header {
        padding: 1rem;
    }

    .forum-category-header .forum-category-icon {
        width: 44px;
        height: 44px;
    }

    .forum-category-header .forum-category-icon i {
        font-size: 1.1rem;
    }

    .forum-category-header .forum-category-title {
        font-size: 1.15rem;
    }

    .forum-category-header .forum-category-description {
        font-size: .76rem;
    }

    .forum-topic-row {
        grid-template-columns: 38px minmax(0, 1fr) 14px;

        padding: .7rem;

        gap: .6rem;
    }

    .forum-topic-icon {
        width: 34px;
        height: 34px;
    }

    .forum-topic-icon i {
        font-size: .82rem;
    }

    .forum-topic-link {
        font-size: .82rem;
    }

    .forum-topic-started {
        font-size: .61rem;
    }
}


/* ---------------------------------------------------------
   ACCESSIBILITY
   --------------------------------------------------------- */

.forum-category-header .btn:focus-visible,
.forum-topic-link:focus-visible,
.forum-last-post-link:focus-visible {
    outline: 0;

    box-shadow:
        0 0 0 .2rem rgba(99, 210, 198, .15);
}

@media (prefers-reduced-motion: reduce) {

    .forum-topic-row,
    .forum-topic-icon,
    .forum-topic-arrow,
    .forum-breadcrumb-link,
    .forum-category-header .btn,
    .forum-pagination .page-link {
        transition: none;
    }
}
</style>