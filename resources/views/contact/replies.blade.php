@extends('layouts.app')

@section('content')

<div class="container" style="max-width:900px">

<h3 class="mb-4">Your Conversations / Conversațiile Tale</h3>

@foreach($contacts as $contact)

<div class="card mb-4 shadow-sm">

<div class="card-header d-flex justify-content-between">

<strong>{{ $contact->subject }}</strong>

@if($contact->resolved)
<span class="badge bg-success">Resolved</span>
@else
<span class="badge bg-warning text-dark">Open</span>
@endif

</div>

<div class="card-body">

@foreach($contact->messages as $msg)

<div class="mb-3 p-3 border rounded">

@if($msg->sender_type == 'guest')

<strong class="text-primary">You / Tu</strong>

@else

<strong class="text-success">Staff</strong>

@endif

<span class="text-muted float-end">
{{ $msg->created_at->format('d M Y H:i') }}
</span>

<hr>

{!! nl2br(e($msg->message)) !!}

</div>

@endforeach


@if(!$contact->resolved)

<form method="POST" action="{{ route('contact.reply', $contact->id) }}">
@csrf

<textarea
name="message"
class="form-control mb-2"
rows="4"
placeholder="Write a reply / Scrie un răspuns..."
required></textarea>

<button class="btn btn-primary">
Send Reply / Trimite Răspuns
</button>

</form>

@else

<div class="alert alert-success mt-3 mb-0">

<strong>Conversation resolved.</strong><br>

EN: This conversation has been closed by staff.<br>
RO: Această conversație a fost închisă de staff.

</div>

@endif

</div>

</div>

@endforeach

</div>

@endsection