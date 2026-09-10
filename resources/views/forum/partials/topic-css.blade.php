<style>
/* =========================================================
   FILEIPLAY — FORUM TOPIC
   Bootstrap 5 / Dark Glass / Teal UI
   ========================================================= */


/* =========================================================
   BREADCRUMB
   ========================================================= */

.forum-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .55rem;

    width: fit-content;
    max-width: 100%;

    padding: .6rem .8rem;

    background: rgba(15, 23, 42, .72);

    border: 1px solid var(--ui-border);
    border-radius: .7rem;

    box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
}

.forum-breadcrumb-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;

    color: #8fa3b8;

    font-size: .8rem;
    font-weight: 650;

    text-decoration: none;

    transition:
        color 160ms ease,
        background 160ms ease;
}

.forum-breadcrumb-link i {
    color: var(--ui-accent);
}

.forum-breadcrumb-link:hover {
    color: #f1f5f9;
}

.forum-breadcrumb-current {
    color: #dbe7f2;
}

.forum-breadcrumb-current:hover {
    color: var(--ui-accent);
}

.forum-breadcrumb-separator {
    color: #50647a;
    font-size: .65rem;
}


/* =========================================================
   TOPIC HEADER
   ========================================================= */

.forum-topic-header {
    position: relative;

    padding: 1.35rem 1.5rem;

    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .95),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);
    border-radius: 1rem;

    box-shadow:
        0 12px 30px rgba(0, 0, 0, .18),
        inset 0 1px 0 rgba(255, 255, 255, .025);

    overflow: hidden;
}

.forum-topic-header::before {
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

.forum-topic-header::after {
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


/* Category */

.forum-topic-category {
    display: inline-flex;
    align-items: center;

    color: var(--ui-accent);

    font-size: .74rem;
    font-weight: 800;

    letter-spacing: .1em;
    text-transform: uppercase;
}

.forum-topic-category i {
    font-size: .8rem;
}


/* Title */

.forum-topic-title {
    max-width: 1050px;

    color: #f8fafc;

    font-size: clamp(1.55rem, 3vw, 2.15rem);
    font-weight: 800;
    line-height: 1.2;

    letter-spacing: -.025em;

    overflow-wrap: anywhere;
}


/* Topic metadata */

.forum-topic-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .75rem 1.1rem;

    color: #8094a9;

    font-size: .8rem;
}

.forum-topic-meta span {
    display: inline-flex;
    align-items: center;
}

.forum-topic-meta i {
    color: #6f879e;
}


/* Locked */

.forum-locked-badge {
    display: inline-flex;
    align-items: center;

    padding: .28rem .55rem;

    color: #ff9da6;

    background: rgba(220, 53, 69, .09);

    border: 1px solid rgba(220, 53, 69, .18);
    border-radius: .45rem;

    font-size: .68rem;
    font-weight: 750;
}


/* =========================================================
   TOPIC ACTIONS
   ========================================================= */

.forum-topic-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;

    gap: .45rem;

    flex-shrink: 0;
}

.forum-topic-actions form {
    margin: 0;
}

.forum-topic-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 36px;

    padding: .5rem .7rem;

    border: 1px solid rgba(255, 255, 255, .09);
    border-radius: .6rem;

    font-size: .72rem;
    font-weight: 700;

    cursor: pointer;
    white-space: nowrap;

    transition:
        transform 160ms ease,
        background 160ms ease,
        border-color 160ms ease,
        color 160ms ease;
}

.forum-topic-action:hover {
    transform: translateY(-1px);
}


/* Pin */

.forum-topic-action.pin {
    color: #f5df70;
    background: rgba(250, 204, 21, .07);
    border-color: rgba(250, 204, 21, .15);
}

.forum-topic-action.pin:hover {
    color: #fff1a8;
    background: rgba(250, 204, 21, .13);
    border-color: rgba(250, 204, 21, .25);
}


