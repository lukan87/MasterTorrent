
@extends('layouts.app')

@section('content')

<div class="container py-5">


    {{-- =========================================================
         BREADCRUMB
         ========================================================= --}}

    <div class="mb-4 forum-breadcrumb">

        <a href="{{ route('forum.index') }}"
           class="text-muted text-decoration-none">

            <i class="bi bi-house-door me-1"></i>

            Forum

        </a>

        <span class="text-muted mx-2">/</span>

        <a href="{{ route('forum.category', $category->slug) }}"
           class="text-muted text-decoration-none">

            {{ $category->name }}

        </a>

    </div>


    {{-- =========================================================
         TOPIC HEADER
         ========================================================= --}}

    <div class="forum-topic-header mb-4">

        <div class="d-flex align-items-start justify-content-between gap-3">

            <div>

                <div class="forum-topic-category mb-2">

                    <i class="bi bi-chat-square-text-fill me-1"></i>

                    {{ $category->name }}

                </div>


                <h1 class="forum-topic-title">

                    {{ $topic->title }}

                </h1>


                <div class="forum-topic-meta">

                    <span>

                        <i class="bi bi-person-circle me-1"></i>

                        Started by

                        <strong>
                            {{ $topic->user->name ?? 'Unknown' }}
                        </strong>

                    </span>


                    <span>

                        <i class="bi bi-clock me-1"></i>

                        {{ $topic->created_at->diffForHumans() }}

                    </span>


                    <span>

                        <i class="bi bi-eye me-1"></i>

                        {{ $topic->views }} views

                    </span>


                    @if($topic->is_locked)

                        <span class="forum-locked-badge">

                            <i class="bi bi-lock-fill me-1"></i>

                            Locked

                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ORIGINAL POST
         ========================================================= --}}

    @if($firstPost)

        <article id="post-{{ $firstPost->id }}"
                 class="forum-post-card forum-main-post mb-4">


            {{-- ORIGINAL POST HEADER --}}

            <div class="original-post-label">

                <span>

                    <i class="bi bi-pin-angle-fill me-1"></i>

                    MAIN POST

                </span>


                <div class="forum-post-actions">

                    @auth

                        {{-- EDIT --}}

                        @if(
                            $firstPost->user_id === auth()->id()
                            || auth()->user()->user_class > \App\Models\UserClass::MODERATOR
                        )

                            <a href="{{ route('forum.post.edit', [
                                'category' => $category->slug,
                                'topic' => $topic->slug,
                                'post' => $firstPost->id,
                            ]) }}"
                               class="forum-post-action edit-action">

                                <i class="bi bi-pencil-square me-1"></i>

                                Edit

                            </a>

                        @endif


                        

                    @endauth


                    {{-- POST ANCHOR --}}

                    <a href="#post-{{ $firstPost->id }}"
                       class="post-anchor">

                        #{{ $firstPost->id }}

                    </a>

                </div>

            </div>


            <div class="row g-0">


                {{-- =================================================
                     USER PANEL
                     ================================================= --}}

                <div class="col-md-3 col-lg-2">

                    <div class="forum-user-panel">


                        {{-- AVATAR --}}

                        <div class="forum-avatar">

                            @if(
                                $firstPost->user &&
                                $firstPost->user->profile_image
                            )

                                <img
                                    src="{{ $firstPost->user->profile_image }}"
                                    alt="{{ $firstPost->user->name }}"
                                >

                            @else

                                <div class="forum-avatar-placeholder">

                                    <i class="bi bi-person-fill"></i>

                                </div>

                            @endif

                        </div>


                        {{-- USERNAME --}}

                        @if($firstPost->user)

                            <a href="{{ route('profile.show', [
                                'id' => $firstPost->user->id,
                                'username' => $firstPost->user->name
                            ]) }}"
                               class="forum-username">

                                {{ $firstPost->user->name }}

                            </a>

                        @else

                            <span class="forum-username">

                                Unknown

                            </span>

                        @endif


                        {{-- TITLE --}}

                        @if($firstPost->user?->title)

                            <div class="forum-user-title">

                                {{ $firstPost->user->title }}

                            </div>

                        @endif


                        {{-- USER CLASS --}}

                        <div class="forum-user-rank">

                            @switch($firstPost->user?->user_class)

                                @case(1)

                                    User

                                    @break

                                @case(2)

                                    Power User

                                    @break

                                @case(3)

                                    VIP

                                    @break

                                @case(4)

                                    Elite User

                                    @break

                                @case(5)

                                    Moderator

                                    @break

                                @case(6)

                                    Administrator

                                    @break

                                @default

                                    Member

                            @endswitch

                        </div>


                        {{-- JOIN DATE / POSTS --}}

                        @if($firstPost->user)

                            <div class="forum-user-info">

                                <i class="bi bi-calendar3 me-1"></i>

                                Joined

                                {{ $firstPost->user->created_at?->format('M Y') }}

                            </div>


                            <div class="forum-user-info">

                                <i class="bi bi-chat-left-text me-1"></i>

                                {{ $firstPost->user->forumPosts()->count() }}

                                posts

                            </div>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     POST CONTENT
                     ================================================= --}}

                <div class="col-md-9 col-lg-10">

                    <div class="forum-post-content">


                        {{-- POST HEADER --}}

                        <div class="forum-post-header">

                            <span>

                                <i class="bi bi-clock me-1"></i>

                                Posted

                                {{ $firstPost->created_at->format('d M Y H:i') }}

                            </span>


                            @if($firstPost->edited_at)

                                <span class="post-edited">

                                    <i class="bi bi-pencil me-1"></i>

                                    Edited

                                    {{ $firstPost->edited_at->diffForHumans() }}

                                </span>

                            @endif

                        </div>


                        {{-- BODY --}}

                        <div class="forum-post-body">

                            {!! nl2br(e($firstPost->body)) !!}

                        </div>

                    </div>

                </div>

            </div>

        </article>

    @endif



    {{-- =========================================================
         REPLIES
         ========================================================= --}}

    @foreach($replies as $post)

        <article id="post-{{ $post->id }}"
                 class="forum-reply-box forum-reply-post mb-4">


            <div class="row g-0">


                {{-- =================================================
                     USER PANEL
                     ================================================= --}}

                <div class="col-md-3 col-lg-2">

                    <div class="forum-user-panel">


                        {{-- AVATAR --}}

                        <div class="forum-avatar">

                            @if(
                                $post->user &&
                                $post->user->profile_image
                            )

                                <img
                                    src="{{ $post->user->profile_image }}"
                                    alt="{{ $post->user->name }}"
                                >

                            @else

                                <div class="forum-avatar-placeholder">

                                    <i class="bi bi-person-fill"></i>

                                </div>

                            @endif

                        </div>


                        {{-- USERNAME --}}

                        @if($post->user)

                            <a href="{{ route('profile.show', [
                                'id' => $post->user->id,
                                'username' => $post->user->name
                            ]) }}"
                               class="forum-username">

                                {{ $post->user->name }}

                            </a>

                        @else

                            <span class="forum-username">

                                Unknown

                            </span>

                        @endif


                        {{-- TITLE --}}

                        @if($post->user?->title)

                            <div class="forum-user-title">

                                {{ $post->user->title }}

                            </div>

                        @endif


                        {{-- USER CLASS --}}

                        <div class="forum-user-rank">

                            @switch($post->user?->user_class)

                                @case(1)

                                    User

                                    @break

                                @case(2)

                                    Power User

                                    @break

                                @case(3)

                                    VIP

                                    @break

                                @case(4)

                                    Elite User

                                    @break

                                @case(5)

                                    Moderator

                                    @break

                                @case(6)

                                    Administrator

                                    @break

                                @default

                                    Member

                            @endswitch

                        </div>


                        {{-- JOIN DATE / POSTS --}}

                        @if($post->user)

                            <div class="forum-user-info">

                                <i class="bi bi-calendar3 me-1"></i>

                                Joined

                                {{ $post->user->created_at?->format('M Y') }}

                            </div>


                            <div class="forum-user-info">

                                <i class="bi bi-chat-left-text me-1"></i>

                                {{ $post->user->forumPosts()->count() }}

                                posts

                            </div>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     POST CONTENT
                     ================================================= --}}

                <div class="col-md-9 col-lg-10">

                    <div class="forum-post-content">


                        {{-- POST HEADER --}}

                        <div class="forum-post-header">

                            <div class="forum-post-meta-left">

                                <span>

                                    <i class="bi bi-reply-fill me-1"></i>

                                    Reply

                                </span>


                                <span>

                                    <i class="bi bi-clock me-1"></i>

                                    {{ $post->created_at->format('d M Y H:i') }}

                                </span>


                                @if($post->edited_at)

                                    <span class="post-edited">

                                        <i class="bi bi-pencil me-1"></i>

                                        Edited

                                        {{ $post->edited_at->diffForHumans() }}

                                    </span>

                                @endif

                            </div>


                            {{-- ACTIONS --}}

                            <div class="forum-post-actions">

                                @auth

                                    {{-- EDIT --}}

                                    @if(
                                        $post->user_id === auth()->id()
                                        || auth()->user()->user_class > \App\Models\UserClass::MODERATOR
                                    )

                                        <a href="{{ route('forum.post.edit', [
                                            'category' => $category->slug,
                                            'topic' => $topic->slug,
                                            'post' => $post->id,
                                        ]) }}"
                                           class="forum-post-action edit-action">

                                            <i class="bi bi-pencil-square me-1"></i>

                                            Edit

                                        </a>

                                    @endif


                                    {{-- DELETE - STAFF ONLY --}}

                                   @if(auth()->user()->user_class > \App\Models\UserClass::MODERATOR)

    <form method="POST"
          action="{{ route('forum.post.delete', [
              'category' => $category->slug,
              'topic' => $topic->slug,
              'post' => $post->id,
          ]) }}"
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this post?');">

        @csrf

        @method('DELETE')

        <button type="submit"
                class="forum-post-action delete-action">

            <i class="bi bi-trash3 me-1"></i>

            Delete

        </button>

    </form>

