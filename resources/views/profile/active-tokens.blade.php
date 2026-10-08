@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="elite-breadcrumb mb-4">
        <a href="{{ route('profile.show', ['id'=>$user->id,'name'=>$user->name]) }}" class="breadcrumb-item">
            <i class="bi bi-person-circle"></i> My Profile
        </a>

        <span class="breadcrumb-separator">
            <i class="bi bi-chevron-right"></i>
        </span>

        <span class="breadcrumb-current">
            <i class="bi bi-ticket-perforated"></i> Active Tokens
        </span>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-semibold mb-0">
                <i class="bi bi-ticket-perforated me-2"></i>
                Your Active Tokens
            </h4>

            <span class="badge token-count">
                {{ $user->slots }}
            </span>
        </div>
    </div>

    <div class="elite-card token-card">

        <div class="token-header">
            <div>
                <div class="token-title">
                    <i class="bi bi-lightning-charge-fill me-2"></i>
                    Free Download / Double Upload Torrents
                </div>
                <div class="token-subtitle">
                    Manage your active torrent slots and their expiry times.
                </div>
            </div>

            <div class="available-slots">
                <span class="available-label">Available Tokens</span>
                <strong>{{ $user->slots }}</strong>
            </div>
        </div>

        <div class="token-body">

            @if($slots->isEmpty())

                <div class="empty-slots">
                    <div class="empty-icon">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <h5>No slots used yet</h5>
                    <p>Your active free-download and double-upload tokens will appear here.</p>
                </div>

            @else

                <div class="table-responsive">
                    <table class="table token-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Torrent</th>
                                <th>Type</th>
                                <th>Expires At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($slots as $slot)
                                <tr>
                                    <td>
                                        <div class="torrent-name">
                                            <i class="bi bi-file-earmark-play me-2"></i>
                                            {{ $slot->torrent->name ?? 'Deleted Torrent' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="slot-types">
                                            @if($slot->free)
                                                <span class="slot-badge free">
                                                    <i class="bi bi-download me-1"></i>
                                                    Free Download
                                                </span>
                                            @endif

                                            @if($slot->double)
                                                <span class="slot-badge double">
                                                    <i class="bi bi-upload me-1"></i>
                                                    Double Upload
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <span class="expiry-time">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $slot->expires_at }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <div class="slot-actions">

                                            @if($slot->expires_at->isPast())

                                                <form action="{{ route('slots.renew', $slot->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn slot-btn renew"
                                                            data-bs-toggle="tooltip"
                                                            title="Renewing the slot will decrease your available slots">
                                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                                        Renew
                                                    </button>
                                                </form>

                                                <form action="{{ route('slots.remove', $slot->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn slot-btn remove"
                                                            data-bs-toggle="tooltip"
                                                            title="Removing the slot means you will have to use another slot to make this torrent free or double upload">
                                                        <i class="bi bi-x-lg me-1"></i>
                                                        Remove
                                                    </button>
                                                </form>

                                            @else

                                                <span class="active-slot">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    Active Slot
                                                </span>

                                                @if($slot->torrent)
                                                    <form action="{{ route('torrents.download', [
                                                        'id' => $slot->torrent->id,
                                                        'slug' => $slot->torrent->slug
                                                    ]) }}"
                                                          method="GET"
                                                          class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="btn slot-btn download"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $slot->torrent->name }}">
                                                            <i class="bi bi-file-earmark-arrow-down-fill me-1"></i>
                                                            Download
                                                        </button>
                                                    </form>
                                                @endif

                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif

        </div>
    </div>
</div>

<style>
/* =========================================================
   FILEIPLAY — ACTIVE TOKENS
   Dark navy / teal forum theme
========================================================= */

.elite-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
    padding: .65rem .85rem;
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.95)), var(--theme-surface, rgba(10,15,27,.88)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .65rem;
    box-shadow: 0 8px 24px var(--theme-shadow, rgba(0,0,0,.22));
    font-size: var(--site-font-body, 13px);
}

