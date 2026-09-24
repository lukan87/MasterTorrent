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
        radial-gradient(circle at 50% 0%, rgba(66, 217, 208, .07), transparent 32%),
        linear-gradient(135deg, #0b1220 0%, #0f172a 55%, #0a111d 100%);
    color: #e7edf5;
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
    background: linear-gradient(135deg, rgba(22, 32, 51, .97), rgba(15, 23, 42, .94));
    border: 1px solid rgba(148, 163, 184, .16);
    border-radius: .85rem;
    box-shadow: 0 18px 45px rgba(0, 0, 0, .3);
}

.logo-wrapper {
    text-align: center;
    margin-bottom: 1.7rem;
}

.app-logo {
    color: #42d9d0;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-shadow: 0 0 18px rgba(66, 217, 208, .18);
}

.logo-subtitle {
    margin-top: .4rem;
    color: #94a3b8;
    font-size: .88rem;
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
    color: #e2e8f0 !important;
    background: rgba(15, 23, 42, .78) !important;
    border: 1px solid rgba(148, 163, 184, .2) !important;
    border-radius: .55rem;
    font-size: .95rem;
    box-shadow: none !important;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.form-control:focus {
    border-color: rgba(66, 217, 208, .58) !important;
    box-shadow: 0 0 0 .2rem rgba(66, 217, 208, .08) !important;
    outline: none;
}

.form-label {
    display: block;
    position: static;
    margin-bottom: .4rem;
    color: #cbd5e1;
    font-size: .88rem;
    font-weight: 600;
}

.btn-elite {
    width: 100%;
    min-height: 46px;
    margin-top: .35rem;
    padding: .65rem 1rem;
    color: #062a2b;
    background: #42d9d0;
    border: 1px solid #42d9d0;
    border-radius: .55rem;
    font-size: .95rem;
    font-weight: 700;
    box-shadow: 0 5px 16px rgba(66, 217, 208, .12);
    transition: all .18s ease;
}

.btn-elite:hover,
.btn-elite:focus {
    color: #031b1c;
    background: #67e3dc;
    border-color: #67e3dc;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(66, 217, 208, .18);
}

.alert {
    border-radius: .55rem;
    font-size: .88rem;
    margin-bottom: 1rem;
}

.alert-success {
    color: #bbf7d0;
    background: rgba(34, 197, 94, .1);
    border: 1px solid rgba(34, 197, 94, .2);
}

.alert-danger {
    color: #fecaca;
    background: rgba(239, 68, 68, .1);
    border: 1px solid rgba(239, 68, 68, .2);
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
    color: #e2e8f0;
    font-size: .95rem;
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
    color: #cbd5e1;
    background: rgba(15, 23, 42, .6);
    border: 1px solid rgba(148, 163, 184, .16);
    border-radius: .5rem;
    font-size: .86rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .18s ease;
}

.help-btn:hover {
    color: #42d9d0;
    background: rgba(66, 217, 208, .07);
    border-color: rgba(66, 217, 208, .3);
    transform: translateY(-1px);
}

.help-btn-danger:hover {
    color: #fca5a5;
    background: rgba(239, 68, 68, .07);
    border-color: rgba(239, 68, 68, .3);
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

        <div class="glass-card" id="tilt-card">

            <div class="logo-wrapper">
                <div class="app-logo">
                    {{ config('app.name') }}
                </div>
                <div class="logo-subtitle">
                    Sign in to your FileIplay account
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
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
                    <label for="login-name" class="form-label">Username</label>
                    <input type="text"
                           id="login-name"
                           name="name"
                           required
                           autocomplete="username"
                           class="form-control"
                           value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="login-password" class="form-label">Password</label>
                    <input type="password"
                           id="login-password"
                           name="password"
                           required
                           autocomplete="current-password"
                           class="form-control">
                </div>

                <button type="submit" class="btn btn-elite">
                    ACCESS FileIplay
                </button>
            </form>

        </div>

        <div class="glass-card help-card text-center">

            <h6 class="help-title">Need Help?</h6>

            <div class="help-links">

                <a href="{{ route('custom.password.recover') }}" class="help-btn">
                    🔑 Forgot Password
                </a>

                <a href="{{ route('register') }}" class="help-btn">
                    🧾 Create Account
                </a>

                <a href="{{ route('contact.create') }}" class="help-btn help-btn-danger">
                    💬 Contact Staff
                </a>

            </div>

        </div>

    </div>

</div>

@endsection
