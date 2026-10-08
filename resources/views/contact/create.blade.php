@extends('layouts.app')

@section('content')

<style>
/* FileIplay Contact Staff — forum style */
.contact-page {
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    padding: 2rem 1rem 3rem;
    color: var(--theme-text, #e5e7eb);
}

.contact-card {
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.96)), var(--theme-surface, rgba(10,15,27,.92)));
    border: 1px solid var(--theme-border, rgba(148,163,184,.18));
    border-radius: .85rem;
    box-shadow: 0 18px 45px var(--theme-shadow, rgba(0,0,0,.28));
    padding: 1.5rem;
}

.contact-title {
    color: var(--theme-text, #f8fafc);
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
}

.instruction-box {
    background: var(--theme-surface, rgba(10,15,27,.55));
    border: 1px solid var(--theme-border, rgba(148,163,184,.18));
    border-left: 3px solid var(--theme-teal-border, #14b8a6);
    border-radius: .6rem;
    color: var(--theme-text, #cbd5e1);
    padding: 1rem 1.1rem;
    margin-bottom: 1rem;
}

.instruction-box.english {
    border-left-color: var(--theme-border, #64748b);
}

.instruction-title {
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    margin-bottom: .65rem;
}

.instruction-box ul {
    margin: 0;
    padding-left: 1.2rem;
}

.instruction-box li {
    margin-bottom: .35rem;
    font-size: var(--site-font-body, 13px);
    line-height: 1.5;
}

.instruction-box li:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    margin-bottom: .4rem;
}

.form-control {
    background: var(--theme-control, rgba(1,4,15,.48)) !important;
    border: 1px solid var(--theme-border, rgba(148,163,184,.22)) !important;
    border-radius: .55rem !important;
    color: var(--theme-text, #f8fafc) !important;
    padding: .68rem .8rem !important;
    font-size: var(--site-font-body, 13px);
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.form-control::placeholder {
    color: var(--theme-muted, #64748b);
}

.form-control:focus {
    background: var(--theme-control, rgba(1,4,15,.62)) !important;
    border-color: var(--theme-teal-border, rgba(45,212,191,.75)) !important;
    box-shadow: 0 0 0 .18rem var(--theme-shadow, rgba(45,212,191,.10)) !important;
    outline: none !important;
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.btn {
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    min-height: 43px;
    padding: .65rem 1rem;
    transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.btn-primary {
    background: var(--theme-teal-action, #0f766e) !important;
    border: 1px solid var(--theme-teal-border, #14b8a6) !important;
    color: var(--theme-on-action, #fff) !important;
    box-shadow: 0 5px 16px var(--theme-shadow, rgba(20,184,166,.14));
}

.btn-primary:hover,
.btn-primary:focus {
    background: var(--theme-teal-action, #0d9488) !important;
    border-color: var(--theme-teal-border, #2dd4bf) !important;
    color: var(--theme-on-action, #fff) !important;
    transform: translateY(-1px);
    box-shadow: 0 7px 20px var(--theme-shadow, rgba(20,184,166,.22));
}

.reply-section {
    border-top: 1px solid var(--theme-border, rgba(148,163,184,.16));
    margin-top: 1.5rem;
    padding-top: 1.35rem;
    text-align: center;
}

.reply-text {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    line-height: 1.55;
    margin-bottom: .75rem;
}

.reply-text strong {
    color: var(--theme-text, #f8fafc);
}

.reply-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: auto !important;
}

@media (max-width: 576px) {
    .contact-page {
        padding: 1rem .75rem 2rem;
    }

    .contact-card {
        padding: 1rem;
    }

    .contact-title {
        font-size: 1.2rem;
    }

    .instruction-box {
        padding: .85rem .9rem;
    }

    .instruction-box li,
    .form-control,
    .form-label,
    .reply-text {
        font-size: var(--site-font-body, 13px);
    }

    .reply-button {
        width: 100% !important;
    }
}
</style>

<div class="contact-page">
    <div class="contact-card">

        <h3 class="contact-title">Contact Staff</h3>

        {{-- Romanian Instructions --}}
        <div class="instruction-box">
            <h5 class="instruction-title">Instrucțiuni (Română)</h5>
            <ul>
                <li>Completați formularul de mai jos pentru a contacta staff-ul site-ului.</li>
                <li>Introduceți o adresă de email validă.</li>
                <li>Staff-ul va analiza mesajul și va răspunde cât mai curând posibil.</li>
                <li>După trimiterea mesajului puteți verifica răspunsul folosind pagina <strong>"Verifică Răspunsul Staff-ului"</strong>.</li>
                <li>Când verificați răspunsul trebuie să introduceți <strong>aceeași adresă de email</strong> folosită în formular.</li>
            </ul>
        </div>

        {{-- English Instructions --}}
        <div class="instruction-box english">
            <h5 class="instruction-title">Instructions (English)</h5>
            <ul>
                <li>Fill in the form below to contact the site staff.</li>
                <li>Please provide a valid email address.</li>
                <li>Staff will review your message and reply as soon as possible.</li>
                <li>After sending the message you can check the reply using the <strong>"Check for staff reply"</strong> page.</li>
                <li>When checking for replies you must enter the <strong>same email address</strong> used in this form.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('contact.store') }}">
            @csrf

            <div class="mb-3">
                <label for="contact-name" class="form-label">Name / Nume</label>
                <input id="contact-name" class="form-control" name="name" value="{{ old('name') }}" placeholder="Name / Nume" autocomplete="name" required>
            </div>

            <div class="mb-3">
                <label for="contact-email" class="form-label">Email address / Adresă email</label>
                <input id="contact-email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email address / Adresă email" autocomplete="email" required>
            </div>

            <div class="mb-3">
                <label for="contact-subject" class="form-label">Subject / Subiect</label>
                <input id="contact-subject" class="form-control" name="subject" value="{{ old('subject') }}" placeholder="Subject / Subiect" required>
            </div>

            <div class="mb-3">
                <label for="contact-message" class="form-label">Message / Mesaj</label>
                <textarea id="contact-message" class="form-control" name="message" rows="6" placeholder="Message / Mesaj" required>{{ old('message') }}</textarea>
            </div>

            {{-- Honeypot --}}
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">

            {{-- JS challenge --}}
            <input type="hidden" name="js_token" id="js_token">

            {{-- Form timestamp --}}
            <input type="hidden" name="form_time" id="form_time">

            <button type="submit" class="btn btn-primary w-100">
                Send Message / Trimite Mesaj
            </button>
        </form>

        <div class="reply-section">
            <p class="reply-text">
                <strong>RO:</strong> Ați trimis deja un mesaj?<br>
                <strong>EN:</strong> Already sent a message?
            </p>

            <a href="{{ route('contact.check') }}" class="btn btn-primary reply-button">
                Verifică Răspunsul Staff-ului / Check Staff Reply
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formTime = document.getElementById('form_time');
    const jsToken = document.getElementById('js_token');

    if (formTime) {
        formTime.value = Date.now();
    }

    if (jsToken) {
        jsToken.value = btoa(navigator.userAgent + Date.now());
    }
});
</script>

@endsection
