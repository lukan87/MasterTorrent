@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">

    {{-- Main topic card --}}
    <div class="card bg-dark bg-opacity-75 text-white shadow-lg border-0 p-4">

        {{-- Topic header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">

            <!-- Title & forum -->
            <div>
                <h1 class="h3 fw-bold mb-1">{{ $topic->title }}</h1>
                <small class="text-light">
                    Forum:
                    <a href="{{ route('forums.show', $topic->forum->id) }}" class="text-info">
                        {{ $topic->forum->name }}
                    </a>
                </small>
                @auth
    @if($topic->isSubscribedBy(auth()->user()))
        <form method="POST" action="{{ route('topics.unsubscribe', $topic) }}">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-warning">
                <i class="bi bi-bell-slash"></i> Unsubscribe
            </button>
        </form>
    @else
        <form method="POST" action="{{ route('topics.subscribe', $topic) }}">
            @csrf
            <button class="btn btn-sm btn-outline-success">
                <i class="bi bi-bell"></i> Subscribe
            </button>
        </form>
    @endif
@endauth

            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <a href="{{ route('forums.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Forums
                </a>

                @php $userClass = Auth::user()?->user_class; @endphp

                @if(Auth::user() && \App\Models\UserClass::userHasPermission($userClass, 'reply'))
                    <a href="#replyForm" class="btn btn-primary">
                        <i class="bi bi-reply-fill me-1"></i> Reply
                    </a>
                @endif
            </div>
        </div>

        {{-- Original Post (OP) --}}
        @if($firstPost)
            @php
                $author = $firstPost->author;
                $avatar = $author->profile_image ?? 'images/default-avatar.gif';
                $authorClass = $author->user_class ?? 1;
                $classColor = \App\Models\UserClass::getClassColor($authorClass);
                $className = \App\Models\UserClass::getClassName($authorClass);
            @endphp
          <div id="post-{{ $firstPost->id }}" class="card mb-4 shadow-sm bg-danger bg-opacity-10 hover-shadow border-0">

                <div class="card-body">

                    {{-- Post header --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">

                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle" width="50" height="50">
                            <div>
                                <span class="fw-semibold" style="color: {{ $classColor }}">
                                    {{ $author->name ?? 'Unknown' }}
                                </span>
                                <small class="text-light fst-italic">({{ $className }})</small>
                            </div>
                        </div>

                      <div class="d-flex align-items-center gap-2">

    {{-- Post ID / permalink --}}
    <a href="#post-{{ $firstPost->id }}"
       class="badge bg-secondary bg-opacity-25 text-decoration-none"
       title="Permalink to post #{{ $firstPost->id }}">
        #{{ $firstPost->id }}
    </a>

    {{-- Time --}}
    <span class="badge bg-info bg-opacity-25">
        <i class="bi bi-clock me-1"></i>
        {{ $firstPost->created_at->diffForHumans() }}
    </span>

</div>

                    </div>

                    {{-- Post content --}}
                    <p class="mb-1">{!! convertCustomTagsToHtml($firstPost->content) !!}</p>
                    

                    {{-- Last edited --}}
                    @if($firstPost->updated_at->gt($firstPost->created_at))
                        <div class="text-light small fst-italic">
                            <i class="bi bi-pencil me-1"></i>
                            Last edited by 
                            @php $editor = $firstPost->editor ?? $firstPost->author; @endphp
                            {{ $editor->name ?? 'Staff' }} 
                            {{ $firstPost->updated_at->diffForHumans() }}
                        </div>
                    @endif

                    {{-- Edit/Delete buttons --}}
                    <div class="mt-3 d-flex gap-2">
                        @if(Auth::id() === $firstPost->user_id || \App\Models\UserClass::userHasPermission($userClass, 'edit_posts'))
                            <a href="{{ route('posts.edit', $firstPost->id) }}" data-bs-toggle="tooltip" title="Edit Post" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </a>
                        @endif

                        @if(\App\Models\UserClass::userHasPermission($userClass, 'delete_posts'))
                            <form action="{{ route('posts.destroy', $firstPost->id) }}" method="POST" data-bs-toggle="tooltip" title="Delete Post" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Replies --}}
        @if($posts->count() > 0)
            <div class="row g-3">
                @foreach($posts as $post)
                    @php
                        $author = $post->author;
                        $avatar = $author->profile_image ?? 'images/default-avatar.gif';
                        $authorClass = $author->user_class ?? 1;
                        $classColor = \App\Models\UserClass::getClassColor($authorClass);
                        $className = \App\Models\UserClass::getClassName($authorClass);
                    @endphp
                    <div class="col-12">
                       <div id="post-{{ $post->id }}" class="card bg-dark bg-opacity-75 shadow-sm hover-shadow border-0">

                            <div class="card-body">

                                {{-- Post header --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle" width="50" height="50">
                                        <div>
                                            <a href="{{ route('profile.show', $author->id) }}">
                                                <span class="fw-semibold" style="color: {{ $classColor }}">
                                                    {{ $author->name ?? 'Unknown' }}
                                                </span>
                                            </a>
                                            <small class="text-light fst-italic">({{ $className }})</small>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">

    {{-- Post ID / permalink --}}
    <a href="#post-{{ $post->id }}"
       class="badge bg-secondary bg-opacity-25 text-decoration-none"
       title="Permalink to post #{{ $post->id }}">
        #{{ $post->id }}
    </a>

    {{-- Time --}}
    <span class="badge bg-danger bg-opacity-25">
        <i class="bi bi-clock me-1"></i>
        {{ $post->created_at->diffForHumans() }}
    </span>

</div>

                                </div>

                                {{-- Post content --}}
                                <p class="mb-1">{!! convertCustomTagsToHtml($post->content) !!}</p>

                                {{-- Last edited --}}
                                @if($post->updated_at->gt($post->created_at))
                                    <div class="text-light small fst-italic">
                                        <i class="bi bi-pencil me-1"></i>
                                        Last edited by 
                                        @php $editor = $post->editor ?? $post->author; @endphp
                                        {{ $editor->name ?? 'Staff' }} 
                                        {{ $post->updated_at->diffForHumans() }}
                                    </div>
                                @endif

                                {{-- Edit/Delete buttons --}}
                                <div class="mt-3 d-flex gap-2">
                                    @if(Auth::id() === $post->user_id || \App\Models\UserClass::userHasPermission($userClass, 'edit_posts'))
                                        <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </a>
                                    @endif

                                    @if(\App\Models\UserClass::userHasPermission($userClass, 'delete_posts'))
                                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $posts->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="alert alert-secondary mt-3">
                <i class="bi bi-exclamation-circle me-1"></i> No posts yet. Be the first to reply!
            </div>
        @endif

{{-- Reply form --}}
@auth

@if(Auth::user() && Auth::user()->forumblock)

<div class="alert alert-danger mt-4 d-flex align-items-center">
    <i class="bi bi-slash-circle-fill me-2 fs-5"></i>
    <div>
        <strong>Forum Posting Disabled</strong><br>
        Your account is currently restricted from posting replies in the forum.
        Please contact staff if you believe this is a mistake.
    </div>
</div>

@elseif(\App\Models\UserClass::userHasPermission($userClass, 'reply'))

<div class="card shadow-sm mt-4 hover-shadow" id="replyForm">
    <div class="card-body">

        <h6 class="mb-3">
            <i class="bi bi-reply-fill me-1"></i> Post a Reply
        </h6>

        {{-- BBCode Toolbar --}}
        <div class="mb-2 d-flex flex-wrap gap-1">

            <!-- Text Formatting -->
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[b]', '[/b]')"><strong>B</strong></button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[i]', '[/i]')"><em>I</em></button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[u]', '[/u]')"><u>U</u></button>

            <!-- Links / Media -->
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[url]', '[/url]')">URL</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[img]', '[/img]')">IMG</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[youtube]', '[/youtube]')">YouTube</button>

            <!-- Quote / Spoiler / Center -->
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[quote]', '[/quote]')">Quote</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[spoiler]', '[/spoiler]')">Spoiler</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="insertTag('[center]', '[/center]')">Center</button>

            <!-- Colors Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle"
                        type="button"
                        id="colorDropdown"
                        data-bs-toggle="dropdown">
                    Color
                </button>

                <ul class="dropdown-menu">

                    @php
                        $colors = ['red','green','blue','orange','purple','cyan','gold','pink','yellow','black','white','gray','darkred','darkblue','darkgreen'];
                    @endphp

                    @foreach($colors as $color)
                        <li>
                            <button type="button"
                                class="dropdown-item"
                                style="color: {{ $color }}"
                                onclick="insertTag('[color={{ $color }}]', '[/color]')">

                                {{ ucfirst($color) }}
                            </button>
                        </li>
                    @endforeach

                </ul>
            </div>

        </div>

        <form method="POST" action="{{ route('posts.store', $topic->id) }}">
            @csrf

            <div class="mb-3">
                <textarea
                    id="bbcodeTextarea"
                    name="content"
                    rows="6"
                    class="form-control"
                    placeholder="Write your reply here..."
                    required></textarea>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="bi bi-send-fill me-1"></i> Reply
            </button>

        </form>

    </div>
</div>

@endif

@endauth

    </div> {{-- End main topic card --}}

</div>

{{-- Optional hover effect --}}
<style>
/* =========================================
   PAGE
========================================= */

.container.py-5{
    max-width: 1350px;
}

/* =========================================
   MAIN TOPIC CARD
========================================= */

.card.bg-dark.bg-opacity-75{

    background:
        linear-gradient(
            180deg,
            rgba(18,18,20,.96),
            rgba(12,12,14,.98)
        ) !important;

    border:
        1px solid rgba(255,255,255,.05) !important;

    border-radius:
        26px !important;

    box-shadow:
        0 16px 60px rgba(0,0,0,.45);

    backdrop-filter:
        blur(14px);
}

/* =========================================
   HEADER
========================================= */

h1.h3{
    font-size: 1.7rem;
    font-weight: 750;
    letter-spacing: -.4px;
}

.text-info{
    color: #5db8ff !important;
}

/* =========================================
   POSTS
========================================= */

.hover-shadow{

    transition:
        transform .16s ease,
        box-shadow .16s ease,
        background .16s ease;

    border-radius:
        22px !important;

    overflow:
        hidden;
}

.hover-shadow:hover{

    transform:
        translateY(-2px);

    box-shadow:
        0 12px 40px rgba(0,0,0,.35);

    background:
        rgba(255,255,255,.02);
}

/* =========================================
   ORIGINAL POST
========================================= */

.bg-danger.bg-opacity-10{

    background:
        linear-gradient(
            135deg,
            rgba(220,53,69,.12),
            rgba(255,255,255,.015)
        ) !important;

    border-left:
        4px solid rgba(220,53,69,.7) !important;
}

/* =========================================
   REPLY POSTS
========================================= */

.card.bg-dark.bg-opacity-75.shadow-sm{

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,.025),
            rgba(255,255,255,.015)
        ) !important;

    border:
        1px solid rgba(255,255,255,.04);
}

