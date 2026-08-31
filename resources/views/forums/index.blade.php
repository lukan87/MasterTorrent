@extends('layouts.app')

@section('content')
@php
    use App\Models\UserClass;

    $userClass = Auth::user()?->user_class ?? 0;
    $isStaff = $userClass >= UserClass::MODERATOR;

    $canManageForums =
        UserClass::userHasPermission($userClass, 'edit_forums') ||
        UserClass::userHasPermission($userClass, 'delete_forums');

    $canEditCategory = UserClass::userHasPermission($userClass, 'edit_categories');
@endphp

<div class="container-fluid glass py-5 mt-3">

    {{-- Page header --}}
    <div class="card bg-dark bg-opacity-50 shadow-sm mb-4 rounded-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h1 class="h3 fw-bold mb-0">
                <i class="bi bi-chat-dots me-2"></i>{{ config('app.name') }} Forums
            </h1>

            @if(UserClass::userHasPermission($userClass, 'create_categories'))
                <a href="{{ route('categories.create') }}" class="btn btn-success btn-sm ms-md-auto">
                    <i class="bi bi-folder-plus me-1"></i> New Category
                </a>
            @endif
        </div>
    </div>

    {{-- Categories --}}
    @foreach($categories as $category)

        @php
            $visibleForums = $category->forums->filter(fn($forum) => $forum->isVisibleTo($userClass));

            $showCategory =
    (!$category->trashed() && ($visibleForums->count() > 0 || $canEditCategory)) ||
    ($category->trashed() && UserClass::userHasPermission($userClass, 'delete_categories'));
        @endphp

        @if($showCategory)
        <div class="card shadow-sm mb-5 category-card {{ $category->trashed() ? 'border border-danger' : '' }} rounded-4">

            {{-- Category header --}}
            <div class="card-header border-bottom d-flex align-items-center p-3">

                <div class="d-flex align-items-center gap-2">
                    @if(!$category->trashed())
                        @if($canEditCategory)
                            <a href="{{ route('forums.category', $category->id) }}"
                               class="fw-semibold text-decoration-none fs-5">
                                <i class="bi bi-folder-fill me-1"></i> {{ $category->name }}
                            </a>
                        @else
                            <span class="fw-semibold fs-5 text-muted">
                                <i class="bi bi-folder me-1"></i> {{ $category->name }}
                            </span>
                        @endif
                    @else
                         <a class="text-muted fw-semibold text-decoration-none fs-5" data-bs-toggle="tooltip" title="Archived Category" >
                                <i class="bi bi-trash-fill me-1"></i> {{ $category->name }}
                            </a>
                    @endif

                    <span class="badge bg-primary rounded-pill">
                        {{ $visibleForums->count() }} Forums
                    </span>
                </div>

                {{-- Category actions --}}
              <div class="ms-auto d-flex gap-2">

    {{-- Restore --}}
    @if($category->trashed() && UserClass::userHasPermission($userClass, 'delete_categories'))
        <form method="POST"
              action="{{ route('forumcategories.restore', $category->id) }}">
            @csrf
            <button class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Restore Category">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>

        {{-- Force delete --}}
        <form method="POST"
              action="{{ route('forumcategories.forceDelete', $category->id) }}"
              onsubmit="return confirm('Permanently delete this category?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete the category and topics forever">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    @endif

    {{-- Edit (only if not trashed) --}}
    @if(!$category->trashed() && $canEditCategory)
        <a href="{{ route('forumcategories.edit', $category->id) }}"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endif

</div>

            </div>

            {{-- Description --}}
            @if($category->description)
                <div class="px-4 pt-3 text-muted small">
                    {{ $category->description }}
                </div>
            @endif

            {{-- Forums --}}
            @if($visibleForums->count())
            <div class="card-body p-0 mt-3">

                {{-- Header --}}
                <div class="row g-0 px-4 py-3 small text-uppercase text-muted bg-secondary">
                    <div class="col-6"></div>
                    <div class="col-1 text-center"><i class="bi bi-chat-left-text fs-4 text-light" data-bs-toggle="tooltip" title="Topics"></i></div>
                    <div class="col-1 text-center"><i class="bi bi-wechat fs-4 text-light" data-bs-toggle="tooltip" title="Posts"></i></div>

                    @if($isStaff)
                        <div class="col-3 text-center"><i class="bi bi-calendar2-check fs-4 text-light" data-bs-toggle="tooltip" title="Last Activity"></i></div>
                        <div class="col-1 text-end"><i class="bi bi-exposure text-light fs-4" data-bs-toggle="tooltip" title="Actions"></i></div>
                    @else
                        <div class="col-4 text-center"><i class="bi bi-calendar2-check fs-4 text-light" data-bs-toggle="tooltip" title="Last Activity"></i></div>
                    @endif
                </div>

                {{-- Rows --}}
                @foreach($visibleForums as $forum)
                <div class="row g-0 px-4 py-3 align-items-center forum-row">

                    {{-- Forum --}}
                    <div class="col-6">
                        <a href="{{ route('forums.show', $forum->id) }}"
                           class="fw-semibold fs-6 text-decoration-none text-white">
                            <i class="bi {{ $forum->is_locked ? 'bi-lock-fill text-danger' : 'bi-chat-dots text-info' }}"></i>
                            {{ $forum->name }}
                        </a>
                        <div class="small text-light d-none d-md-block">
                            {{ $forum->description }}
                        </div>
                    </div>

                    {{-- Topics --}}
                    <div class="col-1 text-center">
                        <span class="badge bg-info rounded-pill">{{ $forum->topics_count ?? 0 }}</span>
                    </div>

                    {{-- Posts --}}
                    <div class="col-1 text-center">
                        <span class="badge bg-danger rounded-pill">{{ $forum->posts_count ?? 0 }}</span>
                    </div>

                    {{-- Last activity --}}
                    <div class="{{ $isStaff ? 'col-3 text-center' : 'col-4 text-center' }} small text-light">
                        @if($forum->lastPost)
                            <span class="fw-semibold"
                                  style="color: {{ UserClass::getClassColor($forum->lastPost->author->user_class) }}">
                                {{ $forum->lastPost->author->name }}
                                <small>({{ UserClass::getClassName($forum->lastPost->author->user_class) }})</small>
                            </span>
                            <a href="{{ route('topics.show', $forum->lastPost->topic->id) }}"
                               class="text-decoration-none text-light d-block">
                                {{ Str::limit($forum->lastPost->topic->title, 60) }}
                            </a>
                            <span class="text-muted">
                                {{ $forum->lastPost->created_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-muted">No posts yet</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    @if($isStaff)
                    <div class="col-1 text-end">
                        @if(UserClass::userHasPermission($userClass, 'edit_forums'))
                            <a href="{{ route('forums.edit', $forum->id) }}"
                               class="btn btn-sm btn-outline-primary mb-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        @endif

                        @if(UserClass::userHasPermission($userClass, 'delete_forums'))
                            <form method="POST" action="{{ route('forums.destroy', $forum->id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    @endif

                </div>
                @endforeach

            </div>
            @endif
        </div>
        @endif
    @endforeach
</div>

<style>

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
