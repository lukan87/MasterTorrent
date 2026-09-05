<style>

/* =========================================================
   FORUM INDEX
========================================================= */

.forum-index-header {

    display: flex;
    align-items: flex-end;
    justify-content: space-between;

    gap: 30px;

    padding: 10px 2px 25px;

}


/* =========================================================
   HEADER
========================================================= */

.forum-header-copy {
    max-width: 760px;
}

.forum-eyebrow {

    display: inline-flex;
    align-items: center;

    margin-bottom: 9px;

    color: #8b5cf6;

    font-size: .72rem;
    font-weight: 800;

    letter-spacing: .16em;

}

.forum-page-title {

    margin: 0;

    font-size: clamp(2.1rem, 4vw, 3.2rem);

    font-weight: 900;

    letter-spacing: -.045em;

    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #dbeafe 45%,
            #c4b5fd 100%
        );

    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

    background-clip: text;

}

.forum-page-subtitle {

    margin: 8px 0 0;

    color: rgba(255,255,255,.52);

    font-size: .95rem;

    line-height: 1.65;

}


/* =========================================================
   ADD CATEGORY BUTTON
========================================================= */

.forum-add-category-btn {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 12px 19px;

    border: 1px solid rgba(129,140,248,.30);

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,.95),
            rgba(124,58,237,.95)
        );

    color: #fff;

    font-size: .88rem;
    font-weight: 800;

    text-decoration: none;

    white-space: nowrap;

    box-shadow:
        0 10px 30px rgba(37,99,235,.20),
        inset 0 1px 0 rgba(255,255,255,.16);

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;

}

.forum-add-category-btn:hover {

    color: #fff;

    transform: translateY(-2px);

    border-color: rgba(165,180,252,.55);

    box-shadow:
        0 16px 35px rgba(37,99,235,.30),
        inset 0 1px 0 rgba(255,255,255,.20);

}


/* =========================================================
   CATEGORY LIST
========================================================= */

.forum-category-list {

    display: flex;

    flex-direction: column;

    gap: 12px;

}


/* =========================================================
   CATEGORY LINK
========================================================= */

.forum-category-link {

    display: block;

    color: inherit;

    text-decoration: none;

}


/* =========================================================
   CATEGORY CARD
========================================================= */

.forum-category-card {

    position: relative;

    display: flex;

    align-items: center;

    gap: 18px;

    min-height: 105px;

    padding: 20px 22px;

    overflow: hidden;

    border: 1px solid rgba(255,255,255,.075);

    border-radius: 17px;

    background:

        linear-gradient(
            135deg,
            rgba(255,255,255,.055),
            rgba(255,255,255,.018)
        );

    box-shadow:

        0 8px 25px rgba(0,0,0,.12),

        inset 0 1px 0 rgba(255,255,255,.035);

    transition:

        transform .22s ease,
        border-color .22s ease,
        box-shadow .22s ease,
        background .22s ease;

}

.forum-category-card::before {

    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 3px;
    height: 100%;

    background:
        linear-gradient(
            180deg,
            #3b82f6,
            #8b5cf6
        );

    opacity: 0;

    transition: opacity .22s ease;

}

.forum-category-link:hover .forum-category-card {

    transform: translateY(-3px);

    border-color: rgba(129,140,248,.28);

    background:

        linear-gradient(
            135deg,
            rgba(59,130,246,.075),
            rgba(139,92,246,.045)
        );

    box-shadow:

        0 16px 35px rgba(0,0,0,.18),

        0 0 0 1px rgba(99,102,241,.04);

}

.forum-category-link:hover .forum-category-card::before {
    opacity: 1;
}


/* =========================================================
   CATEGORY ICON
========================================================= */

.forum-category-icon {

    width: 54px;
    height: 54px;

    flex: 0 0 54px;

    display: flex;

    align-items: center;
    justify-content: center;

    border: 1px solid rgba(129,140,248,.16);

    border-radius: 15px;

    background:

        linear-gradient(
            135deg,
            rgba(59,130,246,.15),
            rgba(139,92,246,.13)
        );

    color: #a5b4fc;

    font-size: 1.35rem;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.06);

    transition: .22s ease;

}

.forum-category-link:hover .forum-category-icon {

    transform: scale(1.06);

    color: #c4b5fd;

    border-color: rgba(165,180,252,.30);

}


/* =========================================================
   CATEGORY CONTENT
========================================================= */

