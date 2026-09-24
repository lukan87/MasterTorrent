@extends('layouts.app')

@section('content')
<div class="container-fluid my-5 modern-wrapper">

    <h3 class="mb-5 text-center text-light peers-title">
        Peers for Torrent:
        @if($torrent)
            <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
               class="text-info fw-bold text-decoration-none torrent-title-link">
                {{ $torrent->name }}
            </a>
        @endif
    </h3>

    <div class="d-flex flex-column align-items-center w-100">

        {{-- ================= SEEDERS ================= --}}
        @if($seeders)
        <div class="col-12 mb-5 peer-section">
            <div class="card modern-card border-0 shadow-lg">

                <div class="card-header modern-header bg-success">
                    <h5 class="mb-0 section-title">
                        <i class="bi bi-upload me-2"></i>
                        Seeders ({{ $seeders->total() }})
                    </h5>
                </div>

                <div class="card-body">

                    @if($seeders->isEmpty())
                        <div class="text-center text-muted py-4 empty-peer">
                            <i class="bi bi-cloud-slash me-2"></i>
                            No active seeders.
                        </div>
                    @else

                        @foreach($seeders as $index => $seeder)
                            <div class="peer-row">

                                {{-- LEFT --}}
                                <div class="peer-left">
                                    <span class="peer-number">
                                        {{ $seeders->firstItem() + $index }}.
                                    </span>

                                    @if($seeder->user)
                                        <a href="{{ route('profile.show', ['id' => $seeder->user->id, 'name' => $seeder->user->name]) }}"
                                           class="peer-name">
                                            {{ $seeder->user->name }}
                                        </a>
                                    @else
                                        <span class="text-danger deleted-user">Deleted User</span>
                                    @endif

                                    @if($seeder->user && $seeder->user->id === $torrent->owner)
                                        <span class="badge bg-warning text-dark owner-badge">
                                            <i class="bi bi-star-fill me-1"></i>
                                            Owner
                                        </span>
                                    @endif

                                    <span class="badge badge-agent">
                                        <i class="bi bi-hdd-network me-1"></i>
                                        {{ $seeder->agent }}
                                    </span>
                                </div>

                                {{-- RIGHT --}}
                                <div class="peer-right">
                                    <span class="badge badge-success-soft">
                                        <i class="bi bi-arrow-up-circle-fill me-1"></i>
                                        Seeder
                                    </span>

                                    <span class="badge badge-time">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $seeder->created_at->diffForHumans() }}
                                    </span>

                                    <span class="badge badge-ip">
                                        <i class="bi bi-globe2 me-1"></i>
                                        {{ $seeder->ip }}
                                    </span>
                                </div>

                            </div>
                        @endforeach

                    @endif
                </div>

                <div class="d-flex justify-content-center py-3 pagination-wrap">
                    {{ $seeders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif


        {{-- ================= LEECHERS ================= --}}
        @if($leechers)
        <div class="col-12 peer-section">
            <div class="card modern-card border-0 shadow-lg">

                <div class="card-header modern-header bg-danger">
                    <h5 class="mb-0 section-title">
                        <i class="bi bi-download me-2"></i>
                        Leechers ({{ $leechers->total() }})
                    </h5>
                </div>

                <div class="card-body">

                    @if($leechers->isEmpty())
                        <div class="text-center text-muted py-4 empty-peer">
                            <i class="bi bi-cloud-slash me-2"></i>
                            No active leechers.
                        </div>
                    @else

                        @foreach($leechers as $index => $leecher)
                            <div class="peer-row">

                                {{-- LEFT --}}
                                <div class="peer-left">
                                    <span class="peer-number">
                                        {{ $leechers->firstItem() + $index }}.
                                    </span>

                                    @if($leecher->user)
                                        <a href="{{ route('profile.show', ['id' => $leecher->user->id, 'name' => $leecher->user->name]) }}"
                                           class="peer-name">
                                            {{ $leecher->user->name }}
                                        </a>
                                    @else
                                        <span class="text-danger deleted-user">Deleted User</span>
                                    @endif

                                    @if($leecher->user && $leecher->user->id === $torrent->owner)
                                        <span class="badge bg-warning text-dark owner-badge">
                                            <i class="bi bi-star-fill me-1"></i>
                                            Owner
                                        </span>
                                    @endif

                                    <span class="badge badge-agent">
                                        <i class="bi bi-hdd-network me-1"></i>
                                        {{ $leecher->agent }}
                                    </span>
                                </div>

                                {{-- RIGHT --}}
                                <div class="peer-right">
                                    <span class="badge badge-danger-soft">
                                        <i class="bi bi-arrow-down-circle-fill me-1"></i>
                                        Left: {{ \App\Helpers\FormatHelper::formatSize($leecher->left) }}
                                    </span>

                                    <span class="badge badge-time">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $leecher->created_at->diffForHumans() }}
                                    </span>

                                    <span class="badge badge-ip">
                                        <i class="bi bi-globe2 me-1"></i>
                                        {{ $leecher->ip }}
                                    </span>
                                </div>

                            </div>
                        @endforeach

                    @endif
                </div>

                <div class="d-flex justify-content-center py-3 pagination-wrap">
                    {{ $leechers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>


<style>
/* =========================================================
   FileIplay Torrent Peers
   Complete responsive styling
   Original Blade functionality preserved
   ========================================================= */

html,
body {
    max-width: 100%;
    overflow-x: hidden !important;
}

.modern-wrapper {
    width: 100%;
    max-width: 1600px;
    margin-left: auto;
    margin-right: auto;
    background: linear-gradient(135deg, #162033, #0f172a);
    padding: 2.5rem;
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: .8rem;
    box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
    overflow: hidden;
}

.peers-title {
    font-size: clamp(1.55rem, 2.5vw, 2rem);
    line-height: 1.35;
    font-weight: 800;
}

.torrent-title-link {
    display: inline;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.peer-section {
    min-width: 0;
}

.modern-card {
    width: 100%;
    min-width: 0;
    background: rgba(255, 255, 255, .035);
    border: 1px solid rgba(255, 255, 255, .07) !important;
    backdrop-filter: blur(10px);
    border-radius: .75rem;
    overflow: hidden;
}

.modern-header {
    font-weight: 700;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, .07);
}

.section-title {
    color: #fff;
    font-size: 1.15rem;
    font-weight: 800;
}

.peer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    width: 100%;
    min-width: 0;
    padding: 1rem .85rem;
    margin-bottom: .65rem;
    border-radius: .65rem;
    background: rgba(255, 255, 255, .035);
    border: 1px solid rgba(255, 255, 255, .045);
    transition: background .2s ease, border-color .2s ease, transform .2s ease;
}

.peer-row:last-child {
    margin-bottom: 0;
}

.peer-row:hover {
    transform: translateY(-1px);
    background: rgba(255, 255, 255, .06);
    border-color: rgba(32, 201, 151, .16);
}

.peer-left {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .45rem;
    flex: 1 1 auto;
    min-width: 0;
}

.peer-number {
    flex: 0 0 auto;
    margin-right: .15rem;
    color: #8f9aa5;
    font-size: 1rem;
    font-weight: 800;
}

.peer-name,
.deleted-user {
    max-width: 100%;
    font-size: 1rem;
    line-height: 1.4;
    font-weight: 700;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.peer-name {
    color: #fff;
    text-decoration: none;
}

.peer-name:hover {
    color: #55d6ba;
}

.deleted-user {
    color: #ff7f8a;
}

.peer-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .45rem;
    flex: 0 1 auto;
    flex-wrap: wrap;
    min-width: 0;
}

.peer-row .badge {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: .42rem .62rem;
    border-radius: .42rem;
    font-size: .82rem;
    line-height: 1.25;
    font-weight: 700;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.owner-badge {
    margin-left: 0 !important;
}

.badge-agent {
    background: rgba(255, 255, 255, .08);
    color: #d0d8dc;
    border: 1px solid rgba(255, 255, 255, .06);
}

.badge-success-soft {
    background: rgba(40, 167, 69, .15);
    color: #72df9c;
    border: 1px solid rgba(40, 167, 69, .18);
}

.badge-danger-soft {
    background: rgba(220, 53, 69, .15);
    color: #ff8c96;
    border: 1px solid rgba(220, 53, 69, .18);
}

.badge-time {
    background: rgba(108, 117, 125, .16);
    color: #c0c9ce;
    border: 1px solid rgba(255, 255, 255, .05);
}

.badge-ip {
    background: rgba(0, 0, 0, .28);
    color: #c0c8cc;
    border: 1px solid rgba(255, 255, 255, .045);
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.empty-peer {
    font-size: 1rem;
}

.pagination-wrap {
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
}

.pagination-wrap .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    justify-content: center;
}

/* Tablet */
@media (max-width: 991.98px) {
    .modern-wrapper {
        padding: 2rem 1.25rem;
    }

    .peer-row {
        gap: .75rem;
    }

    .peer-right {
        max-width: 48%;
    }
}

/* Mobile */
@media (max-width: 767.98px) {
    .modern-wrapper {
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
        padding: 1.25rem .75rem;
        border-radius: .65rem;
    }

    .peers-title {
        margin-bottom: 1.75rem !important;
        font-size: 1.45rem;
    }

    .modern-header {
        padding: .9rem 1rem;
    }

    .section-title {
        font-size: 1.05rem;
    }

    .modern-card .card-body {
        padding: .8rem .7rem;
    }

    .peer-row {
        display: block;
        padding: .9rem .65rem;
    }

    .peer-left {
        width: 100%;
        margin-bottom: .7rem;
    }

    .peer-right {
        width: 100%;
        max-width: none;
        justify-content: flex-start;
        padding-left: 2rem;
    }

    .peer-row .badge {
        font-size: .78rem;
    }

    .peer-name,
    .deleted-user {
        font-size: .95rem;
    }

    .peer-number {
        font-size: .95rem;
    }
}

/* Small phones */
@media (max-width: 479.98px) {
    .modern-wrapper {
        padding: 1rem .5rem;
    }

    .peers-title {
        font-size: 1.3rem;
    }

    .torrent-title-link {
        display: block;
        margin-top: .35rem;
    }

    .peer-row {
        padding: .8rem .55rem;
    }

    .peer-left {
        align-items: flex-start;
    }

    .peer-right {
        padding-left: 0;
        display: grid;
        grid-template-columns: 1fr;
        gap: .35rem;
    }

    .peer-row .badge {
        width: 100%;
        justify-content: flex-start;
        font-size: .77rem;
    }

    .pagination-wrap {
        justify-content: flex-start !important;
    }
}

/* Never allow long content to create a page-wide horizontal scrollbar */
.modern-wrapper *,
.modern-wrapper *::before,
.modern-wrapper *::after {
    box-sizing: border-box;
    max-width: 100%;
}
</style>
@endsection