/* Unpin */

.forum-topic-action.unpin {
    color: #b7c3d0;
    background: rgba(148, 163, 184, .06);
}

.forum-topic-action.unpin:hover {
    color: #fff;
    background: rgba(148, 163, 184, .12);
}


/* Lock */

.forum-topic-action.lock {
    color: #ff9da6;
    background: rgba(220, 53, 69, .07);
    border-color: rgba(220, 53, 69, .16);
}

.forum-topic-action.lock:hover {
    color: #ffd5d9;
    background: rgba(220, 53, 69, .13);
    border-color: rgba(220, 53, 69, .27);
}


/* Unlock */

.forum-topic-action.unlock {
    color: #72dda5;
    background: rgba(34, 197, 94, .07);
    border-color: rgba(34, 197, 94, .15);
}

.forum-topic-action.unlock:hover {
    color: #b4f5cb;
    background: rgba(34, 197, 94, .13);
}


/* Delete */

.forum-topic-action.delete-topic {
    color: #ff858e;
    background: rgba(220, 53, 69, .06);
    border-color: rgba(220, 53, 69, .14);
}

.forum-topic-action.delete-topic:hover {
    color: #ffd0d4;
    background: rgba(220, 53, 69, .14);
}


/* =========================================================
   FOLLOW / LATEST BUTTONS
   ========================================================= */

.forum-follow-btn,
.forum-latest-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 38px;

    padding: .55rem .8rem;

    border-radius: .65rem;

    font-size: .74rem;
    font-weight: 700;

    white-space: nowrap;

    transition:
        transform 160ms ease,
        background 160ms ease,
        border-color 160ms ease,
        color 160ms ease;
}

.forum-follow-btn:hover,
.forum-latest-btn:hover {
    transform: translateY(-1px);
}


/* Follow */

.forum-follow-btn {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .07);

    border: 1px solid rgba(99, 210, 198, .18);
}

.forum-follow-btn:hover {
    color: #b4f4ed;

    background: rgba(99, 210, 198, .13);

    border-color: rgba(99, 210, 198, .3);
}


/* Following */

.forum-follow-btn.following {
    color: #9aaabd;

    background: rgba(148, 163, 184, .06);

    border-color: rgba(148, 163, 184, .12);
}

.forum-follow-btn.following:hover {
    color: #ffadb5;

    background: rgba(220, 53, 69, .08);

    border-color: rgba(220, 53, 69, .18);
}


/* Latest */

.forum-latest-btn {
    color: #b9c8d8;

    background: rgba(148, 163, 184, .06);

    border: 1px solid rgba(148, 163, 184, .13);

    text-decoration: none;
}

.forum-latest-btn:hover {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .07);

    border-color: rgba(99, 210, 198, .18);
}


/* =========================================================
   POST CARD
   ========================================================= */

.forum-post-card,
.forum-reply-box {
    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .94),
            rgba(15, 23, 42, .84)
        );

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    overflow: hidden;

    box-shadow:
        0 10px 28px rgba(0, 0, 0, .17),
        inset 0 1px 0 rgba(255, 255, 255, .02);
}


/* Main post gets slightly stronger treatment */

.forum-main-post {
    border-color: rgba(99, 210, 198, .2);

    box-shadow:
        0 14px 34px rgba(0, 0, 0, .2),
        0 0 0 1px rgba(99, 210, 198, .025);
}


/* =========================================================
   MAIN POST LABEL
   ========================================================= */

.original-post-label {
    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 42px;

    gap: .75rem;

    padding: .6rem 1rem;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .07);

    border-bottom: 1px solid rgba(99, 210, 198, .12);

    font-size: .68rem;
    font-weight: 800;

    letter-spacing: .1em;
}

.original-post-label > span {
    display: inline-flex;
    align-items: center;
}

.original-post-label i {
    font-size: .75rem;
}


/* =========================================================
   POST ACTIONS
   ========================================================= */

