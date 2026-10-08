<style>
.snatch-history { max-width: 1800px; margin-inline: auto; }
.snatch-history .snatch-title-dot { color: var(--forum-accent); }
.snatch-history .snatch-torrent-name { color: var(--theme-text, #dfebf5); font-size: var(--site-font-body, 13px); font-weight: 600; overflow-wrap: anywhere; margin: 0 0 .75rem; line-height: 1.6; }
.snatch-history .forum-page-subtitle { max-width: 680px; }
.snatch-history .snatch-access { display: inline-flex; align-items: center; gap: .5rem; color: var(--forum-muted); font-size: var(--site-font-small, 13px); }
.snatch-history .snatch-swarm { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem 1rem; margin-top: 1.3rem; padding-top: 1rem; border-top: 1px solid var(--theme-teal-border, #80e0cf26); font-size: var(--site-font-small, 13px); }
.snatch-history .snatch-swarm span:first-child { color: var(--forum-accent); }
.snatch-history .snatch-swarm span:last-child { color: var(--theme-blue-text, #9dccf4); }
.snatch-history .snatch-ledger { margin-top: 2.25rem; }
.snatch-history .forum-section-heading { margin-bottom: .65rem; }
.snatch-history .snatch-explainer { color: var(--forum-muted); font-size: var(--site-font-body, 13px); margin-bottom: .5rem; }
.snatch-history #snatch-status { min-height: 1.5rem; margin: 0 0 .5rem; color: var(--forum-accent); font-size: var(--site-font-body, 13px); }
.snatch-history #snatch-status a { color: inherit; text-decoration: underline; }
.snatch-history #snatch-results { scroll-margin-top: 1.5rem; transition: opacity .15s ease; }
.snatch-history #snatch-results[aria-busy="true"] { opacity: .55; }
.snatch-history .snatch-table-panel { border: 1px solid var(--forum-border); border-radius: 16px; overflow: hidden; background: var(--forum-surface); box-shadow: 0 12px 35px var(--theme-shadow, #00000015); }
.snatch-history .snatch-table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--forum-border); }
.snatch-history .snatch-summary { margin: 0; color: var(--theme-text, #c7d5e4); font-size: var(--site-font-body, 13px); }
.snatch-history .snatch-page-size { font-size: var(--site-font-small, 13px); color: var(--forum-muted); white-space: nowrap; }
.snatch-history .snatch-table { --bs-table-bg: transparent; --bs-table-color: var(--theme-text, #d6e2ef); --bs-table-border-color: var(--theme-border, #263547); width: 100%; margin: 0; font-size: var(--site-font-body, 13px); font-variant-numeric: tabular-nums; }
.snatch-history .snatch-table th { background: var(--theme-surface, #0a121a); color: var(--theme-muted, #9fb3c9); padding: 1rem; font-size: var(--site-font-small, 13px); letter-spacing: .055em; text-transform: uppercase; font-weight: 650; white-space: nowrap; border-bottom: 1px solid var(--forum-border); }
.snatch-history .snatch-table td { padding: 1.15rem 1rem; vertical-align: middle; }
.snatch-history .snatch-table tbody tr { transition: background .15s ease; }
.snatch-history .snatch-table tbody tr:hover { background: var(--theme-surface, #121d28); }
.snatch-history .snatch-table tbody tr:last-child td { border-bottom: 0; }
.snatch-history .snatch-member { min-width: 200px; }
.snatch-history .snatch-member > div { display: inline-block; vertical-align: middle; max-width: 180px; overflow-wrap: anywhere; }
.snatch-history .snatch-member a { color: var(--theme-text, #e4eef8); font-weight: 650; text-decoration: none; }
.snatch-history .snatch-member a:hover { color: var(--forum-accent); }
.snatch-history .snatch-avatar { display: inline-grid; place-items: center; width: 34px; height: 34px; margin-right: .65rem; border-radius: 10px; color: var(--forum-accent); background: var(--theme-teal-soft, #80e0cf0d); border: 1px solid var(--theme-teal-border, #80e0cf26); text-transform: uppercase; font-weight: 700; vertical-align: middle; }
.snatch-history .snatch-owner { display: block; margin-top: .3rem; color: var(--theme-amber-text, #efc58f); font-size: var(--site-font-small, 13px); }
.snatch-history .snatch-state { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .55rem; border-radius: 6px; font-size: var(--site-font-small, 13px); white-space: nowrap; border: 1px solid var(--theme-border, #ffffff0c); margin-block: .15rem; }
.snatch-history .snatch-state::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.snatch-history .is-seeding { color: var(--theme-teal-text, #80e0cf); background: var(--theme-teal-soft, #80e0cf0d); }
.snatch-history .is-downloading { color: var(--theme-blue-text, #9dccf4); background: var(--theme-blue-soft, #9dccf40d); }
.snatch-history .is-inactive { color: var(--theme-muted, #a5b4c7); background: var(--theme-surface-alt, #a5b4c70a); }
.snatch-history .is-warning { color: var(--theme-red-text, #ffacb3); background: var(--theme-red-soft, #ffacb30d); }
.snatch-history .snatch-transfer { white-space: nowrap; }
.snatch-history .snatch-transfer strong { font-weight: 600; }
.snatch-history .snatch-transfer small { display: block; color: var(--forum-muted); font-size: var(--site-font-small, 13px); margin-top: .3rem; }
.snatch-history .snatch-transfer.upload strong { color: var(--theme-teal-text, #80e0cf); }
.snatch-history .snatch-transfer.download strong { color: var(--theme-blue-text, #9dccf4); }
.snatch-history .snatch-ratio { color: var(--theme-blue-text, #c5baff); font-weight: 650; }
.snatch-history .snatch-seedtime { min-width: 145px; font-size: var(--site-font-small, 13px); color: var(--theme-text, #d5cfbd); }
.snatch-history .snatch-date { white-space: nowrap; font-size: var(--site-font-small, 13px); color: var(--theme-muted, #a5b4c7); }
.snatch-history .snatch-table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-top: 1px solid var(--forum-border); color: var(--forum-muted); font-size: var(--site-font-small, 13px); }
.snatch-history .snatch-table-footer nav { margin-left: auto; }
.snatch-history .snatch-table-footer nav p { display: none; }
.snatch-history .pagination { --bs-pagination-bg: var(--theme-surface, #0a121a); --bs-pagination-color: var(--theme-text, #c7d5e4); --bs-pagination-border-color: var(--theme-border, #2b3a4c); --bs-pagination-hover-bg: var(--theme-surface, #16242e); --bs-pagination-hover-color: var(--theme-teal-text, #80e0cf); --bs-pagination-hover-border-color: var(--theme-teal-border, #80e0cf); --bs-pagination-active-bg: var(--theme-teal-soft, #80e0cf); --bs-pagination-active-color: var(--theme-text, #102c2b); --bs-pagination-active-border-color: var(--theme-teal-border, #80e0cf); --bs-pagination-disabled-bg: var(--theme-surface, #0d141e); --bs-pagination-disabled-color: var(--theme-muted, #728399); --bs-pagination-disabled-border-color: var(--theme-border, #2b3a4c); --bs-pagination-focus-bg: var(--theme-surface, #16242e); --bs-pagination-focus-color: var(--theme-teal-text, #80e0cf); gap: .3rem; margin: 0; flex-wrap: wrap; }
.snatch-history .pagination .page-link { border-radius: 7px; font-size: var(--site-font-small, 13px); min-width: 34px; text-align: center; }
.snatch-history .snatch-empty { text-align: center; padding: 3.5rem 1.5rem !important; }
.snatch-history .snatch-empty > i { display: block; color: var(--forum-accent); font-size: 2.2rem; margin-bottom: 1rem; }
.snatch-history .snatch-empty strong { display: block; font-size: var(--site-font-body, 13px); margin-bottom: .5rem; }
.snatch-history .snatch-empty span { color: var(--forum-muted); }
.snatch-history :is(.table-responsive, [data-snatch-summary]):focus-visible { outline: 2px solid var(--forum-accent); outline-offset: -3px; }
@media (max-width: 767px) {
    .snatch-history .forum-index-header { grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
    .snatch-history .forum-overview { padding: 1rem; }
    .snatch-history .forum-overview-symbol { display: none; }
    .snatch-history .forum-section-heading { align-items: flex-start; }
    .snatch-history .forum-section-heading h2 { font-size: 1.15rem; }
    .snatch-history .snatch-table-toolbar, .snatch-history .snatch-table-footer { padding: 1rem; }
    .snatch-history .snatch-table-footer nav { width: 100%; margin: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .snatch-history #snatch-results, .snatch-history .snatch-table tbody tr { transition: none; }
}
</style>
