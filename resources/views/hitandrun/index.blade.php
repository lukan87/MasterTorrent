
<style>
html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden !important;
}

.container, .container-fluid, .row, .table-responsive {
    max-width: 100%;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

.seeding-table {
    width: 100%;
    min-width: 0 !important;
}

.seeding-table th,
.seeding-table td {
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
    vertical-align: middle;
}

.seeding-table .torrent-name {
    min-width: 0;
    max-width: 360px;
}

.seeding-table .action-cell {
    min-width: 130px;
}

@media (max-width: 991.98px) {
    .seeding-table th,
    .seeding-table td {
        padding: .7rem .6rem;
    }

    .seeding-table .torrent-name {
        max-width: 280px;
    }
}

@media (max-width: 767.98px) {
    .page-title, h1 {
        font-size: 1.55rem !important;
        line-height: 1.25;
    }

    .page-subtitle {
        font-size: .95rem !important;
    }

    .table-responsive {
        overflow-x: visible;
    }

    .seeding-table,
    .seeding-table tbody,
    .seeding-table tr,
    .seeding-table td {
        display: block;
        width: 100%;
    }

    .seeding-table thead {
        display: none;
    }

    .seeding-table tbody tr {
        margin-bottom: .9rem;
        padding: .75rem;
        border: 1px solid var(--ui-border, rgba(255,255,255,.08));
        border-radius: .7rem;
        background: rgba(15, 23, 42, .72);
    }

    .seeding-table td {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        min-width: 0 !important;
        max-width: none !important;
        padding: .55rem .2rem;
        border: 0;
        text-align: right;
    }

    .seeding-table td::before {
        content: attr(data-label);
        flex: 0 0 42%;
        text-align: left;
        font-weight: 700;
        opacity: .78;
    }

    .seeding-table td.torrent-name-cell {
        display: block;
        text-align: left;
        padding-bottom: .8rem;
    }

    .seeding-table td.torrent-name-cell::before {
        display: block;
        margin-bottom: .35rem;
    }

    .seeding-table .torrent-name {
        display: block;
        max-width: 100%;
    }

    .seeding-table .action-cell {
        display: block;
        min-width: 0 !important;
        padding-top: .8rem;
        text-align: left;
        border-top: 1px solid var(--ui-border, rgba(255,255,255,.08));
    }

    .seeding-table .action-cell::before {
        display: block;
        margin-bottom: .45rem;
    }

    .seeding-table .action-cell .btn,
    .seeding-table .action-cell form {
        width: 100%;
    }
}

@media (max-width: 479.98px) {
    .seeding-table tbody tr {
        padding: .65rem;
    }

    .seeding-table td {
        flex-direction: column;
        gap: .2rem;
        text-align: left;
    }

    .seeding-table td::before {
        flex-basis: auto;
    }
}

.badge, .btn, .text-nowrap {
    max-width: 100%;
}

.badge {
    white-space: normal;
    overflow-wrap: anywhere;
}
</style>

@extends('layouts.app')

@section('content')
<div class="container py-4 seeding-page">
    <div class="seeding-hero mb-4">
        <div class="hero-badge"><i class="bi bi-broadcast-pin me-2"></i>SEEDING REQUIRED</div>
        <h1 class="hero-title">{{ isset($viewedUser) ? $viewedUser->name . "'s" : 'Your' }} Torrents That Need Seeding</h1>
        <p class="hero-subtitle">Torrents that still require seeding time to complete their requirement.</p>
    </div>

    @if($torrents->isEmpty())
        <div class="empty-card"><i class="bi bi-check2-circle"></i><div><h2>All caught up</h2><p>{{ isset($viewedUser) ? $viewedUser->name : 'You' }} don't have any torrents that need seeding.</p></div></div>
    @else
        <div class="seeding-card">
            <div class="table-responsive">
                <table class="table seeding-table align-middle mb-0">
                    <thead><tr>
                        <th>Torrent Name</th><th>Seeding Duration</th><th>Ratio</th><th>Created At</th><th>Completed At</th><th>Updated At</th><th>Prewarned</th><th>Remaining</th><th>Status</th><th></th>
                    </tr></thead>
                    <tbody>
                    @foreach ($torrents as $torrent)
                        <tr>
                            <td class="torrent-cell" data-label="Torrent Name"><a class="torrent-name" href="{{ route('torrents.show', $torrent->torrent_id) }}">{{ $torrent->torrent->name }}</a><div class="torrent-meta"><span><i class="bi bi-arrow-up-circle"></i> Seeders: {{ $torrent->torrent->seeders }}</span><span><i class="bi bi-arrow-down-circle"></i> Leechers: {{ $torrent->torrent->leechers }}</span></div></td>
                            <td class="nowrap" data-label="Seeding Duration">{{ \App\Helpers\FormatHelper::formatTime($torrent->seedtime) }}</td>
                            <td data-label="Ratio">@php $uploaded=$torrent->uploaded??0; $downloaded=$torrent->downloaded??0; $ratio=$downloaded>0?number_format($uploaded/$downloaded,2):($uploaded>0?'∞':'N/A'); @endphp<span class="ratio-badge">{{ $ratio }}</span></td>
                            <td class="date-cell" data-label="Created At">{{ $torrent->created_at->toDayDateTimeString() }}</td>
                            <td class="date-cell" data-label="Completed At">{{ $torrent->completed_at ? $torrent->completed_at->toDayDateTimeString() : 'N/A' }}</td>
                            <td class="date-cell" data-label="Updated At">{{ $torrent->updated_at }}</td>
                            <td data-label="Prewarned">@if($torrent->prewarned_at)<span class="prewarned-badge"><i class="bi bi-exclamation-triangle"></i>{{ $torrent->prewarned_at->diffForHumans() }}</span>@else<span class="muted-value">—</span>@endif</td>
                            <td data-label="Remaining Seeding Time">@php $remainingTime=config('hitrun.seedtime')-$torrent->seedtime; @endphp @if($remainingTime>0)<span class="remaining-badge">{{ intdiv($remainingTime,3600) }}h {{ intdiv($remainingTime%3600,60) }}m</span>@else<span class="complete-badge"><i class="bi bi-check-circle"></i> Fully seeded</span>@endif</td>
                            <td data-label="Seeding Status">@if($torrent->active==1)<span class="status-badge active"><span class="status-dot"></span>Yes</span>@else<span class="status-badge inactive"><span class="status-dot"></span>No</span>@endif</td>
                            <td data-label="Action">@if(auth()->id()===$torrent->user_id)<form action="{{ route('bonus.buySeedtime') }}" method="POST" class="buy-seedtime-form">@csrf<input type="hidden" name="torrent_id" value="{{ $torrent->torrent_id }}"><button type="submit" class="buy-btn" data-bs-toggle="tooltip" title="{{ config('seedbonus.shop.seedtime',1000) }} seedbonus points"><i class="bi bi-hourglass-split"></i> Buy Seedtime</button></form>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-center mt-4">{{ $torrents->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

<style>
.seeding-page{color:#e5e7eb}.seeding-hero,.seeding-card,.empty-card{background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.88));border:1px solid var(--ui-border,rgba(148,163,184,.16));box-shadow:0 14px 34px rgba(0,0,0,.25)}.seeding-hero{padding:26px 30px;border-radius:.75rem}.hero-badge{display:inline-flex;align-items:center;padding:.48rem .82rem;margin-bottom:.8rem;border-radius:.5rem;background:rgba(20,184,166,.09);border:1px solid rgba(20,184,166,.22);color:#67e8df;font-size:.85rem;font-weight:800;letter-spacing:.7px}.hero-title{margin:0 0 .55rem;color:#f8fafc;font-size:clamp(1.7rem,3vw,2.45rem);font-weight:800}.hero-subtitle{margin:0;color:#94a3b8;font-size:1rem}.seeding-card{overflow:hidden;border-radius:.7rem}.seeding-table{min-width:1250px;color:#cbd5e1;font-size:.95rem}.seeding-table thead th{padding:.95rem .85rem;background:rgba(2,6,23,.62);border-bottom:1px solid rgba(148,163,184,.16);color:#94a3b8;font-size:.82rem;white-space:nowrap}.seeding-table tbody td{padding:.9rem;border-bottom:1px solid rgba(148,163,184,.08);background:transparent;vertical-align:middle}.seeding-table tbody tr:hover td{background:rgba(20,184,166,.035)}.torrent-cell{min-width:270px}.torrent-name{color:#67e8df;font-weight:700;font-size:1rem;text-decoration:none}.torrent-name:hover{color:#99f6ef;text-decoration:underline}.torrent-meta{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.35rem;color:#64748b;font-size:.8rem}.torrent-meta i{color:#67e8df;margin-right:.15rem}.nowrap,.date-cell{white-space:nowrap}.date-cell{min-width:145px;color:#94a3b8!important}.ratio-badge,.remaining-badge,.complete-badge,.prewarned-badge,.status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .5rem;border-radius:.4rem;font-size:.8rem;font-weight:700;white-space:nowrap}.ratio-badge{background:rgba(20,184,166,.08);border:1px solid rgba(20,184,166,.18);color:#67e8df}.remaining-badge{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.18);color:#fbbf24}.complete-badge{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.18);color:#86efac}.prewarned-badge{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);color:#fca5a5}.muted-value{color:#475569}.status-badge{min-width:48px;justify-content:center}.status-badge.active{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.18);color:#86efac}.status-badge.inactive{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);color:#fca5a5}.status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}.buy-seedtime-form{margin:0}.buy-btn{display:inline-flex;align-items:center;gap:.4rem;min-height:40px;padding:.55rem .8rem;border:1px solid rgba(20,184,166,.3);border-radius:.5rem;background:rgba(20,184,166,.1);color:#67e8df;font-size:.82rem;font-weight:700;white-space:nowrap;transition:.18s}.buy-btn:hover{transform:translateY(-1px);background:rgba(20,184,166,.18);border-color:rgba(20,184,166,.48);color:#99f6ef}.empty-card{display:flex;align-items:center;gap:1rem;padding:24px;border-radius:.7rem}.empty-card>i{width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:.55rem;background:rgba(34,197,94,.09);border:1px solid rgba(34,197,94,.18);color:#86efac;font-size:1.15rem}.empty-card h2{margin:0 0 .25rem;color:#f8fafc;font-size:1.25rem}.empty-card p{margin:0;color:#94a3b8;font-size:.95rem}.seeding-page .pagination{--bs-pagination-bg:rgba(22,32,51,.92);--bs-pagination-border-color:rgba(148,163,184,.16);--bs-pagination-color:#94a3b8;--bs-pagination-hover-bg:rgba(20,184,166,.1);--bs-pagination-hover-color:#67e8df;--bs-pagination-active-bg:rgba(20,184,166,.16);--bs-pagination-active-border-color:rgba(20,184,166,.35);--bs-pagination-active-color:#67e8df}@media(max-width:768px){.seeding-page{padding-top:1.25rem!important}.seeding-hero{padding:20px}.hero-title{font-size:1.55rem}.seeding-table{min-width:1050px}}@media(prefers-reduced-motion:reduce){.buy-btn{transition:none}}
</style>

@endsection
