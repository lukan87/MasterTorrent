@extends('layouts.app')

@section('content')
@php
    use App\Models\UserClass;

    $user = auth()->user();
    $userClass = $user?->user_class ?? 0;
@endphp

<div class="container-fluid py-5">

    {{-- PAGE TITLE TILE --}}
    <div class="mb-4">
        <div class="card bg-dark bg-opacity-75 border-0 shadow-lg rounded-4 text-center py-4">
            <h1 class="fw-bold mb-1">
                {{ config('app.name') }}
                <span class="text-muted fw-normal">| Topics</span>
            </h1>
            <div class="text-muted small">
                Browse discussions inside
                <span class="fw-semibold text-light">{{ $forum->name }}</span>
            </div>
        </div>
    </div>

    {{-- MAIN FORUM CARD --}}
    <div class="card bg-dark bg-opacity-50 text-white shadow-lg border-0 p-4 rounded-4">

        {{-- Forum header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">{{ $forum->name }}</h2>
                <small class="text-light">{{ $forum->description }}</small>
            </div>

            <div class="d-flex gap-2 flex-wrap">

    <a href="{{ route('forums.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Forums
    </a>

    @if($user && UserClass::userHasPermission($userClass, 'create_topics') && !$forum->is_locked)

        @if($user->forumblock)

            <button class="btn btn-danger btn-sm"
                    data-bs-toggle="tooltip"
                    title="You are currently blocked from creating forum topics">
                <i class="bi bi-slash-circle me-1"></i> Create Topic
            </button>

        @else

            <a href="{{ route('topics.create', $forum) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Create Topic
            </a>

        @endif

    @endif

</div>
        </div>

        {{-- TOPICS LIST --}}
        @if($topics->count())
            <div class="row g-3">

                @foreach($topics as $topic)
                @php
                    $isOwner  = $user && $topic->user_id === $user->id;
                    $canEdit = $isOwner || $userClass >= UserClass::MODERATOR;
                    $canDelete = $userClass >= UserClass::ADMIN;
                @endphp

                <div class="col-12">
                    <div class="card bg-dark bg-opacity-40 border-0 shadow-sm p-3 rounded-4 forum-topic-card">

                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-4">

                            {{-- LEFT: Topic info --}}
                            <div class="flex-grow-1">

                                {{-- Title --}}
                                <a href="{{ route('topics.show', $topic) }}"
                                   class="fw-semibold fs-6 text-white text-decoration-none d-flex align-items-center gap-2">
                                    <i class="bi bi-chat-left-text text-info"></i>
                                    {{ $topic->title }}

                                    @if($topic->is_locked)
                                        <span class="badge bg-danger ms-2">Locked</span>
                                    @endif
                                </a>

                                {{-- Stats & last post --}}
                                <div class="small text-light mt-1 d-flex flex-wrap gap-3 align-items-center">

                                    <span>
                                        <i class="bi bi-file-text me-1"></i>
                                        {{ $topic->posts_count ?? 0 }}
                                        {{ Str::plural('post', $topic->posts_count) }}
                                    </span>

                                    @if($topic->lastPost)
                                        <span class="d-flex align-items-center gap-1">
                                            <i class="bi bi-person-circle"></i>

                                            <span style="color: {{ UserClass::getClassColor($topic->lastPost->author->user_class ?? 1) }}">
                                                {{ $topic->lastPost->author->name ?? 'Unknown' }}
                                            </span>

                                            <span class="badge bg-secondary">
                                                {{ UserClass::getClassName($topic->lastPost->author->user_class ?? 1) }}
                                            </span>

                                            <i class="bi bi-clock ms-2 me-1"></i>
                                            {{ $topic->lastPost->created_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- MIDDLE: Metadata --}}
                            <div class="small text-light text-md-end">
                                <div>
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $topic->created_at->diffForHumans() }}
                                </div>
                                <div>
                                    by
                                    <span style="color: {{ UserClass::getClassColor($topic->author->user_class ?? 1) }}">
                                        {{ $topic->author->name ?? 'Unknown' }}
                                    </span>
                                    <span class="badge bg-secondary ms-1">
                                        {{ UserClass::getClassName($topic->author->user_class ?? 1) }}
                                    </span>
                                </div>
                            </div>

                            {{-- RIGHT: Actions --}}
                            @if($canEdit || $canDelete)
                                <div class="d-flex gap-2 align-items-start ms-md-3">

                                    @if($canEdit)
                                        <a href="{{ route('topics.edit', $topic) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Edit topic">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif

                                    @if($canDelete)
                                        <form method="POST"
                                              action="{{ route('topics.destroy', $topic) }}"
                                              onsubmit="return confirm('Delete this topic?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete topic">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            @endif

                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            {{-- Pagination --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $topics->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="alert alert-secondary mt-3">
                <i class="bi bi-exclamation-circle me-1"></i>
                No topics yet in this forum.
            </div>
        @endif

    </div>
</div>

{{-- PREMIUM STYLES --}}
<style>
.forum-topic-card {
    transition: all 0.25s ease;
    backdrop-filter: blur(4px);
}

.forum-topic-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 35px rgba(0,0,0,0.35);
    background-color: rgba(50,50,50,0.55);
}

.forum-topic-card a:hover {
    text-decoration: underline;
}

.badge {
    font-size: 0.65rem;
    font-weight: 500;
}

.btn-outline-primary,
.btn-outline-danger {
    border-radius: 8px;
}

/* =========================================
   FORUM PAGE
========================================= */

.container-fluid.glass{
    background: transparent !important;
    padding-top: 1.5rem !important;
}

/* =========================================
   PAGE HEADER
========================================= */

.card.bg-dark.bg-opacity-50{

    background: rgba(18,18,20,.72) !important;

    border: 1px solid rgba(255,255,255,.06);

    backdrop-filter: blur(10px);

    border-radius: 22px !important;

    box-shadow:
        0 10px 40px rgba(0,0,0,.35);
}

.card.bg-dark.bg-opacity-50 .card-body{
    padding: 1.2rem 1.5rem;
}

.card.bg-dark.bg-opacity-50 h1{
    font-size: 1.45rem;
    letter-spacing: -.3px;
}

/* =========================================
   CATEGORY CARD
========================================= */

.category-card{

    background:
        linear-gradient(
            180deg,
            rgba(26,26,30,.92),
            rgba(16,16,18,.96)
        );

    border: 1px solid rgba(255,255,255,.06);

    backdrop-filter: blur(14px);

    overflow: hidden;

    box-shadow:
        0 12px 45px rgba(0,0,0,.35);

    transition:
        transform .18s ease,
        box-shadow .18s ease;
}

.category-card:hover{
    box-shadow:
        0 16px 50px rgba(0,0,0,.45);
}

/* =========================================
   CATEGORY HEADER
========================================= */

.category-card .card-header{

    background:
        linear-gradient(
            90deg,
            rgba(255,255,255,.03),
            rgba(255,255,255,.015)
        );

    border-bottom:
        1px solid rgba(255,255,255,.06) !important;

    padding:
        1rem 1.3rem !important;
}

.category-card .card-header a{
    transition: opacity .15s ease;
}

.category-card .card-header a:hover{
    opacity: .85;
}

/* =========================================
   CATEGORY DESCRIPTION
========================================= */

.category-card .text-muted.small{

    color: rgba(255,255,255,.52) !important;

    font-size: .83rem;

    line-height: 1.5;
}

/* =========================================
   FORUM TABLE HEADER
========================================= */

.category-card .bg-secondary{

    background:
        rgba(255,255,255,.035) !important;

    border-top:
        1px solid rgba(255,255,255,.04);

    border-bottom:
        1px solid rgba(255,255,255,.05);
}

.category-card .bg-secondary i{
    opacity: .8;
}

/* =========================================
   FORUM ROWS
========================================= */

.forum-row{

    transition:
        background .15s ease,
        transform .15s ease;

    border-bottom:
        1px solid rgba(255,255,255,.04);

    min-height: 88px;
}

.forum-row:last-child{
    border-bottom: none;
}

.forum-row:hover{

    background:
        rgba(255,255,255,.03);

    transform:
        translateX(2px);
}

/* =========================================
   FORUM TITLES
========================================= */

.forum-row .fw-semibold{

    font-size: .98rem;

    font-weight: 650 !important;

    letter-spacing: -.1px;
}

.forum-row .small.text-light{

    color:
        rgba(255,255,255,.52) !important;

    font-size: .8rem;

    margin-top: 3px;

    line-height: 1.45;
}

/* =========================================
   LAST ACTIVITY
========================================= */

.forum-row .text-muted{
    color: rgba(255,255,255,.42) !important;
}

.forum-row .text-center.small{
    font-size: .8rem;
    line-height: 1.45;
}

/* =========================================
   BADGES
========================================= */

.badge{

    font-weight: 600;

    padding:
        .42rem .62rem;

    border-radius:
        999px;
}

.badge.bg-info{
    background:
        linear-gradient(
            135deg,
            #0dcaf0,
            #0aa2c0
        ) !important;
}

.badge.bg-danger{
    background:
        linear-gradient(
            135deg,
            #dc3545,
            #9f1d2a
        ) !important;
}

.badge.bg-primary{
    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        ) !important;
}

/* =========================================
   ACTION BUTTONS
========================================= */

.btn-outline-primary,
.btn-outline-danger,
.btn-outline-success{

    border-width: 1px;

    backdrop-filter: blur(6px);

    transition:
        all .15s ease;
}

.btn-outline-primary:hover,
.btn-outline-danger:hover,
.btn-outline-success:hover{

    transform:
        translateY(-1px);

    box-shadow:
        0 6px 18px rgba(0,0,0,.28);
}

/* =========================================
   LINKS
========================================= */

a{
    transition:
        color .15s ease,
        opacity .15s ease;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .forum-row{

        padding-top:
            .9rem !important;

        padding-bottom:
            .9rem !important;
    }

    .forum-row .fw-semibold{
        font-size: .9rem;
    }

    .forum-row .small{
        font-size: .74rem;
    }

    .category-card .card-header{
        padding:
            .85rem 1rem !important;
    }

    .card.bg-dark.bg-opacity-50 .card-body{
        padding:
            1rem;
    }

}
</style>
@endsection
