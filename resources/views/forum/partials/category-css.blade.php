<style>

/* =========================================================
   CATEGORY HEADER
   ========================================================= */

.forum-category-header {

    display: flex;

    align-items: center;

    gap: 18px;

    padding: 24px;

    background:
        rgba(15,20,35,.96);

    border:
        1px solid rgba(255,255,255,.07);

    border-radius: 20px;

    box-shadow:
        0 12px 35px rgba(0,0,0,.25);

}


.forum-category-icon {

    width: 58px;

    height: 58px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 16px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color: white;

    font-size: 1.4rem;

    box-shadow:
        0 10px 25px rgba(59,130,246,.25);

}


.forum-category-title {

    margin: 0;

    font-size: 1.8rem;

    font-weight: 800;

    color: white;

}


.forum-category-description {

    margin-top: 5px;

    color:
        rgba(255,255,255,.55);

}


.forum-new-topic-btn {

    border: none;

    border-radius: 12px;

    padding: 11px 17px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color: white;

    font-weight: 700;

    box-shadow:
        0 8px 20px rgba(37,99,235,.25);

    transition: .2s ease;

}


.forum-new-topic-btn:hover {

    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 12px 25px rgba(37,99,235,.35);

}


/* =========================================================
   TOPIC LIST
   ========================================================= */

.forum-topic-list {

    display: flex;

    flex-direction: column;

    gap: 10px;

}


.forum-topic-row {

    display: flex;

    align-items: center;

    gap: 18px;

    padding: 20px;

    background:
        rgba(15,20,35,.94);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius: 16px;

    transition:
        transform .2s ease,
        border-color .2s ease,
        background .2s ease;

}


.forum-topic-row:hover {

    transform: translateY(-2px);

    background:
        rgba(20,27,45,.98);

    border-color:
        rgba(59,130,246,.25);

}


.topic-pinned {

    border-color:
        rgba(250,204,21,.18);

}


.topic-locked {

    opacity: .92;

}


/* =========================================================
   TOPIC ICON
   ========================================================= */

.forum-topic-icon {

    width: 46px;

    height: 46px;

    flex: 0 0 46px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        rgba(59,130,246,.12);

    color:
        #93c5fd;

    font-size: 1rem;

}


.topic-pinned .forum-topic-icon {

    background:
        rgba(250,204,21,.12);

    color:
        #fde047;

}


.topic-locked .forum-topic-icon {

    background:
        rgba(239,68,68,.12);

    color:
        #f87171;

}


/* =========================================================
   MAIN INFORMATION
   ========================================================= */

.forum-topic-main {

    flex: 1;

    min-width: 0;

}


.forum-topic-title-row {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

}


.forum-topic-link {

    color: white;

    font-size: 1.05rem;

    font-weight: 800;

    text-decoration: none;

    overflow-wrap: anywhere;

}


.forum-topic-link:hover {

    color:
        #93c5fd;

}


.forum-topic-badges {

    display: flex;

    gap: 6px;

    flex-wrap: wrap;

}


.forum-topic-badge {

    padding:
        4px 8px;

    border-radius:
        8px;

    font-size:
        .65rem;

    font-weight:
        800;

    text-transform:
        uppercase;

    letter-spacing:
        .04em;

}


.forum-topic-badge.pinned {

    background:
        rgba(250,204,21,.12);

    color:
        #fde047;

}


.forum-topic-badge.locked {

    background:
        rgba(239,68,68,.12);

    color:
        #f87171;

}


.forum-topic-started {

    margin-top:
        7px;

    color:
        rgba(255,255,255,.45);

    font-size:
        .78rem;

}


.forum-topic-started strong {

    color:
        rgba(255,255,255,.75);

}


/* =========================================================
   STATS
   ========================================================= */

.forum-topic-stats {

    display: flex;

    gap: 22px;

    flex: 0 0 auto;

}


.forum-topic-stat {

    min-width: 55px;

    text-align: center;

}


.forum-topic-stat strong {

    display: block;

    color: white;

    font-size: 1rem;

}


.forum-topic-stat span {

    display: block;

    margin-top: 3px;

    color:
        rgba(255,255,255,.4);

    font-size:
        .7rem;

}


/* =========================================================
   LAST POST
   ========================================================= */

.forum-last-post {

    width: 150px;

    flex: 0 0 150px;

    padding-left: 18px;

    border-left:
        1px solid rgba(255,255,255,.06);

}


.forum-last-post-label {

    color:
        rgba(255,255,255,.35);

    font-size:
        .68rem;

}


