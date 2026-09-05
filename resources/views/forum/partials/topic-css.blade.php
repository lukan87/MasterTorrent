<style>

/* =========================================================
   FORUM POST CARD
   ========================================================= */

.forum-post-card {

    background: rgba(15, 20, 35, .96);

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.25);

}


/* =========================================================
   ORIGINAL / MAIN POST
   ========================================================= */

.forum-main-post {

    border:
        1px solid rgba(59,130,246,.40);

    box-shadow:
        0 15px 40px rgba(37,99,235,.12),
        0 5px 20px rgba(0,0,0,.25);

}


.forum-main-post .forum-user-panel {

    background:
        rgba(59,130,246,.06);

}


.original-post-label {

    min-height: 42px;

    padding: 9px 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    background:
        rgba(59,130,246,.12);

    border-bottom:
        1px solid rgba(59,130,246,.20);

    color:
        #93c5fd;

    font-size:
        .72rem;

    font-weight:
        800;

    letter-spacing:
        .08em;

}


/* =========================================================
   POST ACTIONS
   ========================================================= */

.forum-post-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 12px;

    margin-left: auto;

    flex-shrink: 0;

}


.forum-post-action {

    display: inline-flex;

    align-items: center;

    white-space: nowrap;

    border: 0;

    background: transparent;

    padding: 2px 0;

    font-size: .78rem;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition:
        color .2s ease,
        transform .2s ease;

}


/* EDIT */

.edit-action {

    color:
        #93c5fd;

}


.edit-action:hover {

    color:
        #ffffff;

    transform:
        translateY(-1px);

}


/* DELETE */

.delete-action {

    color:
        #f87171;

}


.delete-action:hover {

    color:
        #fca5a5;

}


.delete-action:disabled {

    opacity:
        .65;

    cursor:
        not-allowed;

    transform:
        none;

}


/* =========================================================
   POST ANCHOR
   ========================================================= */

.post-anchor {

    display: inline-flex;

    align-items: center;

    color:
        rgba(255,255,255,.40);

    text-decoration:
        none;

    font-size:
        .75rem;

    font-weight:
        700;

    white-space:
        nowrap;

}


.post-anchor:hover {

    color:
        #93c5fd;

}


/* =========================================================
   USER PANEL
   ========================================================= */

.forum-user-panel {

    height:
        100%;

    padding:
        24px 18px;

    text-align:
        center;

    background:
        rgba(255,255,255,.025);

    border-right:
        1px solid rgba(255,255,255,.06);

}


/* =========================================================
   AVATAR
   ========================================================= */

.forum-avatar {

    width:
        90px;

    height:
        90px;

    margin:
        0 auto 12px;

    border-radius:
        50%;

    overflow:
        hidden;

    border:
        3px solid rgba(255,255,255,.08);

}


.forum-avatar img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.forum-avatar-placeholder {

    width:
        100%;

    height:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:
        white;

    font-size:
        2rem;

}


/* =========================================================
   USERNAME
   ========================================================= */

.forum-username {

    display:
        block;

    color:
        #fff;

    font-size:
        1.05rem;

    font-weight:
        800;

    text-decoration:
        none;

}


.forum-username:hover {

    color:
        #93c5fd;

}


/* =========================================================
   USER TITLE
   ========================================================= */

.forum-user-title {

    margin-top:
        4px;

    color:
        #94a3b8;

    font-size:
        .85rem;

}


/* =========================================================
   USER RANK
   ========================================================= */

.forum-user-rank {

    display:
        inline-block;

    margin-top:
        10px;

    padding:
        5px 10px;

    border-radius:
        20px;

    background:
        rgba(59,130,246,.15);

    color:
        #93c5fd;

    font-size:
        .75rem;

    font-weight:
        700;

}


/* =========================================================
   USER INFO
   ========================================================= */

.forum-user-info {

    margin-top:
        12px;

    color:
        rgba(255,255,255,.5);

    font-size:
        .75rem;

}


/* =========================================================
   POST CONTENT
   ========================================================= */

.forum-post-content {

    min-height:
        220px;

    padding:
        20px 24px;

}


/* =========================================================
   POST HEADER
   ========================================================= */

.forum-post-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    padding-bottom:
        12px;

    margin-bottom:
        18px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    color:
        rgba(255,255,255,.45);

    font-size:
        .8rem;

}


.forum-post-meta-left {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        12px;

}


/* =========================================================
   EDITED LABEL
   ========================================================= */

.post-edited {

    color:
        rgba(147,197,253,.70);

}


