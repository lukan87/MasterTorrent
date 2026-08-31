@extends('layouts.app')

@section('content')

<div class="container py-4">

{{-- BREADCRUMB --}}
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
        <i class="bi bi-heart"></i>
        Thanks
    </span>

</div>


{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">

    <div class="d-flex align-items-center gap-2">

        <h4 class="fw-semibold mb-0">
            <i class="bi bi-heart-fill text-danger me-2"></i>
            {{ $user->name }} — Thanks
        </h4>

        <span class="badge bg-danger">
            {{ $thanks->total() }}
        </span>

    </div>

    <a href="{{ route('profile.show', ['id'=>$user->id,'name'=>$user->name]) }}"
       class="btn btn-outline-light btn-sm">

        <i class="bi bi-arrow-left me-1"></i>
        Back to Profile

    </a>

</div>


@if($thanks->isEmpty())

<div class="elite-card text-center p-5">

    <i class="bi bi-heart display-5 text-muted mb-3"></i>

    <h5 class="text-muted">No thanks yet</h5>

</div>

@else


<div class="row g-4">

@foreach($thanks as $thank)

<div class="col-12">

<div class="thanks-card p-4 h-100">

{{-- Torrent --}}
@if($thank->torrent)

<div class="thanks-torrent mb-3">

<i class="bi bi-film text-warning me-2"></i>

<a href="{{ route('torrents.show',$thank->torrent->id) }}"
   class="fancy-link fw-semibold">

{{ $thank->torrent->name }}

 @if($thank->torrent->trashed())
        <i class="bi bi-trash text-danger ms-1"
           data-bs-toggle="tooltip"
           title="Torrent deleted"></i>
    @endif

</a>

</div>

@endif


{{-- Header --}}
<div class="d-flex align-items-start gap-3 flex-wrap">

<div class="thanks-avatar">

@if($user->profile_image)
<img src="{{ $user->profile_image }}">
@else
<div class="avatar-placeholder">
<i class="bi bi-person-fill"></i>
</div>
@endif

</div>

<div class="flex-grow-1">

<div class="d-flex align-items-center gap-2 flex-wrap">

<strong class="text-danger">

<i class="bi bi-heart-fill me-1"></i>
Thanked

</strong>

<span class="text-muted small">
• {{ $thank->created_at->diffForHumans() }}
</span>

</div>

</div>

</div>

</div>

</div>

@endforeach

</div>


{{-- Pagination --}}
<div class="mt-4 d-flex justify-content-center">
{{ $thanks->links('pagination::bootstrap-5') }}
</div>

@endif

</div>


<style>

/* THANKS CARD */

.thanks-card{

background:#161b22;

border:1px solid rgba(255,255,255,0.06);

border-radius:14px;

box-shadow:0 10px 25px rgba(0,0,0,0.55);

transition:0.25s ease;

}

.thanks-card:hover{

transform:translateY(-4px);

border-color:#30363d;

box-shadow:0 18px 45px rgba(0,0,0,0.65);

}


/* TORRENT */

.thanks-torrent{

background:rgba(255,255,255,0.03);

border:1px solid rgba(255,255,255,0.05);

padding:8px 12px;

border-radius:8px;

font-size:0.9rem;

display:flex;

align-items:center;

gap:6px;

}


/* AVATAR */

.thanks-avatar{

width:48px;

height:48px;

flex-shrink:0;

}

.thanks-avatar img{

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


/* MOBILE */

@media (max-width:768px){

.thanks-card{
padding:18px;
}

.thanks-avatar{
width:40px;
height:40px;
}

.thanks-torrent{
font-size:0.85rem;
}

}

</style>

@endsection