@extends('layouts.app')

@section('content')

<div class="container py-4">

    {{-- HEADER --}}
    <div class="requests-hero mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>

                <h1 class="requests-title mb-2">
                    <i class="bi bi-megaphone-fill me-2"></i>
                    Torrent Requests
                </h1>

                <p class="requests-subtitle mb-0">
                    Browse community requests or create your own request.
                </p>

            </div>

            <div>

                @if(Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ELITE_USER)

                    <a href="{{ route('requests.create') }}"
                       class="btn modern-create-btn">

                        <i class="bi bi-plus-circle-fill me-2"></i>
                        Create Request

                    </a>

                @else

                    <span data-bs-toggle="tooltip"
                          title="You need to be an Elite user to create requests">

                        <button class="btn modern-disabled-btn" disabled>

                            <i class="bi bi-lock-fill me-2"></i>
                            Create Request

                        </button>

                    </span>

                @endif

            </div>

        </div>

    </div>

    {{-- ALERT --}}
    <div class="modern-alert mb-4">

        <i class="bi bi-exclamation-triangle-fill"></i>

        <span>
            Cererile completate incorect vor fi șterse. Completați toate câmpurile corect, cu informațiile cerute!
        </span>

    </div>

    {{-- REQUESTS --}}
    @if ($requests->count())

        <div class="request-list">

            @foreach ($requests as $request)

                <div class="modern-request-card">

                    <div class="row align-items-center g-3">

                        {{-- CATEGORY --}}
                        <div class="col-xl-2 col-lg-2 col-md-3">

                            <span class="modern-category-badge">

                                <i class="bi bi-tags-fill me-1"></i>

                                {{ $request->category->name ?? 'No Category' }}

                            </span>

                        </div>

                        {{-- NAME --}}
                        <div class="col-xl-4 col-lg-4 col-md-9">

                            <a href="{{ route('requests.show', $request->id) }}"
                               class="request-link">

                                {{ $request->name }}

                            </a>

                        </div>

                        {{-- USER --}}
                        <div class="col-xl-2 col-lg-2 col-md-4">

                            <div class="request-meta">

                                <div class="meta-user">

                                    <i class="bi bi-person-circle me-1"></i>

                                    {{ $request->requester->name ?? 'Unknown' }}

                                </div>

                                <div class="meta-date">

                                    {{ $request->created_at
                                        ? $request->created_at->format('d M Y')
                                        : 'Unknown' }}

                                </div>

                            </div>

                        </div>

                        {{-- IMDB --}}
                        <div class="col-xl-1 col-lg-1 col-md-2 text-md-center">

                            @if ($request->imdb_url)

                                <a href="{{ $request->imdb_url }}"
                                   target="_blank"
                                   class="imdb-btn">

                                    IMDb

                                </a>

                            @else

                                <span class="text-muted small">
                                    N/A
                                </span>

                            @endif

                        </div>

                        {{-- STATUS --}}
                        <div class="col-xl-2 col-lg-2 col-md-3">

                            @if ($request->filled === 'yes')

                                <div class="status-filled">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Filled

                                </div>

                                <div class="filled-user">

                                    {{ $request->filledBy->name ?? 'Unknown' }}

                                </div>

                            @else

                                <div class="status-open">

                                    <i class="bi bi-clock-history"></i>

                                    Open

                                </div>

                            @endif

                        </div>

                        {{-- ACTIONS --}}
                        @if (Auth::check() &&
                            (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR ||
                             Auth::user()->name === $request->requester->name))

                            <div class="col-xl-1 col-lg-1 col-md-12">

                                <div class="request-actions">

                                    <a href="{{ route('requests.edit', $request->id) }}"
                                       class="action-btn edit-btn"
                                       data-bs-toggle="tooltip"
                                       title="Edit Request">

                                        <i class="bi bi-pencil-fill"></i>

                                    </a>

                                    @if (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

                                        <form action="{{ route('requests.destroy', $request->id) }}"
                                              method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="action-btn delete-btn"
                                                    onclick="return confirm('Are you sure you want to delete this request?')"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete Request">

                                                <i class="bi bi-trash-fill"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>

    @else

        <div class="empty-state">

            <i class="bi bi-inbox-fill"></i>

            <h4>No torrent requests found</h4>

            <p class="mb-0">
                There are currently no active requests.
            </p>

        </div>

    @endif

</div>

<style>
/* =========================================
   FILEIPLAY TORRENT REQUESTS
   DARK GLASS / TEAL FORUM STYLE
========================================= */

.requests-hero,
.modern-request-card,
.empty-state {
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

.requests-hero {
    padding: 1.1rem 1.2rem;
    border-left: 3px solid var(--ui-accent, #22d3ee);
    border-radius: .85rem;
}

.requests-title {
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.25;
}

.requests-subtitle {
    color: rgba(226,232,240,.62);
    font-size: 13px;
}

.modern-alert {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .75rem .9rem;
    border: 1px solid rgba(250,204,21,.20);
    border-left: 3px solid rgba(250,204,21,.55);
    border-radius: .7rem;
    background: rgba(250,204,21,.07);
    color: #fde68a;
    font-size: 13px;
    line-height: 1.45;
}

.modern-alert i {
    flex: 0 0 auto;
}

.modern-create-btn,
.modern-disabled-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .5rem .8rem;
    border-radius: .55rem;
    font-size: 13px;
    font-weight: 700;
    transition: all .2s ease;
}

.modern-create-btn {
    border: 1px solid rgba(34,211,238,.28);
    background: rgba(34,211,238,.10);
    color: var(--ui-accent, #22d3ee);
}

.modern-create-btn:hover {
    color: #f8fafc;
    border-color: var(--ui-accent, #22d3ee);
    background: rgba(34,211,238,.16);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0,0,0,.18);
}

.modern-disabled-btn {
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    background: rgba(255,255,255,.04);
    color: rgba(226,232,240,.45);
}

/* Request list */

.request-list {
    display: flex;
    flex-direction: column;
    gap: .65rem;
}

.modern-request-card {
    position: relative;
    overflow: hidden;
    padding: .85rem 1rem;
    border-radius: .8rem;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.modern-request-card::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 2px;
    background: var(--ui-accent, #22d3ee);
    opacity: .45;
}

.modern-request-card:hover {
    transform: translateY(-1px);
    border-color: rgba(34,211,238,.18);
    box-shadow: 0 14px 30px rgba(0,0,0,.22);
}

.modern-category-badge {
    display: inline-flex;
    align-items: center;
    padding: .35rem .6rem;
    border: 1px solid rgba(34,211,238,.20);
    border-radius: 999px;
    background: rgba(34,211,238,.07);
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 600;
}

.request-link {
    color: #f8fafc;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.35;
    text-decoration: none;
    transition: color .2s ease;
}

.request-link:hover {
    color: var(--ui-accent, #22d3ee);
}

.request-meta {
    color: rgba(226,232,240,.62);
}

.meta-user {
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
}

.meta-date {
    margin-top: 2px;
    color: rgba(226,232,240,.48);
    font-size: 12px;
}

.imdb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .3rem .55rem;
    border: 1px solid rgba(245,197,24,.28);
    border-radius: .45rem;
    background: rgba(245,197,24,.10);
    color: #f5c518;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s ease;
}

.imdb-btn:hover {
    color: #fef3c7;
    border-color: rgba(245,197,24,.5);
    background: rgba(245,197,24,.16);
    transform: translateY(-1px);
}

.status-filled,
.status-open {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .3rem .55rem;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.status-filled {
    border: 1px solid rgba(34,197,94,.18);
    background: rgba(34,197,94,.09);
    color: #4ade80;
}

.status-open {
    border: 1px solid rgba(239,68,68,.18);
    background: rgba(239,68,68,.09);
    color: #f87171;
}

.filled-user {
    margin-top: 3px;
    color: rgba(226,232,240,.48);
    font-size: 12px;
}

.request-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .4rem;
}

.action-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ui-border, rgba(255,255,255,.08));
    border-radius: .5rem;
    text-decoration: none;
    transition: all .2s ease;
}

.edit-btn {
    background: rgba(250,204,21,.08);
    color: #fde047;
}

.delete-btn {
    background: rgba(239,68,68,.08);
    color: #f87171;
}

.action-btn:hover {
    transform: translateY(-1px);
    border-color: currentColor;
}

/* Empty state */

.empty-state {
    padding: 3rem 1.25rem;
    border-radius: .85rem;
    text-align: center;
}

.empty-state i {
    display: block;
    margin-bottom: .8rem;
    color: rgba(148,163,184,.55);
    font-size: 2.25rem;
}

.empty-state h4 {
    margin-bottom: .35rem;
    color: #f8fafc;
    font-size: 15px;
    font-weight: 700;
}

.empty-state p {
    color: rgba(226,232,240,.55);
    font-size: 13px;
}

/* Pagination */

.pagination {
    --bs-pagination-bg: rgba(255,255,255,.04);
    --bs-pagination-border-color: var(--ui-border, rgba(255,255,255,.08));
    --bs-pagination-color: #cbd5e1;
    --bs-pagination-hover-bg: rgba(34,211,238,.08);
    --bs-pagination-hover-color: #f8fafc;
    --bs-pagination-focus-color: #f8fafc;
    --bs-pagination-active-bg: rgba(34,211,238,.14);
    --bs-pagination-active-border-color: var(--ui-accent, #22d3ee);
    --bs-pagination-active-color: #f8fafc;
}

.pagination .page-link {
    font-size: 12px;
    border-radius: .45rem !important;
}

/* Mobile */

@media (max-width: 768px) {
    .requests-hero {
        padding: .9rem;
        border-radius: .75rem;
    }

    .requests-title {
        font-size: 18px;
    }

    .requests-subtitle {
        font-size: 12px;
    }

    .modern-request-card {
        padding: .8rem;
        border-radius: .7rem;
    }

    .request-link {
        font-size: 14px;
    }

    .request-actions {
        justify-content: flex-start;
        margin-top: .15rem;
    }

    .modern-create-btn,
    .modern-disabled-btn {
        font-size: 12px;
    }
}
</style>

@endsection