@endif

                                @endauth


                                {{-- POST ANCHOR --}}

                                <a href="#post-{{ $post->id }}"
                                   class="post-anchor">

                                    #{{ $post->id }}

                                </a>

                            </div>

                        </div>


                        {{-- BODY --}}

                        <div class="forum-post-body">

                            {!! nl2br(e($post->body)) !!}

                        </div>

                    </div>

                </div>

            </div>

        </article>

    @endforeach


    {{-- =========================================================
         PAGINATION
         ========================================================= --}}

    @if($replies->hasPages())

        <div class="forum-pagination mt-4">

            {{ $replies->links('pagination::bootstrap-5') }}

        </div>

    @endif



    {{-- =========================================================
         REPLY FORM
         ========================================================= --}}

    @auth

        @if(!$topic->is_locked && !auth()->user()->forumblock)

            <div class="forum-reply-box mt-5">

                <div class="forum-reply-box-header">

                    <div>

                        <h5 class="mb-1">

                            <i class="bi bi-reply-fill me-1"></i>

                            Reply to this topic

                        </h5>

                        <small>

                            Share your thoughts with the community.

                        </small>

                    </div>

                </div>


                <div class="card-body">

                    <form method="POST"
                          action="{{ route('forum.topic.reply', [
                              'category' => $category->slug,
                              'topic' => $topic->slug,
                          ]) }}">

                        @csrf


                        <div class="mb-3">

                            <textarea
                                name="body"
                                rows="6"
                                class="form-control forum-textarea @error('body') is-invalid @enderror"
                                placeholder="Write your reply..."
                                required
                            >{{ old('body') }}</textarea>


                            @error('body')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>


                        <button type="submit"
                                class="btn forum-submit-btn">

                            <i class="bi bi-send-fill me-1"></i>

                            Post Reply

                        </button>

                    </form>

                </div>

            </div>

        @elseif($topic->is_locked)

            <div class="forum-locked-box mt-5">

                <i class="bi bi-lock-fill"></i>

                <div>

                    <strong>
                        This topic is locked
                    </strong>

                    <div>
                        No new replies can be posted.
                    </div>

                </div>

            </div>

        @endif

    @endauth


