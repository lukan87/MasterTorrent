@extends('layouts.app')

@section('content')

<div class="history-wrapper">

    {{-- Back Button --}}
    <div class="mb-4">
        <a href="{{ route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug]) }}"
           class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Torrent
        </a>
    </div>

    {{-- Title --}}
    <h3 class="mb-4 text-light">
        Users That Finished ({{ $histories->total() }}) :
        <span class="text-info fw-bold">{{ $torrent->name }}</span>
    </h3>

    @if($histories->isEmpty())
        <div class="alert alert-dark">
            No users have finished this torrent yet.
        </div>
    @else

        @foreach($histories as $index => $history)

<div class="history-row">

    {{-- COLUMN 1 — USER --}}
    <div class="col-user">

        <div class="user-header">
            <span class="row-number">
                {{ $histories->firstItem() + $index }}.
            </span>

            @if($history->user)
                <a href="{{ route('profile.show', ['id' => $history->user->id, 'name' => $history->user->name]) }}"
                   class="username">
                    {{ $history->user->name }}
                </a>
            @else
                <span class="text-danger">Deleted User</span>
            @endif

            @if($history->user && $history->user->id === $torrent->owner)
                <span class="badge bg-warning text-dark ms-2">Owner</span>
            @endif
        </div>

        <div class="meta-info">
            <span>Started: {{ $history->created_at->format('Y-m-d H:i') }}</span>
            <span>
                Finished:
                {{ $history->completed_at
                    ? $history->completed_at->format('Y-m-d H:i')
                    : 'Incomplete' }}
            </span>
        </div>

    </div>


    {{-- COLUMN 2 — TRANSFER --}}
    <div class="col-transfer">

        <div class="transfer-block upload"
             data-bs-toggle="tooltip"
             title="Actual Uploaded: {{ \App\Helpers\FormatHelper::formatSize($history->actual_uploaded ?? 0) }}">

            <small>Uploaded</small>
            <strong>
                {{ \App\Helpers\FormatHelper::formatSize($history->uploaded) }}
            </strong>
        </div>

        <div class="transfer-block download"
             data-bs-toggle="tooltip"
             title="Actual Downloaded: {{ \App\Helpers\FormatHelper::formatSize($history->actual_downloaded ?? 0) }}">

            <small>Downloaded</small>
            <strong>
                {{ \App\Helpers\FormatHelper::formatSize($history->downloaded) }}
            </strong>
        </div>

    </div>


    {{-- COLUMN 3 — ACTIVITY --}}
    <div class="col-time">

        <div class="time-block">
            <small>Seedtime</small>
            <strong>{{ \App\Helpers\FormatHelper::formatTime($history->seedtime) }}</strong>
        </div>

        <div class="time-block">
            <small>Leech Duration</small>
            <strong>
                @if($history->completed_at)
                    {{ $history->completed_at->diffForHumans($history->created_at, true) }}
                @else
                    Incomplete
                @endif
            </strong>
        </div>

        <div class="time-block">
            <small>Last Active</small>
            <strong>{{ $history->updated_at->diffForHumans() }}</strong>
        </div>

    </div>


    {{-- COLUMN 4 — STATUS --}}
    <div class="col-status">
        <div class="status-badge {{ $history->seeder ? 'status-seeding' : 'status-not' }}">
            {{ $history->seeder ? 'Seeding' : 'Not Seeding' }}
        </div>
    </div>

</div>

        @endforeach

        <div class="mt-4">
            {{ $histories->links('pagination::bootstrap-5') }}
        </div>

    @endif

</div>



<style>

.history-wrapper {
    width: 100%;
    padding: 2rem 3rem;
}

/* FULL WIDTH ROW */
.history-row {
    display: grid;
    grid-template-columns: 2.2fr 1.2fr 1.2fr 1fr;
    gap: 25px;
    align-items: center;
    width: 100%;
    padding: 22px 28px;
    margin-bottom: 18px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
}

.history-row:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
}

/* USER COLUMN */
.user-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.row-number {
    font-weight: 700;
    color: #8a8fa3;
}

.username {
    font-weight: 600;
    color: #fff;
    text-decoration: none;
}

.username:hover {
    color: #0dcaf0;
}

.meta-info {
    margin-top: 6px;
    font-size: 0.85rem;
    color: #aab0c0;
    display: flex;
    gap: 15px;
}

/* TRANSFER COLUMN */
.col-transfer {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.transfer-block small {
    color: #9aa1b5;
}

.transfer-block strong {
    font-size: 1rem;
}

.upload strong {
    color: #28a745;
}

.download strong {
    color: #0d6efd;
}

/* TIME COLUMN */
.col-time {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.time-block small {
    color: #9aa1b5;
}

.time-block strong {
    color: #ffffff;
}

/* STATUS COLUMN */
.col-status {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    display: inline-block;
    width: fit-content;
}

.status-seeding {
    background: rgba(40,167,69,0.2);
    color: #28a745;
}

.status-not {
    background: rgba(220,53,69,0.2);
    color: #dc3545;
}

.last-active {
    font-size: 0.8rem;
    color: #aab0c0;
}

/* Responsive */
@media (max-width: 1200px) {
    .history-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}

</style>

@endsection