.forum-post-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;

    gap: .4rem;

    margin-left: auto;

    flex-shrink: 0;
}

.forum-post-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 28px;

    padding: .28rem .55rem;

    color: #8295aa;

    background: rgba(148, 163, 184, .045);

    border: 1px solid rgba(148, 163, 184, .09);

    border-radius: .45rem;

    font-size: .67rem;
    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition:
        transform 160ms ease,
        color 160ms ease,
        background 160ms ease,
        border-color 160ms ease;
}

.forum-post-action:hover {
    color: var(--ui-accent);

    background: rgba(99, 210, 198, .07);

    border-color: rgba(99, 210, 198, .16);

    transform: translateY(-1px);
}


/* Edit */

.forum-post-action.edit-action {
    color: #9ec5d0;
}

.forum-post-action.edit-action:hover {
    color: #c9f3ee;
}


/* Delete */

.forum-post-action.delete-action {
    color: #ef8991;
}

.forum-post-action.delete-action:hover {
    color: #ffc4c8;

    background: rgba(220, 53, 69, .09);

    border-color: rgba(220, 53, 69, .18);
}


/* Quote */

.forum-post-action.quote-post-btn {
    color: #9db8c2;
}

.forum-post-action.quote-post-btn:hover {
    color: var(--ui-accent);
}


/* Anchor */

.post-anchor {
    display: inline-flex;
    align-items: center;

    color: #60758b;

    font-size: .67rem;
    font-weight: 700;

    text-decoration: none;
}

.post-anchor:hover {
    color: var(--ui-accent);
}


/* =========================================================
   USER PANEL
   ========================================================= */

.forum-user-panel {
    height: 100%;

    padding: 1.4rem 1rem;

    text-align: center;

    background:
        linear-gradient(
            180deg,
            rgba(148, 163, 184, .045),
            rgba(15, 23, 42, .12)
        );

    border-right: 1px solid var(--ui-border);
}


/* =========================================================
   AVATAR
   ========================================================= */

.forum-avatar {
    width: 84px;
    height: 84px;

    margin: 0 auto .75rem;

    overflow: hidden;

    border: 3px solid rgba(99, 210, 198, .12);

    border-radius: 50%;

    background: rgba(15, 23, 42, .7);

    box-shadow:
        0 8px 20px rgba(0, 0, 0, .18);
}

.forum-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.forum-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 100%;
    height: 100%;

    color: var(--ui-accent);

    background:
        linear-gradient(
            135deg,
            rgba(99, 210, 198, .18),
            rgba(61, 179, 167, .06)
        );

    font-size: 1.8rem;
}


/* =========================================================
   USERNAME
   ========================================================= */

.forum-username {
    display: block;

    color: #f1f5f9;

    font-size: 1rem;
    font-weight: 800;

    line-height: 1.3;

    text-decoration: none;

    overflow-wrap: anywhere;

    transition: color 160ms ease;
}

.forum-username:hover {
    color: var(--ui-accent);
}


/* =========================================================
   USER TITLE
   ========================================================= */

.forum-user-title {
    margin-top: .25rem;

    color: #8295a9;

    font-size: .76rem;
    line-height: 1.4;
}


/* =========================================================
   USER RANK
   ========================================================= */

.forum-user-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin-top: .6rem;

    padding: .28rem .55rem;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .08);

    border: 1px solid rgba(99, 210, 198, .14);

    border-radius: 999px;

    font-size: .65rem;
    font-weight: 750;
}


/* =========================================================
   USER INFORMATION
   ========================================================= */

.forum-user-info {
    margin-top: .65rem;

    color: #71859b;

    font-size: .75rem;
}

.forum-user-info i {
    color: #617990;
}


/* =========================================================
   POST CONTENT
   ========================================================= */

.forum-post-content {
    min-height: 220px;

    padding: 1.25rem 1.5rem;
}


