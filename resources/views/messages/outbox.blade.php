@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-3">

<h1 class="fw-bold text-white">
<i class="bi bi-send-fill me-2"></i>Outbox
</h1>

<a href="{{ route('messages.inbox') }}" class="btn btn-outline-light">
<i class="bi bi-inbox-fill me-1"></i>Inbox
</a>

</div>

@if($conversations->isEmpty())

<div class="alert alert-dark d-flex align-items-center">
<i class="bi bi-info-circle-fill me-2"></i>
No conversations yet.
</div>

@else

<div class="table-responsive" style="max-height:70vh">

<table class="table align-middle text-white table-gmail">

<thead class="table-head">

<tr>

<th style="width:5%">
<i class="bi bi-hash"></i>
</th>

<th>
<i class="bi bi-person-check-fill me-1"></i>User
</th>

<th>
<i class="bi bi-chat-text me-1"></i>Last Message
</th>

<th>
<i class="bi bi-clock me-1"></i>Updated
</th>

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

<tr>

<td>
{{ ($conversations->currentPage() - 1) * $conversations->perPage() + $index + 1 }}
</td>

<td>

<div class="d-flex align-items-center gap-2">

<img src="{{ $other->profile_image ?? asset('default-avatar.png') }}"
width="32"
height="32"
class="rounded-circle">

{{ $other->name }}

</div>

</td>

<td>

@if($last)

<a href="{{ route('messages.show',$last->id) }}"
class="text-decoration-none text-white fw-bold">

{{ Str::limit(strip_tags($last->body),60) }}

</a>

@else

<span class="text-muted">
No messages yet
</span>

@endif

</td>

<td>

{{ $last ? $last->created_at->diffForHumans() : '-' }}

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
}

.table-gmail{
border-radius:12px;
overflow:hidden;
background:#1c1c1c;
font-size:.95rem;
}

.table-head th{
position:sticky;
top:0;
background:linear-gradient(90deg,#2c2c2c,#3a3a3a);
color:#f8f9fa;
z-index:10;
box-shadow:0 2px 5px rgba(0,0,0,.5);
padding:.75rem;
}

.table-gmail tbody tr{
transition:all .2s ease-in-out;
}

.table-gmail tbody tr:hover{
background:#2a2a2a;
cursor:pointer;
}

.table th,
.table td{
vertical-align:middle;
}

.table-responsive{
overflow-y:auto;
}

</style>

@endsection
