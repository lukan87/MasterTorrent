@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

<h1 class="fw-bold text-white mb-0">
<i class="bi bi-inbox-fill me-2"></i>Inbox
</h1>

<a href="{{ route('messages.outbox') }}" class="btn btn-outline-light btn-sm">
<i class="bi bi-send-fill me-1"></i>Outbox
</a>

</div>

@if($conversations->isEmpty())

<div class="alert alert-dark d-flex align-items-center justify-content-center">
<i class="bi bi-info-circle-fill me-2"></i>
No conversations yet.
</div>

@else

<div class="table-responsive" style="max-height:70vh">

<table class="table align-middle text-white table-gmail mb-0">

<thead class="table-head">

<tr>
<th style="width:5%"><i class="bi bi-hash"></i></th>
<th><i class="bi bi-person-circle me-1"></i>User</th>
<th><i class="bi bi-chat-text me-1"></i>Last Message</th>
<th class="text-center"><i class="bi bi-check-circle me-1"></i>Status</th>
<th><i class="bi bi-clock me-1"></i>Updated</th>
</tr>

</thead>

<tbody>

@foreach($conversations as $index => $conversation)

@php
$last = $conversation->lastMessage;

$other = Auth::id() == $conversation->user_one
? $conversation->userTwo
: $conversation->userOne;
@endphp

<tr class="{{ $last && !$last->is_read && $last->receiver_id == Auth::id() ? 'unread-message' : '' }}">

<td>
{{ ($conversations->currentPage() - 1) * $conversations->perPage() + $index + 1 }}
</td>

<td>

<div class="d-flex align-items-center gap-2">

<img src="{{ $other->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
width="32" height="32"
class="rounded-circle">

{{ $other->name }}

</div>

</td>

<td>

@if($last)

<a href="{{ route('messages.show',$last->id) }}"
class="text-decoration-none text-white fw-semibold">

{{ Str::limit(strip_tags($last->body),60) }}

</a>

@else

<span class="text-muted">No messages yet</span>

@endif

</td>

<td class="text-center">

@if($last && !$last->is_read && $last->receiver_id == Auth::id())

<span class="badge bg-success px-2 py-1">
<i class="bi bi-star-fill me-1"></i>New
</span>

@else

<span class="badge bg-secondary px-2 py-1">Read</span>

@endif

</td>

<td>

@if($last)
{{ $last->created_at->diffForHumans() }}
@else
-
@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@if($conversations->hasPages())

<div class="d-flex justify-content-center mt-3">
{{ $conversations->links('pagination::bootstrap-5') }}
</div>

@endif

@endif

</div>


<style>

body{
background:#121212;
color:#f8f9fa;
}

.table-gmail{
border-radius:12px;
background:#1c1c1c;
font-size:.95rem;
border-collapse:separate;
border-spacing:0;
overflow:hidden;
}

.table-head th{
position:sticky;
top:0;
background:linear-gradient(90deg,#2c2c2c,#3a3a3a);
color:#f8f9fa;
z-index:10;
box-shadow:0 2px 5px rgba(0,0,0,.4);
padding:.75rem;
white-space:nowrap;
}

.table-gmail tbody tr{
transition:background .25s ease;
}

.table-gmail tbody tr:hover{
background:#2a2a2a;
cursor:pointer;
}

.unread-message{
font-weight:600;
background:#212121 !important;
box-shadow:inset 4px 0 0 #28a745;
}

.table th,.table td{
vertical-align:middle;
white-space:nowrap;
}

.table-responsive{
overflow-y:auto;
border-radius:12px;
}

@media (max-width:768px){

h1{
font-size:1.4rem;
}

.table-gmail th,
.table-gmail td{
font-size:.85rem;
white-space:normal;
}

}

</style>

@endsection