.forum-last-post-user {

    margin-top:
        3px;

    color:
        #93c5fd;

    font-size:
        .78rem;

    font-weight:
        700;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


.forum-last-post-time {

    margin-top:
        2px;

    color:
        rgba(255,255,255,.4);

    font-size:
        .68rem;

}

/* =========================================================
   LAST POST LINK
   ========================================================= */

.forum-last-post-link {

    display: block;

    color: inherit;

    text-decoration: none;

    padding: 8px 10px;

    margin: -8px -10px;

    border-radius: 10px;

    transition:
        background .2s ease,
        transform .2s ease;

}


.forum-last-post-link:hover {

    background:
        rgba(59,130,246,.08);

    transform: translateX(2px);

}


.forum-last-post-link:hover .forum-last-post-user {

    color: #93c5fd;

}


/* LAST POST LABEL */

.forum-last-post-label {

    color:
        rgba(255,255,255,.40);

    font-size:
        .68rem;

    text-transform:
        uppercase;

    letter-spacing:
        .06em;

    font-weight:
        700;

}


/* LAST POST USER */

.forum-last-post-user {

    margin-top:
        3px;

    color:
        rgba(255,255,255,.82);

    font-size:
        .82rem;

    font-weight:
        700;

    transition:
        color .2s ease;

}


/* LAST POST TIME */

.forum-last-post-time {

    margin-top:
        2px;

    color:
        rgba(255,255,255,.40);

    font-size:
        .72rem;

}


/* =========================================================
   ARROW
   ========================================================= */

.forum-topic-arrow {

    color:
        rgba(255,255,255,.25);

    font-size:
        1rem;

}


.forum-topic-row:hover .forum-topic-arrow {

    color:
        #93c5fd;

}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.forum-empty-state {

    padding:
        70px 20px;

    text-align:
        center;

    background:
        rgba(15,20,35,.94);

    border:
        1px solid rgba(255,255,255,.06);

    border-radius:
        18px;

}


.forum-empty-icon {

    width:
        70px;

    height:
        70px;

    margin:
        0 auto 18px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        20px;

    background:
        rgba(59,130,246,.10);

    color:
        #93c5fd;

    font-size:
        1.8rem;

}


.forum-empty-state h4 {

    color:
        white;

    font-weight:
        800;

}


.forum-empty-state p {

    color:
        rgba(255,255,255,.45);

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-category-header {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

        padding:
            18px;

    }


    .forum-category-icon {

        width:
            48px;

        height:
            48px;

        flex:
            0 0 48px;

    }


    .forum-category-title {

        font-size:
            1.35rem;

    }


    .forum-new-topic-btn {

        width:
            100%;

    }


    .forum-category-header > div:last-child {

        width:
            100%;

    }


    .forum-topic-row {

        align-items:
            flex-start;

        gap:
            12px;

        padding:
            15px;

    }


    .forum-topic-icon {

        width:
            38px;

        height:
            38px;

        flex:
            0 0 38px;

        font-size:
            .85rem;

    }


    .forum-topic-title-row {

        display:
            block;

    }


    .forum-topic-badges {

        margin-top:
            6px;

    }


    .forum-topic-stats {

        display:
            none;

    }


    .forum-last-post {

        display:
            none;

    }


    .forum-topic-arrow {

        display:
            none;

    }

}


/* =========================================================
   NEW REPLIES BADGE
   ========================================================= */

.forum-topic-badge.new {

    background: rgba(34, 197, 94, .12);

    border: 1px solid rgba(34, 197, 94, .25);

    color: #86efac;

}

/* =========================================================
   FORUM BREADCRUMB
   ========================================================= */

.forum-breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 10px 14px;

    background: rgba(15, 20, 35, .70);

    border: 1px solid rgba(255,255,255,.06);

    border-radius: 12px;

    width: fit-content;
    max-width: 100%;

    box-shadow:
        0 6px 20px rgba(0,0,0,.12);
}

.forum-breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    color: rgba(255,255,255,.50);

    text-decoration: none;

    font-size: .82rem;
    font-weight: 700;

    transition:
        color .2s ease,
        background .2s ease;
}

.forum-breadcrumb-link:hover {
    color: #93c5fd;
    transform: translateX(-2px);
}

.forum-breadcrumb-current {
    color: rgba(255,255,255,.85);
}

.forum-breadcrumb-current:hover {
    color: #60a5fa;
}

.forum-breadcrumb-separator {
    color: rgba(255,255,255,.20);
    font-size: .65rem;
}

.forum-edit-category-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 12px;
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.85);
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
    transition: .2s ease;
}

.forum-edit-category-btn:hover {
    color: white;
    background: rgba(37,99,235,.20);
    border-color: rgba(96,165,250,.35);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(37,99,235,.18);
}

@media (max-width: 767px) {

    .forum-edit-category-btn,
    .forum-new-topic-btn {
        flex: 1 1 auto;
    }

}

.forum-delete-category-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 15px;
    border: 1px solid rgba(239,68,68,.20);
    border-radius: 12px;
    background: rgba(239,68,68,.08);
    color: #fca5a5;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
    transition: .2s ease;
}

.forum-delete-category-btn:hover {
    color: white;
    background: rgba(239,68,68,.20);
    border-color: rgba(248,113,113,.40);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(239,68,68,.15);
}

@media (max-width: 767px) {

    .forum-delete-category-btn {
        flex: 1 1 auto;
    }

}
</style>