/* =========================================================
   POST HEADER
   ========================================================= */

.forum-post-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: .75rem;

    padding-bottom: .7rem;
    margin-bottom: 1rem;

    border-bottom: 1px solid var(--ui-border);

    color: #71869d;

    font-size: .72rem;
}

.forum-post-meta-left {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .45rem 1rem;
}

.forum-post-meta-left span {
    display: inline-flex;
    align-items: center;
}

.forum-post-meta-left i {
    color: #637b91;
}

.post-edited {
    color: #83aaa9;
}


/* =========================================================
   POST BODY
   ========================================================= */

.forum-post-body {
    color: #dce7f2;

    font-size: 1rem;

    line-height: 1.8;

    overflow-wrap: anywhere;
    word-break: break-word;
}

.forum-post-body p {
    margin-bottom: 1rem;
}

.forum-post-body p:last-child {
    margin-bottom: 0;
}


/* =========================================================
   REPLY POSTS
   ========================================================= */

.forum-reply-post {
    position: relative;
}

.forum-reply-post:hover {
    border-color: rgba(99, 210, 198, .13);
}


/* =========================================================
   REACTIONS
   ========================================================= */

.forum-like-section {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .55rem;

    margin-top: 1.25rem;
    padding-top: .75rem;

    border-top: 1px solid var(--ui-border);
}

.forum-reactions {
    display: inline-flex;
    align-items: center;

    gap: .15rem;

    padding: .25rem .35rem;

    background: rgba(148, 163, 184, .045);

    border: 1px solid rgba(148, 163, 184, .09);

    border-radius: .65rem;
}

.forum-like-section form {
    margin: 0;
}

.forum-reaction-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 34px;
    height: 34px;

    padding: 0;

    background: transparent;

    border: 0;
    border-radius: .5rem;

    cursor: pointer;

    transition:
        transform 150ms ease,
        background 150ms ease,
        box-shadow 150ms ease;
}

.forum-reaction-btn:hover {
    background: rgba(255, 255, 255, .07);

    transform: translateY(-2px) scale(1.06);
}

.forum-reaction-btn.active {
    background: rgba(99, 210, 198, .1);

    box-shadow:
        0 0 0 1px rgba(99, 210, 198, .25);
}

.forum-reaction-emoji {
    font-size: 1.12rem;
    line-height: 1;
}


/* Reaction summary */

.reaction-summary {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .25rem;

    margin-left: .25rem;
}

.reaction-summary-item {
    display: inline-flex;
    align-items: center;

    gap: .2rem;

    padding: .22rem .4rem;

    color: #aab8c7;

    background: rgba(148, 163, 184, .05);

    border: 1px solid rgba(148, 163, 184, .07);

    border-radius: .45rem;

    font-size: .68rem;
    font-weight: 700;
}


/* Reaction total */

.reaction-total {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 24px;
    min-height: 22px;

    padding: 0 .4rem;

    color: #e5a0a5;

    background: rgba(220, 53, 69, .08);

    border: 1px solid rgba(220, 53, 69, .1);

    border-radius: .5rem;

    font-size: .68rem;
    font-weight: 700;
}


/* =========================================================
   REACTED BY
   ========================================================= */

.forum-liked-by {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;

    gap: .3rem;

    color: #71869b;

    font-size: .7rem;
}

.liked-by-heart {
    color: #e77983;
    font-size: .68rem;
}

.liked-by-label {
    color: #71869b;
}

.liked-by-user {
    color: #9bc7cf;

    font-weight: 700;

    text-decoration: none;
}

.liked-by-user:hover {
    color: var(--ui-accent);
}

.liked-by-comma {
    color: #52677c;
}

.liked-by-more {
    padding: .18rem .4rem;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .06);

    border: 1px solid rgba(99, 210, 198, .1);

    border-radius: .4rem;

    font-size: .65rem;
    font-weight: 700;

    cursor: pointer;
}

