@extends('layouts.app')

@section('content')

<div class="container-fluid mt-5">

<div class="row g-4">


<div class="col-lg-4">

<div class="card glass border-0">

<div class="card-header">

Ticket Info

</div>

<div class="card-body">

<div class="mb-2">

<strong>Status:</strong>

<span class="badge bg-info">

{{ $ticket->status }}

</span>

</div>


<div class="mb-2">

<strong>Priority:</strong>

{{ $ticket->priority }}

</div>


<div class="mb-2">

<strong>Category:</strong>

{{ $ticket->category->name }}

</div>


<div class="mb-2">

<strong>Creator:</strong>

{{ $ticket->user->name }}

</div>


<div class="mb-2">

<strong>Created:</strong>

{{ $ticket->created_at->diffForHumans() }}

</div>


@if($ticket->assignedStaff)

<div class="mb-2">

<strong>Assigned Staff:</strong>

{{ $ticket->assignedStaff->name }}

</div>

@endif

@if($ticket->claimedBy)

<div class="mb-2">

<strong>Claimed By:</strong>

{{ $ticket->claimedBy->name }}

</div>

@endif


@if(Auth::user()->user_class > 5)

<hr>

<h6 class="mb-3">Staff Controls</h6>

<form method="POST" action="{{ route('tickets.claim',$ticket->id) }}">
@csrf
<button class="btn btn-sm btn-warning w-100 mb-2">
Claim Ticket
</button>
</form>


<form method="POST" action="{{ route('tickets.status',$ticket->id) }}">
@csrf

<select name="status" class="form-select mb-2">

<option value="Open" {{ $ticket->status=='Open'?'selected':'' }}>Open</option>
<option value="Waiting Staff" {{ $ticket->status=='Waiting Staff'?'selected':'' }}>Waiting Staff</option>
<option value="Waiting User" {{ $ticket->status=='Waiting User'?'selected':'' }}>Waiting User</option>
<option value="Resolved" {{ $ticket->status=='Resolved'?'selected':'' }}>Resolved</option>
<option value="Closed" {{ $ticket->status=='Closed'?'selected':'' }}>Closed</option>

</select>

<button class="btn btn-sm btn-primary w-100">
Update Status
</button>

</form>


<form method="POST"
action="{{ route('tickets.lock',$ticket->id) }}"
class="mt-2">

@csrf

<button class="btn btn-sm btn-danger w-100">

Lock Ticket

</button>

</form>

@endif


<hr>

<h6 class="mb-3">Activity</h6>

@foreach($ticket->events->sortByDesc('created_at')->take(10) as $event)

<div class="small mb-2">

<strong>

@if($event->user)
{{ $event->user->name }}
@endif

</strong>

{{ $event->event }}

<br>

<span class="text-muted">

{{ $event->created_at->diffForHumans() }}

</span>

</div>

@endforeach


</div>

</div>



</div>



<div class="col-lg-8">

<div class="card glass border-0">

<div class="card-header">

Conversation

</div>

<div class="card-body">


{{-- <div class="mb-4">

<strong>{{ $ticket->user->name }}</strong>

<p class="mt-2">

{{ $ticket->description }}

</p>

</div> --}}

<div id="new-reply-alert" class="alert alert-success d-none mb-3">
<i class="bi bi-bell"></i>
<strong>New reply from staff</strong>
<button class="btn btn-sm btn-light ms-3" onclick="scrollToReply()">
View
</button>
</div>


<div id="ticket-replies">


@foreach($ticket->responses as $response)

@if(!$response->is_staff_note || Auth::user()->user_class > 5)

<div class="border-top pt-3 mb-3 {{ $loop->first ? 'border-0' : '' }}">

<strong>
{{ $response->user->name }}

@if($loop->first)
<span class="badge bg-primary ms-2">Author</span>
@endif

@if($response->is_staff_note)

<span class="badge bg-warning text-dark ms-2">

Staff Note

</span>

@endif

</strong>

<span class="text-muted">

{{ $response->created_at->diffForHumans() }}

</span>

<p class="mt-2">

{{ $response->message }}

</p>

@if($response->attachments->count())

<div class="mt-2">

@foreach($response->attachments as $file)

<a href="{{ route('tickets.download',$file->id) }}"
class="btn btn-sm btn-outline-light">

<i class="bi bi-paperclip"></i>
{{ $file->file_name }}

</a>

@endforeach

</div>

@endif

</div>

@endif

@endforeach

</div>



@if(!$ticket->is_locked)

<hr>

<form method="POST"
action="{{ route('tickets.reply',$ticket->id) }}"
enctype="multipart/form-data">

@csrf

<textarea
name="message"
rows="4"
class="form-control mb-3"
required></textarea>

