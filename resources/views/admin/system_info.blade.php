@extends('layouts.app')

@section('content')

<div class="container-fluid px-3 px-md-4 py-3 system-info-page">

    <!-- HEADER -->
    <div class="system-info-header mb-4">

        <div>
            <h3 class="system-info-title">
                <i class="bi bi-cpu"></i>
                System Information
            </h3>

            <p class="system-info-subtitle">
                Server status and diagnostics
            </p>
        </div>

        <span class="server-control-badge">
            <i class="bi bi-server"></i>
            Server Control
        </span>

    </div>


    <!-- SYSTEM STATS -->
    <div class="row g-3">

        <!-- PHP -->
        <div class="col-xl-3 col-md-6">
            <div class="system-card system-stat-card h-100">

                <div class="system-icon">
                    <i class="bi bi-code-slash"></i>
                </div>

                <div class="system-label">
                    PHP Version
                </div>

                <div class="system-value">
                    {{ $phpVersion }}
                </div>

            </div>
        </div>


        <!-- OS -->
        <div class="col-xl-3 col-md-6">
            <div class="system-card system-stat-card h-100">

                <div class="system-icon">
                    <i class="bi bi-hdd-stack"></i>
                </div>

                <div class="system-label">
                    Operating System
                </div>

                <div class="system-value system-value-small">
                    {{ $os }}
                </div>

            </div>
        </div>


        <!-- UPTIME -->
        <div class="col-xl-3 col-md-6">
            <div class="system-card system-stat-card h-100">

                <div class="system-icon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="system-label">
                    System Uptime
                </div>

                <div class="system-value">
                    {{ $uptime }}
                </div>

            </div>
        </div>


        <!-- CACHE -->
        <div class="col-xl-3 col-md-6">
            <div class="system-card system-stat-card h-100">

                <div class="system-icon">
                    <i class="bi bi-lightning"></i>
                </div>

                <div class="system-label">
                    Cache Status
                </div>

                <div class="system-value system-value-small">
                    {{ $cacheStatus }}
                </div>

            </div>
        </div>

    </div>


    <!-- RESOURCE USAGE -->
    <div class="row g-3 mt-1">

        <!-- DISK -->
        <div class="col-md-6">

            <div class="system-card resource-card">

                <div class="resource-title">
                    <i class="bi bi-hdd-network"></i>
                    Disk Storage
                </div>

                <div class="resource-value">
                    {{ number_format($storage / 1073741824, 2) }} GB free of
                    {{ number_format($diskTotal / 1073741824, 2) }} GB
                </div>

                <div class="progress system-progress">

                    <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: {{ 100 - (($storage / $diskTotal) * 100) }}%"
                    ></div>

                </div>

            </div>

        </div>


        <!-- RAM -->
        <div class="col-md-6">

            <div class="system-card resource-card">

                <div class="resource-title">
                    <i class="bi bi-memory"></i>
                    RAM Usage
                </div>

                <div class="resource-value">
                    {{ $ramUsage['used'] }} MB used of {{ $ramUsage['total'] }} MB
                </div>

                <div class="progress system-progress">

                    <div
                        class="progress-bar ram-progress"
                        role="progressbar"
                        style="width: {{ ($ramUsage['used'] / $ramUsage['total']) * 100 }}%"
                    ></div>

                </div>

            </div>

        </div>

    </div>


    <!-- CPU LOAD -->
    <div class="row g-3 mt-1">

        <div class="col-12">

            <div class="system-card resource-card">

                <div class="resource-title">
                    <i class="bi bi-speedometer2"></i>
                    CPU Load Average
                </div>

                <div class="cpu-load-list">

                    <span class="cpu-load-badge">
                        <span>1 min</span>
                        {{ $cpuLoad[0] }}
                    </span>

                    <span class="cpu-load-badge">
                        <span>5 min</span>
                        {{ $cpuLoad[1] }}
                    </span>

                    <span class="cpu-load-badge">
                        <span>15 min</span>
                        {{ $cpuLoad[2] }}
                    </span>

                </div>

            </div>

        </div>

    </div>


    <!-- ACTIONS -->
    <div class="system-card maintenance-card mt-3">

        <div class="maintenance-header">
            <div class="maintenance-icon">
                <i class="bi bi-tools"></i>
            </div>

            <div>
                <h5>Maintenance Actions</h5>
                <p>Manage application caches and inspect registered routes.</p>
            </div>
        </div>


        <div class="row g-2 maintenance-actions">

            <div class="col-xl-3 col-md-6">
                <form action="{{ route('admin.systemInfo.clearCache') }}" method="POST">
                    @csrf

                    <button type="submit" class="maintenance-btn danger-btn w-100">
                        <i class="bi bi-lightning-charge"></i>
                        Clear Cache
                    </button>
                </form>
            </div>


            <div class="col-xl-3 col-md-6">
                <form action="{{ route('admin.systemInfo.clearViews') }}" method="POST">
                    @csrf

                    <button type="submit" class="maintenance-btn warning-btn w-100">
                        <i class="bi bi-eye-slash"></i>
                        Clear Views
                    </button>
                </form>
            </div>


            <div class="col-xl-3 col-md-6">
                <form action="{{ route('admin.systemInfo.clearConfig') }}" method="POST">
                    @csrf

                    <button type="submit" class="maintenance-btn success-btn w-100">
                        <i class="bi bi-sliders"></i>
                        Clear Config
                    </button>
                </form>
            </div>


            <div class="col-xl-3 col-md-6">
                <form action="{{ route('admin.systemInfo.clearRoutes') }}" method="POST">
                    @csrf

                    <button type="submit" class="maintenance-btn primary-btn w-100">
                        <i class="bi bi-diagram-3"></i>
                        Clear Routes
                    </button>
                </form>
            </div>


            <div class="col-xl-3 col-md-6">
                <a
                    href="{{ route('admin.systemInfo.showRoutes') }}"
                    class="maintenance-btn info-btn w-100"
                >
                    <i class="bi bi-list"></i>
                    Show Routes
                </a>
            </div>

        </div>

    </div>

