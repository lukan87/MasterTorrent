@extends('layouts.app')

@section('content')

<style>
/* FileIplay Check Staff Reply — forum style */
.reply-check-page {
    width: 100%;
    max-width: 620px;
    margin: 0 auto;
    padding: 2rem 1rem 3rem;
}

.reply-check-card {
    background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.92));
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .85rem;
    box-shadow: 0 18px 45px rgba(0,0,0,.28);
    overflow: hidden;
}

.reply-check-body {
    padding: 1.5rem;
}

.reply-check-title {
    color: #f8fafc;
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
}

.instruction-box {
    background: rgba(15,23,42,.55);
    border: 1px solid rgba(148,163,184,.18);
    border-left: 3px solid #14b8a6;
    border-radius: .6rem;
    color: #cbd5e1;
    padding: 1rem 1.1rem;
    margin-bottom: 1rem;
}

.instruction-title {
    color: #f8fafc;
    font-size: .95rem;
    font-weight: 700;
    margin-bottom: .55rem;
}

.instruction-box ul {
    margin: 0;
    padding-left: 1.2rem;
}

.instruction-box li {
    margin-bottom: .35rem;
    font-size: .9rem;
    line-height: 1.5;
}

.instruction-box li:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    color: #cbd5e1;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: .4rem;
}

.form-control {
    min-height: 45px;
    background: rgba(2,6,23,.48) !important;
    border: 1px solid rgba(148,163,184,.22) !important;
    border-radius: .55rem !important;
    color: #f8fafc !important;
    padding: .68rem .8rem !important;
    font-size: .95rem;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.form-control::placeholder {
    color: #64748b;
}

.form-control:focus {
    background: rgba(2,6,23,.62) !important;
    border-color: rgba(45,212,191,.75) !important;
    box-shadow: 0 0 0 .18rem rgba(45,212,191,.10) !important;
    outline: none !important;
}

.btn {
    min-height: 43px;
    border-radius: .55rem;
    font-size: .9rem;
    font-weight: 700;
    padding: .65rem 1rem;
    transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.btn-primary {
    background: #0f766e !important;
    border: 1px solid #14b8a6 !important;
    color: #fff !important;
    box-shadow: 0 5px 16px rgba(20,184,166,.14);
}

.btn-primary:hover,
.btn-primary:focus {
    background: #0d9488 !important;
    border-color: #2dd4bf !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 7px 20px rgba(20,184,166,.22);
}

.btn-secondary-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(15,23,42,.75);
    border: 1px solid rgba(148,163,184,.22);
    color: #cbd5e1;
    text-decoration: none;
}

.btn-secondary-custom:hover,
.btn-secondary-custom:focus {
    background: rgba(30,41,59,.95);
    border-color: rgba(45,212,191,.55);
    color: #fff;
    transform: translateY(-1px);
}

.alert {
    border-radius: .6rem;
    font-size: .9rem;
}

.alert-danger {
    background: rgba(127,29,29,.35);
    border: 1px solid rgba(248,113,113,.28);
    color: #fecaca;
}

@media (max-width: 576px) {
    .reply-check-page {
        padding: 1rem .75rem 2rem;
    }

    .reply-check-body {
        padding: 1rem;
    }

    .reply-check-title {
        font-size: 1.15rem;
    }

    .instruction-box {
        padding: .85rem .9rem;
    }

    .instruction-box li,
    .form-control,
    .form-label {
        font-size: .9rem;
    }

    .back-button {
        width: 100%;
    }
}
</style>

<div class="reply-check-page">
    <div class="reply-check-card">
        <div class="reply-check-body">

            <h4 class="reply-check-title text-center">
                Check Staff Reply
            </h4>

            <div class="instruction-box">
                <div class="instruction-title">Instrucțiuni / Instructions</div>

                <ul>
                    <li>
                        <strong>RO:</strong>
                        Introduceți adresa de email folosită când ați trimis mesajul către staff.
                    </li>
                    <li>
                        <strong>EN:</strong>
                        Enter the same email address you used when sending the message to staff.
                    </li>
                </ul>
            </div>

            @if(session('error'))
                <div class="alert alert-danger mb-3">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.replies') }}">
                @csrf

                <div class="mb-3">
                    <label for="reply-email" class="form-label">
                        Email address
                    </label>

                    <input
                        id="reply-email"
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="Email address used in contact form"
                        autocomplete="email"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Check Replies / Verifică Răspunsul
                </button>
            </form>

            <div class="text-center">
                <a href="{{ route('login') }}" class="btn btn-secondary-custom back-button">
                    ← Back to Login / Înapoi la Autentificare
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
