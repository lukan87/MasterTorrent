@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

<div class="card glass shadow-lg border-0">

<div class="card-header d-flex justify-content-between align-items-center">
<h5 class="mb-0 fw-bold">
<i class="bi bi-cloud-arrow-up me-2"></i>
Torrents Uploaded by {{ $user->name }}
</h5>

<a href="{{ route('profile.show',['id'=>$user->id,'name'=>$user->name]) }}"
class="btn btn-outline-light btn-sm">
<i class="bi bi-arrow-left"></i> Back
</a>
</div>


<div class="card-body p-0">

@if($torrents->isEmpty())

<div class="alert alert-info text-center m-4">
No torrents uploaded by this user
</div>

@else


<!-- Header (Browse style sorting layout) -->

<div class="row browse-header text-uppercase small fw-bold g-0 px-3 py-2">

<div class="col-md-6">
Name
</div>

<div class="col-md-1 text-center">
Seed
</div>

<div class="col-md-1 text-center">
Leech
</div>

<div class="col-md-1 text-center">
Done
</div>

<div class="col-md-2 text-center">
Uploaded
</div>

@if(Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::OWNER)
<div class="col-md-1 text-center">
Action
</div>
@endif

</div>


<!-- Torrent rows -->

@foreach($torrents as $torrent)

<div class="row torrent-row g-0 px-3 py-3 align-items-center {{ $torrent->trashed() ? 'torrent-deleted' : '' }}">

<div class="col-md-6">

<a href="{{ route('torrents.show',['id'=>$torrent->id,'slug'=>$torrent->slug]) }}"
class="torrent-name">

{{ $torrent->name }}

@if($torrent->trashed())
<span class="badge bg-danger ms-2">Deleted</span>
@endif

</a>

</div>


<div class="col-md-1 text-center">
<span class="badge bg-success">
{{ $torrent->seeders }}
</span>
</div>


<div class="col-md-1 text-center">
<span class="badge bg-danger">
{{ $torrent->leechers }}
</span>
</div>


<div class="col-md-1 text-center">
<span class="badge bg-primary">
{{ $torrent->times_completed }}
</span>
</div>


<div class="col-md-2 text-center small text-muted">
{{ $torrent->created_at->diffForHumans() }}
</div>

@if(
    !$torrent->trashed() &&
    (Auth::id() === $user->id || Auth::user()->user_class >= \App\Models\UserClass::OWNER)
)

<div class="col-md-1 text-center">

<button
class="btn btn-sm btn-danger"
data-bs-toggle="modal"
data-bs-target="#deleteModal"
data-torrent-slug="{{ $torrent->slug }}"
data-torrent-name="{{ $torrent->name }}">

<i class="bi bi-trash"></i>

</button>

</div>

@endif


</div>

@endforeach


@endif

</div>

</div>


<div class="d-flex justify-content-center mt-4">
{{ $torrents->links('pagination::bootstrap-5') }}
</div>

</div>



<style>

.browse-header{
background: rgba(255,255,255,0.03);
border-bottom:1px solid rgba(255,255,255,0.06);
}


.torrent-row{
border-bottom:1px solid rgba(255,255,255,0.04);
transition: all .2s ease;
}

.torrent-row:hover{
background: rgba(255,255,255,0.03);
}


.torrent-name{
font-weight:600;
text-decoration:none;
color:#fff;
}

.torrent-name:hover{
color:#6ea8ff;
}


.torrent-deleted{
opacity:.55;
background: rgba(255,80,80,0.05);
}


</style>

@endsection