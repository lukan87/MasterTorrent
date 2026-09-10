<style>
/* =========================================================
   FILEIPLAY — FORUM INDEX
   Bootstrap 5 friendly / dark glass UI
   ========================================================= */

/* ---------- Forum Header ---------- */

.forum-index-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 2rem;
    padding: 1.75rem 0 1.5rem;
    border-bottom: 1px solid var(--ui-border);
}

.forum-header-copy {
    min-width: 0;
}

.forum-eyebrow {
    display: inline-flex;
    align-items: center;
    margin-bottom: .55rem;
    color: var(--ui-accent);
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
}

.forum-eyebrow i {
    font-size: .9rem;
}

.forum-page-title {
    margin: 0;
    color: #f8fafc;
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 800;
    line-height: 1.05;
    letter-spacing: -.035em;
}

.forum-page-subtitle {
    max-width: 720px;
    margin: .7rem 0 0;
    color: var(--ui-text-muted);
    font-size: .98rem;
    line-height: 1.65;
}

/* ---------- Add Category Button ---------- */

.forum-add-category-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    padding: .7rem 1rem;
    color: #062523;
    background: linear-gradient(
        135deg,
        var(--ui-accent),
        var(--ui-accent-strong)
    );
    border: 1px solid rgba(99, 210, 198, .35);
    border-radius: .7rem;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .22);
    font-size: .86rem;
    font-weight: 700;
    text-decoration: none;
    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        filter 160ms ease;
}

.forum-add-category-btn:hover {
    color: #031716;
    filter: brightness(1.06);
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
}

.forum-add-category-btn:focus-visible {
    outline: 0;
    box-shadow:
        0 0 0 .2rem rgba(99, 210, 198, .18),
        0 10px 25px rgba(0, 0, 0, .25);
}

/* ---------- Category List ---------- */

.forum-category-list {
    display: flex;
    flex-direction: column;
    gap: .85rem;
    margin-top: 1.5rem;
}

/* ---------- Category Link ---------- */

.forum-category-link {
    display: block;
    color: inherit;
    text-decoration: none;
}

.forum-category-link:focus-visible {
    outline: 0;
}

/* ---------- Category Card ---------- */

.forum-category-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1rem;
    min-height: 104px;
    padding: 1.15rem 1.25rem;
    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .94),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 10px 28px rgba(0, 0, 0, .16),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    transition:
        transform 180ms ease,
        border-color 180ms ease,
        box-shadow 180ms ease,
        background .22s ease;
}

/* subtle light at the top */
.forum-category-card::before {
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
    pointer-events: none;
}

/* teal accent line */
.forum-category-card::after {
    content: "";
    position: absolute;
    top: 16px;
    bottom: 16px;
    left: 0;
    width: 3px;

    background: linear-gradient(
        180deg,
        var(--ui-accent),
        var(--ui-accent-strong)
    );

    border-radius: 0 4px 4px 0;
    opacity: .75;
    transition: opacity 180ms ease, width 180ms ease;
}

.forum-category-link:hover .forum-category-card {
    transform: translateY(-3px);

    border-color: rgba(99, 210, 198, .22);

    background:
        linear-gradient(
            135deg,
            rgba(27, 43, 61, .96),
            rgba(16, 27, 46, .92)
        );

    box-shadow:
        0 16px 34px rgba(0, 0, 0, .24),
        0 0 0 1px rgba(99, 210, 198, .04);
}

.forum-category-link:hover .forum-category-card::after {
    width: 4px;
    opacity: 1;
}

.forum-category-link:focus-visible .forum-category-card {
    border-color: rgba(99, 210, 198, .45);
    box-shadow:
        0 0 0 .2rem rgba(99, 210, 198, .12),
        0 14px 30px rgba(0, 0, 0, .22);
}

/* ---------- Category Icon ---------- */

.forum-category-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 52px;
    width: 52px;
    height: 52px;

    color: var(--ui-accent);

    background:
        linear-gradient(
            135deg,
            rgba(99, 210, 198, .14),
            rgba(61, 179, 167, .05)
        );

    border: 1px solid rgba(99, 210, 198, .15);
    border-radius: .85rem;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, .04),
        0 8px 18px rgba(0, 0, 0, .12);

    transition:
        transform 180ms ease,
        background 180ms ease,
        border-color 180ms ease;
}

.forum-category-icon i {
    font-size: 1.35rem;
}

.forum-category-link:hover .forum-category-icon {
    transform: scale(1.05);
    background:
        linear-gradient(
            135deg,
            rgba(99, 210, 198, .2),
            rgba(61, 179, 167, .08)
        );
    border-color: rgba(99, 210, 198, .28);
}

/* ---------- Category Content ---------- */

.forum-category-content {
    flex: 1 1 auto;
    min-width: 0;
}

.forum-category-title-row {
    display: flex;
    align-items: center;
    gap: .75rem;
}

.forum-category-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.35;
    transition: color 160ms ease;
}

.forum-category-link:hover .forum-category-title {
    color: #ffffff;
}

.forum-category-description {
    margin: .3rem 0 0;
    color: var(--ui-text-muted);
    font-size: .85rem;
    line-height: 1.55;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.forum-category-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;

    width: 28px;
    height: 28px;

    color: #7890a9;
    background: rgba(148, 163, 184, .055);
    border: 1px solid rgba(148, 163, 184, .08);
    border-radius: 50%;

    transition:
        transform 180ms ease,
        color 180ms ease,
        background 180ms ease;
}

.forum-category-arrow i {
    font-size: .78rem;
}

.forum-category-link:hover .forum-category-arrow {
    color: var(--ui-accent);
    background: rgba(99, 210, 198, .09);
    transform: translateX(3px);
}

/* ---------- Topic Count ---------- */

