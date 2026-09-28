<style>
/* =========================================================
   FILEIPLAY MOVIE DETAILS — FORUM STYLE
   ========================================================= */

.movie-page{
    position:relative;
    min-height:100vh;
    background:#070b14;
    color:#e2e8f0;
    overflow-x:hidden;
}

.movie-backdrop-wrapper{
    position:fixed;
    inset:0;
    z-index:0;
    overflow:hidden;
    pointer-events:none;
}

.movie-backdrop-image{
    width:100%;
    height:100%;
    object-fit:cover;
    filter:blur(1px) brightness(.28) saturate(.72);
    transform:scale(1.04);
}

.movie-backdrop-overlay{
    position:absolute;
    inset:0;
    z-index:1;
    background:linear-gradient(
        to bottom,
        rgba(220, 225, 241, 0),
        rgba(62, 71, 93, 0.49) 48%,
        #070b14 90%,
        #070b14 100%
    );
}

.movie-page > .container-fluid{
    position:relative;
    z-index:3;
}

/* HERO */
.movie-hero-card{
    position:relative;
    z-index:5;
    margin-bottom:34px;
    padding:26px;
    background:linear-gradient(
        135deg,
        rgba(64, 76, 101, 0.2),
        rgba(3, 6, 12, 0.56)
    );
    border:1px solid rgba(255,255,255,.08);
    border-radius:.8rem;
    box-shadow:0 18px 50px rgba(0,0,0,.42);
    backdrop-filter:blur(1px);
}

.movie-poster-container{
    position:relative;
    max-width:280px;
    margin:auto;
}

.movie-main-poster{
    position:relative;
    z-index:2;
    display:block;
    width:100%;
    border-radius:.65rem;
    border:1px solid rgba(255,255,255,.09);
    box-shadow:0 18px 38px rgba(0,0,0,.45);
    transition:.25s ease;
}

.movie-main-poster:hover{
    transform:translateY(-3px);
}

.movie-poster-glow{
    position:absolute;
    inset:12% 8%;
    z-index:1;
    background:rgba(45,212,191,.10);
    filter:blur(35px);
    border-radius:50%;
}

.movie-poster-actions{
    position:absolute;
    z-index:4;
    left:50%;
    bottom:14px;
    transform:translateX(-50%);
    display:flex;
    gap:8px;
}

.movie-circle-btn{
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:.55rem;
    color:#fff;
    text-decoration:none;
    border:1px solid rgba(255,255,255,.14);
    box-shadow:0 8px 18px rgba(0,0,0,.30);
    transition:.2s ease;
}

.movie-circle-btn:hover{
    transform:translateY(-2px);
    color:#fff;
    border-color:rgba(255,255,255,.28);
}

.tmdb-btn{
    background:rgba(1,180,228,.82);
}

.imdb-btn{
    background:rgba(245,197,24,.88);
    color:#111;
}

.imdb-btn:hover{
    color:#111;
}

/* INFO */
.movie-title-row{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
}

.movie-title{
    margin:0;
    color:#f1f5f9;
    font-size:2.45rem;
    font-weight:700;
    line-height:1.15;
    letter-spacing:-.02em;
}

.movie-year-pill,
.movie-rating-pill{
    display:inline-flex;
    align-items:center;
    padding:.28rem .55rem;
    border-radius:.4rem;
    font-size:.72rem;
    font-weight:600;
}

.movie-year-pill{
    background:rgba(45,212,191,.08);
    border:1px solid rgba(45,212,191,.20);
    color:#8ff5e6;
}

.movie-rating-pill{
    background:rgba(245,197,24,.10);
    border:1px solid rgba(245,197,24,.18);
    color:#f5c518;
}

.movie-views-pill i{
    margin-right:4px;
}

.movie-genres-row{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-top:14px;
}

.movie-genre-pill{
    padding:.28rem .55rem;
    border-radius:.4rem;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.08);
    color:rgba(226,232,240,.72);
    font-size:.72rem;
}

.movie-overview-box{
    max-width:900px;
    margin-top:18px;
    padding:.8rem .95rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    border-left:3px solid rgba(45,212,191,.42);
    border-radius:.5rem;
}

.movie-overview-box p{
    margin:0;
    color:rgba(226,232,240,.78);
    font-size:.86rem;
    line-height:1.65;
}

/* META */
.movie-meta-grid{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:18px;
}

