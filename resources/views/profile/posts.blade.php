@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="elite-breadcrumb mb-4">
        <a href="{{ url('/') }}" class="breadcrumb-item">
            <i class="bi bi-house-door"></i> Home
        </a>
        <span class="breadcrumb-separator"><i class="bi bi-chevron-right"></i></span>
        <a href="{{ route('profile.show', ['id'=>$user->id,'name'=>$user->name]) }}" class="breadcrumb-item">
            <i class="bi bi-person-circle"></i> {{ $user->name }}
        </a>
        <span class="breadcrumb-separator"><i class="bi bi-chevron-right"></i></span>
        <span class="breadcrumb-current">
            <i class="bi bi-chat-square-text"></i> Forum Posts
        </span>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-semibold mb-0">
                <i class="bi bi-chat-square-text me-2"></i>
                {{ $user->name }} — Forum Posts
            </h4>
            <span class="badge bg-primary">{{ $posts->total() }}</span>
        </div>

        <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
           class="btn btn-outline-light btn-sm elite-outline-btn">
            <i class="bi bi-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    @if($posts->isEmpty())
        <div class="elite-card empty-posts text-center p-5">
            <i class="bi bi-chat-square-text display-6 mb-3"></i>
            <h5>No forum posts yet</h5>
        </div>
    @else
        <div class="row g-3">
            @foreach($posts as $post)
                <div class="col-12">
                    <div class="elite-card post-card p-4">

                        @if($post->topic)
                            <div class="post-topic mb-3">
                                <i class="bi bi-chat-left-text me-2"></i>
                                <a href="{{ route('forum.topic', [
                                    'category' => $post->topic->category->slug,
                                    'topic' => $post->topic->slug,
                                ]) }}" class="fancy-link fw-semibold">
                                    {{ $post->topic->title }}
                                </a>
                            </div>
                        @endif

                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="post-avatar">
                                @if($user->profile_image)
                                    <img src="{{ $user->profile_image }}" alt="{{ $user->name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <strong>{{ $user->name }}</strong>
                                    <span class="text-muted small">
                                        • {{ $post->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="post-content">
                            {!! convertCustomTagsToHtml($post->body) !!}
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    @endif
</div>

<style>
/* =========================================================
   FILEIPLAY — PROFILE FORUM POSTS
   Dark navy / teal forum theme
========================================================= */

.elite-breadcrumb {
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:.45rem;
    padding:.65rem .85rem;
    background:linear-gradient(135deg,rgba(22,32,51,.95),rgba(15,23,42,.88));
    border:1px solid var(--ui-border,rgba(255,255,255,.08));
    border-radius:.65rem;
    box-shadow:0 8px 24px rgba(0,0,0,.22);
    font-size:.82rem;
}

.elite-breadcrumb a {
    color:rgba(255,255,255,.72);
    text-decoration:none;
    transition:color .2s ease;
}

.elite-breadcrumb a:hover { color:var(--ui-accent,#2dd4bf); }

.breadcrumb-separator { color:rgba(255,255,255,.25); font-size:.7rem; }
.breadcrumb-current { color:rgba(255,255,255,.9); }

.elite-breadcrumb i { margin-right:.25rem; }

h4 {
    color:#f1f5f9;
    font-size:1.05rem;
}

h4 .bi { color:var(--ui-accent,#2dd4bf) !important; }

.elite-outline-btn {
    border-color:rgba(255,255,255,.14);
    color:rgba(255,255,255,.78);
    border-radius:.5rem;
    font-size:.8rem;
    padding:.38rem .7rem;
    transition:all .2s ease;
}

.elite-outline-btn:hover,
.elite-outline-btn:focus {
    color:#fff;
    border-color:rgba(45,212,191,.55);
    background:rgba(45,212,191,.08);
    box-shadow:0 0 0 .15rem rgba(45,212,191,.08);
}

.badge.bg-primary {
    background:rgba(45,212,191,.12) !important;
    border:1px solid rgba(45,212,191,.28);
    color:#8ff5e6;
    font-size:.72rem;
    font-weight:600;
    padding:.3rem .5rem;
    border-radius:.42rem;
}

.elite-card {
    background:linear-gradient(135deg,rgba(22,32,51,.96),rgba(15,23,42,.9));
    border:1px solid var(--ui-border,rgba(255,255,255,.08));
    border-radius:.7rem;
    box-shadow:0 10px 28px rgba(0,0,0,.24);
}

.post-card {
    overflow:hidden;
    transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease;
}

.post-card:hover {
    transform:translateY(-2px);
    border-color:rgba(45,212,191,.22);
    box-shadow:0 14px 34px rgba(0,0,0,.3);
}

.post-topic {
    display:flex;
    align-items:center;
    min-width:0;
    padding:.55rem .75rem;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.065);
    border-radius:.5rem;
    font-size:.82rem;
}

.post-topic > i { flex:0 0 auto; color:var(--ui-accent,#2dd4bf); }

.post-topic a {
    min-width:0;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    color:#dce7f3;
    text-decoration:none;
}

.post-topic a:hover { color:var(--ui-accent,#2dd4bf); }

.post-avatar {
    width:44px;
    height:44px;
    flex:0 0 44px;
}

.post-avatar img,
.avatar-placeholder {
    width:44px;
    height:44px;
    object-fit:cover;
    border-radius:.55rem;
    border:1px solid rgba(255,255,255,.09);
}

.avatar-placeholder {
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(7,14,27,.72);
    color:rgba(255,255,255,.4);
    font-size:1.2rem;
}

.post-card strong {
    color:#e8f0f7;
    font-size:.88rem;
    font-weight:600;
}

.post-card .text-muted {
    color:rgba(203,213,225,.55) !important;
    font-size:.75rem !important;
}

.post-content {
    color:rgba(226,232,240,.86);
    font-size:.88rem;
    line-height:1.65;
    overflow-wrap:anywhere;
}

.post-content p:last-child { margin-bottom:0; }

.post-content a { color:var(--ui-accent,#2dd4bf); }

.post-content img {
    max-width:100%;
    height:auto;
    border-radius:.5rem;
}

.post-content pre {
    max-width:100%;
    overflow-x:auto;
    padding:.75rem;
    background:rgba(0,0,0,.25);
    border:1px solid rgba(255,255,255,.06);
    border-radius:.5rem;
}

.post-content blockquote {
    margin:.75rem 0;
    padding:.55rem .8rem;
    border-left:3px solid rgba(45,212,191,.55);
    background:rgba(45,212,191,.035);
    color:rgba(226,232,240,.7);
    border-radius:0 .4rem .4rem 0;
}

.empty-posts {
    color:rgba(203,213,225,.55);
}

.empty-posts i {
    color:rgba(45,212,191,.55);
}

.empty-posts h5 {
    color:rgba(226,232,240,.62);
    font-size:.95rem;
}

.pagination {
    --bs-pagination-bg:rgba(22,32,51,.9);
    --bs-pagination-border-color:rgba(255,255,255,.08);
    --bs-pagination-color:rgba(255,255,255,.68);
    --bs-pagination-hover-bg:rgba(45,212,191,.08);
    --bs-pagination-hover-color:#8ff5e6;
    --bs-pagination-hover-border-color:rgba(45,212,191,.28);
    --bs-pagination-active-bg:rgba(45,212,191,.16);
    --bs-pagination-active-border-color:rgba(45,212,191,.4);
    --bs-pagination-active-color:#9ff8eb;
    font-size:.78rem;
}

.pagination .page-link {
    border-radius:.42rem;
    margin:0 .12rem;
}

@media (max-width:768px) {
    .container.py-4 { padding-top:1rem !important; }

    .elite-breadcrumb {
        margin-bottom:1rem !important;
        font-size:.76rem;
        padding:.6rem .7rem;
    }

    h4 { font-size:.95rem; }

    .post-card { padding:1rem !important; }

    .post-avatar,
    .post-avatar img,
    .avatar-placeholder {
        width:40px;
        height:40px;
    }

    .post-avatar { flex-basis:40px; }

    .post-content {
        font-size:.84rem;
        line-height:1.6;
    }

    .post-topic { font-size:.78rem; }

    .post-topic a { white-space:normal; }
}
</style>
@endsection