/* =========================================================
   POST BODY
   ========================================================= */

.forum-post-body {

    color:
        rgba(255,255,255,.9);

    line-height:
        1.75;

    font-size:
        .98rem;

    overflow-wrap:
        anywhere;

    word-break:
        break-word;

}


/* =========================================================
   TOPIC HEADER
   ========================================================= */

.forum-topic-header {

    padding:
        10px 0;

}


.forum-topic-category {

    color:
        #93c5fd;

    font-size:
        .8rem;

    font-weight:
        800;

    text-transform:
        uppercase;

    letter-spacing:
        .08em;

}


.forum-topic-title {

    margin:
        0 0 12px;

    color:
        #fff;

    font-size:
        2rem;

    font-weight:
        800;

    line-height:
        1.25;

    overflow-wrap:
        anywhere;

}


.forum-topic-meta {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        15px;

    color:
        rgba(255,255,255,.50);

    font-size:
        .85rem;

}


.forum-locked-badge {

    display:
        inline-flex;

    align-items:
        center;

    padding:
        5px 10px;

    border-radius:
        20px;

    background:
        rgba(239,68,68,.15);

    color:
        #f87171;

    font-weight:
        700;

}


/* =========================================================
   REPLY BOX
   ========================================================= */

.forum-reply-box {

    background:
        rgba(15,20,35,.96);

    border:
        1px solid rgba(255,255,255,.07);

    border-radius:
        18px;

    overflow:
        hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.25);

}


.forum-reply-box-header {

    padding:
        18px 24px;

    background:
        rgba(255,255,255,.025);

    border-bottom:
        1px solid rgba(255,255,255,.06);

}


.forum-reply-box-header h5 {

    color:
        #fff;

    font-weight:
        800;

}


.forum-reply-box-header small {

    color:
        rgba(255,255,255,.45);

}


/* =========================================================
   TEXTAREA
   ========================================================= */

.forum-textarea {

    min-height:
        140px;

    background:
        rgba(0,0,0,.20);

    border:
        1px solid rgba(255,255,255,.10);

    color:
        #fff;

    resize:
        vertical;

}


.forum-textarea::placeholder {

    color:
        rgba(255,255,255,.35);

}


.forum-textarea:focus {

    background:
        rgba(0,0,0,.25);

    color:
        #fff;

    border-color:
        rgba(59,130,246,.60);

    box-shadow:
        0 0 0 .2rem rgba(59,130,246,.10);

}


/* =========================================================
   SUBMIT BUTTON
   ========================================================= */

.forum-submit-btn {

    border:
        0;

    border-radius:
        12px;

    padding:
        11px 18px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:
        #fff;

    font-weight:
        700;

    transition:
        .2s ease;

}


.forum-submit-btn:hover {

    color:
        #fff;

    transform:
        translateY(-1px);

}


/* =========================================================
   LOCKED BOX
   ========================================================= */

.forum-locked-box {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;

    padding:
        20px;

    border:
        1px solid rgba(239,68,68,.20);

    border-radius:
        16px;

    background:
        rgba(239,68,68,.08);

    color:
        #fca5a5;

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


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-breadcrumb {
        width: 100%;
        padding: 9px 12px;
        gap: 8px;
    }

    .forum-breadcrumb-link {
        font-size: .78rem;
    }

    .forum-breadcrumb-current span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

}


/* =========================================================
   PAGINATION
   ========================================================= */

.forum-pagination {

    display:
        flex;

    justify-content:
        center;

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-topic-title {

        font-size:
            1.45rem;

    }


    .forum-topic-meta {

        gap:
            8px 12px;

        font-size:
            .78rem;

    }


    .original-post-label {

        min-height:
            44px;

        padding:
            9px 14px;

        gap:
            8px;

    }


    .forum-post-actions {

        gap:
            8px;

    }


    .forum-post-action {

        font-size:
            .72rem;

    }


    .forum-user-panel {

        border-right:
            0;

        border-bottom:
            1px solid rgba(255,255,255,.06);

        padding:
            18px;

    }


    .forum-avatar {

        width:
            70px;

        height:
            70px;

    }


    .forum-post-content {

        padding:
            18px;

        min-height:
            auto;

    }


    .forum-post-header {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

        gap:
            10px;

    }


    .forum-post-meta-left {

        gap:
            8px;

    }


    .forum-post-header .forum-post-actions {

        width:
            100%;

        justify-content:
            flex-start;

        margin-left:
            0;

        padding-top:
            4px;

    }

}

/*Topic Actions*/
/* =========================================================
   TOPIC ACTIONS
   ========================================================= */