.liked-by-more:hover {
    color: #c1f5ef;
    background: rgba(99, 210, 198, .12);
}


/* =========================================================
   REACTION MODAL
   ========================================================= */

.forum-like-modal {
    background:
        linear-gradient(
            135deg,
            rgba(22, 32, 51, .98),
            rgba(15, 23, 42, .97)
        );

    border: 1px solid var(--ui-border);

    border-radius: 1rem;

    box-shadow:
        0 25px 70px rgba(0, 0, 0, .45);
}

.forum-like-modal .modal-header {
    border-bottom-color: var(--ui-border);
}

.forum-like-modal .modal-title {
    color: #f1f5f9;

    font-size: 1rem;
    font-weight: 750;
}

.forum-like-modal .modal-title i {
    color: var(--ui-accent);
}

.forum-like-modal .modal-body {
    max-height: 55vh;
    overflow-y: auto;
}

.forum-like-user {
    display: flex;
    align-items: center;

    gap: .75rem;

    padding: .65rem 0;

    border-bottom: 1px solid rgba(148, 163, 184, .07);
}

.forum-like-user:last-child {
    border-bottom: 0;
}

.forum-like-avatar {
    width: 42px;
    height: 42px;

    flex-shrink: 0;

    overflow: hidden;

    border: 2px solid rgba(99, 210, 198, .12);

    border-radius: 50%;
}

.forum-like-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.forum-like-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 100%;
    height: 100%;

    color: var(--ui-accent);

    background: rgba(99, 210, 198, .08);
}

.forum-like-username {
    color: #dce7f2;

    font-size: .8rem;
    font-weight: 700;

    text-decoration: none;
}

.forum-like-username:hover {
    color: var(--ui-accent);
}

.forum-modal-reaction {
    margin-left: auto;

    font-size: 1.15rem;
}


/* =========================================================
   REPLY BOX
   ========================================================= */

.forum-reply-box {
    border-color: rgba(99, 210, 198, .14);
}

.forum-reply-box-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 1rem 1.35rem;

    background: rgba(99, 210, 198, .045);

    border-bottom: 1px solid rgba(99, 210, 198, .1);
}

.forum-reply-box-header h5 {
    color: #f1f5f9;

    font-size: 1rem;
    font-weight: 800;
}

.forum-reply-box-header h5 i {
    color: var(--ui-accent);
}

.forum-reply-box-header small {
    color: #73879c;

    font-size: .75rem;
}


/* =========================================================
   TEXTAREA
   ========================================================= */

.forum-textarea {
    min-height: 150px;

    color: #e5edf7 !important;

    background:
        rgba(8, 15, 29, .72) !important;

    border: 1px solid rgba(148, 163, 184, .16) !important;

    border-radius: .75rem;

    font-size: .92rem;

    line-height: 1.7;

    resize: vertical;

    transition:
        border-color 160ms ease,
        box-shadow 160ms ease,
        background 160ms ease;
}

.forum-textarea::placeholder {
    color: #65798e;
}

.forum-textarea:focus {
    color: #fff !important;

    background: rgba(8, 15, 29, .9) !important;

    border-color: var(--ui-accent) !important;

    box-shadow:
        0 0 0 .2rem rgba(99, 210, 198, .11) !important;
}


/* =========================================================
   SUBMIT
   ========================================================= */

.forum-submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 40px;

    padding: .55rem .9rem;

    color: #062523;

    background:
        linear-gradient(
            135deg,
            var(--ui-accent),
            var(--ui-accent-strong)
        );

    border: 1px solid rgba(99, 210, 198, .3);

    border-radius: .65rem;

    font-size: .78rem;
    font-weight: 750;

    box-shadow: 0 7px 18px rgba(0, 0, 0, .16);

    transition:
        transform 160ms ease,
        box-shadow 160ms ease,
        filter 160ms ease;
}