/* =========================================
   POST BODY
========================================= */

.card-body{
    padding: 1.35rem 1.5rem;
}

.card-body p{

    font-size: .96rem;

    line-height: 1.7;

    color:
        rgba(255,255,255,.92);
}

/* =========================================
   USER AREA
========================================= */

.card-body img.rounded-circle{

    width: 52px;
    height: 52px;

    object-fit: cover;

    border:
        2px solid rgba(255,255,255,.12);

    box-shadow:
        0 4px 14px rgba(0,0,0,.35);
}

.fw-semibold{
    font-weight: 700 !important;
}

/* =========================================
   CLASS TEXT
========================================= */

.fst-italic{

    opacity: .7;

    font-size: .78rem;
}

/* =========================================
   BADGES
========================================= */

.badge{

    font-size: .72rem;

    font-weight: 600;

    padding:
        .42rem .7rem;

    border-radius:
        999px;

    letter-spacing:
        .2px;
}

.bg-secondary.bg-opacity-25{

    background:
        rgba(255,255,255,.08) !important;
}

.bg-info.bg-opacity-25{

    background:
        rgba(13,202,240,.15) !important;

    color:
        #7ddfff;
}

.bg-danger.bg-opacity-25{

    background:
        rgba(220,53,69,.15) !important;

    color:
        #ff9aa4;
}