<div id="typing-indicator" class="typing-indicator d-none"></div>


<input type="file"
name="attachment[]" multiple
class="form-control mb-3">

@if(Auth::user()->user_class > 5)

<div class="form-check mb-3">

<input type="checkbox"
name="staff_note"
class="form-check-input"
id="staffNote">

<label class="form-check-label" for="staffNote">
Internal Staff Note
</label>

</div>

@endif


<button class="btn btn-primary">

Reply

</button>

</form>

@else

<hr>

<div class="alert alert-warning">
<i class="bi bi-lock"></i> This ticket is locked.
</div>

<form method="POST" action="{{ route('tickets.lock',$ticket->id) }}">
@csrf

<button class="btn btn-success btn-sm"
@if(Auth::id() == $ticket->user_id)
data-bs-toggle="tooltip"
title="Only unlock if you have the same issue! If unlocking and spamming, you will get a warning!"
@endif
>
<i class="bi bi-unlock"></i> Unlock Ticket
</button>

</form>

@endif


</div>

</div>

</div>

</div>

</div>

<style>

#new-reply-alert{
position:sticky;
top:0;
z-index:5;
}

.typing-indicator{
display:flex;
align-items:center;
gap:6px;
font-size:13px;
color:#9ca3af;
margin-bottom:10px;
}
.typing-indicator{
transition:opacity .25s ease;
}

.typing-name{
color:#fff;
font-weight:600;
}

.typing-dots{
display:inline-flex;
gap:3px;
margin-left:4px;
}

.typing-dots span{
width:6px;
height:6px;
background:#9ca3af;
border-radius:50%;
animation:typingBounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(2){
animation-delay:.2s;
}

.typing-dots span:nth-child(3){
animation-delay:.4s;
}

@keyframes typingBounce{
0%,80%,100%{
transform:scale(0);
opacity:.4;
}
40%{
transform:scale(1);
opacity:1;
}
}

</style>
<script>

document.addEventListener("DOMContentLoaded", function(){

let lastReplyCount = {{ $ticket->responses->count() }};
let ticketReplies = document.getElementById("ticket-replies");
let alertBox = document.getElementById("new-reply-alert");

function renderReply(reply){

let attachments = '';

if(reply.attachments.length){

reply.attachments.forEach(file => {

attachments += `
<a href="/tickets/download/${file.id}"
class="btn btn-sm btn-outline-light me-1">
<i class="bi bi-paperclip"></i> ${file.file_name}
</a>
`;

});

}

let badge = reply.is_staff_note
? '<span class="badge bg-warning text-dark ms-2">Staff Note</span>'
: '';

return `
<div class="border-top pt-3 mb-3 new-reply">

<strong>
${reply.user.name}
${badge}
</strong>

<span class="text-muted">
just now
</span>

<p class="mt-2">
${reply.message}
</p>

<div class="mt-2">
${attachments}
</div>

</div>
`;

}

window.scrollToReply = function(){
let el = document.querySelector('.new-reply:last-child');
if(el){
el.scrollIntoView({behavior:'smooth'});
}
alertBox.classList.add("d-none");
};

setInterval(function(){

fetch("{{ route('tickets.fetchReplies',$ticket->id) }}")

.then(res => res.json())

.then(data => {

if(data.length > lastReplyCount){

let newReplies = data.slice(lastReplyCount);

newReplies.forEach(reply => {

ticketReplies.insertAdjacentHTML(
'beforeend',
renderReply(reply)
);

});

lastReplyCount = data.length;

alertBox.classList.remove("d-none");

}

});

},5000);


/* --------------------------
   STAFF TYPING
-------------------------- */

let textarea = document.querySelector('textarea[name="message"]');

if(textarea){

let typingTimer;

textarea.addEventListener("input", function(){

clearTimeout(typingTimer);

fetch("/tickets/{{ $ticket->id }}/typing",{
method:"POST",
headers:{
'X-CSRF-TOKEN':'{{ csrf_token() }}',
'Content-Type':'application/json'
}
});

typingTimer = setTimeout(()=>{},2000);

});

setInterval(function(){

fetch("/tickets/{{ $ticket->id }}/typing-status")

.then(res=>res.json())

.then(data=>{

let indicator = document.getElementById("typing-indicator");

if(!indicator) return;

if(data.typing){

if(data.user_id != {{ auth()->id() }}){

indicator.innerHTML =
`<span class="typing-name">${data.name}</span> is typing
<span class="typing-dots">
<span></span><span></span><span></span>
</span>`;

indicator.classList.remove("d-none");

}

}else{

indicator.classList.add("d-none");

}

});

},3000);

}

});

</script>

@endsection