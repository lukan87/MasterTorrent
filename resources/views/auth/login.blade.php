@extends('layouts.app')

@section('content')

<style>
html,
body {
    min-height: 100%;
    max-width: 100%;
    overflow-x: hidden !important;
}

body {
    margin: 0;
    background:
        radial-gradient(circle at 50% 0%, var(--theme-teal-soft, rgba(66, 217, 208, .07)), transparent 32%),
        linear-gradient(135deg, var(--theme-surface, #070c15) 0%, var(--theme-surface, #0a0f1b) 55%, var(--theme-surface, #060b13) 100%);
    color:  var(--theme-text, #e7edf5);
}

.fileiplay-login-page {
    min-height: calc(100vh - 70px);
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    box-sizing: border-box;
}

.auth-wrapper {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 460px;
}

.glass-card {
    width: 100%;
    padding: 2rem;
    box-sizing: border-box;
    background: linear-gradient(
        135deg,
        var(--theme-surface, rgba(14,21,33,.97)),
        var(--theme-surface, rgba(10,15,27,.94))
    );
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .16));
    border-radius: .85rem;
    box-shadow: 0 18px 45px var(--theme-shadow, rgba(0, 0, 0, .3));
}

.logo-wrapper {
    text-align: center;
    margin-bottom: 1.7rem;
}

.app-logo {
    color: var(--theme-teal-text, #42d9d0);
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-shadow: 0 0 18px var(--theme-shadow, rgba(66, 217, 208, .18));
}

.logo-subtitle {
    margin-top: .4rem;
    color: var(--theme-muted, #94a3b8);
    font-size: var(--site-font-body, 13px);
}

/* Login information */
.login-info {
    margin-bottom: 1.25rem;
    padding: .85rem 1rem;
    color:  var(--theme-text, #cbd5e1);
    background: var(--theme-teal-soft, rgba(66, 217, 208, .06));
    border: 1px solid var(--theme-teal-border, rgba(66, 217, 208, .16));
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    line-height: 1.55;
}

.login-info strong {
    color: var(--theme-teal-text, #67e3dc);
}

.form-group {
    position: relative;
    margin-bottom: 1rem;
}

.form-control {
    width: 100%;
    min-height: 48px;
    padding: .75rem .9rem;
    box-sizing: border-box;
    color: var(--theme-text, #e2e8f0) !important;
    background: var(--theme-control, rgba(10,15,27,.78)) !important;
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .2)) !important;
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    box-shadow: none !important;
    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-control::placeholder {
    color: var(--theme-muted, #64748b);
}

.form-control:focus {
    border-color: var(--theme-teal-border, rgba(66, 217, 208, .58)) !important;
    box-shadow: 0 0 0 .2rem var(--theme-shadow, rgba(66, 217, 208, .08)) !important;
    outline: none;
}

.form-label {
    display: block;
    position: static;
    margin-bottom: .4rem;
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
}

.form-help {
    display: block;
    margin-top: .4rem;
    color: var(--theme-muted, #64748b);
    font-size: var(--site-font-body, 13px);
    line-height: 1.4;
}

.btn-elite {
    width: 100%;
    min-height: 46px;
    margin-top: .35rem;
    padding: .65rem 1rem;
    color: var(--theme-on-action, #062a2b);
    background: var(--theme-teal-action, #42d9d0);
    border: 1px solid var(--theme-teal-border, #42d9d0);
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    box-shadow: 0 5px 16px var(--theme-shadow, rgba(66, 217, 208, .12));
    transition: all .18s ease;
}

.btn-elite:hover,
.btn-elite:focus {
    color: var(--theme-on-action, #031b1c);
    background: var(--theme-teal-action, #67e3dc);
    border-color: var(--theme-teal-border, #67e3dc);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px var(--theme-shadow, rgba(66, 217, 208, .18));
}

.alert {
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    margin-bottom: 1rem;
}

.alert-success {
    color: var(--theme-text, #bbf7d0);
    background: var(--theme-green-soft, rgba(34, 197, 94, .1));
    border: 1px solid var(--theme-green-border, rgba(34, 197, 94, .2));
}

.alert-danger {
    color: var(--theme-text, #fecaca);
    background: var(--theme-red-soft, rgba(239, 68, 68, .1));
    border: 1px solid var(--theme-red-border, rgba(239, 68, 68, .2));
}

.alert ul {
    padding-left: 1.2rem;
}

.help-card {
    margin-top: .9rem;
    padding: 1.25rem 1.5rem;
}

.help-title {
    margin-bottom: 1rem;
    color: var(--theme-text, #e2e8f0);
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    letter-spacing: .04em;
}

.help-links {
    display: grid;
    grid-template-columns: 1fr;
    gap: .55rem;
}

.help-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: .55rem .75rem;
    color: var(--theme-text, #cbd5e1);
    background: var(--theme-surface, rgba(10,15,27,.6));
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .16));
    border-radius: .5rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    text-decoration: none;
    transition: all .18s ease;
}

.help-btn:hover {
    color: var(--theme-teal-text, #42d9d0);
    background: var(--theme-teal-soft, rgba(66, 217, 208, .07));
    border-color: var(--theme-teal-border, rgba(66, 217, 208, .3));
    transform: translateY(-1px);
}

.help-btn-danger:hover {
    color: var(--theme-red-text, #fca5a5);
    background: var(--theme-red-soft, rgba(239, 68, 68, .07));
    border-color: var(--theme-red-border, rgba(239, 68, 68, .3));
}

@media (max-width: 575.98px) {
    .fileiplay-login-page {
        align-items: flex-start;
        padding: 1.25rem .65rem;
    }

    .auth-wrapper {
        max-width: 100%;
    }

    .glass-card {
        padding: 1.35rem;
        border-radius: .7rem;
    }

    .app-logo {
        font-size: 1.65rem;
    }

    .logo-wrapper {
        margin-bottom: 1.35rem;
    }

    .help-card {
        padding: 1.1rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .btn-elite,
    .help-btn,
    .form-control {
        transition: none !important;
    }
}
</style>

<div class="fileiplay-login-page">

    <div class="auth-wrapper">

        <div class="glass-card">

            <div class="logo-wrapper">

                <div class="app-logo">
                    {{ config('app.name') }}
                </div>

                <div class="logo-subtitle">
                    Sign in to your FileIplay account
                </div>

            </div>

            <div class="login-info">
                You can sign in using either your
                <strong>username</strong> or your
                <strong>registered email address</strong>.
                If you do not remember your username, simply use the
                email address associated with your FileIplay account.
            </div>

            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">

                @csrf

                <div class="form-group">

                    <label for="login-name" class="form-label">
                        Username or Email
                    </label>

                    <input
                        type="text"
                        id="login-name"
                        name="name"
                        required
                        autofocus
                        autocomplete="username"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Enter your username or email address"
                    >

                    <span class="form-help">
                        You can use either your FileIplay username or
                        the email address registered to your account.
                    </span>

                </div>

                <div class="form-group">

                    <label for="login-password" class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        id="login-password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="form-control"
                        placeholder="Enter your password"
                    >

                </div>

                <button type="submit" class="btn btn-elite">
                    ACCESS FileIplay
                </button>

            </form>

        </div>

        <div class="glass-card help-card text-center">

            <h6 class="help-title">
                Need Help?
            </h6>

            <div class="help-links">
                <a href="{{ route('activation.notice') }}" class="help-btn">Resend activation email</a>

                <a
                    href="{{ route('custom.password.recover') }}"
                    class="help-btn"
                >
                    🔑 Forgot Password
                </a>

                <a
                    href="{{ route('register') }}"
                    class="help-btn"
                >
                    🧾 Create Account
                </a>

                <a
                    href="{{ route('contact.create') }}"
                    class="help-btn help-btn-danger"
                >
                    💬 Contact Staff
                </a>

            </div>

        </div>

    </div>

</div>

@endsection