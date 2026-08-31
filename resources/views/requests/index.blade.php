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
   HERO
========================================= */

.requests-hero{

    padding:28px;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(25,30,50,.92),
            rgba(10,14,24,.96)
        );

    border:1px solid rgba(255,255,255,.06);

    box-shadow:
        0 15px 40px rgba(0,0,0,.35);

    backdrop-filter:blur(16px);
}

.requests-title{

    font-size:2rem;

    font-weight:800;

    color:#fff;
}

.requests-subtitle{

    color:rgba(255,255,255,.65);

    font-size:.95rem;
}

/* =========================================
   ALERT
========================================= */

.modern-alert{

    display:flex;
    align-items:center;
    gap:12px;

    padding:16px 18px;

    border-radius:18px;

    background:
        rgba(250,204,21,.12);

    border:
        1px solid rgba(250,204,21,.22);

    color:#fde68a;

    font-weight:500;
}

/* =========================================
   BUTTONS
========================================= */

.modern-create-btn{

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    border:none;

    color:#fff;

    padding:12px 18px;

    border-radius:14px;

    font-weight:700;

    transition:.25s ease;
}

.modern-create-btn:hover{

    transform:translateY(-2px);

    color:#fff;

    box-shadow:
        0 10px 25px rgba(59,130,246,.35);
}

.modern-disabled-btn{

    border:none;

    background:
        rgba(255,255,255,.08);

    color:rgba(255,255,255,.55);

    padding:12px 18px;

    border-radius:14px;

    font-weight:700;
}

/* =========================================
   REQUEST CARD
========================================= */

.request-list{
    display:flex;
    flex-direction:column;
    gap:16px;
}

.modern-request-card{

    padding:22px;

    border-radius:22px;

    background:
        linear-gradient(
            145deg,
            rgba(20,25,40,.94),
            rgba(10,14,24,.96)
        );

    border:
        1px solid rgba(255,255,255,.05);

    backdrop-filter:blur(14px);

    transition:.25s ease;

    box-shadow:
        0 10px 25px rgba(0,0,0,.22);
}

.modern-request-card:hover{

    transform:translateY(-3px);

    box-shadow:
        0 18px 35px rgba(0,0,0,.35);
}

/* =========================================
   CATEGORY
========================================= */

.modern-category-badge{

    display:inline-flex;
    align-items:center;

    padding:8px 14px;

    border-radius:999px;

    background:
        rgba(148,163,184,.14);

    color:#cbd5e1;

    font-size:.8rem;

    font-weight:700;
}

/* =========================================
   REQUEST LINK
========================================= */

.request-link{

    color:#fff;

    font-size:1rem;

    font-weight:700;

    text-decoration:none;

    transition:.2s ease;
}

.request-link:hover{
    color:#93c5fd;
}

/* =========================================
   META
========================================= */

.request-meta{
    color:rgba(255,255,255,.65);
}

.meta-user{

    font-size:.92rem;

    font-weight:600;
}

.meta-date{

    font-size:.78rem;

    margin-top:3px;
}

/* =========================================
   IMDB
========================================= */

.imdb-btn{

    display:inline-block;

    padding:7px 12px;

    border-radius:10px;

    background:#f5c518;

    color:#111;

    text-decoration:none;

    font-size:.78rem;

    font-weight:800;

    transition:.2s ease;
}

.imdb-btn:hover{

    transform:translateY(-2px);

    color:#111;
}

/* =========================================
   STATUS
========================================= */

.status-filled,
.status-open{

    display:inline-flex;
    align-items:center;
    gap:7px;

    padding:8px 13px;

    border-radius:999px;

    font-size:.82rem;

    font-weight:700;
}

.status-filled{

    background:
        rgba(34,197,94,.14);

    color:#4ade80;
}

.status-open{

    background:
        rgba(239,68,68,.14);

    color:#f87171;
}

.filled-user{

    margin-top:6px;

    font-size:.78rem;

    color:rgba(255,255,255,.55);
}

/* =========================================
   ACTIONS
========================================= */

.request-actions{

    display:flex;
    justify-content:flex-end;
    gap:10px;
}

.action-btn{

    width:40px;
    height:40px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:12px;

    text-decoration:none;

    border:none;

    transition:.2s ease;
}

.edit-btn{

    background:
        rgba(250,204,21,.14);

    color:#fde047;
}

.delete-btn{

    background:
        rgba(239,68,68,.14);

    color:#f87171;
}

.action-btn:hover{

    transform:translateY(-2px);
}

/* =========================================
   EMPTY STATE
========================================= */

.empty-state{

    text-align:center;

    padding:60px 20px;

    border-radius:24px;

    background:
        linear-gradient(
            145deg,
            rgba(20,25,40,.94),
            rgba(10,14,24,.96)
        );

    border:
        1px solid rgba(255,255,255,.05);
}

.empty-state i{

    font-size:3rem;

    color:#64748b;

    margin-bottom:16px;
}

.empty-state h4{
    color:#fff;
}

.empty-state p{
    color:rgba(255,255,255,.6);
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .requests-title{
        font-size:1.6rem;
    }

    .modern-request-card{
        padding:18px;
    }

    .request-actions{
        justify-content:flex-start;
        margin-top:10px;
    }
}

</style>

@endsection