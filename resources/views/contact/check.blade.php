@extends('layouts.app')

@section('content')

<div class="container" style="max-width:600px">

<div class="card shadow-lg border-0 mt-4">

<div class="card-body p-4">

<h4 class="mb-3 text-center">Check Staff Reply</h4>

<div class="alert alert-info">

<strong>Instrucțiuni / Instructions</strong>

<ul class="mb-0 mt-2">

<li>
<strong>RO:</strong> Introduceți adresa de email folosită când ați trimis mesajul către staff.
</li>

<li>
<strong>EN:</strong> Enter the same email address you used when sending the message to staff.
</li>

</ul>

</div>

@if(session('error'))
<div class="alert alert-danger">
{{ session('error') }}
</div>
@endif

<form method="POST" action="{{ route('contact.replies') }}">
@csrf

<div class="mb-3">

<input
type="email"
name="email"
class="form-control form-control-lg"
placeholder="Email address used in contact form"
required>

</div>

<button class="btn btn-primary w-100 mb-3">

Check Replies / Verifică Răspunsul

</button>

</form>

<div class="text-center">

<a href="{{ route('login') }}" class="btn btn-outline-secondary">

← Back to Login / Înapoi la Autentificare

</a>

</div>

</div>

</div>

</div>

@endsection