/* =========================================
   LINKS
========================================= */

.card-body a{

    text-decoration:
        none;

    transition:
        opacity .15s ease,
        color .15s ease;
}

.card-body a:hover{

    opacity:
        .88;

    text-decoration:
        none;
}

/* =========================================
   BUTTONS
========================================= */

.btn{

    border-radius:
        12px;

    font-weight:
        600;

    transition:
        all .15s ease;
}

.btn:hover{

    transform:
        translateY(-1px);

    box-shadow:
        0 8px 22px rgba(0,0,0,.28);
}

/* =========================================
   EDITED TEXT
========================================= */

.text-light.small.fst-italic{

    opacity:
        .58;

    font-size:
        .76rem;

    margin-top:
        10px;
}

/* =========================================
   REPLY FORM
========================================= */

#replyForm{

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.03),
            rgba(255,255,255,.015)
        );

    border:
        1px solid rgba(255,255,255,.05);

    border-radius:
        22px !important;
}

/* =========================================
   TOOLBAR
========================================= */

#replyForm .btn-secondary{

    background:
        rgba(255,255,255,.08);

    border:
        1px solid rgba(255,255,255,.06);

    color:
        #ddd;
}

#replyForm .btn-secondary:hover{

    background:
        rgba(255,255,255,.14);
}