.forum-category-content {

    flex: 1 1 auto;

    min-width: 0;

}

.forum-category-title-row {

    display: flex;

    align-items: center;

    gap: 10px;

}

.forum-category-title {

    margin: 0;

    color: rgba(255,255,255,.94);

    font-size: 1.05rem;

    font-weight: 800;

    letter-spacing: -.015em;

    transition: color .2s ease;

}

.forum-category-link:hover .forum-category-title {
    color: #c4b5fd;
}

.forum-category-description {

    margin: 5px 0 0;

    color: rgba(255,255,255,.46);

    font-size: .84rem;

    line-height: 1.5;

    overflow: hidden;

    text-overflow: ellipsis;

}


/* =========================================================
   ARROW
========================================================= */

.forum-category-arrow {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 25px;
    height: 25px;

    color: rgba(255,255,255,.25);

    font-size: .75rem;

    transition:
        transform .2s ease,
        color .2s ease;

}

.forum-category-link:hover .forum-category-arrow {

    color: #a5b4fc;

    transform: translateX(3px);

}


/* =========================================================
   TOPIC COUNT
========================================================= */

.forum-topic-count {

    flex: 0 0 80px;

    display: flex;

    flex-direction: column;

    align-items: flex-end;

    justify-content: center;

    padding-left: 10px;

    border-left: 1px solid rgba(255,255,255,.07);

}

.forum-topic-count strong {

    color: rgba(255,255,255,.92);

    font-size: 1.05rem;

    font-weight: 800;

}