</div>

<style>
    ```css
/* =========================================================
   FORUM POST CARD
   ========================================================= */

.forum-post-card {

    background: rgba(15, 20, 35, .96);

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.25);

}


/* =========================================================
   ORIGINAL / MAIN POST
   ========================================================= */

.forum-main-post {

    border:
        1px solid rgba(59,130,246,.40);

    box-shadow:
        0 15px 40px rgba(37,99,235,.12),
        0 5px 20px rgba(0,0,0,.25);

}


.forum-main-post .forum-user-panel {

    background:
        rgba(59,130,246,.06);

}


.original-post-label {

    min-height: 42px;

    padding: 9px 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    background:
        rgba(59,130,246,.12);

    border-bottom:
        1px solid rgba(59,130,246,.20);

    color:
        #93c5fd;

    font-size:
        .72rem;

    font-weight:
        800;

    letter-spacing:
        .08em;

}


/* =========================================================
   POST ACTIONS
   ========================================================= */

.forum-post-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 12px;

    margin-left: auto;

    flex-shrink: 0;

}


.forum-post-action {

    display: inline-flex;

    align-items: center;

    white-space: nowrap;

    border: 0;

    background: transparent;

    padding: 2px 0;

    font-size: .78rem;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    transition:
        color .2s ease,
        transform .2s ease;

}


/* EDIT */

.edit-action {

    color:
        #93c5fd;

}


.edit-action:hover {

    color:
        #ffffff;

    transform:
        translateY(-1px);

}


/* DELETE */

.delete-action {

    color:
        #f87171;

}


.delete-action:hover {

    color:
        #fca5a5;

}


.delete-action:disabled {

    opacity:
        .65;

    cursor:
        not-allowed;

    transform:
        none;

}


/* =========================================================
   POST ANCHOR
   ========================================================= */

.post-anchor {

    display: inline-flex;

    align-items: center;

    color:
        rgba(255,255,255,.40);

    text-decoration:
        none;

    font-size:
        .75rem;

    font-weight:
        700;

    white-space:
        nowrap;

}


.post-anchor:hover {

    color:
        #93c5fd;

}


/* =========================================================
   USER PANEL
   ========================================================= */

.forum-user-panel {

    height:
        100%;

    padding:
        24px 18px;

    text-align:
        center;

    background:
        rgba(255,255,255,.025);

    border-right:
        1px solid rgba(255,255,255,.06);

}


/* =========================================================
   AVATAR
   ========================================================= */

.forum-avatar {

    width:
        90px;

    height:
        90px;

    margin:
        0 auto 12px;

    border-radius:
        50%;

    overflow:
        hidden;

    border:
        3px solid rgba(255,255,255,.08);

}


.forum-avatar img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.forum-avatar-placeholder {

    width:
        100%;

    height:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:
        white;

    font-size:
        2rem;

}


/* =========================================================
   USERNAME
   ========================================================= */

.forum-username {

    display:
        block;

    color:
        #fff;

    font-size:
        1.05rem;

    font-weight:
        800;

    text-decoration:
        none;

}


.forum-username:hover {

    color:
        #93c5fd;

}


/* =========================================================
   USER TITLE
   ========================================================= */

.forum-user-title {

    margin-top:
        4px;

    color:
        #94a3b8;

    font-size:
        .85rem;

}


/* =========================================================
   USER RANK
   ========================================================= */

.forum-user-rank {

    display:
        inline-block;

    margin-top:
        10px;

    padding:
        5px 10px;

    border-radius:
        20px;

    background:
        rgba(59,130,246,.15);

    color:
        #93c5fd;

    font-size:
        .75rem;

    font-weight:
        700;

}


/* =========================================================
   USER INFO
   ========================================================= */

.forum-user-info {

    margin-top:
        12px;

    color:
        rgba(255,255,255,.5);

    font-size:
        .75rem;

}


/* =========================================================
   POST CONTENT
   ========================================================= */

.forum-post-content {

    min-height:
        220px;

    padding:
        20px 24px;

}


/* =========================================================
   POST HEADER
   ========================================================= */

.forum-post-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    padding-bottom:
        12px;

    margin-bottom:
        18px;

    border-bottom:
        1px solid rgba(255,255,255,.06);

    color:
        rgba(255,255,255,.45);

    font-size:
        .8rem;

}


.forum-post-meta-left {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        12px;

}


/* =========================================================
   EDITED LABEL
   ========================================================= */

.post-edited {

    color:
        rgba(147,197,253,.70);

}


/* =========================================================
   POST BODY
   ========================================================= */

.forum-post-body {

    color:
        rgba(255,255,255,.9);

    line-height:
        1.75;

    font-size:
        .98rem;

    overflow-wrap:
        anywhere;

    word-break:
        break-word;

}


/* =========================================================
   TOPIC HEADER
   ========================================================= */

.forum-topic-header {

    padding:
        10px 0;

}


.forum-topic-category {

    color:
        #93c5fd;

    font-size:
        .8rem;

    font-weight:
        800;

    text-transform:
        uppercase;

    letter-spacing:
        .08em;

}


.forum-topic-title {

    margin:
        0 0 12px;

    color:
        #fff;

    font-size:
        2rem;

    font-weight:
        800;

    line-height:
        1.25;

    overflow-wrap:
        anywhere;

}


.forum-topic-meta {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        15px;

    color:
        rgba(255,255,255,.50);

    font-size:
        .85rem;

}


.forum-locked-badge {

    display:
        inline-flex;

    align-items:
        center;

    padding:
        5px 10px;

    border-radius:
        20px;

    background:
        rgba(239,68,68,.15);

    color:
        #f87171;

    font-weight:
        700;

}


/* =========================================================
   REPLY BOX
   ========================================================= */

.forum-reply-box {

    background:
        rgba(15,20,35,.96);

    border:
        1px solid rgba(255,255,255,.07);

    border-radius:
        18px;

    overflow:
        hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.25);

}


.forum-reply-box-header {

    padding:
        18px 24px;

    background:
        rgba(255,255,255,.025);

    border-bottom:
        1px solid rgba(255,255,255,.06);

}


.forum-reply-box-header h5 {

    color:
        #fff;

    font-weight:
        800;

}


.forum-reply-box-header small {

    color:
        rgba(255,255,255,.45);

}


/* =========================================================
   TEXTAREA
   ========================================================= */

.forum-textarea {

    min-height:
        140px;

    background:
        rgba(0,0,0,.20);

    border:
        1px solid rgba(255,255,255,.10);

    color:
        #fff;

    resize:
        vertical;

}


.forum-textarea::placeholder {

    color:
        rgba(255,255,255,.35);

}


.forum-textarea:focus {

    background:
        rgba(0,0,0,.25);

    color:
        #fff;

    border-color:
        rgba(59,130,246,.60);

    box-shadow:
        0 0 0 .2rem rgba(59,130,246,.10);

}


/* =========================================================
   SUBMIT BUTTON
   ========================================================= */

.forum-submit-btn {

    border:
        0;

    border-radius:
        12px;

    padding:
        11px 18px;

    background:
        linear-gradient(135deg,#2563eb,#7c3aed);

    color:
        #fff;

    font-weight:
        700;

    transition:
        .2s ease;

}


.forum-submit-btn:hover {

    color:
        #fff;

    transform:
        translateY(-1px);

}


/* =========================================================
   LOCKED BOX
   ========================================================= */

.forum-locked-box {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;

    padding:
        20px;

    border:
        1px solid rgba(239,68,68,.20);

    border-radius:
        16px;

    background:
        rgba(239,68,68,.08);

    color:
        #fca5a5;

}


/* =========================================================
   BREADCRUMB
   ========================================================= */

.forum-breadcrumb a {

    transition:
        color .2s ease;

}


.forum-breadcrumb a:hover {

    color:
        #93c5fd !important;

}


/* =========================================================
   PAGINATION
   ========================================================= */

.forum-pagination {

    display:
        flex;

    justify-content:
        center;

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 767px) {

    .forum-topic-title {

        font-size:
            1.45rem;

    }


    .forum-topic-meta {

        gap:
            8px 12px;

        font-size:
            .78rem;

    }


    .original-post-label {

        min-height:
            44px;

        padding:
            9px 14px;

        gap:
            8px;

    }


    .forum-post-actions {

        gap:
            8px;

    }


    .forum-post-action {

        font-size:
            .72rem;

    }


    .forum-user-panel {

        border-right:
            0;

        border-bottom:
            1px solid rgba(255,255,255,.06);

        padding:
            18px;

    }


    .forum-avatar {

        width:
            70px;

        height:
            70px;

    }


    .forum-post-content {

        padding:
            18px;

        min-height:
            auto;

    }


    .forum-post-header {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

        gap:
            10px;

    }


    .forum-post-meta-left {

        gap:
            8px;

    }


    .forum-post-header .forum-post-actions {

        width:
            100%;

        justify-content:
            flex-start;

        margin-left:
            0;

        padding-top:
            4px;

    }

}
</style>

@endsection

