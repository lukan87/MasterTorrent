@extends('layouts.app')

@section('title', 'Manage Happy Hours')

@section('content')

<div class="container glass mt-5 px-4 py-5">

    {{-- PAGE HEADER --}}
   <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>
        <h2 class="fw-bold text-white mb-1">Happy Hours</h2>
        <p class="text-white-50 small mb-0">
            Manage automatic and manual events
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap align-items-stretch">

    <!-- Automatic Toggle -->
    <form action="{{ route('happyhour.toggleAutomatic') }}" method="POST" class="d-flex">
        @csrf
        <button type="submit"
                class="btn btn-modern {{ $automatic ? 'btn-danger-modern' : 'btn-success-modern' }}">
            {{ $automatic ? 'Disable Automatic' : 'Enable Automatic' }}
        </button>
    </form>

{{-- Start New Happy Hour --}}
@if($automatic)
    <span data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="Cannot start manual Happy Hour while Automatic mode is enabled">
        <a href="#"
           class="btn btn-modern btn-primary-modern disabled-btn d-flex align-items-center justify-content-center">
            Start New Happy Hour
        </a>
    </span>
@else
    <a href="{{ route('happyhour.create') }}"
       class="btn btn-modern btn-primary-modern d-flex align-items-center justify-content-center">
        Start New Happy Hour
    </a>
@endif

</div>

</div>

    {{-- AUTOMATIC STATUS --}}
    <div class="status-card mb-4 {{ $automatic ? 'active' : 'inactive' }}">
        @if($automatic)
            <div>
                <strong>{{ $theme['name'] }}</strong>
                <div class="small text-white-50 mt-1">
                    {{ $theme['upload_multiplier'] }}x Upload •
                    {{ $theme['duration_hours'] }}h •
                    {{ $theme['free_download'] ? 'Free Download' : 'No Free Download' }}
                </div>
            </div>
            <span class="badge bg-success px-3 py-2">ACTIVE</span>
        @else
            <div>No automatic Happy Hour enabled.</div>
            <span class="badge bg-secondary px-3 py-2">OFF</span>
        @endif
    </div>


    {{-- TABLE CARD --}}
    <div class="card rounded-lg shadow-sm">
        <div class="card-body p-0">

            {{-- HEADER --}}
            <div class="row g-0 border-bottom small text-uppercase text-muted align-items-center torrent-header rounded">

                <div class="col-3 px-3 py-3"></div>
                <div class="col-1 text-center py-3"><i class="bi bi-cloud-plus fs-3 text-success" data-bs-toggle="tooltip" title="Upload Multiplier"></i></div>
                <div class="col-1 text-center py-3"><i class="bi bi-cloud-download fs-3 text-info" data-bs-toggle="tooltip" title="Free Download"></i></div>
                <div class="col-2 text-center py-3"><i class="bi bi-play-btn-fill fs-3 text-success" data-bs-toggle="tooltip" title="Started"></i></div>
                <div class="col-2 text-center py-3"><i class="bi bi-stop-circle-fill fs-3 text-danger" data-bs-toggle="tooltip" title="End"></i></div>
                <div class="col-1 text-center py-3"><i class="bi bi-activity fs-3 text-warning" data-bs-toggle="tooltip" title="Status"></i></div>
                <div class="d-none d-md-block col-1 text-center py-3"><i class="bi bi-person-check fs-3 text-info" data-bs-toggle="tooltip" title="Started By"></i></div>
                <div class="col-1 text-center py-3"><i class="bi bi-gear fs-3 text-info" data-bs-toggle="tooltip" title="Actions"></i></div>

            </div>

            {{-- ROWS --}}
            @forelse($happyHours as $hh)

            <div class="row g-0 align-items-center border-bottom py-3 {{ $hh->automatic ? 'happyhour-auto' : '' }}">

                {{-- THEME --}}
                <div class="col-3 px-3 fw-bold">
                    {{ $hh->theme }}
                    @if($hh->automatic)
                        <span class="badge bg-info ms-2">AUTO</span>
                    @endif
                </div>

                {{-- UPLOAD --}}
                <div class="col-1 text-center fw-bold text-info">
                    {{ $hh->upload_multiplier }}x
                </div>

                {{-- FREE --}}
                <div class="col-1 text-center">
                    <span class="badge {{ $hh->free_download ? 'bg-success' : 'bg-secondary' }}">
                        {{ $hh->free_download ? 'Yes' : 'No' }}
                    </span>
                </div>

                {{-- START --}}
                <div class="col-2 text-center small">
                    {{ $hh->start_at?->format('M d, Y H:i') ?? '—' }}
                </div>

                {{-- END --}}
                <div class="col-2 text-center small">
                    {{ $hh->end_at?->format('M d, Y H:i') ?? '—' }}
                </div>

                {{-- STATUS --}}
                <div class="col-1 text-center">
                    <span class="badge {{ $hh->active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $hh->active ? 'Active' : 'Stopped' }}
                    </span>
                </div>

                {{-- STARTED BY --}}
                <div class="d-none d-md-block col-1 text-center small">
                    {{ $hh->user?->name ?? 'System' }}
                </div>

                {{-- ACTIONS --}}
                <div class="col-1 text-center">

                    @if($hh->active)
                    <form action="{{ route('happyhour.stop', $hh) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Stop">
                            <i class="bi bi-stop-circle"></i>
                        </button>
                    </form>
                    @endif

                    {{-- DELETE --}}
@if($automatic)
    <span data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="Cannot delete Happy Hours while Automatic mode is enabled">
        <button class="btn btn-sm btn-danger disabled-btn">
            <i class="bi bi-trash"></i>
        </button>
    </span>
@else
    <form action="{{ route('happyhour.destroy', $hh) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Delete this Happy Hour?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-danger"
                data-bs-toggle="tooltip"
                title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endif

                </div>

            </div>

            @empty
            <div class="text-center py-5 text-muted">
                No Happy Hours found.
            </div>
            @endforelse

        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $happyHours->links('pagination::bootstrap-5') }}
    </div>

</div>


<style>

/* Sticky header */
.torrent-header {
    position: sticky;
    top: 56px;
    z-index: 20;
    background: #1d1c1c;
}

/* Automatic Highlight */
.happyhour-auto {
    position: relative;
    background: rgba(53, 47, 99, 0.25);
}

.happyhour-auto::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg,#9aa4ffbe,#5065c477);
    border-radius: 0 4px 4px 0;
}

/* Row hover */
.row.border-bottom:hover {
    background: rgba(255,255,255,0.05);
    transition: 0.2s ease;
}

/* =====================================
   Unified Modern Button Style
   ===================================== */

.btn-modern {
    height: 42px;
    padding: 0 22px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}

/* Enable (green) */
.btn-success-modern {
    background: linear-gradient(135deg, #00c853, #009624);
    color: #fff;
}

/* Disable (red) */
.btn-danger-modern {
    background: linear-gradient(135deg, #ff4d4d, #b30000);
    color: #fff;
}

/* Create (blue) */
.btn-primary-modern {
    background: linear-gradient(135deg, #00c6ff, #0072ff);
    color: #fff;
}

/* Hover effect */
.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.35);
    color: #fff;
}

/* Status card */
.status-card {
    padding: 20px;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-card.active {
    background: rgba(0,200,83,0.12);
    border: 1px solid rgba(0,200,83,0.3);
}

.status-card.inactive {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
}

/* Disabled state */
.disabled-btn {
    opacity: 0.5;
    pointer-events: none;
    cursor: not-allowed;
}

</style>

@endsection