.movie-meta-card{
    min-width:115px;
    padding:.65rem .75rem;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.065);
    border-radius:.5rem;
}

.movie-meta-label{
    display:block;
    margin-bottom:3px;
    color:rgba(203,213,225,.48);
    font-size:.66rem;
    font-weight:600;
    text-transform:uppercase;
}

.movie-meta-value{
    color:#e8f0f7;
    font-size:.82rem;
    font-weight:600;
}

/* COLLECTION */
.collection-box{
    display:inline-block;
    max-width:100%;
    margin-top:18px;
    padding:.65rem .8rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    border-radius:.5rem;
}

.collection-label{
    display:block;
    margin-bottom:3px;
    color:rgba(203,213,225,.48);
    font-size:.64rem;
    font-weight:600;
    letter-spacing:1.2px;
}

.collection-link{
    color:#8ff5e6;
    font-size:.82rem;
    font-weight:600;
    text-decoration:none;
}

.collection-link:hover{
    color:#fff;
}

/* TRAILERS / WATCH */
.movie-trailer-section{
    margin-top:20px;
}

.movie-section-title{
    margin-bottom:10px;
    color:#e8f0f7;
    font-size:.92rem;
    font-weight:600;
}

.movie-trailer-buttons{
    display:flex;
    flex-wrap:wrap;
    gap:7px;
}

.movie-trailer-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    max-width:100%;
    padding:.42rem .65rem;
    border-radius:.45rem;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(255,255,255,.08);
    color:rgba(226,232,240,.78);
    font-size:.73rem;
    text-decoration:none;
    transition:.2s ease;
}

.movie-trailer-btn i{
    color:var(--ui-accent,#2dd4bf);
}

.movie-trailer-btn:hover{
    color:#fff;
    background:rgba(45,212,191,.07);
    border-color:rgba(45,212,191,.25);
    transform:translateY(-1px);
}

.watch-actions{
    margin-top:18px;
}

.watch-now-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .85rem;
    border-radius:.5rem;
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.25);
    color:#8ff5e6;
    font-size:.78rem;
    font-weight:600;
    text-decoration:none;
    transition:.2s ease;
}

.watch-now-btn:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
    transform:translateY(-1px);
}

.watch-delete-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .85rem;
    border-radius:.5rem;
    background:rgba(239,68,68,.10);
    border:1px solid rgba(239,68,68,.30);
    color:#fca5a5;
    font-size:.78rem;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    transition:.2s ease;
    font-family:inherit;
}

.watch-delete-btn:hover{
    color:#fff;
    background:#ef4444;
    border-color:#ef4444;
    transform:translateY(-1px);
}

.vip-required-box{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:.55rem .8rem;
    border-radius:.5rem;
    background:rgba(239,68,68,.08);
    border:1px solid rgba(239,68,68,.18);
    color:#fca5a5;
    font-size:.76rem;
    font-weight:600;
}

/* CAST */
.movie-cast-section{
    position:relative;
    z-index:5;
    width:100%;
    margin:0 auto 34px;
    text-align:center;
}

.movie-section-header{
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
}

.movie-section-header h2{
    margin:0 0 12px;
    color:#e8f0f7;
    font-size:1.1rem;
    font-weight:650;
    text-align:center;
}

.movie-cast-slider{
    display:flex;
    align-items:flex-start;
    justify-content:center;
    gap:12px;
    width:100%;
    overflow-x:auto;
    padding:2px 2px 10px;
    scrollbar-width:thin;
    scrollbar-color:rgba(45,212,191,.25) transparent;
}

.movie-cast-slider::-webkit-scrollbar{
    height:5px;
}

.movie-cast-slider::-webkit-scrollbar-thumb{
    background:rgba(45,212,191,.22);
    border-radius:10px;
}

.movie-cast-card{
    flex:0 0 145px;
    width:145px;
    min-width:145px;
    overflow:hidden;
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.94),
        rgba(15,23,42,.90)
    );
    border:1px solid rgba(255,255,255,.07);
    border-radius:.65rem;
    box-shadow:0 8px 20px rgba(0,0,0,.22);
    text-decoration:none;
    transition:.2s ease;
}

.movie-cast-card:hover{
    transform:translateY(-3px);
    border-color:rgba(45,212,191,.22);
    box-shadow:0 12px 26px rgba(0,0,0,.30);
}

