@extends('layouts.app')

@push('scripts') @vite('resources/js/seedbox-browser.js') @endpush
@section('content')
<div data-page-browser data-browse-url="{{ route('seedboxes.torrents', $seedbox) }}" data-poll="20000" data-seedbox="{{ $seedbox->id }}">
    @include('seedboxes.torrents-results')
</div>




<style>
/* =========================================
   FILEIPLAY SEEDBOX TORRENT CONTROL
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.seedbox-page {
    color: var(--theme-text, #e2e8f0);
}

.seedbox-hero,
.modern-card,
.modern-stat-card {
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.95)), var(--theme-surface, rgba(10,15,27,.84)));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px var(--theme-shadow, rgba(0,0,0,.18));
}

.seedbox-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.05rem 1.2rem;
    border-left: 3px solid var(--ui-accent, var(--theme-teal-border, #22d3ee));
    border-radius: .85rem;
}

.seedbox-hero::after {
    content: "";
    position: absolute;
    top: -100px;
    right: -90px;
    width: 210px;
    height: 210px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--theme-teal-soft, rgba(34,211,238,.09)), transparent 70%);
    pointer-events: none;
}

.hero-content { min-width: 0; }

.hero-kicker {
    margin-bottom: .3rem;
    color: var(--ui-accent, var(--theme-teal-text, #22d3ee));
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
    letter-spacing: 1.1px;
}

.hero-title {
    margin: 0;
    color: var(--theme-text, #f8fafc);
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.hero-title i { color: var(--ui-accent, var(--theme-teal-text, #22d3ee)); }

.hero-subtitle {
    margin-top: .35rem;
    color: var(--theme-muted, rgba(226,232,240,.58));
    font-size: var(--site-font-body, 13px);
}

.connection-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .65rem;
    border-radius: .55rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.connection-badge.online {
    border: 1px solid var(--theme-green-border, rgba(34,197,94,.22));
    background: var(--theme-green-soft, rgba(34,197,94,.08));
    color: var(--theme-green-text, #86efac);
}

.connection-badge.offline {
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.22));
    background: var(--theme-red-soft, rgba(239,68,68,.08));
    color: var(--theme-red-text, #fca5a5);
}

.modern-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .45rem .7rem;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .55rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0245));
    color: var(--theme-muted, rgba(226,232,240,.72));
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.modern-back-btn:hover {
    border-color: var(--theme-teal-border, rgba(34,211,238,.28));
    background: var(--theme-teal-soft, rgba(34,211,238,.06));
    color:  var(--theme-text, #fff);
}

/* Alerts */
.modern-alert {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .65rem .8rem;
    margin-bottom: .8rem;
    border-radius: .65rem;
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
}

.modern-alert-success {
    border: 1px solid var(--theme-green-border, rgba(34,197,94,.20));
    border-left: 3px solid var(--theme-green-border, rgba(34,197,94,.60));
    background: var(--theme-green-soft, rgba(34,197,94,.07));
}

.modern-alert-danger {
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.20));
    border-left: 3px solid var(--theme-red-border, rgba(239,68,68,.60));
    background: var(--theme-red-soft, rgba(239,68,68,.07));
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: .65rem;
}

.modern-stat-card {
    padding: .8rem .55rem;
    border-radius: .7rem;
    text-align: center;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0175));
    transition: transform .2s ease, border-color .2s ease;
}

.modern-stat-card:hover {
    transform: translateY(-1px);
    border-color: var(--theme-teal-border, rgba(34,211,238,.20));
}

.modern-stat-card i {
    display: block;
    margin-bottom: .3rem;
    font-size: 16px;
}

