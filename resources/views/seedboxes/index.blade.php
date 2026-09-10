@extends('layouts.app')

@section('content')

@php
    $authUserId = auth()->id();

    $isDev = auth()->user()->user_class == \App\Models\UserClass::WEB_DEVELOPER;

    $userSeedboxes = $seedboxes->where('user_id', $authUserId);

    $otherSeedboxes = $isDev
        ? $seedboxes->where('user_id', '!=', $authUserId)
        : collect();
@endphp

<div class="seedbox-page-wrapper">

    <div class="container py-5">

        {{-- HERO --}}
        <div class="seedbox-hero mb-4">

            <div class="hero-content">

                <div class="hero-kicker">
                    STORAGE • SERVERS • AUTOMATION
                </div>

                <h1 class="hero-title">

                    <i class="bi bi-hdd-network-fill me-2"></i>

                    Seedbox Management

                </h1>

                <div class="hero-subtitle">

                    Manage your personal seedboxes and remote torrent clients.

                </div>

            </div>

            <a href="{{ route('seedboxes.create') }}"
               class="btn hero-add-btn">

                <i class="bi bi-plus-circle-fill me-2"></i>

                Add Seedbox

            </a>

        </div>

        {{-- ALERTS --}}
        @foreach (['success', 'error'] as $msg)

            @if(session($msg))

                <div class="modern-alert {{ $msg === 'success'
                    ? 'modern-alert-success'
                    : 'modern-alert-danger' }}">

                    <div class="d-flex align-items-center gap-2">

                        <i class="bi {{ $msg === 'success'
                            ? 'bi-check-circle-fill'
                            : 'bi-exclamation-triangle-fill' }}"></i>

                        {{ session($msg) }}

                    </div>

                    <button class="btn-close btn-close-white"
                            data-bs-dismiss="alert"></button>

                </div>

            @endif

        @endforeach

        {{-- USER SEEDBOXES --}}
        <div class="modern-section-card mb-4">

            <div class="section-header">

                <div>

                    <h4 class="section-title text-info">

                        <i class="bi bi-person-circle me-2"></i>

                        Your Seedboxes

                    </h4>

                    <div class="section-subtitle">

                        Your connected remote servers

                    </div>

                </div>

                <div class="section-count">

                    {{ $userSeedboxes->count() }}

                </div>

            </div>

            @if($userSeedboxes->isEmpty())

                <div class="empty-state">

                    <i class="bi bi-hdd-stack"></i>

                    <h5>No Seedboxes Added</h5>

                    <p>
                        You haven't added any seedboxes yet.
                    </p>

                </div>

            @else

                @include('seedboxes.partials.seedbox-table', [
                    'boxes' => $userSeedboxes,
                    'highlightOwner' => true
                ])

            @endif

        </div>

        {{-- OTHER SEEDBOXES --}}
        @if($isDev && $otherSeedboxes->isNotEmpty())

            <div class="modern-section-card">

                <div class="section-header">

                    <div>

                        <h4 class="section-title text-warning">

                            <i class="bi bi-people-fill me-2"></i>

                            Other Seedboxes

                        </h4>

                        <div class="section-subtitle">

                            Developer overview of all servers

                        </div>

                    </div>

                    <div class="section-count warning-count">

                        {{ $otherSeedboxes->count() }}

                    </div>

                </div>

                @include('seedboxes.partials.seedbox-table', [
                    'boxes' => $otherSeedboxes,
                    'highlightOwner' => false
                ])

            </div>

        @endif

    </div>

</div>

<style>
/* =========================================
   FILEIPLAY SEEDBOX MANAGEMENT
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.seedbox-page-wrapper {
    color: #e2e8f0;
}

.seedbox-hero,
.modern-section-card {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 10px 28px rgba(0,0,0,.18);
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
    border-left: 3px solid var(--ui-accent, #22d3ee);
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
    background: radial-gradient(circle, rgba(34,211,238,.09), transparent 70%);
    pointer-events: none;
}

.hero-content {
    min-width: 0;
}

.hero-kicker {
    margin-bottom: .3rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.hero-title {
    margin: 0;
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.3;
}

.hero-title i {
    color: var(--ui-accent, #22d3ee);
}

.hero-subtitle {
    margin-top: .35rem;
    color: rgba(226,232,240,.58);
    font-size: 13px;
    line-height: 1.45;
}

.hero-add-btn {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .5rem .75rem;
    border: 1px solid rgba(34,211,238,.28);
    border-radius: .55rem;
    background: rgba(34,211,238,.10);
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s ease;
}

.hero-add-btn:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.16);
    color: #fff;
    transform: translateY(-1px);
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
    color: #e2e8f0;
    font-size: 13px;
}

.modern-alert-success {
    border: 1px solid rgba(34,197,94,.20);
    border-left: 3px solid rgba(34,197,94,.60);
    background: rgba(34,197,94,.07);
}

.modern-alert-success i {
    color: #86efac;
}

.modern-alert-danger {
    border: 1px solid rgba(239,68,68,.20);
    border-left: 3px solid rgba(239,68,68,.60);
    background: rgba(239,68,68,.07);
}

.modern-alert-danger i {
    color: #fca5a5;
}

/* Section cards */