.movie-cast-image-wrapper{
    width:100%;
    height:205px;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    background:rgba(7,14,27,.72);
}

.movie-cast-image{
    display:block;
    width:100%;
    height:100%;
    object-fit:contain;
    object-position:center center;
    margin:0 auto;
    transition:.25s ease;
}

.movie-cast-card:hover .movie-cast-image{
    transform:scale(1.035);
}

.movie-cast-info{
    padding:.65rem .7rem;
    text-align:center;
}

.movie-cast-info h6{
    margin:0 0 .2rem;
    overflow:hidden;
    color:#e8f0f7;
    font-size:.78rem;
    font-weight:600;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.movie-cast-info p{
    margin:0;
    overflow:hidden;
    color:rgba(203,213,225,.52);
    font-size:.68rem;
    line-height:1.35;
    text-overflow:ellipsis;
    white-space:nowrap;
}

/* SIMILAR */
.similar-section{
    margin-top:4px;
}

.similar-card{
    display:block;
}

.similar-card .movie-cast-image-wrapper{
    height:215px;
}

/* TORRENTS */
.torrent-section{
    text-align:left;
}

.torrent-section .movie-section-header{
    justify-content:space-between;
}

.torrent-section .movie-section-header h2{
    text-align:left;
}

.results-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:24px;
    height:19px;
    padding:0 6px;
    margin-left:6px;
    border-radius:999px;
    background:#22d3c5;
    color:#061311;
    font-size:11px;
    font-weight:800;
    vertical-align:middle;
}

.btn-more-torrents{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:.4rem .65rem;
    border:1px solid rgba(255,255,255,.10);
    border-radius:.45rem;
    background:rgba(255,255,255,.035);
    color:rgba(226,232,240,.78);
    font-size:.72rem;
    font-weight:600;
    text-decoration:none;
    transition:.2s ease;
}

.btn-more-torrents:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
}

.torrent-list{
    display:flex;
    flex-direction:column;
    gap:7px;
}

.torrent-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:.65rem .75rem;
    border-radius:.5rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    color:#e8f0f7;
    text-decoration:none;
    transition:.2s ease;
}

.torrent-row:hover{
    background:rgba(45,212,191,.055);
    border-color:rgba(45,212,191,.20);
}

.torrent-row-main{
    display:flex;
    flex-direction:column;
    min-width:0;
}

.torrent-row-main strong{
    overflow:hidden;
    color:#e8f0f7;
    font-size:.78rem;
    font-weight:600;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.torrent-row-main small{
    font-size:.68rem;
}

.torrent-row-meta{
    display:flex;
    align-items:center;
    gap:10px;
    flex:0 0 auto;
    font-size:.72rem;
    font-weight:600;
}

.torrent-row-meta .seeders{
    color:#4ade80;
}

.torrent-row-meta .leechers{
    color:#f87171;
}

.torrent-row-meta i.bi-chevron-right{
    color:rgba(255,255,255,.42);
}

/* COMMENTS */
.comments-modern-wrapper{
    position:relative;
    z-index:5;
    margin-bottom:34px;
}

.comments-modern-card{
    background:linear-gradient(
        135deg,
        rgba(22,32,51,.94),
        rgba(15,23,42,.88)
    );
    border:1px solid rgba(255,255,255,.08);
    border-radius:.8rem;
    padding:18px;
    box-shadow:0 14px 36px rgba(0,0,0,.30);
    backdrop-filter:blur(14px);
}

.comments-header{
    border-bottom:1px solid rgba(255,255,255,.06);
    margin-bottom:14px;
    padding-bottom:10px;
}

.comments-header h3{
    margin:0;
    color:#e8f0f7;
    font-size:1rem;
    font-weight:650;
}

.comment-form-modern textarea{
    width:100%;
    min-height:105px;
    padding:.7rem .8rem;
    resize:vertical;
    outline:none;
    border:1px solid rgba(255,255,255,.08);
    border-radius:.5rem;
    background:rgba(255,255,255,.025);
    color:#e8f0f7;
    font-size:.8rem;
}

.comment-form-modern textarea:focus{
    border-color:rgba(45,212,191,.30);
    box-shadow:0 0 0 2px rgba(45,212,191,.06);
}

.comment-form-modern textarea::placeholder{
    color:rgba(203,213,225,.38);
}

.submit-comment-btn{
    margin-top:8px;
    padding:.45rem .75rem;
    border:1px solid rgba(45,212,191,.22);
    border-radius:.45rem;
    background:rgba(45,212,191,.09);
    color:#8ff5e6;
    font-size:.74rem;
    font-weight:600;
    transition:.2s ease;
}

.submit-comment-btn:hover{
    color:#061311;
    background:#2dd4c5;
    border-color:#2dd4c5;
}

.single-comment-card{
    margin-top:10px;
    padding:.75rem;
    border-radius:.55rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.06);
}