.elite-breadcrumb a {
    color: var(--theme-muted, rgba(255,255,255,.72));
    text-decoration: none;
    transition: color .2s ease;
}

.elite-breadcrumb a:hover {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.breadcrumb-separator {
    color: var(--theme-muted, rgba(255,255,255,.25));
    font-size: var(--site-font-small, 13px);
}

.breadcrumb-current {
    color: var(--theme-text, rgba(255,255,255,.9));
}

.elite-breadcrumb i {
    margin-right: .25rem;
}

h4 {
    color: var(--theme-text, #f1f5f9);
    font-size: 1.05rem;
}

h4 .bi {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf)) !important;
}

.token-count {
    background: var(--theme-teal-soft, rgba(45,212,191,.12));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.28));
    color: var(--theme-teal-text, #8ff5e6);
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    padding: .3rem .52rem;
    border-radius: .42rem;
}

.elite-card {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.96)), var(--theme-surface, rgba(10,15,27,.9)));
    border: 1px solid var(--ui-border, var(--theme-border, rgba(255,255,255,.08)));
    border-radius: .7rem;
    box-shadow: 0 10px 28px var(--theme-shadow, rgba(0,0,0,.24));
    overflow: hidden;
}

.token-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.07));
    background: var(--theme-surface-alt, rgba(255,255,255,0.0126));
}

