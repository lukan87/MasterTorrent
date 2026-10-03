<style>
/* Keep the landing page styles separate from topic and category views. */
.forum-index {
    --forum-surface: #0d141e;
    --forum-border: #2b3a4c;
    --forum-muted: #a5b4c7;
    --forum-accent: #80e0cf;
    color: #f1f5f9;
}
.forum-index .forum-index-header {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 260px;
    gap: 2rem;
    align-items: center;
    padding: clamp(1.5rem, 4vw, 3rem);
    border: 1px solid #354e58;
    border-radius: 24px;
    background: radial-gradient(ellipse at 95% 0%, #254b4b80, transparent 60%), linear-gradient(120deg, #101b24, #0d151d);
    box-shadow: 0 16px 40px #00000018;
}
.forum-index .forum-eyebrow {
    display: flex;
    align-items: center;
    gap: .55rem;
    color: var(--forum-accent);
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.forum-index .forum-page-title {
    margin: 1rem 0;
    font-size: clamp(2rem, 4vw, 3.3rem);
    font-weight: 750;
    line-height: 1.12;
    letter-spacing: -.045em;
    color: #f5fafb;
}
.forum-index .forum-page-subtitle { max-width: 510px; color: #b1c2cf; font-size: .96rem; line-height: 1.75; margin: 0; }
.forum-index .forum-header-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.6rem; }
.forum-index .forum-primary-btn,
.forum-index .forum-secondary-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: .65rem;
    min-height: 44px;
    padding: .65rem 1rem;
    border: 1px solid transparent;
    border-radius: 10px;
    font: inherit;
    font-size: .85rem;
    font-weight: 650;
    text-decoration: none;
    transition: background .18s ease, border-color .18s ease;
}
.forum-index .forum-primary-btn { background: var(--forum-accent); color: #102c2b; }
.forum-index .forum-primary-btn:hover { background: #a3f0e2; color: #102c2b; }
.forum-index .forum-secondary-btn { background: #ffffff04; border-color: #415262; color: #d5e1ea; }
.forum-index .forum-secondary-btn:hover { background: #ffffff0a; border-color: var(--forum-accent); color: #fff; }
.forum-index .forum-overview { padding: 1.5rem; text-align: center; background: #10222b80; border: 1px solid #80e0cf24; border-radius: 18px; }
.forum-index .forum-overview-symbol { color: var(--forum-accent); font-size: 2.6rem; margin-bottom: .5rem; }
.forum-index .forum-overview > p { color: #d3e4e5; font-size: .9rem; margin: 0 0 1.4rem; }
.forum-index .forum-stats { display: grid; grid-template-columns: 1fr 1fr; margin: 0; }
.forum-index .forum-stats > div { display: flex; flex-direction: column-reverse; gap: .2rem; min-width: 0; }
.forum-index .forum-stats > div + div { border-left: 1px solid #80e0cf26; }
.forum-index .forum-stats dt { color: var(--forum-muted); font-size: .75rem; font-weight: 400; }
.forum-index .forum-stats dd { margin: 0; font-size: 1.65rem; font-weight: 700; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.forum-index .forum-toolbar { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 0; margin-bottom: 1.5rem; border-bottom: 1px solid var(--forum-border); }
.forum-index .forum-search-bar { display: flex; align-items: center; gap: .85rem; flex: 1; min-width: 0; padding: .4rem .4rem .4rem 1rem; background: #0b121b; border: 1px solid var(--forum-border); border-radius: 13px; }
.forum-index .forum-search-bar > i { color: var(--forum-muted); }
.forum-index .forum-search-bar input { min-width: 0; width: 100%; flex: 1; padding: .5rem 0; background: transparent; border: 0; color: #f1f5f9; font: inherit; font-size: .9rem; }
.forum-index .forum-search-bar input::placeholder { color: #99a9bc; opacity: 1; }
.forum-index .forum-search-bar:focus-within { border-color: var(--forum-accent); }
.forum-index .forum-toolbar > a { flex-shrink: 0; }
.forum-index :is(a, button, input):focus-visible { outline: 2px solid var(--forum-accent); outline-offset: 4px; }
.forum-index #forum-categories { scroll-margin-top: 1.5rem; }
.forum-index .forum-section-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
.forum-index .forum-section-heading .forum-eyebrow { color: var(--forum-muted); font-size: .63rem; margin-bottom: .45rem; }
.forum-index .forum-section-heading h2 { color: #f1f5f9; font-size: 1.4rem; font-weight: 700; letter-spacing: -.025em; margin: 0; }
.forum-index .forum-category-total { color: #b7c7d7; font-size: .75rem; border: 1px solid var(--forum-border); border-radius: 30px; padding: .35rem .75rem; white-space: nowrap; }
.forum-index .forum-category-list { border: 1px solid var(--forum-border); border-radius: 16px; background: var(--forum-surface); }
.forum-index .forum-category-link { --category-accent: #80e0cf; display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; color: inherit; text-decoration: none; border-radius: 12px; transition: background .18s ease; }
.forum-index .forum-category-row + .forum-category-row { border-top: 1px solid var(--forum-border); }
.forum-index .forum-category-row:nth-child(4n+2) .forum-category-link { --category-accent: #acbaff; }
.forum-index .forum-category-row:nth-child(4n+3) .forum-category-link { --category-accent: #efc58f; }
.forum-index .forum-category-row:nth-child(4n+4) .forum-category-link { --category-accent: #9dccf4; }
.forum-index .forum-category-link:hover { background: #141e2a; }
.forum-index .forum-category-icon { display: grid; place-items: center; flex: 0 0 52px; height: 52px; border: 1px solid #ffffff12; border-radius: 14px; color: var(--category-accent); background: #ffffff06; font-size: 1.4rem; }
.forum-index .forum-category-content { flex: 1; min-width: 0; overflow-wrap: anywhere; }
.forum-index .forum-category-title { margin: 0; color: #e9f0f7; font-size: 1rem; font-weight: 650; line-height: 1.4; }
.forum-index .forum-category-link:hover .forum-category-title { color: var(--category-accent); }
.forum-index .forum-category-description { margin: .35rem 0 0; color: var(--forum-muted); font-size: .84rem; line-height: 1.6; }
.forum-index .forum-topic-count { display: flex; flex-direction: column; align-items: center; min-width: 76px; gap: .2rem; font-variant-numeric: tabular-nums; }
.forum-index .forum-topic-count strong { font-size: 1.1rem; font-weight: 650; }
.forum-index .forum-topic-count span { color: var(--forum-muted); font-size: .72rem; }
.forum-index .forum-category-arrow { color: #a5b4c7; font-size: 1.1rem; }
.forum-index .forum-community-note { display: flex; justify-content: center; gap: .6rem; margin: 1.5rem 0 0; color: var(--forum-muted); font-size: .76rem; line-height: 1.6; }
.forum-index .forum-community-note i { color: var(--forum-accent); }
.forum-index .forum-empty-state { padding: 3.5rem 1.5rem; text-align: center; }
.forum-index .forum-empty-icon { color: var(--forum-accent); font-size: 2.5rem; margin-bottom: 1rem; }
.forum-index .forum-empty-state h3 { font-size: 1.2rem; }
.forum-index .forum-empty-state p { color: var(--forum-muted); margin: 0; font-size: .9rem; }
/* Moderation tools retain their existing actions and confirmations. */
.forum-index .forum-deleted-categories { border: 1px solid #67434a; border-radius: 16px; background: #291f2980; padding: 1.5rem; }
.forum-index .forum-deleted-header,
.forum-index .forum-deleted-heading,
.forum-index .forum-deleted-category-card,
.forum-index .forum-deleted-category-main,
.forum-index .forum-deleted-actions { display: flex; align-items: center; gap: 1rem; }
.forum-index .forum-deleted-header { justify-content: space-between; margin-bottom: 1.25rem; }
.forum-index .forum-deleted-heading h3 { font-size: 1.1rem; margin: 0 0 .35rem; }
.forum-index .forum-deleted-heading p { color: var(--forum-muted); font-size: .82rem; margin: 0; }
.forum-index .forum-deleted-icon,
.forum-index .forum-deleted-category-icon { color: #f2a5ad; font-size: 1.3rem; }
.forum-index .forum-deleted-count { color: #eab1b8; font-size: .75rem; white-space: nowrap; }
.forum-index .forum-deleted-category-card { justify-content: space-between; flex-wrap: wrap; border-top: 1px solid #67434a80; padding-top: 1rem; margin-top: 1rem; }
.forum-index .forum-deleted-category-main { flex: 1 1 260px; min-width: 0; }
.forum-index .forum-deleted-category-info { min-width: 0; overflow-wrap: anywhere; }
.forum-index .forum-deleted-category-info h4 { font-size: .95rem; margin: 0 0 .35rem; }
.forum-index .forum-deleted-category-info p { color: var(--forum-muted); font-size: .82rem; margin-bottom: .4rem; }
.forum-index .forum-deleted-date { color: #bbacb5; font-size: .72rem; }
.forum-index .forum-deleted-actions { flex-wrap: wrap; gap: .6rem; }
.forum-index .forum-deleted-actions button { min-height: 42px; border-radius: 8px; padding: .55rem .8rem; font-size: .78rem; font-weight: 600; }
.forum-index .forum-restore-category-btn { color: #a0e8d9; background: #0e2622; border: 1px solid #37665c; }
.forum-index .forum-permanent-delete-category-btn { color: #ffb8bf; background: #2a181e; border: 1px solid #83505b; }
.forum-index .forum-deleted-actions button:hover { filter: brightness(1.2); }
@media (max-width: 991.98px) {
    .forum-index .forum-index-header { grid-template-columns: minmax(0, 1fr) 210px; gap: 1.5rem; }
    .forum-index .forum-overview { padding: 1.25rem 1rem; }
}
@media (max-width: 767.98px) {
    .forum-index .forum-index-header { grid-template-columns: 1fr; border-radius: 18px; }
    .forum-index .forum-overview { display: flex; align-items: center; gap: 1rem; text-align: left; }
    .forum-index .forum-overview-symbol { font-size: 1.8rem; margin: 0; }
    .forum-index .forum-overview > p { display: none; }
    .forum-index .forum-stats { flex: 1; text-align: center; }
    .forum-index .forum-stats dd { font-size: 1.3rem; }
    .forum-index .forum-toolbar { flex-wrap: wrap; margin-bottom: 1rem; }
    .forum-index .forum-search-bar { flex-basis: 100%; }
    .forum-index .forum-category-link { gap: .85rem; padding: 1.15rem; }
    .forum-index .forum-category-icon { flex-basis: 44px; height: 44px; font-size: 1.2rem; }
    .forum-index .forum-topic-count { min-width: 48px; }
    .forum-index .forum-category-arrow { display: none; }
    .forum-index .forum-deleted-header { align-items: flex-start; flex-direction: column; }
}
@media (max-width: 419.98px) {
    .forum-index .forum-category-link { display: grid; grid-template-columns: 40px minmax(0, 1fr); }
    .forum-index .forum-category-icon { height: 40px; }
    .forum-index .forum-topic-count { grid-column: 2; flex-direction: row; gap: .4rem; }
    .forum-index .forum-topic-count strong { font-size: .8rem; }
    .forum-index .forum-category-total { display: none; }
    .forum-index .forum-search-bar { gap: .5rem; padding-left: .75rem; }
    .forum-index .forum-search-bar .forum-primary-btn { padding-inline: .7rem; }
}
@media (prefers-reduced-motion: reduce) {
    .forum-index *, .forum-index *::before, .forum-index *::after { transition: none !important; }
}
</style>
<style>
.forum-index .forum-category-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(220px, 30%); align-items: center; }
.forum-index .forum-category-activity { min-width: 0; margin: 1rem 1.5rem 1rem 0; padding-left: 1.5rem; border-left: 1px solid var(--forum-border); }
.forum-index .forum-category-activity-label { display: block; color: var(--forum-muted); font-size: .65rem; letter-spacing: .08em; text-transform: uppercase; margin-bottom: .35rem; }
.forum-index .forum-category-activity a { display: block; color: var(--forum-accent); font-size: .88rem; font-weight: 600; text-decoration: none; overflow-wrap: anywhere; }
.forum-index .forum-category-activity a:hover { text-decoration: underline; }
.forum-index .forum-category-activity p { margin: .3rem 0 0; color: var(--forum-muted); font-size: .75rem; overflow-wrap: anywhere; }
@media (max-width: 767.98px) {
    .forum-index .forum-category-row { grid-template-columns: minmax(0, 1fr); }
    .forum-index .forum-category-activity { margin: 0 1.15rem 1.15rem; padding: .85rem 0 0; border-left: 0; border-top: 1px solid var(--forum-border); }
}
</style>