.forum-topic-actions {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}


.forum-topic-actions form {
    margin: 0;
}


.forum-topic-action {

    border: 1px solid rgba(255,255,255,.08);

    border-radius: 12px;

    padding: 10px 15px;

    font-size: .85rem;

    font-weight: 700;

    transition: all .2s ease;

    cursor: pointer;

    white-space: nowrap;

}


.forum-topic-action.lock {

    background: rgba(239,68,68,.12);

    color: #f87171;

    border-color: rgba(239,68,68,.20);

}


.forum-topic-action.lock:hover {

    background: rgba(239,68,68,.20);

    color: #fca5a5;

    transform: translateY(-1px);

}


.forum-topic-action.unlock {

    background: rgba(34,197,94,.12);

    color: #4ade80;

    border-color: rgba(34,197,94,.20);

}


.forum-topic-action.unlock:hover {

    background: rgba(34,197,94,.20);

    color: #86efac;

    transform: translateY(-1px);

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-topic-actions {
        width: 100%;
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        box-sizing: border-box;
    }

    .forum-topic-actions form {
        flex: 0 1 auto;
        width: auto;
        margin: 0;
    }

    .forum-topic-action {
        width: auto;
        max-width: 100%;
        box-sizing: border-box;
        padding: 7px 10px;
        font-size: .75rem;
        white-space: nowrap;
    }

    .forum-topic-actions .post-anchor {
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        white-space: nowrap;
    }

}
/*Topic Actions*/

/* =========================================================
   PIN / UNPIN
   ========================================================= */

.forum-topic-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}


.forum-topic-action.pin {

    background: rgba(250,204,21,.12);

    color: #fde047;

    border-color: rgba(250,204,21,.20);

}


.forum-topic-action.pin:hover {

    background: rgba(250,204,21,.20);

    color: #fef08a;

    transform: translateY(-1px);

}


.forum-topic-action.unpin {

    background: rgba(148,163,184,.12);

    color: #cbd5e1;

    border-color: rgba(148,163,184,.20);

}


.forum-topic-action.unpin:hover {

    background: rgba(148,163,184,.20);

    color: #f8fafc;

    transform: translateY(-1px);

}


/* =========================================================
   DELETE TOPIC
   ========================================================= */

.forum-topic-action.delete-topic {

    background: rgba(90, 29, 29, 0.12);

    color: #f87171;

    border-color: rgba(220, 38, 38, .22);

}


.forum-topic-action.delete-topic:hover {

    background: rgba(220, 38, 38, .22);

    color: #fca5a5;

    transform: translateY(-1px);

}


/* =========================================================
   POST HEADER
   ========================================================= */

.forum-post-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    flex-wrap: wrap;

}


.forum-post-header-left {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-wrap: wrap;

}


/* =========================================================
   POST ACTIONS
   ========================================================= */

.forum-post-actions {

    display: flex;

    align-items: center;

    gap: 8px;

}


/* =========================================================
   QUOTE BUTTON
   ========================================================= */

.forum-post-action {

    display: inline-flex;

    align-items: center;

    border: 1px solid rgba(255,255,255,.08);

    border-radius: 8px;

    padding: 3px 9px;

    background: rgba(255,255,255,.04);

    color: rgba(255,255,255,.55);

    font-size: .72rem;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition: all .2s ease;

}


.forum-post-action:hover {

    background: rgba(59,130,246,.12);

    border-color: rgba(59,130,246,.25);

    color: #93c5fd;

    transform: translateY(-1px);

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-post-header {

        align-items: flex-start;

    }


    .forum-post-header-left {

        width: 100%;

    }


    .forum-post-actions {
    width: 100%;
    justify-content: flex-start;
    gap: 8px;
    flex-wrap: wrap;
}

}

/* =========================================================
   FOLLOW TOPIC
   ========================================================= */

.forum-follow-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: 1px solid rgba(59,130,246,.30);

    border-radius: 10px;

    padding: 9px 14px;

    background: rgba(59,130,246,.10);

    color: #93c5fd;

    font-size: .82rem;

    font-weight: 700;

    white-space: nowrap;

    cursor: pointer;

    transition:
        background .2s ease,
        border-color .2s ease,
        transform .2s ease;

}


.forum-follow-btn:hover {

    background: rgba(59,130,246,.18);

    border-color: rgba(59,130,246,.45);

    color: #bfdbfe;

    transform: translateY(-1px);

}


/* FOLLOWING STATE */