.forum-submit-btn:hover {
    color: #031716;

    filter: brightness(1.06);

    transform: translateY(-2px);

    box-shadow: 0 11px 24px rgba(0, 0, 0, .24);
}


/* =========================================================
   LOCKED TOPIC BOX
   ========================================================= */

.forum-locked-box {
    display: flex;
    align-items: center;

    gap: .85rem;

    padding: 1rem 1.15rem;

    color: #ffadb5;

    background:
        linear-gradient(
            135deg,
            rgba(220, 53, 69, .09),
            rgba(80, 25, 34, .12)
        );

    border: 1px solid rgba(220, 53, 69, .17);

    border-radius: .85rem;

    box-shadow: 0 8px 22px rgba(0, 0, 0, .12);
}

.forum-locked-box > i {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;

    flex-shrink: 0;

    color: #ff858e;

    background: rgba(220, 53, 69, .09);

    border-radius: .6rem;
}

.forum-locked-box strong {
    color: #ffd0d4;

    font-size: .84rem;
}

.forum-locked-box div div {
    margin-top: .15rem;

    color: #a77b81;

    font-size: .72rem;
}


/* =========================================================
   PAGINATION
   ========================================================= */

.forum-pagination {
    display: flex;
    justify-content: center;

    padding-top: .25rem;
}

.forum-pagination .pagination {
    gap: .3rem;
    margin-bottom: 0;
}

.forum-pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 36px;
    min-height: 36px;

    color: #aebfd0;

    background: rgba(22, 32, 51, .8);

    border: 1px solid var(--ui-border);

    border-radius: .55rem;

    font-size: .74rem;
    font-weight: 650;

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
}

.forum-pagination .page-item.disabled .page-link {
    color: #4e6175;

    background: rgba(15, 23, 42, .5);

    border-color: rgba(148, 163, 184, .07);
}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 991.98px) {

    .forum-topic-header {
        padding: 1.15rem;
    }

    .forum-topic-title {
        font-size: 1.65rem;
    }

    .forum-user-panel {
        padding-inline: .75rem;
    }

    .forum-avatar {
        width: 76px;
        height: 76px;
    }
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767.98px) {

    .forum-breadcrumb {
        width: 100%;
    }

    .forum-breadcrumb-current {
        min-width: 0;
        max-width: 65%;
    }

    .forum-breadcrumb-current span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    .forum-topic-header {
        padding: 1rem;
    }

    .forum-topic-header > .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }

    .forum-topic-title {
        font-size: 1.4rem;
    }

    .forum-topic-meta {
        gap: .45rem .75rem;
        font-size: .72rem;
    }


    .forum-topic-actions {
        width: 100%;

        justify-content: flex-start;

        margin-top: .5rem;
    }

    .forum-topic-action {
        min-height: 32px;

        padding: .4rem .55rem;

        font-size: .65rem;
    }


    .forum-post-actions {
        width: 100%;

        justify-content: flex-start;

        margin-left: 0;
    }

    .original-post-label {
        align-items: flex-start;
        flex-wrap: wrap;

        padding: .65rem .8rem;
    }


    .forum-user-panel {
        padding: 1rem;

        border-right: 0;
        border-bottom: 1px solid var(--ui-border);
    }

    .forum-avatar {
        width: 70px;
        height: 70px;
    }

    .forum-user-info {
        display: inline-block;

        margin-inline: .35rem;
    }


    .forum-post-content {
        min-height: auto;

        padding: 1rem;
    }

    .forum-post-header {
        align-items: flex-start;
        flex-wrap: wrap;

        gap: .55rem;
    }

    .forum-post-meta-left {
        gap: .4rem .75rem;
    }

    .forum-post-body {
        font-size: .94rem;
        line-height: 1.72;
    }


    .forum-follow-btn,
    .forum-latest-btn {
        width: 100%;
    }


    .forum-like-section {
        gap: .45rem;
    }


    .forum-reply-box-header {
        padding: .9rem 1rem;
    }

    .forum-reply-box .card-body {
        padding: 1rem !important;
    }

    .forum-textarea {
        min-height: 135px;
    }

    .forum-submit-btn {
        width: 100%;
    }
}