.forum-topic-count {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    flex: 0 0 90px;
    padding-left: .75rem;
    border-left: 1px solid var(--ui-border);
}

.forum-topic-count strong {
    color: #e8f1f8;
    font-size: 1.1rem;
    font-weight: 750;
    line-height: 1.2;
}

.forum-topic-count span {
    margin-top: .2rem;
    color: #71859d;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

/* ---------- Empty State ---------- */

.forum-empty-state {
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

.forum-empty-state h3 {
    margin-bottom: .4rem;
    color: #e8f1f8;
    font-size: 1.05rem;
    font-weight: 700;
}

.forum-empty-state p {
    margin: 0;
    color: var(--ui-text-muted);
    font-size: .86rem;
}

/* =========================================================
   DELETED CATEGORIES
   ========================================================= */

.forum-deleted-categories {
    padding-top: 1.5rem;
    border-top: 1px solid var(--ui-border);
}

.forum-deleted-categories h2,
.forum-deleted-categories h3 {
    color: #e8f1f8;
}

.forum-deleted-categories .forum-category-card {
    background:
        linear-gradient(
            135deg,
            rgba(65, 29, 38, .68),
            rgba(31, 23, 35, .76)
        );

    border-color: rgba(220, 53, 69, .15);
}

.forum-deleted-categories .forum-category-card::after {
    background: linear-gradient(
        180deg,
        #dc3545,
        #a71d2a
    );
}

.forum-deleted-categories .forum-category-icon {
    color: #ff8d99;
    background: rgba(220, 53, 69, .09);
    border-color: rgba(220, 53, 69, .15);
}

.forum-deleted-categories .forum-category-title {
    color: #f3d9dc;
}

.forum-deleted-categories .forum-category-description {
    color: #b99ca1;
}

.forum-deleted-categories .forum-category-link:hover .forum-category-card {
    border-color: rgba(220, 53, 69, .3);
    background:
        linear-gradient(
            135deg,
            rgba(73, 31, 41, .76),
            rgba(35, 24, 37, .82)
        );
}

/* ---------- Deleted Action Buttons ---------- */

.forum-deleted-categories .btn {
    position: relative;
    z-index: 2;
}

.forum-deleted-categories form {
    position: relative;
    z-index: 3;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 767.98px) {

    .forum-index-header {
        align-items: stretch;
        flex-direction: column;
        gap: 1.15rem;
        padding-top: 1rem;
    }

    .forum-page-title {
        font-size: 2rem;
    }

    .forum-page-subtitle {
        font-size: .9rem;
    }

    .forum-add-category-btn {
        align-self: flex-start;
    }

    .forum-category-card {
        min-height: 0;
        padding: 1rem;
        gap: .85rem;
    }

    .forum-category-icon {
        flex-basis: 46px;
        width: 46px;
        height: 46px;
    }

    .forum-category-icon i {
        font-size: 1.15rem;
    }

    .forum-category-title {
        font-size: .96rem;
    }

    .forum-category-description {
        font-size: .8rem;
    }

    .forum-topic-count {
        flex-basis: 65px;
        padding-left: .6rem;
    }

    .forum-topic-count strong {
        font-size: .95rem;
    }

    .forum-topic-count span {
        font-size: .6rem;
    }

    .forum-category-arrow {
        display: none;
    }
}

@media (max-width: 575.98px) {

    .forum-index-header {
        margin-bottom: .25rem;
    }

    .forum-page-title {
        font-size: 1.8rem;
    }

    .forum-category-card {
        align-items: flex-start;
    }

    .forum-category-icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
        border-radius: .7rem;
    }

    .forum-category-icon i {
        font-size: 1rem;
    }

    .forum-topic-count {
        flex-basis: auto;
        min-width: 52px;
    }

    .forum-topic-count span {
        display: none;
    }

    .forum-topic-count strong {
        font-size: .85rem;
    }
}

@media (max-width: 419.98px) {

    .forum-category-card {
        padding: .85rem;
        gap: .7rem;
    }

    .forum-category-icon {
        flex-basis: 38px;
        width: 38px;
        height: 38px;
    }

    .forum-category-title {
        font-size: .9rem;
    }

    .forum-category-description {
        font-size: .75rem;
        line-height: 1.45;
    }

    .forum-topic-count {
        min-width: 42px;
        padding-left: .45rem;
    }
}

/* ---------- Reduced Motion ---------- */

@media (prefers-reduced-motion: reduce) {

    .forum-category-card,
    .forum-category-icon,
    .forum-category-arrow,
    .forum-add-category-btn {
        transition: none;
    }
}

/* =========================================================
   SEARCH BAR
   ========================================================= */

.forum-search-bar {
    display: flex;
    gap: 8px;
    align-items: center;
}

.forum-search-bar .form-control {
    background: var(--input-bg, rgba(255, 255, 255, 0.05));
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
    color: var(--text-primary, #e2e8f0);
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 0.9rem;
    max-width: 300px;
    transition: border-color 0.2s ease;
}

.forum-search-bar .form-control:focus {
    border-color: var(--accent-color, #3b82f6);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}

.forum-search-bar .btn-search {
    background: var(--accent-color, #3b82f6);
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s ease;
}

.forum-search-bar .btn-search:hover {
    background: var(--accent-hover, #2563eb);
}

.forum-sort-btn {
    padding: 4px 12px;
    font-size: 0.82rem;
    border-radius: 20px;
    background: transparent;
    color: var(--text-secondary, #94a3b8);
    border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
    text-decoration: none;
    transition: all 0.2s ease;
}

.forum-sort-btn:hover {
    color: var(--text-primary, #e2e8f0);
    border-color: var(--accent-color, #3b82f6);
    background: rgba(59, 130, 246, 0.08);
}
</style>