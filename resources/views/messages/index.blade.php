@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

<h1 class="fw-bold text-white mb-4">
<i class="bi bi-chat-left-dots-fill me-2"></i>
Messages
</h1>

<div class="card message-card shadow-lg">

<div class="card-header d-flex justify-content-between align-items-center">
<h2 class="h5 mb-0 text-white">
<i class="bi bi-inbox-fill me-2"></i>Conversations
</h2>
</div>

<div class="card-body p-0">

@if($conversations->isEmpty())

<div class="p-4 text-muted text-center">
<i class="bi bi-inbox fs-2 d-block mb-2"></i>
No conversations yet.
</div>

@else

@foreach($conversations as $conversation)

@php
    $last = $conversation->lastMessage;

    $other = Auth::id() == $conversation->user_one
        ? $conversation->userTwo
        : $conversation->userOne;
@endphp

<a href="{{ $last ? route('messages.show', $last->id) : '#' }}"
class="message-row d-flex align-items-center gap-3 text-decoration-none text-white
{{ $last && !$last->is_read && $last->receiver_id == Auth::id() ? 'unread' : '' }}">

<img src="{{ $other->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
class="avatar">

<div class="flex-grow-1">

<div class="fw-semibold">
{{ $other->name }}
</div>

<div class="text-muted small">
{{ $last ? Str::limit(strip_tags($last->body), 60) : 'No messages yet' }}
</div>

</div>

<div class="text-muted small text-end">

{{ $last ? $last->created_at->diffForHumans() : '' }}

@if($last && !$last->is_read && $last->receiver_id == Auth::id())
    <span class="badge bg-success ms-2">New</span>
@endif

</div>

</a>

@endforeach

@endif

</div>

</div>

</div>

<style>

body {
background:#121212;
}

.message-card{
background:#1c1c1c;
border:none;
border-radius:14px;
overflow:hidden;
}

.message-card .card-header{
background:linear-gradient(90deg,#2c2c2c,#3a3a3a);
border-bottom:1px solid #2a2a2a;
}

.message-row{
padding:14px 16px;
border-bottom:1px solid #262626;
transition:all .2s;
}

.message-row:hover{
background:#2a2a2a;
transform:translateY(-1px);
}

.avatar{
width:42px;
height:42px;
border-radius:50%;
object-fit:cover;
}

.message-row.unread{
font-weight:600;
position:relative;
}

.message-row.unread::before{
content:'';
position:absolute;
left:0;
top:0;
width:4px;
height:100%;
background:#28a745;
border-radius:0 4px 4px 0;
}

@media (max-width:768px){

.message-row{
padding:12px;
}

.avatar{
width:36px;
height:36px;
}

}

</style>

@endsection