/* =========================================================
   SMALL MOBILE
   ========================================================= */

@media (max-width: 575.98px) {

    .forum-topic-title {
        font-size: 1.25rem;
    }

    .forum-topic-category {
        font-size: .66rem;
    }

    .forum-topic-meta {
        font-size: .68rem;
    }

    .forum-post-body {
        font-size: .92rem;
    }

    .forum-post-action {
        font-size: .62rem;
    }

    .forum-reaction-btn {
        width: 31px;
        height: 31px;
    }

    .forum-reaction-emoji {
        font-size: 1rem;
    }

    .forum-liked-by {
        font-size: .66rem;
    }
}


/* =========================================================
   ACCESSIBILITY / REDUCED MOTION
   ========================================================= */

.forum-topic-action:focus-visible,
.forum-follow-btn:focus-visible,
.forum-latest-btn:focus-visible,
.forum-post-action:focus-visible,
.forum-reaction-btn:focus-visible,
.liked-by-more:focus-visible,
.forum-submit-btn:focus-visible,
.forum-breadcrumb-link:focus-visible {
    outline: 0;

    box-shadow:
        0 0 0 .2rem rgba(99, 210, 198, .14);
}

@media (prefers-reduced-motion: reduce) {

    .forum-topic-action,
    .forum-follow-btn,
    .forum-latest-btn,
    .forum-post-action,
    .forum-reaction-btn,
    .forum-submit-btn,
    .forum-breadcrumb-link,
    .forum-pagination .page-link {
        transition: none;
    }
}

.forum-user-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    margin-top: 6px;
    padding: 5px 11px;
    border: 1px solid color-mix(
        in srgb,
        var(--user-class-color) 35%,
        transparent
    );
    border-radius: 999px;
    background: color-mix(
        in srgb,
        var(--user-class-color) 10%,
        transparent
    );
    color: var(--user-class-color);
    font-size: 0.74rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: 0.02em;
}


/* =========================================================
   BBCode TOOLBAR
   ========================================================= */

.bbcode-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 9px 10px;
    margin-bottom: 0;
    border: 1px solid rgba(203, 213, 225, 0.12);
    border-bottom: 0;
    border-radius: 12px 12px 0 0;
    background: rgba(15, 23, 42, 0.88);
}

.bbcode-btn {
    width: 34px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid rgba(203, 213, 225, 0.10);
    border-radius: 7px;
    background: rgba(255, 255, 255, 0.045);
    color: #b8c7d9;
    font-size: 0.82rem;
    cursor: pointer;
    transition:
        color 0.18s ease,
        background 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.bbcode-btn:hover {
    color: #63d2c6;
    background: rgba(99, 210, 198, 0.10);
    border-color: rgba(99, 210, 198, 0.30);
    transform: translateY(-1px);
}

.bbcode-btn:active {
    transform: translateY(0);
}

.bbcode-btn:focus-visible {
    outline: 2px solid rgba(99, 210, 198, 0.55);
    outline-offset: 2px;
}

.bbcode-divider {
    width: 1px;
    height: 22px;
    margin: 0 3px;
    background: rgba(203, 213, 225, 0.12);
}

/* Make textarea connect visually to toolbar */

.bbcode-toolbar + textarea {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

@media (max-width: 576px) {

    .bbcode-toolbar {
        gap: 5px;
        padding: 8px;
    }

    .bbcode-btn {
        width: 32px;
        height: 30px;
    }

    .bbcode-divider {
        display: none;
    }
}


/* =========================================================
   PREVENT HORIZONTAL OVERFLOW
   ========================================================= */

html,
body {
    max-width: 100%;
    overflow-x: hidden;
}
</style>