</div>


<style>
/* =========================================
   FILEIPLAY SYSTEM INFORMATION
   Dark glass + teal forum style
========================================= */

.system-info-page {
    max-width: 1550px;
}

.system-info-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.system-info-title {
    margin: 0;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 700;
}

.system-info-title i {
    margin-right: .4rem;
    color: var(--ui-accent, #22d3c5);
}

.system-info-subtitle {
    margin: .2rem 0 0;
    color: #64748b;
    font-size: 12px;
}

.server-control-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .4rem .65rem;
    color: #67e8f9;
    background: rgba(14,116,144,.18);
    border: 1px solid rgba(34,211,238,.2);
    border-radius: .45rem;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.server-control-badge i {
    font-size: 12px;
}

/* CARDS */

.system-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        rgba(22,32,51,.95),
        rgba(15,23,42,.84)
    );
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .75rem;
    box-shadow: 0 8px 22px rgba(0,0,0,.2);
    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
}

.system-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.system-card:hover {
    transform: translateY(-2px);
    border-color: rgba(34,211,197,.24);
    box-shadow: 0 12px 28px rgba(0,0,0,.25);
}

/* SYSTEM STAT CARDS */

.system-stat-card {
    padding: .85rem;
}

.system-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .6rem;
    font-size: 17px;
}

.system-label {
    margin-top: .65rem;
    margin-bottom: .15rem;
    color: #64748b;
    font-size: 12px;
}

.system-value {
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.system-value-small {
    font-size: 13px;
}

/* RESOURCE CARDS */

.resource-card {
    padding: .9rem;
}

.resource-title {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin-bottom: .6rem;
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 700;
}

.resource-title i {
    color: var(--ui-accent, #22d3c5);
}

.resource-value {
    margin-bottom: .5rem;
    color: #94a3b8;
    font-size: 12px;
}

.system-progress {
    height: 6px;
    overflow: hidden;
    background: rgba(30,41,59,.9);
    border-radius: 999px;
}

.system-progress .progress-bar {
    background: var(--ui-accent, #22d3c5);
}

.system-progress .ram-progress {
    background: #f59e0b;
}

/* CPU */

.cpu-load-list {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
}

.cpu-load-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .55rem;
    color: #cbd5e1;
    background: rgba(51,65,85,.42);
    border: 1px solid rgba(148,163,184,.14);
    border-radius: .4rem;
    font-size: 11px;
    font-weight: 600;
}

.cpu-load-badge span {
    color: #64748b;
    font-weight: 500;
}

/* MAINTENANCE */

.maintenance-card {
    padding: .9rem;
}

.maintenance-header {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin-bottom: .9rem;
}

.maintenance-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ui-accent, #22d3c5);
    background: rgba(34,211,197,.07);
    border: 1px solid rgba(34,211,197,.16);
    border-radius: .55rem;
    font-size: 16px;
}

.maintenance-header h5 {
    margin: 0 0 .15rem;
    color: #e2e8f0;
    font-size: 14px;
    font-weight: 700;
}

.maintenance-header p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
}

.maintenance-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    min-height: 35px;
    padding: .4rem .65rem;
    border-radius: .45rem;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: .15s ease;
}

.maintenance-btn:hover {
    transform: translateY(-1px);
}

.danger-btn {
    color: #fecaca;
    background: rgba(127,29,29,.55);
    border: 1px solid rgba(248,113,113,.2);
}

.danger-btn:hover {
    color: #fee2e2;
    background: rgba(153,27,27,.7);
    border-color: rgba(248,113,113,.35);
}

.warning-btn {
    color: #fde68a;
    background: rgba(120,53,15,.55);
    border: 1px solid rgba(251,191,36,.2);
}

.warning-btn:hover {
    color: #fef3c7;
    background: rgba(146,64,14,.7);
    border-color: rgba(251,191,36,.35);
}

.success-btn {
    color: #bbf7d0;
    background: rgba(20,83,45,.55);
    border: 1px solid rgba(74,222,128,.2);
}

.success-btn:hover {
    color: #dcfce7;
    background: rgba(22,101,52,.7);
    border-color: rgba(74,222,128,.35);
}

.primary-btn {
    color: #a5f3fc;
    background: rgba(14,116,144,.22);
    border: 1px solid rgba(34,211,238,.2);
}

.primary-btn:hover {
    color: #cffafe;
    background: rgba(14,116,144,.35);
    border-color: rgba(34,211,238,.35);
}

.info-btn {
    color: #a5f3fc;
    background: rgba(15,118,110,.25);
    border: 1px solid rgba(45,212,191,.2);
}

.info-btn:hover {
    color: #ccfbf1;
    background: rgba(15,118,110,.4);
    border-color: rgba(45,212,191,.35);
}

/* MOBILE */

@media (max-width: 767.98px) {
    .system-info-page {
        padding-top: .75rem !important;
        padding-bottom: .75rem !important;
    }

    .system-info-header {
        align-items: flex-start;
    }

    .system-stat-card {
        padding: .75rem;
    }
}

@media (max-width: 480px) {
    .system-info-header {
        flex-direction: column;
        gap: .65rem;
    }

    .server-control-badge {
        align-self: flex-start;
    }

    .maintenance-btn {
        min-height: 37px;
    }
}
</style>

@endsection
