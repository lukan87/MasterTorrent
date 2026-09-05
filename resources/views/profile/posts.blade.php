@extends('layouts.app')

@section('content')

<div class="container py-4">

    <div class="elite-breadcrumb mb-4">

    <a href="{{ url('/') }}" class="breadcrumb-item">
        <i class="bi bi-house-door"></i>
        Home
    </a>

    <span class="breadcrumb-separator">
        <i class="bi bi-chevron-right"></i>
    </span>

    <a href="{{ route('profile.show', ['id'=>$user->id,'name'=>$user->name]) }}" 
       class="breadcrumb-item">

        <i class="bi bi-person-circle"></i>
        {{ $user->name }}
    </a>

    <span class="breadcrumb-separator">
        <i class="bi bi-chevron-right"></i>
    </span>

    <span class="breadcrumb-current">

        <i class="bi bi-chat-square-text"></i>
        Forum Posts

    </span>

</div>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">

    <div class="d-flex align-items-center gap-2">

        <h4 class="fw-semibold mb-0">
            <i class="bi bi-chat-square-text me-2 text-primary"></i>
            {{ $user->name }} — Forum Posts
        </h4>

        <span class="badge bg-primary">
            {{ $posts->total() }}
        </span>

    </div>

    <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}"
       class="btn btn-outline-light btn-sm elite-outline-btn">

        <i class="bi bi-arrow-left me-1"></i>
        Back to Profile

    </a>

</div>

@if($posts->isEmpty())

<div class="elite-card text-center p-5">

    <i class="bi bi-chat-square-text display-5 text-muted mb-3"></i>

    <h5 class="text-muted">No forum posts yet</h5>

</div>

@else

<div class="row g-4">

@foreach($posts as $post)

<div class="col-12">

    <div class="elite-card post-card p-4 h-100">

        @if($post->topic)

<div class="post-topic mb-3">

    <i class="bi bi-chat-left-text text-info me-2"></i>

   <a href="{{ route('forum.topic', [
    'category' => $post->topic->category->slug,
    'topic' => $post->topic->slug,
]) }}"
   class="fancy-link fw-semibold">

        {{ $post->topic->title }}

    </a>

</div>

@endif

        {{-- Post Header --}}
        <div class="d-flex align-items-start gap-3 mb-3 flex-wrap">

            {{-- Avatar --}}
            <div class="post-avatar">

                @if($user->profile_image)
                    <img src="{{ $user->profile_image }}">
                @else
                    <div class="avatar-placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                @endif

            </div>

            {{-- User + Meta --}}
            <div class="flex-grow-1">

                <div class="d-flex align-items-center flex-wrap gap-2">

                    <strong class="text-primary">
                        {{ $user->name }}
                    </strong>

                    <span class="text-muted small">
                        • {{ $post->created_at->diffForHumans() }}
                    </span>

                </div>

            </div>

        </div>


        {{-- Post Content --}}
        <div class="post-content">

            {!! convertCustomTagsToHtml($post->body) !!}

        </div>

    </div>

</div>

@endforeach

</div>


{{-- Pagination --}}
<div class="mt-4 d-flex justify-content-center">
    {{ $posts->links() }}
</div>

@endif

</div>



<style>

/* ===============================
   POST CARD
================================*/

.post-card{

    background:#161b22;
    border:1px solid rgba(255,255,255,0.06);

    border-radius:14px;

    transition:0.25s ease;

    box-shadow:0 10px 25px rgba(0,0,0,0.55);
}

.post-card:hover{

    transform:translateY(-4px);

    border-color:#30363d;

    box-shadow:0 18px 45px rgba(0,0,0,0.65);
}


/* ===============================
   TOPIC HEADER
================================*/

.post-topic{

    background:rgba(255,255,255,0.03);

    border:1px solid rgba(255,255,255,0.05);

    padding:8px 12px;

    border-radius:8px;

    font-size:0.9rem;

    display:flex;
    align-items:center;

    gap:6px;
}


/* ===============================
   AVATAR
================================*/

.post-avatar{

    width:48px;
    height:48px;

    flex-shrink:0;
}

.post-avatar img{

    width:100%;
    height:100%;

    object-fit:cover;

    border-radius:12px;

    border:1px solid rgba(255,255,255,0.08);
}

.avatar-placeholder{

    width:100%;
    height:100%;

    display:flex;

    align-items:center;
    justify-content:center;

    border-radius:12px;

    background:#0d1117;

    font-size:1.4rem;
}


/* ===============================
   CONTENT
================================*/

.post-content{

    font-size:0.95rem;

    line-height:1.7;

    margin-top:6px;
}


/* ===============================
   HEADER AREA
================================*/

.post-card .d-flex strong{

    font-size:0.95rem;
}

.post-card .text-muted{

    font-size:0.8rem;
}


/* ===============================
   MOBILE
================================*/

@media (max-width:768px){

.post-card{
    padding:18px;
}

.post-avatar{
    width:40px;
    height:40px;
}

.post-topic{
    font-size:0.85rem;
}

}


</style>

@endsection