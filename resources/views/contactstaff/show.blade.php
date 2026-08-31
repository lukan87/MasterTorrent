@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4">

<div class="row justify-content-center">

<div class="col-xl-10">

<h3 class="mb-4">Contact Message</h3>

<div class="card glass mb-4">

<div class="card-header">
<strong>Subject: {{ $contact->subject }}</strong>
</div>

<div class="card-body">

<div class="row mb-3">

<div class="col-md-4">
<strong>Name:</strong> {{ $contact->name }}
</div>

<div class="col-md-4">
<strong>Email:</strong> {{ $contact->email }}
</div>

<div class="col-md-4 text-md-end">
<strong>Sent:</strong> {{ $contact->created_at->format('d M Y H:i') }}
</div>

</div>

<hr>

<h5 class="mb-4">Conversation</h5>

<div class="conversation-box">

@foreach($contact->messages as $msg)

@if($msg->sender_type == 'guest')

<div class="message-row guest">

<div class="message-bubble guest">

<div class="message-header">

<strong class="text-primary">Guest</strong>

<span class="text-muted">
{{ $msg->created_at->format('d M Y H:i') }}
</span>

</div>

<div class="message-text">
{!! nl2br(e($msg->message)) !!}
</div>

</div>

</div>

@else

<div class="message-row staff">

<div class="message-bubble staff">

<div class="message-header">

<strong class="text-success">
Staff ({{ $msg->staff->name ?? 'Staff' }})
</strong>

<span class="text-muted">
{{ $msg->created_at->format('d M Y H:i') }}
</span>

</div>

<div class="message-text">
{!! nl2br(e($msg->message)) !!}
</div>

</div>

</div>

@endif

@endforeach

</div>


@if(!$contact->resolved)

<hr>

<h4 class="mb-3">Reply to User</h4>

<form method="POST" action="{{ route('contactstaff.answer', $contact->id) }}">
@csrf

<textarea name="reply"
class="form-control mb-3"
rows="5"
placeholder="Write your reply..."
required></textarea>

<button class="btn btn-success">
Reply
</button>

</form>

<form method="POST" action="{{ route('contactstaff.resolve', $contact->id) }}">
@csrf

<button type="submit" class="btn btn-warning mt-3">
Resolve Conversation
</button>

</form>

@endif


@if($contact->resolved)

<div class="alert alert-success mt-3">
This conversation has been resolved.
</div>

@endif

</div>

</div>

</div>

</div>

</div>

<script>

window.onload = function(){

const box = document.querySelector('.conversation-box');

if(box){
window.scrollTo(0,document.body.scrollHeight);
}

}

</script>


<style>

.conversation-box{
display:flex;
flex-direction:column;
gap:20px;
}

.message-row{
display:flex;
width:100%;
}

.message-row.guest{
justify-content:flex-end;
}

.message-row.staff{
justify-content:flex-;
}

.message-bubble{
padding:15px;
border-radius:12px;
max-width:70%;
box-shadow:0 2px 6px rgba(0, 0, 0, 0.62);
}

.message-bubble.guest{
background:0 2px 6px rgba(75, 69, 69, 0.62);
}

.message-bubble.staff{
background:0 2px 6px rgba(0, 0, 0, 0.62);
}

.message-header{
display:flex;
justify-content:space-between;
font-size:13px;
margin-bottom:6px;
}

.message-text{
font-size:15px;
line-height:1.5;
}

</style>

@endsection