.forum-topic-count span {

    margin-top: 2px;

    color: rgba(255,255,255,.35);

    font-size: .68rem;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .06em;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.forum-empty-state {

    padding: 55px 25px;

    text-align: center;

    border: 1px dashed rgba(255,255,255,.10);

    border-radius: 17px;

    background: rgba(255,255,255,.018);

}

.forum-empty-icon {

    width: 58px;
    height: 58px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background: rgba(99,102,241,.10);

    color: #a5b4fc;

    font-size: 1.4rem;

}

.forum-empty-state h3 {

    margin-bottom: 6px;

    color: rgba(255,255,255,.88);

    font-size: 1.05rem;

    font-weight: 800;

}

.forum-empty-state p {

    margin: 0;

    color: rgba(255,255,255,.40);

    font-size: .85rem;

}


/* =========================================================
   DELETED CATEGORIES
========================================================= */

.forum-deleted-categories {

    overflow: hidden;

    border: 1px solid rgba(248,113,113,.15);

    border-radius: 18px;

    background:

        linear-gradient(
            135deg,
            rgba(127,29,29,.12),
            rgba(69,10,10,.07)
        );

    box-shadow:
        0 12px 35px rgba(0,0,0,.12);

}


/* =========================================================
   DELETED HEADER
========================================================= */

.forum-deleted-header {

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    padding: 20px 22px;

    border-bottom: 1px solid rgba(248,113,113,.12);

    background:
        linear-gradient(
            135deg,
            rgba(127,29,29,.22),
            rgba(127,29,29,.10)
        );

}

.forum-deleted-heading {

    display: flex;

    align-items: center;

    gap: 14px;

}

.forum-deleted-icon {

    width: 45px;
    height: 45px;

    flex: 0 0 45px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: rgba(239,68,68,.13);

    color: #fca5a5;

    font-size: 1rem;

}

.forum-deleted-header h3 {

    margin: 0;

    color: #fca5a5;

    font-size: 1rem;

    font-weight: 850;

}

.forum-deleted-header p {

    margin: 3px 0 0;

    color: rgba(255,255,255,.40);

    font-size: .78rem;

}

.forum-deleted-count {

    padding: 6px 10px;

    border: 1px solid rgba(248,113,113,.18);

    border-radius: 8px;

    background: rgba(127,29,29,.18);

    color: #fca5a5;

    font-size: .68rem;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .05em;

    white-space: nowrap;

}


/* =========================================================
   DELETED LIST
========================================================= */

.forum-deleted-category-list {

    display: flex;

    flex-direction: column;

    gap: 8px;

    padding: 12px;

}


/* =========================================================
   DELETED CARD
========================================================= */

.forum-deleted-category-card {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    padding: 15px 16px;

    border: 1px solid rgba(248,113,113,.09);

    border-radius: 13px;

    background: rgba(127,29,29,.12);

    transition:
        border-color .2s ease,
        background .2s ease;

}

.forum-deleted-category-card:hover {

    border-color: rgba(248,113,113,.17);

    background: rgba(127,29,29,.17);

}


/* =========================================================
   DELETED MAIN
========================================================= */

.forum-deleted-category-main {

    display: flex;

    align-items: center;

    gap: 13px;

    min-width: 0;

}

.forum-deleted-category-icon {

    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 11px;

    background: rgba(239,68,68,.10);

    color: #fca5a5;

}

.forum-deleted-category-info {

    min-width: 0;

}

.forum-deleted-category-info h4 {

    margin: 0 0 3px;

    color: #fca5a5;

    font-size: .9rem;

    font-weight: 800;

}

.forum-deleted-category-info p {

    margin: 0 0 5px;

    color: rgba(255,255,255,.42);

    font-size: .77rem;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}

.forum-deleted-date {

    color: rgba(255,255,255,.28);

    font-size: .68rem;

}


/* =========================================================
   DELETED ACTIONS
========================================================= */

.forum-deleted-actions {

    display: flex;

    align-items: center;

    gap: 7px;

    flex: 0 0 auto;

}

.forum-deleted-actions form {
    margin: 0;
}


/* =========================================================
   RESTORE
========================================================= */

.forum-restore-category-btn {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 8px 13px;

    border: 1px solid rgba(74,222,128,.22);

    border-radius: 9px;

    background: rgba(34,197,94,.08);

    color: #86efac;

    font-size: .76rem;

    font-weight: 750;

    white-space: nowrap;

    transition: .2s ease;

}

.forum-restore-category-btn:hover {

    color: #fff;

    background: rgba(34,197,94,.18);

    border-color: rgba(74,222,128,.38);

    transform: translateY(-1px);

}


/* =========================================================
   PERMANENT DELETE
========================================================= */

.forum-permanent-delete-category-btn {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 8px 13px;

    border: 1px solid rgba(248,113,113,.22);

    border-radius: 9px;

    background: rgba(127,29,29,.13);

    color: #fca5a5;

    font-size: .76rem;

    font-weight: 750;

    white-space: nowrap;

    transition: .2s ease;

}

.forum-permanent-delete-category-btn:hover {

    color: #fff;

    background: rgba(185,28,28,.30);

    border-color: rgba(248,113,113,.45);

    transform: translateY(-1px);

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767px) {

    .forum-index-header {

        align-items: flex-start;

        flex-direction: column;

        gap: 18px;

        padding-bottom: 20px;

    }

    .forum-add-category-btn {

        width: 100%;

    }

    .forum-category-card {

        min-height: auto;

        padding: 16px;

        gap: 13px;

    }

    .forum-category-icon {

        width: 46px;
        height: 46px;

        flex-basis: 46px;

        border-radius: 13px;

        font-size: 1.1rem;

    }

    .forum-category-title {

        font-size: .92rem;

    }

    .forum-category-description {

        font-size: .75rem;

    }

    .forum-topic-count {

        flex: 0 0 52px;

        padding-left: 7px;

    }

    .forum-topic-count strong {

        font-size: .9rem;

    }

    .forum-topic-count span {

        font-size: .58rem;

    }

    .forum-category-arrow {

        display: none;

    }


    /* Deleted section */

    .forum-deleted-header {

        align-items: flex-start;

        flex-direction: column;

        padding: 17px;

    }

    .forum-deleted-count {

        align-self: flex-start;

    }

    .forum-deleted-category-card {

        align-items: flex-start;

        flex-direction: column;

        gap: 14px;

        padding: 14px;

    }

    .forum-deleted-category-main {

        width: 100%;

    }

    .forum-deleted-category-info p {

        white-space: normal;

    }

    .forum-deleted-actions {

        width: 100%;

        flex-direction: column;

    }

    .forum-deleted-actions form {

        width: 100%;

    }

    .forum-restore-category-btn,
    .forum-permanent-delete-category-btn {

        width: 100%;

    }

}


/* =========================================================
   EXTRA SMALL DEVICES
========================================================= */

@media (max-width: 420px) {

    .forum-page-title {
        font-size: 2rem;
    }

    .forum-category-card {
        gap: 10px;
        padding: 14px;
    }

    .forum-category-icon {
        width: 42px;
        height: 42px;
        flex-basis: 42px;
    }

    .forum-topic-count {
        flex-basis: 46px;
    }

}

</style>