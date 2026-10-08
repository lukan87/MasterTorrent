<style>
/* HERO */
.movie-hero-card{
    position:relative;
    z-index:5;
    margin-bottom:34px;
    padding:26px;
    background:linear-gradient(
        135deg,
        var(--theme-surface-alt, rgba(42,49,66,0.2)),
        var(--theme-surface-alt, rgba(2,4,8,0.56))
    );
    border:1px solid var(--theme-border, rgba(255,255,255,.08));
    border-radius:.8rem;
    box-shadow:0 18px 50px var(--theme-shadow, rgba(0,0,0,.42));
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
    color:var(--theme-text, #fff);
    text-decoration:none;
    border:1px solid var(--theme-border, rgba(255,255,255,.14));
    box-shadow:0 8px 18px var(--theme-shadow, rgba(0,0,0,.30));
    transition:.2s ease;
}

.movie-circle-btn:hover{
    transform:translateY(-2px);
    color:var(--theme-text, #fff);
    border-color:var(--theme-border, rgba(255,255,255,.28));
}

.tmdb-btn{
    background:var(--theme-teal-action, rgba(1,180,228,.82));
}

.imdb-btn{
    background:var(--theme-amber-action, rgba(245,197,24,.88));
    color:var(--theme-on-action, #111);
}

.imdb-btn:hover{
    color:var(--theme-text, #111);
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
    color:var(--theme-text, #f1f5f9);
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
    font-size:var(--site-font-small, 13px);
    font-weight:600;
}

.movie-year-pill{
    background:var(--theme-teal-soft, rgba(45,212,191,.08));
    border:1px solid var(--theme-teal-border, rgba(45,212,191,.20));
    color:var(--theme-teal-text, #8ff5e6);
}

.movie-rating-pill{
    background:var(--theme-amber-soft, rgba(245,197,24,.10));
    border:1px solid var(--theme-amber-border, rgba(245,197,24,.18));
    color:var(--theme-amber-text, #f5c518);
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
    background:var(--theme-surface-alt, rgba(255,255,255,0.0245));
    border:1px solid var(--theme-border, rgba(255,255,255,.08));
    color:var(--theme-muted, rgba(226,232,240,.72));
    font-size:var(--site-font-small, 13px);
}

.movie-overview-box{
    max-width:900px;
    margin-top:18px;
    padding:.8rem .95rem;
    background:var(--theme-surface-alt, rgba(255,255,255,0.0175));
    border:1px solid var(--theme-border, rgba(255,255,255,.065));
    border-left:3px solid var(--theme-teal-border, rgba(45,212,191,.42));
    border-radius:.5rem;
}

.movie-overview-box p{
    margin:0;
    color:var(--theme-muted, rgba(226,232,240,.78));
    font-size:var(--site-font-body, 13px);
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
    background:var(--theme-surface-alt, rgba(255,255,255,0.021));
    border:1px solid var(--theme-border, rgba(255,255,255,.065));
    border-radius:.5rem;
}

.movie-meta-label{
    display:block;
    margin-bottom:3px;
    color:var(--theme-muted, rgba(203,213,225,.48));
    font-size:var(--site-font-small, 13px);
    font-weight:600;
    text-transform:uppercase;
}

.movie-meta-value{
    color:var(--theme-text, #e8f0f7);
    font-size:var(--site-font-body, 13px);
    font-weight:600;
}
</style>