.modern-section-card {
    padding: .95rem;
    border-radius: .85rem;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    margin-bottom: .75rem;
    padding-bottom: .7rem;
    border-bottom: 1px solid var(--ui-border, rgba(255,255,255,.08));
}

.section-title {
    margin: 0;
    color: #f8fafc !important;
    font-size: 15px;
    font-weight: 700;
}

.section-title.text-info {
    color: var(--ui-accent, #22d3ee) !important;
}

.section-title.text-warning {
    color: #fbbf24 !important;
}

.section-title i {
    color: var(--ui-accent, #22d3ee);
}

.section-title.text-warning i {
    color: #fbbf24;
}

.section-subtitle {
    margin-top: .2rem;
    color: rgba(226,232,240,.45);
    font-size: 12px;
}

.section-count {
    min-width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(34,211,238,.22);
    border-radius: .55rem;
    background: rgba(34,211,238,.08);
    color: var(--ui-accent, #22d3ee);
    font-size: 13px;
    font-weight: 700;
}

.warning-count {
    border-color: rgba(245,158,11,.22);
    background: rgba(245,158,11,.07);
    color: #fbbf24;
}

/* Empty state */

.empty-state {
    padding: 2.2rem 1rem;
    text-align: center;
    border: 1px dashed var(--ui-border, rgba(255,255,255,.08));
    border-radius: .7rem;
    background: rgba(255,255,255,.018);
}

.empty-state i {
    display: block;
    margin-bottom: .55rem;
    color: var(--ui-accent, #22d3ee);
    font-size: 2rem;
}

.empty-state h5 {
    margin-bottom: .3rem;
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
}

.empty-state p {
    margin: 0;
    color: rgba(226,232,240,.48);
    font-size: 13px;
}

/* Seedbox partial/table */

.modern-section-card .table-responsive {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .65rem;
    overflow-x: auto;
}

.modern-section-card .table {
    margin-bottom: 0;
    color: #e2e8f0;
    font-size: 13px;
}

.modern-section-card .table thead th {
    padding: .6rem .7rem;
    border-bottom: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
    background: rgba(255,255,255,.025);
    color: rgba(226,232,240,.55);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    white-space: nowrap;
}

.modern-section-card .table tbody td {
    padding: .65rem .7rem;
    border-color: var(--ui-border, rgba(255,255,255,.06)) !important;
    color: rgba(226,232,240,.78);
    vertical-align: middle;
}

.modern-section-card .table tbody tr {
    transition: background .2s ease;
}

.modern-section-card .table tbody tr:hover {
    background: rgba(34,211,238,.035);
}

/* Common controls inside the included seedbox table */

.modern-section-card .btn {
    border-radius: .5rem !important;
    font-size: 12px !important;
    font-weight: 700 !important;
}

.modern-section-card .btn-primary {
    border: 1px solid rgba(34,211,238,.25);
    background: rgba(34,211,238,.09);
    color: var(--ui-accent, #22d3ee);
}

.modern-section-card .btn-primary:hover {
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.15);
    color: #fff;
}

.modern-section-card .btn-danger {
    border: 1px solid rgba(239,68,68,.22);
    background: rgba(239,68,68,.08);
    color: #fca5a5;
}

.modern-section-card .btn-danger:hover {
    background: rgba(239,68,68,.14);
    color: #fff;
}

.modern-section-card .btn-warning {
    border: 1px solid rgba(245,158,11,.22);
    background: rgba(245,158,11,.08);
    color: #fbbf24 !important;
}

.modern-section-card .form-control,
.modern-section-card .form-select {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08)) !important;
    border-radius: .5rem !important;
    background: rgba(255,255,255,.035) !important;
    color: #f8fafc !important;
    font-size: 13px;
    box-shadow: none !important;
}

.modern-section-card .form-control:focus,
.modern-section-card .form-select:focus {
    border-color: rgba(34,211,238,.42) !important;
    box-shadow: 0 0 0 3px rgba(34,211,238,.07) !important;
}

.modern-section-card .form-control::placeholder {
    color: rgba(226,232,240,.35);
}

/* Password toggle */

.modern-section-card button[onclick^="togglePassword"] {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .45rem;
    background: rgba(255,255,255,.04);
    color: rgba(226,232,240,.65);
    font-size: 12px;
}

.modern-section-card button[onclick^="togglePassword"]:hover {
    border-color: rgba(34,211,238,.28);
    color: var(--ui-accent, #22d3ee);
}

/* Mobile */

@media (max-width: 767.98px) {
    .seedbox-page-wrapper .container {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .seedbox-hero {
        padding: .9rem;
    }

    .hero-title {
        font-size: 18px;
    }

    .hero-subtitle {
        font-size: 12px;
    }

    .hero-add-btn {
        width: 100%;
    }

    .modern-section-card {
        padding: .75rem;
    }

    .section-title {
        font-size: 14px;
    }

    .section-subtitle {
        font-size: 11px;
    }

    .modern-alert {
        align-items: flex-start;
        font-size: 12px;
    }

    .modern-section-card .table {
        font-size: 12px;
    }
}
</style>

<script>

function togglePassword(id){

    const input = document.getElementById('password-' + id);

    const button = input.nextElementSibling;

    if(input.type === 'password'){

        input.type = 'text';

        button.textContent = 'Hide';

    }else{

        input.type = 'password';

        button.textContent = 'Show';
    }
}

</script>

@endsection