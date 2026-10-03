<style>
.snatch-history { max-width: 1800px; margin-inline: auto; }
.snatch-history .snatch-title-dot { color: var(--forum-accent); }
.snatch-history .snatch-torrent-name { color: #dfebf5; font-size: .95rem; font-weight: 600; overflow-wrap: anywhere; margin: 0 0 .75rem; line-height: 1.6; }
.snatch-history .forum-page-subtitle { max-width: 680px; }
.snatch-history .snatch-access { display: inline-flex; align-items: center; gap: .5rem; color: var(--forum-muted); font-size: .75rem; }
.snatch-history .snatch-swarm { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem 1rem; margin-top: 1.3rem; padding-top: 1rem; border-top: 1px solid #80e0cf26; font-size: .73rem; }
.snatch-history .snatch-swarm span:first-child { color: var(--forum-accent); }
.snatch-history .snatch-swarm span:last-child { color: #9dccf4; }
.snatch-history .snatch-ledger { margin-top: 2.25rem; }
.snatch-history .forum-section-heading { margin-bottom: .65rem; }
.snatch-history .snatch-explainer { color: var(--forum-muted); font-size: .8rem; margin-bottom: .5rem; }
.snatch-history #snatch-status { min-height: 1.5rem; margin: 0 0 .5rem; color: var(--forum-accent); font-size: .8rem; }
.snatch-history #snatch-status a { color: inherit; text-decoration: underline; }
.snatch-history #snatch-results { scroll-margin-top: 1.5rem; transition: opacity .15s ease; }
.snatch-history #snatch-results[aria-busy="true"] { opacity: .55; }
.snatch-history .snatch-table-panel { border: 1px solid var(--forum-border); border-radius: 16px; overflow: hidden; background: var(--forum-surface); box-shadow: 0 12px 35px #00000015; }
.snatch-history .snatch-table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--forum-border); }
.snatch-history .snatch-summary { margin: 0; color: #c7d5e4; font-size: .78rem; }
.snatch-history .snatch-page-size { font-size: .7rem; color: var(--forum-muted); white-space: nowrap; }
.snatch-history .snatch-table { --bs-table-bg: transparent; --bs-table-color: #d6e2ef; --bs-table-border-color: #263547; width: 100%; margin: 0; font-size: .8rem; font-variant-numeric: tabular-nums; }
.snatch-history .snatch-table th { background: #0a121a; color: #9fb3c9; padding: 1rem; font-size: .65rem; letter-spacing: .055em; text-transform: uppercase; font-weight: 650; white-space: nowrap; border-bottom: 1px solid var(--forum-border); }
.snatch-history .snatch-table td { padding: 1.15rem 1rem; vertical-align: middle; }
.snatch-history .snatch-table tbody tr { transition: background .15s ease; }
.snatch-history .snatch-table tbody tr:hover { background: #121d28; }
.snatch-history .snatch-table tbody tr:last-child td { border-bottom: 0; }
.snatch-history .snatch-member { min-width: 200px; }
.snatch-history .snatch-member > div { display: inline-block; vertical-align: middle; max-width: 180px; overflow-wrap: anywhere; }
.snatch-history .snatch-member a { color: #e4eef8; font-weight: 650; text-decoration: none; }
.snatch-history .snatch-member a:hover { color: var(--forum-accent); }
.snatch-history .snatch-avatar { display: inline-grid; place-items: center; width: 34px; height: 34px; margin-right: .65rem; border-radius: 10px; color: var(--forum-accent); background: #80e0cf0d; border: 1px solid #80e0cf26; text-transform: uppercase; font-weight: 700; vertical-align: middle; }
.snatch-history .snatch-owner { display: block; margin-top: .3rem; color: #efc58f; font-size: .63rem; }
.snatch-history .snatch-state { display: inline-flex; align-items: center; gap: .4rem; padding: .3rem .55rem; border-radius: 6px; font-size: .68rem; white-space: nowrap; border: 1px solid #ffffff0c; margin-block: .15rem; }
.snatch-history .snatch-state::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.snatch-history .is-seeding { color: #80e0cf; background: #80e0cf0d; }
.snatch-history .is-downloading { color: #9dccf4; background: #9dccf40d; }
.snatch-history .is-inactive { color: #a5b4c7; background: #a5b4c70a; }
.snatch-history .is-warning { color: #ffacb3; background: #ffacb30d; }
.snatch-history .snatch-transfer { white-space: nowrap; }
.snatch-history .snatch-transfer strong { font-weight: 600; }
.snatch-history .snatch-transfer small { display: block; color: var(--forum-muted); font-size: .66rem; margin-top: .3rem; }
.snatch-history .snatch-transfer.upload strong { color: #80e0cf; }
.snatch-history .snatch-transfer.download strong { color: #9dccf4; }
.snatch-history .snatch-ratio { color: #c5baff; font-weight: 650; }
.snatch-history .snatch-seedtime { min-width: 145px; font-size: .74rem; color: #d5cfbd; }
.snatch-history .snatch-date { white-space: nowrap; font-size: .72rem; color: #a5b4c7; }
.snatch-history .snatch-table-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-top: 1px solid var(--forum-border); color: var(--forum-muted); font-size: .73rem; }
.snatch-history .snatch-table-footer nav { margin-left: auto; }
.snatch-history .snatch-table-footer nav p { display: none; }
.snatch-history .pagination { --bs-pagination-bg: #0a121a; --bs-pagination-color: #c7d5e4; --bs-pagination-border-color: #2b3a4c; --bs-pagination-hover-bg: #16242e; --bs-pagination-hover-color: #80e0cf; --bs-pagination-hover-border-color: #80e0cf; --bs-pagination-active-bg: #80e0cf; --bs-pagination-active-color: #102c2b; --bs-pagination-active-border-color: #80e0cf; --bs-pagination-disabled-bg: #0d141e; --bs-pagination-disabled-color: #728399; --bs-pagination-disabled-border-color: #2b3a4c; --bs-pagination-focus-bg: #16242e; --bs-pagination-focus-color: #80e0cf; gap: .3rem; margin: 0; flex-wrap: wrap; }
.snatch-history .pagination .page-link { border-radius: 7px; font-size: .75rem; min-width: 34px; text-align: center; }
.snatch-history .snatch-empty { text-align: center; padding: 3.5rem 1.5rem !important; }
.snatch-history .snatch-empty > i { display: block; color: var(--forum-accent); font-size: 2.2rem; margin-bottom: 1rem; }
.snatch-history .snatch-empty strong { display: block; font-size: .95rem; margin-bottom: .5rem; }
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
