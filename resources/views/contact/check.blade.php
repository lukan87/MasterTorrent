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
    background: linear-gradient(135deg, var(--theme-surface, rgba(14,21,33,.96)), var(--theme-surface, rgba(10,15,27,.92)));
    border: 1px solid var(--theme-border, rgba(148,163,184,.18));
    border-radius: .85rem;
    box-shadow: 0 18px 45px var(--theme-shadow, rgba(0,0,0,.28));
    overflow: hidden;
}

.reply-check-body {
    padding: 1.5rem;
}

.reply-check-title {
    color: var(--theme-text, #f8fafc);
    font-size: 1.3rem;
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

.instruction-title {
    color: var(--theme-text, #f8fafc);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    margin-bottom: .55rem;
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
    min-height: 45px;
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

.btn {
    min-height: 43px;
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
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

.btn-secondary-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--theme-surface, rgba(10,15,27,.75));
    border: 1px solid var(--theme-border, rgba(148,163,184,.22));
    color: var(--theme-text, #cbd5e1);
    text-decoration: none;
}

.btn-secondary-custom:hover,
.btn-secondary-custom:focus {
    background: var(--theme-surface, rgba(20,27,38,.95));
    border-color: var(--theme-teal-border, rgba(45,212,191,.55));
    color: var(--theme-text, #fff);
    transform: translateY(-1px);
}

.alert {
    border-radius: .6rem;
    font-size: var(--site-font-body, 13px);
}

.alert-danger {
    background: var(--theme-red-soft, rgba(127,29,29,.35));
    border: 1px solid var(--theme-red-border, rgba(248,113,113,.28));
    color: var(--theme-text, #fecaca);
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
        font-size: var(--site-font-body, 13px);
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
