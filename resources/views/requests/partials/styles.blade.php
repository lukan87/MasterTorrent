<style>
.rq { --rq-border:var(--theme-border, rgba(148,163,184,.2)); color:var(--theme-text, #e2e8f0); max-width:1320px; margin:0 auto; padding:2rem 0; }
.rq .rq-panel { background:linear-gradient(135deg,var(--theme-surface, #0f1824),var(--theme-surface, #0a1018)); border:1px solid var(--rq-border); border-radius:18px; padding:1.5rem; }
.rq .rq-hero { display:flex; justify-content:space-between; align-items:center; gap:1.5rem; margin-bottom:1.5rem; border-top:3px solid var(--theme-teal-border, #22d3ee); }
.rq .rq-kicker { color:var(--theme-teal-text, #67e8f9); font-size:var(--site-font-small, 13px); font-weight:700; letter-spacing:.14em; text-transform:uppercase; margin-bottom:.6rem; }
.rq h1 { font-size:clamp(1.6rem,3vw,2.3rem); font-weight:700; overflow-wrap:anywhere; }
.rq h2 { font-size:1.15rem; font-weight:700; }
.rq .rq-muted { color:var(--theme-muted, #a8b7ca); }
.rq .rq-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
.rq .rq-stat strong { display:block; font-size:1.7rem; color:var(--theme-text, #f8fafc); }
.rq .rq-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
.rq .rq-card { display:flex; gap:1.2rem; padding:1.2rem; }
.rq .rq-card-body { min-width:0; flex:1; }
.rq .rq-card h2 { margin:.7rem 0; line-height:1.45; overflow-wrap:anywhere; }
.rq .rq-card h2 a { color:var(--theme-text, #f1f5f9); text-decoration:none; }
.rq .rq-card h2 a:hover { color:var(--theme-teal-text, #67e8f9); }
.rq .rq-poster { width:86px; height:128px; flex-shrink:0; object-fit:cover; border-radius:10px; background:#17202e; }
.rq [hidden] { display:none !important; }
.rq .rq-placeholder { display:flex; align-items:center; justify-content:center; color:var(--theme-muted, #91a7c0); font-size:2rem; }
.rq .rq-badge { display:inline-block; padding:.3rem .65rem; font-size:var(--site-font-small, 13px); font-weight:700; border-radius:50px; background:var(--theme-surface, #123e4a); color:var(--theme-teal-text, #7ee7f4); }
.rq .rq-badge-filled { background:var(--theme-surface, #0f2820); color:var(--theme-green-text, #8aebbb); }
.rq .rq-btn { background:var(--theme-teal-action, #67e8f9); border:1px solid var(--theme-teal-border, #67e8f9); color:var(--theme-on-action, #08202a); font-weight:700; border-radius:9px; padding:.6rem 1rem; white-space:nowrap; }
.rq .rq-btn:hover { background:var(--theme-teal-action, #a5f3fc); color:var(--theme-on-action, #08202a); }
.rq .rq-secondary { background:transparent; border:1px solid var(--theme-border, #60758d); color:var(--theme-text, #e2e8f0); border-radius:9px; padding:.6rem 1rem; }
.rq .rq-secondary:hover { background:var(--theme-surface, #17202e); color:var(--theme-text, white); }
.rq .form-control,.rq .form-select { background-color:var(--theme-control, #080f18); color:var(--theme-text, #edf4fc); border-color:var(--theme-border, #53667e); border-radius:8px; }
.rq .form-control::placeholder { color:var(--theme-muted, #92a3b8); }
.rq .form-control:focus,.rq .form-select:focus { border-color:var(--theme-teal-border, #67e8f9); box-shadow:0 0 0 3px var(--theme-shadow, #22d3ee30); }
.rq label { font-size:var(--site-font-body, 13px); font-weight:600; margin-bottom:.45rem; }
.rq .rq-detail { display:grid; grid-template-columns:minmax(0,2fr) minmax(260px,1fr); gap:1.5rem; }
.rq .rq-description { white-space:pre-wrap; overflow-wrap:anywhere; line-height:1.8; }
.rq .rq-actions { display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; }
.rq a:focus-visible,.rq button:focus-visible { outline:3px solid var(--theme-teal-border, #67e8f9); outline-offset:4px; }
.rq .rq-empty { text-align:center; padding:3rem 1rem; }
@media(max-width:767px) { .rq { padding:1rem 0; } .rq .rq-grid,.rq .rq-detail { grid-template-columns:1fr; } .rq .rq-hero { align-items:flex-start; flex-direction:column; } .rq .rq-panel { padding:1rem; } .rq .rq-stats { gap:.5rem; } .rq .rq-stat { font-size:var(--site-font-body, 13px); } }

.rq .rq-vote { display:inline-flex; align-items:center; gap:.6rem; border:1px solid var(--theme-border, #46617b); border-radius:10px; color:var(--theme-text, #dcebf9); background:var(--theme-surface, #0c1926); padding:.55rem .85rem; }
.rq .rq-vote:hover,.rq .rq-voted { background:var(--theme-teal-soft, #124352); border-color:var(--theme-teal-border, #67e8f9); color:var(--theme-teal-text, #a5f3fc); }
.rq .rq-vote i { font-size:1.2rem; }
.rq .rq-feature { position:relative; overflow:hidden; padding:2rem; }
.rq .rq-backdrop { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.2; }
.rq .rq-feature::after { content:''; position:absolute; inset:0; background:linear-gradient(90deg,var(--theme-surface, #10192570),var(--theme-surface, #101925e8)); pointer-events:none; }
.rq .rq-feature-content { position:relative; z-index:1; display:flex; align-items:flex-start; gap:2rem; }
.rq .rq-media-poster { width:160px; border-radius:12px; box-shadow:0 12px 30px #0005; }
.rq .rq-media-copy { min-width:0; }
.rq .rq-media-copy h2 { font-size:clamp(1.4rem,2.5vw,2rem); }
.rq .rq-tagline { color:var(--theme-text, #c1d9e9); font-style:italic; }
.rq .rq-facts,.rq .rq-genres { display:flex; flex-wrap:wrap; gap:.5rem 1rem; margin:.8rem 0; font-size:var(--site-font-body, 13px); }
.rq .rq-facts i { color:var(--theme-amber-text, #fbd375); }
.rq .rq-genres span { border:1px solid var(--theme-border, #7891a84d); padding:.2rem .7rem; border-radius:30px; background:var(--theme-surface, #10192580); }
.rq .rq-overview { line-height:1.8; color:var(--theme-text, #dbe6f2); overflow-wrap:anywhere; }
.rq .rq-attribution { font-size:var(--site-font-small, 13px); color:var(--theme-muted, #9ab0c5); margin:1rem 0 0; }
.rq .rq-game { display:grid; grid-template-columns:minmax(200px,1fr) minmax(0,1.7fr); gap:2rem; }
.rq .rq-game-art img { width:100%; border-radius:12px; }
@media(max-width:600px) { .rq .rq-feature-content { flex-direction:column; gap:1rem; } .rq .rq-media-poster { width:110px; } .rq .rq-game { grid-template-columns:1fr; gap:1rem; } }
.rq .rq-voters span { cursor:help; border-bottom:1px dotted var(--theme-border, #60758d); overflow-wrap:anywhere; }
.rq .rq-voters span:focus-visible { outline:2px solid var(--theme-teal-border, #67e8f9); outline-offset:4px; }
</style>