.token-title {
    color: var(--theme-text, #e8f0f7);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.token-title i {
    color: var(--ui-accent, var(--theme-teal-text, #2dd4bf));
}

.token-subtitle {
    margin-top: .2rem;
    color: var(--theme-muted, rgba(203,213,225,.52));
    font-size: var(--site-font-body, 13px);
}

.available-slots {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .4rem .65rem;
    background: var(--theme-teal-soft, rgba(45,212,191,.055));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.16));
    border-radius: .5rem;
    white-space: nowrap;
}

.available-label {
    color: var(--theme-muted, rgba(203,213,225,.58));
    font-size: var(--site-font-small, 13px);
}

.available-slots strong {
    color: var(--theme-teal-text, #8ff5e6);
    font-size: var(--site-font-body, 13px);
}

.token-body {
    padding: 1rem;
}

.token-table {
    color: var(--theme-muted, rgba(226,232,240,.84));
    font-size: var(--site-font-body, 13px);
    border-color: var(--theme-border, rgba(255,255,255,.065));
}

.token-table thead th {
    padding: .7rem .75rem;
    background: var(--theme-surface, rgba(5,9,18,.42));
    border-bottom: 1px solid var(--theme-border, rgba(255,255,255,.08));
    color: var(--theme-muted, rgba(203,213,225,.58));
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .035em;
    white-space: nowrap;
}

.token-table tbody td {
    padding: .75rem;
    background: transparent;
    border-color: var(--theme-border, rgba(255,255,255,.055));
    vertical-align: middle;
}

.token-table tbody tr {
    transition: background .18s ease;
}

.token-table tbody tr:hover td {
    background: var(--theme-teal-soft, rgba(45,212,191,.025));
}

.torrent-name {
    display: flex;
    align-items: center;
    color: var(--theme-text, #dce7f3);
    font-weight: 600;
    line-height: 1.4;
}

.torrent-name i {
    flex: 0 0 auto;
    color: var(--theme-teal-text, rgba(45,212,191,.7));
}

.slot-types {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem;
}

.slot-badge {
    display: inline-flex;
    align-items: center;
    padding: .28rem .48rem;
    border-radius: .4rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    white-space: nowrap;
}

.slot-badge.free {
    color: var(--theme-teal-text, #8fe7c3);
    background: var(--theme-teal-soft, rgba(52,211,153,.08));
    border: 1px solid var(--theme-teal-border, rgba(52,211,153,.2));
}

.slot-badge.double {
    color: var(--theme-teal-text, #86e5f3);
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.2));
}

.expiry-time {
    color: var(--theme-muted, rgba(203,213,225,.68));
    font-size: var(--site-font-body, 13px);
    white-space: nowrap;
}

.expiry-time i {
    color: var(--theme-teal-text, rgba(45,212,191,.65));
}

.slot-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: .35rem;
}

.slot-btn {
    border-radius: .42rem;
    padding: .34rem .58rem;
    font-size: var(--site-font-small, 13px);
    font-weight: 600;
    line-height: 1.2;
    transition: all .18s ease;
}

.slot-btn.renew {
    color: var(--theme-amber-text, #f6d98b);
    background: var(--theme-amber-soft, rgba(245,158,11,.08));
    border: 1px solid var(--theme-amber-border, rgba(245,158,11,.22));
}

.slot-btn.renew:hover {
    color: var(--theme-text, #fff1c2);
    background: var(--theme-amber-soft, rgba(245,158,11,.15));
    border-color: var(--theme-amber-border, rgba(245,158,11,.4));
}

.slot-btn.remove {
    color: var(--theme-red-text, #f5a7af);
    background: var(--theme-red-soft, rgba(239,68,68,.07));
    border: 1px solid var(--theme-red-border, rgba(239,68,68,.2));
}

.slot-btn.remove:hover {
    color: var(--theme-text, #ffd0d4);
    background: var(--theme-red-soft, rgba(239,68,68,.14));
    border-color: var(--theme-red-border, rgba(239,68,68,.38));
}

.slot-btn.download {
    color: var(--theme-teal-text, #9ff8eb);
    background: var(--theme-teal-soft, rgba(45,212,191,.08));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.22));
}

.slot-btn.download:hover {
    color:  var(--theme-text, #fff);
    background: var(--theme-teal-soft, rgba(45,212,191,.15));
    border-color: var(--theme-teal-border, rgba(45,212,191,.4));
}

.active-slot {
    display: inline-flex;
    align-items: center;
    color: var(--theme-muted, rgba(203,213,225,.5));
    font-size: var(--site-font-small, 13px);
    white-space: nowrap;
}

.active-slot i {
    color: var(--theme-teal-text, #5ee0b9);
}

.empty-slots {
    padding: 2.5rem 1rem;
    text-align: center;
}

.empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: .75rem;
    border-radius: .65rem;
    background: var(--theme-teal-soft, rgba(45,212,191,.06));
    border: 1px solid var(--theme-teal-border, rgba(45,212,191,.14));
    color: var(--theme-teal-text, rgba(45,212,191,.55));
    font-size: 1.25rem;
}

.empty-slots h5 {
    margin-bottom: .25rem;
    color: var(--theme-muted, rgba(226,232,240,.7));
    font-size: var(--site-font-body, 13px);
}

.empty-slots p {
    margin: 0;
    color: var(--theme-muted, rgba(203,213,225,.45));
    font-size: var(--site-font-body, 13px);
}

@media (max-width: 768px) {
    .container.py-4 {
        padding-top: 1rem !important;
    }

    .elite-breadcrumb {
        margin-bottom: 1rem !important;
        font-size: var(--site-font-body, 13px);
        padding: .6rem .7rem;
    }

    h4 {
        font-size: var(--site-font-body, 13px);
    }

    .token-header {
        align-items: flex-start;
        flex-direction: column;
        padding: .85rem;
    }

    .available-slots {
        width: 100%;
        justify-content: space-between;
    }

    .token-body {
        padding: .55rem;
    }

    .token-table {
        min-width: 760px;
        font-size: var(--site-font-body, 13px);
    }

    .token-table thead th,
    .token-table tbody td {
        padding: .65rem;
    }

    .slot-actions {
        justify-content: flex-start;
    }
}
</style>
@endsection
