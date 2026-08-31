@extends('layouts.app')

@section('content')
<div class="container-fluid my-5 modern-wrapper">

    <h3 class="mb-5 text-center text-light">
        Peers for Torrent:
        @if($torrent)
            <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}" 
               class="text-info fw-bold text-decoration-none">
                {{ $torrent->name }}
            </a>
        @endif
    </h3>

    <div class="d-flex flex-column align-items-center w-100">

        {{-- ================= SEEDERS ================= --}}
        @if($seeders)
        <div class="col-12 mb-5">
            <div class="card modern-card border-0 shadow-lg">

                <div class="card-header modern-header bg-success">
                    <h5 class="mb-0">
                        <i class="bi bi-upload me-2"></i>
                        Seeders ({{ $seeders->total() }})
                    </h5>
                </div>

                <div class="card-body">

                    @if($seeders->isEmpty())
                        <div class="text-center text-muted py-4">
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
                                        <span class="text-danger">Deleted User</span>
                                    @endif

                                    @if($seeder->user && $seeder->user->id === $torrent->owner)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Owner
                                        </span>
                                    @endif

                                    <span class="badge badge-agent ms-2">
                                        {{ $seeder->agent }}
                                    </span>
                                </div>

                                {{-- RIGHT --}}
                                <div class="peer-right">
                                    <span class="badge badge-success-soft">
                                        Seeder
                                    </span>

                                    <span class="badge badge-time">
                                        {{ $seeder->created_at->diffForHumans() }}
                                    </span>

                                    <span class="badge badge-ip">
                                        {{ $seeder->ip }}
                                    </span>
                                </div>

                            </div>
                        @endforeach

                    @endif
                </div>

                <div class="d-flex justify-content-center py-3">
                    {{ $seeders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif


        {{-- ================= LEECHERS ================= --}}
        @if($leechers)
        <div class="col-12">
            <div class="card modern-card border-0 shadow-lg">

                <div class="card-header modern-header bg-danger">
                    <h5 class="mb-0">
                        <i class="bi bi-download me-2"></i>
                        Leechers ({{ $leechers->total() }})
                    </h5>
                </div>

                <div class="card-body">

                    @if($leechers->isEmpty())
                        <div class="text-center text-muted py-4">
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
                                        <span class="text-danger">Deleted User</span>
                                    @endif

                                    @if($leecher->user && $leecher->user->id === $torrent->owner)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Owner
                                        </span>
                                    @endif

                                    <span class="badge badge-agent ms-2">
                                        {{ $leecher->agent }}
                                    </span>
                                </div>

                                {{-- RIGHT --}}
                                <div class="peer-right">
                                    <span class="badge badge-danger-soft">
                                        Left: {{ \App\Helpers\FormatHelper::formatSize($leecher->left) }}
                                    </span>

                                    <span class="badge badge-time">
                                        {{ $leecher->created_at->diffForHumans() }}
                                    </span>

                                    <span class="badge badge-ip">
                                        {{ $leecher->ip }}
                                    </span>
                                </div>

                            </div>
                        @endforeach

                    @endif
                </div>

                <div class="d-flex justify-content-center py-3">
                    {{ $leechers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif

    </div>
</div>


<style>
.modern-wrapper {
    background: linear-gradient(135deg, #1e1e2f, #14141f);
    padding: 2.5rem;
    border-radius: 14px;
}

.modern-card {
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(10px);
    border-radius: 14px;
}

.modern-header {
    font-weight: 600;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.peer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    margin-bottom: 12px;
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    transition: all 0.25s ease;
}

.peer-row:hover {
    transform: translateY(-3px);
    background: rgba(255,255,255,0.07);
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
}

.peer-left {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.peer-right {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.peer-number {
    font-weight: 700;
    margin-right: 10px;
    color: #8a8fa3;
}

.peer-name {
    font-weight: 600;
    color: #ffffff;
    text-decoration: none;
}

.peer-name:hover {
    color: #0dcaf0;
}

.badge-agent {
    background: rgba(255,255,255,0.08);
    color: #ccc;
}

.badge-success-soft {
    background: rgba(40,167,69,0.2);
    color: #28a745;
}

.badge-danger-soft {
    background: rgba(220,53,69,0.2);
    color: #dc3545;
}

.badge-time {
    background: rgba(108,117,125,0.2);
    color: #adb5bd;
}

.badge-ip {
    background: rgba(0,0,0,0.5);
    color: #bbb;
}
</style>
@endsection