.comment-user-row{
    display:flex;
    align-items:center;
    gap:9px;
    margin-bottom:8px;
}

.comment-avatar{
    width:34px;
    height:34px;
    flex:0 0 34px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:rgba(45,212,191,.10);
    border:1px solid rgba(45,212,191,.20);
    color:#8ff5e6;
    font-size:.72rem;
    font-weight:700;
}

.comment-user-row h6{
    margin:0 0 2px;
    color:#e8f0f7;
    font-size:.76rem;
    font-weight:600;
}

.comment-user-row small{
    color:rgba(203,213,225,.45);
    font-size:.65rem;
}

.comment-text{
    margin:0;
    color:rgba(226,232,240,.76);
    font-size:.87rem;
    line-height:1.55;
}

.no-comments-box{
    padding:22px;
    border:1px dashed rgba(255,255,255,.08);
    border-radius:.55rem;
    background:rgba(255,255,255,.02);
    color:rgba(203,213,225,.48);
    font-size:.75rem;
    text-align:center;
}

/* RESPONSIVE */
@media(max-width:992px){
    .movie-hero-card{
        margin-top:82px;
        padding:22px;
    }

    .movie-title{
        font-size:2rem;
    }

    .movie-poster-container{
        max-width:245px;
    }
}

@media(max-width:768px){
    .movie-hero-card{
        margin-top:70px;
        margin-bottom:26px;
        padding:16px;
        border-radius:.65rem;
    }

    .movie-title{
        font-size:1.65rem;
    }

    .movie-overview-box p{
        font-size:.82rem;
    }

    .movie-meta-grid{
        gap:6px;
    }

    .movie-meta-card{
        min-width:calc(50% - 3px);
        padding:.55rem .6rem;
    }

    .movie-trailer-btn{
        width:100%;
    }

    .movie-cast-section{
        margin-bottom:28px;
    }

    .movie-section-header h2{
        font-size:1rem;
    }

    .movie-cast-slider{
        justify-content:flex-start;
    }

    .movie-cast-card{
        flex-basis:125px;
        min-width:125px;
        width:125px;
    }

    .movie-cast-image-wrapper{
        height:175px;
    }

    .similar-card .movie-cast-image-wrapper{
        height:185px;
    }

    .comments-modern-card{
        padding:14px;
    }

    .torrent-row{
        align-items:flex-start;
        flex-direction:column;
    }

    .torrent-row-meta{
        width:100%;
        justify-content:space-between;
    }
}
.movie-cast-image-wrapper{position:relative}

.in-library-badge{
    position:absolute;
    top:8px;
    left:8px;
    z-index:3;
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:.22rem .5rem;
    border-radius:999px;
    background:rgba(45,212,191,.92);
    color:#06291f;
    font-size:.62rem;
    font-weight:700;
    line-height:1;
    box-shadow:0 3px 8px rgba(0,0,0,.35);
    pointer-events:none;
}

.similar-title-link{
    color:#8ff5e6;
    text-decoration:none;
}
.similar-title-link:hover{
    color:#fff;
    text-decoration:underline;
}

/* =========================================================
   SIMILAR MOVIES — DATABASE STATUS
   ========================================================= */

.similar-card {
    position: relative;
}

/* Available in our database */
.similar-card-available {
    cursor: pointer;
}

/* Not available in our database */
.similar-card-unavailable {
    cursor: default;
    opacity: .78;
}

/* Make unavailable poster grayscale */
.similar-poster-unavailable {
    filter: grayscale(100%);
    opacity: .60;
    transition:
        filter .25s ease,
        opacity .25s ease,
        transform .25s ease;
}

/* Keep available posters normal */
.similar-card-available .movie-cast-image {
    filter: none;
    opacity: 1;
}

/* Available hover */
.similar-card-available:hover {
    transform: translateY(-3px);
}

/* Unavailable hover — don't lift the card */
.similar-card-unavailable:hover {
    transform: none;
}