.forum-follow-btn.following {

    background: rgba(255,255,255,.04);

    border-color: rgba(255,255,255,.10);

    color: rgba(255,255,255,.55);

}


.forum-follow-btn.following:hover {

    background: rgba(220,38,38,.10);

    border-color: rgba(220,38,38,.25);

    color: #fca5a5;

}


/* MOBILE */

@media (max-width: 767px) {

    .forum-follow-btn {

        width: 100%;

    }

}

/* =========================================================
   LATEST REPLY BUTTON
   ========================================================= */

.forum-latest-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: 1px solid rgba(255,255,255,.12);

    border-radius: 10px;

    padding: 9px 14px;

    background: rgba(255,255,255,.04);

    color: rgba(255,255,255,.70);

    font-size: .82rem;

    font-weight: 700;

    text-decoration: none;

    white-space: nowrap;

    transition:
        background .2s ease,
        border-color .2s ease,
        color .2s ease,
        transform .2s ease;

}

.forum-latest-btn:hover {

    background: rgba(59,130,246,.12);

    border-color: rgba(59,130,246,.30);

    color: #93c5fd;

    transform: translateY(-1px);

}

@media (max-width: 767px) {

    .forum-latest-btn {

        width: 100%;

    }

}

/* =========================================================
   POST LIKES
   ========================================================= */

.forum-like-section {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,.06);
}

/* =========================================================
   REACTIONS
   ========================================================= */

.forum-reactions {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 7px;
    background: rgba(255,255,255,.035);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 12px;
}

.forum-reaction-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    padding: 0;
    border: 0;
    border-radius: 9px;
    background: transparent;
    cursor: pointer;
    transition: all .18s ease;
}

.forum-reaction-btn:hover {
    background: rgba(255,255,255,.08);
    transform: translateY(-2px) scale(1.08);
}

.forum-reaction-btn.active {
    background: rgba(59,130,246,.15);
    box-shadow: 0 0 0 1px rgba(59,130,246,.30);
}

.forum-reaction-emoji {
    font-size: 1.15rem;
    line-height: 1;
    transition: transform .18s ease;
}

.forum-reaction-btn:hover .forum-reaction-emoji {
    transform: scale(1.15);
}

.reaction-total {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    margin-left: 4px;
    padding: 0 6px;
    border-radius: 11px;
    background: rgba(59,130,246,.10);
    color: rgba(255,255,255,.65);
    font-size: .72rem;
    font-weight: 700;
}

.forum-like-section form {
    margin: 0;
}

.like-action {
    color: rgba(255,255,255,.55);
}

.like-action:hover {
    color: #f87171;
}

.like-action.liked {
    color: #f87171;
}

.like-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    margin-left: 3px;
    padding: 0 5px;
    border-radius: 10px;
    background: rgba(248,113,113,.12);
    font-size: .7rem;
    font-weight: 700;
}

.forum-like-count {
    color: #f87171;
    font-size: .78rem;
    font-weight: 700;
}

.forum-liked-by {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
    font-size: .75rem;
}

.liked-by-label {
    color: rgba(255,255,255,.40);
}

.liked-by-user {
    color: #93c5fd;
    text-decoration: none;
    font-weight: 700;
}

.liked-by-user:hover {
    color: #bfdbfe;
    text-decoration: underline;
}

/* =========================================================
   LIKED BY
   ========================================================= */

.forum-liked-by {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
    font-size: .75rem;
    color: rgba(255,255,255,.45);
}

.liked-by-heart {
    color: #f87171;
    font-size: .7rem;
}

.liked-by-label {
    color: rgba(255,255,255,.40);
}

.liked-by-user {
    color: #93c5fd;
    font-weight: 700;
    text-decoration: none;
}

.liked-by-user:hover {
    color: #bfdbfe;
}

.liked-by-comma {
    color: rgba(255,255,255,.35);
    margin-left: -3px;
}

.liked-by-more {
    border: 0;
    padding: 0 5px;
    background: rgba(59,130,246,.10);
    border-radius: 6px;
    color: #93c5fd;
    font-size: .72rem;
    font-weight: 700;
    cursor: pointer;
}

.liked-by-more:hover {
    background: rgba(59,130,246,.18);
    color: #bfdbfe;
}

.reaction-summary {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 5px;
}

.reaction-summary-item {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 6px;
    border-radius: 8px;
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.65);
    font-size: .72rem;
    font-weight: 700;
}

  .forum-like-avatar {
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 50%;
}

.forum-like-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

html,
body {
    max-width: 100%;
    overflow-x: hidden;
}
</style>