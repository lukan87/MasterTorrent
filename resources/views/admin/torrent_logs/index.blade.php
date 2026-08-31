@extends('layouts.app')

@section('content')

@php
    $actions = [
        'uploaded' => ['color' => 'success', 'icon' => 'bi-upload'],
        'edited' => ['color' => 'warning', 'icon' => 'bi-pencil-square'],
        'deleted' => ['color' => 'danger', 'icon' => 'bi-trash'],
        'restored' => ['color' => 'info', 'icon' => 'bi-arrow-counterclockwise'],
        'force_deleted' => ['color' => 'dark', 'icon' => 'bi-x-octagon'],
    ];
@endphp

<div class="container-fluid mt-3 glass px-4 py-4 shadow-sm rounded">



<div class="card glass mb-4 mt-5 shadow-sm">
    <div class="card-body">

        <form method="GET" class="d-flex flex-column gap-3">

            @php
                $currentAction = request('action');
            @endphp

            {{-- 🔘 ACTION FILTERS --}}
            @php
    $currentAction = request('action');
    $range = request('range');
@endphp

{{-- 📅 DATE FILTER --}}
<div class="d-flex flex-wrap gap-2 mb-2">

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => 'today'])) }}"
       class="btn btn-sm {{ $range === 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
        Today
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => '7'])) }}"
       class="btn btn-sm {{ $range === '7' ? 'btn-primary' : 'btn-outline-primary' }}">
        7 days
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->except('range'), ['range' => '30'])) }}"
       class="btn btn-sm {{ $range === '30' ? 'btn-primary' : 'btn-outline-primary' }}">
        30 days
    </a>

</div>

{{-- 🔘 ACTION FILTERS WITH COUNTS --}}
<div class="d-flex flex-wrap gap-2">

    <a href="{{ route('admin.torrent_logs.index', request()->except('action')) }}"
       class="btn btn-sm {{ !$currentAction ? 'btn-dark' : 'btn-outline-dark' }}">
        All ({{ $counts['all'] }})
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'uploaded'])) }}"
       class="btn btn-sm {{ $currentAction === 'uploaded' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="bi bi-upload"></i> Uploaded ({{ $counts['uploaded'] }})
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'edited'])) }}"
       class="btn btn-sm {{ $currentAction === 'edited' ? 'btn-warning' : 'btn-outline-warning' }}">
        <i class="bi bi-pencil-square"></i> Edited ({{ $counts['edited'] }})
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'deleted'])) }}"
       class="btn btn-sm {{ $currentAction === 'deleted' ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="bi bi-trash"></i> Deleted ({{ $counts['deleted'] }})
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'restored'])) }}"
       class="btn btn-sm {{ $currentAction === 'restored' ? 'btn-info' : 'btn-outline-info' }}">
        <i class="bi bi-arrow-counterclockwise"></i> Restored ({{ $counts['restored'] }})
    </a>

    <a href="{{ route('admin.torrent_logs.index', array_merge(request()->all(), ['action' => 'force_deleted'])) }}"
       class="btn btn-sm {{ $currentAction === 'force_deleted' ? 'btn-dark' : 'btn-outline-secondary' }}">
        <i class="bi bi-x-octagon"></i> Force Deleted ({{ $counts['force_deleted'] }})
    </a>

</div>

            {{-- 🔽 DROPDOWN FILTERS --}}
            <div class="d-flex flex-wrap gap-2 align-items-center">

                {{-- CLASS --}}
                <select name="user_class"
                        class="form-select form-select-sm"
                        style="max-width: 200px;"
                        onchange="this.form.submit()">

                    <option value="">All Classes</option>

                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}"
                            {{ request('user_class') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>

                {{-- USER --}}
                <select name="user_id"
                        class="form-select form-select-sm"
                        style="max-width: 220px;"
                        onchange="this.form.submit()">

                    <option value="">All Users</option>

                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                {{-- KEEP ACTION --}}
                @if(request('action'))
                    <input type="hidden" name="action" value="{{ request('action') }}">
                @endif

                {{-- RESET --}}
                <a href="{{ route('admin.torrent_logs.index') }}"
                   class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>

            </div>

        </form>

    </div>
</div>



    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Torrent Logs</h4>

        <span class="badge bg-secondary">
            {{ $logs->total() }} entries
        </span>
    </div>

    {{-- HEADER ROW --}}
    <div class="row g-0 border-bottom small text-uppercase text-muted align-items-center fw-semibold">

        <div class="d-none d-md-block col-md-2 text-center py-2">
            User
        </div>

        <div class="col-12 col-md-5 px-2 py-2">
            Torrent
        </div>

        <div class="col-6 col-md-2 text-center py-2">
            Action
        </div>

        <div class="col-6 col-md-2 text-center py-2">
            Date
        </div>

        <div class="d-none d-md-block col-md-1 text-center py-2">
            View
        </div>
    </div>

    {{-- ROWS --}}
    @forelse($logs as $log)

    @php
        $action = $actions[$log->action] ?? ['color' => 'secondary', 'icon' => 'bi-question-circle'];

        $rowClass = match($log->action) {
            'deleted' => 'bg-danger-subtle',
            'edited' => 'bg-warning-subtle',
            'restored' => 'bg-info-subtle',
            'force_deleted' => 'bg-dark text-white',
            default => ''
        };
    @endphp

    <div class="row g-0 align-items-center border-bottom py-3 {{ $rowClass }} hover-row"
         onclick="window.location='{{ route('admin.torrent_logs.show', $log->id) }}'"
         style="cursor:pointer;">

        {{-- USER --}}
        <div class="d-none d-md-block col-md-2 text-center fw-semibold">
            {{ $log->user->name ?? 'Unknown' }}
        </div>

        {{-- TORRENT + DESCRIPTION --}}
        <div class="col-12 col-md-5 px-2">

            <div class="fw-bold fs-6">
                {{ $log->torrent->name ?? 'Deleted Torrent' }}
            </div>

            {{-- description adds context (huge improvement) --}}
            @if($log->description)
                <div class="small text-muted mt-1 text-truncate" style="max-width: 100%;">
                    {{ $log->description }}
                </div>
            @endif

            {{-- mobile user --}}
            <div class="small text-muted d-md-none mt-1">
                by {{ $log->user->name ?? 'Unknown' }}
            </div>

        </div>

        {{-- ACTION --}}
        <div class="col-6 col-md-2 text-center">
            <span class="badge bg-{{ $action['color'] }} px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi {{ $action['icon'] }}"></i>
                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
            </span>
        </div>

        {{-- DATE --}}
        <div class="col-6 col-md-2 text-center small fw-semibold">
            {{ $log->created_at->format('M d, Y') }}

            <div class="small text-muted">
                {{ $log->created_at->format('H:i') }}
            </div>
        </div>

        {{-- VIEW BUTTON (desktop only) --}}
        <div class="d-none d-md-block col-md-1 text-center">
            <a href="{{ route('admin.torrent_logs.show', $log->id) }}"
               class="btn btn-sm btn-outline-primary"
               onclick="event.stopPropagation();">
                <i class="bi bi-eye"></i>
            </a>
        </div>

    </div>

    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No logs found.
        </div>
    @endforelse

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>

</div>

{{-- HOVER EFFECT --}}
<style>
.hover-row:hover {
    background-color: rgba(0, 0, 0, 0.04);
    transition: 0.2s;
}
</style>

@endsection