/* Slight hover effect on unavailable poster */
.similar-card-unavailable:hover .similar-poster-unavailable {
    filter: grayscale(100%);
    opacity: .72;
    transform: scale(1.02);
}


/* =========================================================
   STATUS ICON
   ========================================================= */

.similar-status-badge {
    position: absolute;
    top: 8px;
    right: 8px;

    width: 28px;
    height: 28px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: rgba(5, 10, 18, .88);
    backdrop-filter: blur(6px);

    font-size: 13px;

    z-index: 4;

    pointer-events: none;

    box-shadow: 0 4px 10px rgba(0, 0, 0, .35);
}


/* Online */
.similar-status-online {
    color: #4ade80;
    border: 1px solid rgba(74, 222, 128, .35);
}


/* Not in database */
.similar-status-missing {
    color: rgba(255, 255, 255, .45);
    border: 1px solid rgba(255, 255, 255, .12);
}


/* =========================================================
   UNAVAILABLE TITLE
   ========================================================= */

.similar-title-unavailable {
    color: rgba(232, 240, 247, .58);
    cursor: default;
}


/* Available title */
.similar-title-link {
    color: #8ff5e6;
    text-decoration: none;
    transition: color .2s ease;
}

.similar-title-link:hover {
    color: #fff;
    text-decoration: underline;
}

/* =========================================================
   UBLOCK ORIGIN NOTICE
========================================================= */

.ublock-notice {
    position: relative;

    display: flex;
    align-items: center;
    gap: 16px;

    width: 100%;

    margin: 0 0 25px 0;
    padding: 16px 18px;

    background:
        linear-gradient(
            135deg,
            rgba(25, 29, 36, 0.96),
            rgba(16, 19, 24, 0.96)
        );

    border: 1px solid rgba(255, 255, 255, 0.08);
    border-left: 4px solid #dc3545;

    border-radius: 14px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.03);

    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}


/* =========================================================
   ICON
========================================================= */

.ublock-notice-icon {
    flex: 0 0 auto;

    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            145deg,
            rgba(220, 53, 69, 0.25),
            rgba(220, 53, 69, 0.08)
        );

    border: 1px solid rgba(220, 53, 69, 0.25);

    color: #ff5b6b;

    font-size: 1.45rem;

    box-shadow:
        0 5px 15px rgba(220, 53, 69, 0.12);
}


/* =========================================================
   CONTENT
========================================================= */

.ublock-notice-content {
    flex: 1;
    min-width: 0;
}


.ublock-notice-title {
    margin-bottom: 4px;

    color: #ffffff;

    font-size: 1rem;
    font-weight: 700;

    letter-spacing: 0.2px;
}


.ublock-notice-text {
    color: rgba(255, 255, 255, 0.65);

    font-size: 0.88rem;
    line-height: 1.55;
}


.ublock-notice-text strong {
    color: #ffffff;
    font-weight: 700;
}


/* =========================================================
   BUTTON
========================================================= */

.ublock-notice-button {
    flex: 0 0 auto;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 9px 15px;

    color: #ffffff;
    text-decoration: none;

    font-size: 0.82rem;
    font-weight: 600;

    white-space: nowrap;

    background:
        linear-gradient(
            135deg,
            #dc3545,
            #b02a37
        );

    border: 1px solid rgba(255, 255, 255, 0.08);

    border-radius: 9px;

    box-shadow:
        0 5px 15px rgba(220, 53, 69, 0.2);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}


.ublock-notice-button:hover {
    color: #ffffff;

    background:
        linear-gradient(
            135deg,
            #e44757,
            #c02d3d
        );

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(220, 53, 69, 0.3);
}


.ublock-notice-button i {
    font-size: 0.85rem;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767.98px) {

    .ublock-notice {
        align-items: flex-start;
        flex-wrap: wrap;

        gap: 12px;

        padding: 14px;
        margin-bottom: 18px;
    }


    .ublock-notice-icon {
        width: 42px;
        height: 42px;

        font-size: 1.2rem;
    }


    .ublock-notice-content {
        flex: 1 1 calc(100% - 60px);
    }


    .ublock-notice-title {
        font-size: 0.95rem;
    }


    .ublock-notice-text {
        font-size: 0.82rem;
    }


    .ublock-notice-button {
        width: 100%;

        padding: 10px 14px;

        margin-top: 2px;
    }

}
</style>