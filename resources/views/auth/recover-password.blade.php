@extends('layouts.app')

@section('content')

<style>
/* FileIplay password recovery — forum style */
body {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(circle at 50% 0%, rgba(20,184,166,.08), transparent 38%),
        #0b1120;
    color: #e5e7eb;
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    overflow-x: hidden;
}

.auth-wrapper {
    min-height: calc(100vh - 70px);
    width: 100%;
    padding: 2rem 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.glass-card {
    width: 100%;
    max-width: 560px;
    padding: 2rem;
    background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.92));
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .85rem;
    box-shadow: 0 18px 45px rgba(0,0,0,.32);
}

.logo-wrapper {
    text-align: center;
    margin-bottom: 1.5rem;
}

.app-logo {
    display: inline-block;
    color: #2dd4bf;
    font-size: 1.65rem;
    font-weight: 800;
    letter-spacing: 2px;
    text-shadow: 0 0 18px rgba(45,212,191,.18);
}

.recovery-title {
    color: #f8fafc;
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: .8px;
    margin-bottom: 1.25rem;
}

.form-label {
    color: #cbd5e1;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: .45rem;
}

.form-control {
    min-height: 44px;
    background: rgba(2,6,23,.48) !important;
    border: 1px solid rgba(148,163,184,.22) !important;
    border-radius: .55rem !important;
    color: #f8fafc !important;
    padding: .65rem .8rem !important;
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
    min-height: 42px;
    border-radius: .55rem;
    font-size: .9rem;
    font-weight: 700;
    padding: .6rem 1rem;
    transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
}

.btn-recover {
    background: #0f766e;
    border: 1px solid #14b8a6;
    color: #fff;
    box-shadow: 0 5px 16px rgba(20,184,166,.16);
}

.btn-recover:hover,
.btn-recover:focus {
    background: #0d9488;
    border-color: #2dd4bf;
    color: #fff;
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
    border-radius: .55rem;
    border: 1px solid transparent;
    font-size: .9rem;
}

.alert-danger {
    background: rgba(127,29,29,.35);
    border-color: rgba(248,113,113,.28);
    color: #fecaca;
}

.alert-success {
    background: rgba(6,78,59,.38);
    border-color: rgba(45,212,191,.28);
    color: #a7f3d0;
}

@media (max-width: 576px) {
    .auth-wrapper {
        min-height: calc(100vh - 40px);
        padding: 1rem .75rem;
        align-items: flex-start;
    }

    .glass-card {
        padding: 1.25rem;
        margin-top: 1rem;
    }

    .app-logo {
        font-size: 1.4rem;
    }

    .d-flex.gap-3 {
        gap: .5rem !important;
    }

    .d-flex.gap-3 .btn {
        flex: 1 1 100%;
    }
}
</style>

<div class="auth-wrapper">
    <div class="glass-card">

        <div class="logo-wrapper">
            <div class="app-logo">{{ config('app.name') }}</div>
        </div>

        <h4 class="text-center recovery-title">RECOVER PASSWORD</h4>

        @if(session('status'))
            <div class="alert alert-success mb-3">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('custom.password.update') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" required>
            </div>

            <div class="mb-3">
                <label for="recovery_code" class="form-label">Recovery Code</label>
                <input id="recovery_code" type="text" name="recovery_code" class="form-control" autocomplete="one-time-code" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input id="password" type="password" name="password" class="form-control" autocomplete="new-password" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
            </div>

            <div class="d-flex gap-3 flex-wrap mt-4">
                <button type="submit" class="btn btn-recover">RESET PASSWORD</button>
                <a href="{{ route('login') }}" class="btn btn-secondary-custom">Login</a>
                <a href="{{ route('register') }}" class="btn btn-secondary-custom">Register</a>
            </div>
        </form>

    </div>
</div>

@endsection
