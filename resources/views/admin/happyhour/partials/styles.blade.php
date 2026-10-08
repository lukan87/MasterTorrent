<style>
.happyhour-workspace {
    --hh-surface: var(--theme-surface, #0d141e);
    --hh-border: var(--theme-border, #2b3a4c);
    --hh-muted: var(--theme-muted, #a5b4c7);
    --hh-accent: var(--theme-teal-text, #80e0cf);
    max-width: 1440px;
    margin: 0 auto;
    padding: clamp(1rem, 3vw, 2rem);
    background: var(--theme-surface, #0a1119);
    border: 1px solid var(--hh-border);
    border-radius: 24px;
    box-shadow: 0 16px 40px var(--theme-shadow, #00000025);
    color: var(--theme-text, #f1f5f9);
}
.happyhour-workspace.hh-create { max-width: 900px; }
.happyhour-workspace .hh-header {
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
    gap: 1.5rem; padding: clamp(1.25rem, 3vw, 2.25rem); margin-bottom: 1.5rem;
    border: 1px solid var(--theme-border, #354e58); border-radius: 18px;
    background: linear-gradient(120deg, var(--theme-surface, #101e25), var(--theme-surface, #0d151d));
}
.happyhour-workspace .hh-eyebrow { color: var(--hh-accent); font-size: var(--site-font-small, 13px); font-weight: 700; letter-spacing: .14em; text-transform: uppercase; margin-bottom: .75rem; }
.happyhour-workspace h1 { color: var(--theme-text, #f5fafb); font-size: clamp(1.8rem, 3vw, 2.6rem); letter-spacing: -.04em; font-weight: 750; margin-bottom: .75rem; }
.happyhour-workspace h2 { color: var(--theme-text, #e9f0f7); font-size: 1.1rem; font-weight: 650; margin-bottom: 1rem; }
.happyhour-workspace p { line-height: 1.7; }
.happyhour-workspace .text-muted { color: var(--hh-muted) !important; }
.happyhour-workspace .card { background: var(--hh-surface); border: 1px solid var(--hh-border); border-radius: 16px; color: var(--theme-text, #d5e1ea); box-shadow: none; }
.happyhour-workspace .card-body { padding: 1.5rem; }
.happyhour-workspace .hh-live-panel { border-top: 3px solid var(--hh-accent); }
.happyhour-workspace .hh-auto-panel { border-top: 3px solid var(--theme-blue-border, #acbaff); }
.happyhour-workspace .hh-history { padding: 1.5rem; background: var(--hh-surface); border: 1px solid var(--hh-border); border-radius: 16px; }
.happyhour-workspace .table-responsive { border: 1px solid var(--hh-border); border-radius: 12px; }
.happyhour-workspace .table { --bs-table-bg: var(--theme-surface, #0d141e); --bs-table-color: var(--theme-text, #d5e1ea); --bs-table-border-color: var(--theme-border, #2b3a4c); color: var(--theme-text, #d5e1ea); margin: 0; }
.happyhour-workspace .table > :not(caption) > * > * { background: var(--theme-surface, #0d141e); color: var(--theme-text, #d5e1ea); padding: 1rem; border-color: var(--hh-border); box-shadow: none; }
.happyhour-workspace .table thead th { background: var(--theme-surface, #121c27); color: var(--theme-text, #b9ccdc); font-size: var(--site-font-small, 13px); letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; }
.happyhour-workspace .table tbody tr:nth-child(even) td { background: var(--theme-surface, #101923); }
.happyhour-workspace .table tbody tr:hover td { background: var(--theme-surface, #141f2b); }
.happyhour-workspace .table small { color: var(--hh-muted); margin-top: .25rem; }
.happyhour-workspace .table td:first-child { min-width: 170px; overflow-wrap: anywhere; }
.happyhour-workspace .table td:nth-child(3) { min-width: 190px; font-variant-numeric: tabular-nums; }
.happyhour-workspace .btn { min-height: 44px; padding: .65rem 1rem; border-radius: 10px; font-size: var(--site-font-body, 13px); font-weight: 650; }
.happyhour-workspace .btn-primary { background: var(--theme-teal-action, var(--hh-accent)); color: var(--theme-on-action, #102c2b); border-color: var(--hh-accent); }
.happyhour-workspace .btn-primary:hover { background: var(--theme-teal-action, #a3f0e2); border-color: var(--theme-teal-border, #a3f0e2); color: var(--theme-on-action, #102c2b); }
.happyhour-workspace .btn-outline-secondary { background: var(--theme-surface, #121b24); border-color: var(--theme-border, #415262); color: var(--theme-text, #d5e1ea); }
.happyhour-workspace .btn-outline-warning { background: var(--theme-surface, #261f15); border-color: var(--theme-border, #806542); color: var(--theme-amber-text, #f1d29d); }
.happyhour-workspace .btn-outline-success { background: var(--theme-surface, #0e2622); border-color: var(--theme-border, #37665c); color: var(--theme-teal-text, #a0e8d9); }
.happyhour-workspace .btn-outline-danger { background: var(--theme-surface, #2a181e); border-color: var(--theme-border, #83505b); color: var(--theme-red-text, #ffb8bf); }
.happyhour-workspace .btn[class*="btn-outline-"]:hover { filter: brightness(1.2); }
.happyhour-workspace .badge { padding: .45rem .65rem; border-radius: 7px; font-weight: 600; }
.happyhour-workspace .form-label { color: var(--theme-text, #d5e1ea); font-size: var(--site-font-body, 13px); font-weight: 600; }
.happyhour-workspace :is(.form-control, .form-select) { background-color: var(--theme-control, #091019); color: var(--theme-text, #f1f5f9); border: 1px solid var(--theme-border, #415262); border-radius: 10px; min-height: 46px; color-scheme: dark; }
.happyhour-workspace .form-select { --bs-form-select-bg-img: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23b9ccdc' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E"); }
.happyhour-workspace :is(.form-control, .form-select):focus { border-color: var(--hh-accent); box-shadow: 0 0 0 3px var(--theme-shadow, #80e0cf25); }
.happyhour-workspace .form-check-input { border-color: var(--theme-border, #64748b); background-color: var(--theme-control, #192632); }
.happyhour-workspace .form-check-input:checked { background-color: var(--theme-teal-soft, #238674); border-color: var(--hh-accent); }
.happyhour-workspace .alert-info { background: var(--theme-surface, #0f2626); border: 1px solid var(--theme-border, #37665c); color: var(--theme-text, #c7f3e9); border-radius: 12px; }
.happyhour-workspace .hh-back { display: inline-block; color: var(--hh-accent); margin-bottom: 1.25rem; text-decoration: none; }
.happyhour-workspace .hh-back:hover { text-decoration: underline; }
.happyhour-workspace .page-link { background: var(--theme-surface, #121b24); border-color: var(--theme-border, #415262); color: var(--theme-text, #d5e1ea); }
.happyhour-workspace .active > .page-link { background: var(--theme-teal-action, var(--hh-accent)); color: var(--theme-on-action, #102c2b); border-color: var(--hh-accent); }
.happyhour-workspace .disabled > .page-link { background: var(--theme-surface, #0d141e); color: var(--theme-muted, #a5b4c7); }
.happyhour-workspace :is(a, button, input, select):focus-visible { outline: 2px solid var(--hh-accent); outline-offset: 4px; }
@media (max-width: 767.98px) {
    .happyhour-workspace { border-radius: 16px; }
    .happyhour-workspace .hh-header { border-radius: 12px; }
    .happyhour-workspace .hh-header > .btn { width: 100%; }
    .happyhour-workspace .hh-history, .happyhour-workspace .card-body { padding: 1rem; }
}
</style>
