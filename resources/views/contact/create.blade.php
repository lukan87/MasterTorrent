@extends('layouts.app')

@section('content')

<div class="container mt-5" style="max-width:700px">

<h3 class="mb-4">Contact Staff</h3>

<!-- Romanian Instructions -->

<div class="alert alert-info mb-4">

<h5>Instrucțiuni (Română)</h5>

<ul class="mb-0">

<li>Completați formularul de mai jos pentru a contacta staff-ul site-ului.</li>

<li>Introduceți o adresă de email validă.</li>

<li>Staff-ul va analiza mesajul și va răspunde cât mai curând posibil.</li>

<li>După trimiterea mesajului puteți verifica răspunsul folosind pagina <strong>"Verifică Răspunsul Staff-ului"</strong>.</li>

<li>Când verificați răspunsul trebuie să introduceți <strong>aceeași adresă de email</strong> folosită în formular.</li>

</ul>

</div>


<!-- English Instructions -->

<div class="alert alert-secondary mb-4">

<h5>Instructions (English)</h5>

<ul class="mb-0">

<li>Fill in the form below to contact the site staff.</li>

<li>Please provide a valid email address.</li>

<li>Staff will review your message and reply as soon as possible.</li>

<li>After sending the message you can check the reply using the <strong>"Check for staff reply"</strong> page.</li>

<li>When checking for replies you must enter the <strong>same email address</strong> used in this form.</li>

</ul>

</div>


<form method="POST" action="{{ route('contact.store') }}">
@csrf

<input class="form-control mb-3" name="name" placeholder="Name / Nume" required>

<input class="form-control mb-3" name="email" placeholder="Email address / Adresă email" required>

<input class="form-control mb-3" name="subject" placeholder="Subject / Subiect" required>

<textarea class="form-control mb-3" name="message" rows="6" placeholder="Message / Mesaj" required></textarea>

<!-- Honeypot -->
<input type="text" name="website" style="display:none">

<!-- JS challenge -->
<input type="hidden" name="js_token" id="js_token">

<!-- Form timestamp -->
<input type="hidden" name="form_time" id="form_time">

<button class="btn btn-primary w-100">
Send Message / Trimite Mesaj
</button>

</form>


<script>

/* record time when form loaded */

document.getElementById('form_time').value = Date.now();

/* create JS token */

document.getElementById('js_token').value =
btoa(navigator.userAgent + Date.now());

</script>

<hr>

<div class="text-center">

<p class="mb-2">

<strong>RO:</strong> Ați trimis deja un mesaj?<br>
<strong>EN:</strong> Already sent a message?

</p>

<a href="{{ route('contact.check') }}" class="btn btn-primary btn-lg">

Verifică Răspunsul Staff-ului / Check Staff Reply

</a>

</div>

</div>

@endsection