/* =========================================
   TEXTAREA
========================================= */

#bbcodeTextarea{

    background:
        rgba(10,10,12,.9);

    border:
        1px solid rgba(255,255,255,.08);

    color:
        #fff;

    border-radius:
        18px;

    padding:
        1rem 1.1rem;

    font-size:
        .95rem;

    line-height:
        1.7;

    min-height:
        180px;

    resize:
        vertical;
}

#bbcodeTextarea:focus{

    outline:
        none;

    border-color:
        rgba(13,110,253,.5);

    box-shadow:
        0 0 0 3px rgba(13,110,253,.15);
}

/* =========================================
   PAGINATION
========================================= */

.pagination .page-link{

    background:
        rgba(255,255,255,.04);

    border:
        1px solid rgba(255,255,255,.05);

    color:
        #ddd;

    margin:
        0 3px;

    border-radius:
        10px;
}

.pagination .page-item.active .page-link{

    background:
        #0d6efd;

    border-color:
        #0d6efd;
}

/* =========================================
   ALERTS
========================================= */

.alert{

    border:
        none;

    border-radius:
        18px;
}

/* =========================================
   MOBILE
========================================= */

@media(max-width:768px){

    .card-body{
        padding: 1rem;
    }

    h1.h3{
        font-size: 1.3rem;
    }

    .card-body p{
        font-size: .9rem;
        line-height: 1.6;
    }

    .card-body img.rounded-circle{
        width: 42px;
        height: 42px;
    }

    .btn{
        font-size: .82rem;
    }

    #bbcodeTextarea{
        min-height: 140px;
    }

}

</style>

<script>
function insertTag(openTag, closeTag) {
    const textarea = document.getElementById('bbcodeTextarea');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);

    if (selectedText.length > 0) {
        // Wrap selected text
        const replacement = openTag + selectedText + closeTag;
        textarea.setRangeText(replacement, start, end, 'end');
        textarea.focus();
        textarea.selectionStart = start + openTag.length;
        textarea.selectionEnd = start + openTag.length + selectedText.length;
    } else {
        // Insert tags with cursor in the middle
        const replacement = openTag + closeTag;
        textarea.setRangeText(replacement, start, end, 'end');
        textarea.focus();
        textarea.selectionStart = start + openTag.length;
        textarea.selectionEnd = start + openTag.length;
    }
}
</script>


@endsection