.modern-stat-card .text-info,
.modern-stat-card .text-primary {
    color: var(--ui-accent, var(--theme-teal-text, #22d3ee)) !important;
}

.stat-value {
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.stat-label {
    margin-top: .2rem;
    color: var(--theme-muted, rgba(226,232,240,.45));
    font-size: var(--site-font-small, 13px);
}

/* Cards */
.modern-card {
    overflow: hidden;
    border-radius: .85rem;
}

.modern-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .75rem .95rem;
    border-bottom: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
}

.modern-card-header h5 {
    margin: 0;
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}

.modern-card-header .text-info {
    color: var(--ui-accent, var(--theme-teal-text, #22d3ee)) !important;
}

.modern-card-body { padding: .95rem; }

.torrent-count {
    min-width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.20));
    border-radius: .5rem;
    background: var(--theme-teal-soft, rgba(34,211,238,.07));
    color: var(--ui-accent, var(--theme-on-action, #22d3ee));
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

/* Inputs */
.modern-label {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.modern-input {
    min-height: 40px;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08))) !important;
    border-radius: .6rem !important;
    background: var(--theme-control, rgba(255,255,255,0.0245)) !important;
    color: var(--theme-text, #f8fafc) !important;
    font-size: var(--site-font-body, 13px);
    box-shadow: none !important;
}

.modern-input:focus {
    border-color: var(--theme-teal-border, rgba(34,211,238,.45)) !important;
    background: var(--theme-teal-soft, rgba(34,211,238,.035)) !important;
    box-shadow: 0 0 0 3px var(--theme-shadow, rgba(34,211,238,.08)) !important;
}

.modern-input::file-selector-button {
    margin: -0.375rem .75rem -0.375rem -0.75rem;
    padding: .375rem .7rem;
    border: 0;
    border-right: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    background: var(--theme-teal-soft, rgba(34,211,238,.06));
    color: var(--ui-accent, var(--theme-on-action, #22d3ee));
}

/* Search */
.modern-search-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem .4rem .35rem .65rem;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .65rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0175));
}

.search-icon {
    flex: 0 0 auto;
    color: var(--ui-accent, var(--theme-teal-text, #22d3ee));
    font-size: 14px;
}

.modern-search-input {
    min-width: 0;
    flex: 1;
    border: 0 !important;
    outline: 0 !important;
    background: transparent !important;
    color: var(--theme-text, #f8fafc) !important;
    font-size: var(--site-font-body, 13px);
    box-shadow: none !important;
}

.modern-search-input::placeholder { color: var(--theme-muted, rgba(226,232,240,.35)); }

.modern-search-btn,
.modern-upload-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.28));
    border-radius: .55rem;
    background: var(--theme-teal-soft, rgba(34,211,238,.10));
    color: var(--ui-accent, var(--theme-on-action, #22d3ee));
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    transition: all .2s ease;
}

.modern-search-btn {
    min-height: 38px;
    padding: 0 .85rem;
}

.modern-upload-btn { min-height: 40px; }

.modern-search-btn:hover,
.modern-upload-btn:hover {
    border-color: var(--ui-accent, var(--theme-teal-border, #22d3ee));
    background: var(--theme-teal-soft, rgba(34,211,238,.16));
    color:  var(--theme-text, #fff);
    transform: translateY(-1px);
}

/* Table */
.table-responsive {
    border-radius: .65rem;
}

.modern-table {
    min-width: 850px;
    margin-bottom: 0;
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
}

.modern-table tbody tr {
    border-color: var(--ui-border, var(--theme-border, rgba(255,255,255,.06))) !important;
    transition: background .2s ease;
}

.modern-table tbody tr:hover {
    background: var(--theme-teal-soft, rgba(34,211,238,.025));
}

.modern-table td {
    padding: .75rem .7rem;
    border-color: var(--ui-border, var(--theme-border, rgba(255,255,255,.06))) !important;
    vertical-align: middle;
}

/* Progress */
.progress-column { width: 150px; }

.modern-progress {
    position: relative;
    height: 24px;
    overflow: hidden;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.07)));
    border-radius: .45rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0315));
}

.progress-bar {
    height: 100%;
    border-radius: .35rem;
    transition: width .4s ease;
}

.bg-success-gradient { background: linear-gradient(90deg, var(--theme-green-soft, #16a34a), var(--theme-green-soft, #22c55e)); }
.bg-info-gradient { background: linear-gradient(90deg, var(--theme-teal-soft, #0891b2), var(--theme-teal-soft, #22d3ee)); }
.bg-warning-gradient { background: linear-gradient(90deg, var(--theme-amber-soft, #d97706), var(--theme-amber-soft, #f59e0b)); }
.bg-danger-gradient { background: linear-gradient(90deg, var(--theme-red-soft, #dc2626), var(--theme-red-soft, #ef4444)); }

.progress-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

/* Torrent info */
.torrent-name {
    margin-bottom: .4rem;
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    overflow-wrap: anywhere;
}

.torrent-name .text-info { color: var(--ui-accent, var(--theme-teal-text, #22d3ee)) !important; }

.torrent-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem;
    margin-bottom: .4rem;
}

.torrent-badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .45rem;
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}

.status-badge {
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.18));
    background: var(--theme-teal-soft, rgba(34,211,238,.06));
    color: var(--ui-accent, var(--theme-on-action, #22d3ee));
}

.ratio-badge {
    border: 1px solid var(--theme-teal-border, rgba(34,211,238,.15));
    background: var(--theme-teal-soft, rgba(34,211,238,.045));
    color: var(--theme-teal-text, #a5f3fc);
}

.upload-badge {
    border: 1px solid var(--theme-green-border, rgba(34,197,94,.16));
    background: var(--theme-green-soft, rgba(34,197,94,.055));
    color: var(--theme-green-text, #86efac);
}

.download-badge {
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.16));
    background: var(--theme-red-soft, rgba(239,68,68,.055));
    color: var(--theme-red-text, #fca5a5);
}

.torrent-extra {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
    color: var(--theme-muted, rgba(226,232,240,.45));
    font-size: var(--site-font-small, 13px);
}

.tracker-badge {
    display: inline-flex;
    align-items: center;
    padding: .18rem .4rem;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.07)));
    border-radius: .4rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0175));
}

/* Actions */
.torrent-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .35rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .5rem;
    background: var(--theme-surface-alt, rgba(255,255,255,0.0245));
    color: var(--theme-muted, rgba(226,232,240,.72));
    text-decoration: none;
    transition: all .2s ease;
}

.action-btn:hover {
    border-color: var(--theme-teal-border, rgba(34,211,238,.25));
    background: var(--theme-teal-soft, rgba(34,211,238,.07));
    color:  var(--theme-text, #fff);
    transform: translateY(-1px);
}

.action-btn-danger { color: var(--theme-red-text, #fca5a5); }

.action-btn-danger:hover {
    border-color: var(--theme-red-border, rgba(239,68,68,.25));
    background: var(--theme-red-soft, rgba(239,68,68,.07));
    color: var(--theme-text, #fff);
}

.action-btn-info { color: var(--ui-accent, var(--theme-teal-text, #22d3ee)); }

/* Pagination */
.pagination { margin-bottom: 0; }

.pagination .page-link {
    border-color: var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    background: var(--theme-surface-alt, rgba(255,255,255,0.0245));
    color: var(--theme-muted, rgba(226,232,240,.72));
    font-size: var(--site-font-small, 13px);
}

.pagination .page-link:hover {
    border-color: var(--theme-teal-border, rgba(34,211,238,.25));
    background: var(--theme-teal-soft, rgba(34,211,238,.07));
    color:  var(--theme-text, #fff);
}

.pagination .active .page-link {
    border-color: var(--theme-teal-border, rgba(34,211,238,.30));
    background: var(--theme-teal-soft, rgba(34,211,238,.12));
    color: var(--ui-accent, var(--theme-on-action, #22d3ee));
}

/* Mobile */
@media (max-width: 1100px) {
    .stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 767.98px) {
    .seedbox-page .container-fluid {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .seedbox-hero { padding: .9rem; }
    .hero-title { font-size: 18px; }
    .hero-subtitle { font-size: var(--site-font-small, 13px); }

    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .modern-card-body { padding: .75rem; }

    .modern-search-wrap { flex-wrap: wrap; }
    .modern-search-btn { width: 100%; }

    .torrent-actions { flex-wrap: wrap; }
}
</style>

@endsection