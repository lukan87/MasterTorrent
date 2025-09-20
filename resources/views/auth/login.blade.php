@extends('layouts.app')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #1a1a1a, #121212);
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
        color: #ddd;
    }

    .auth-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 100vh;
        padding: 1rem;
    }

    .glass-card {
        background: rgba(30, 30, 30, 0.85);
        border-radius: 20px;
        padding: 2.5rem;
        width: 100%;
        max-width: 500px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.7);
    }

    .glass-card-header {
        text-align: center;
        font-size: 2rem;
        font-weight: 600;
        letter-spacing: 1px;
        color: #9fbad1;
        text-shadow: 0 0 6px rgba(159, 186, 209, 0.4);
        margin-bottom: 1.5rem;
    }

    /* Inputs */
    .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 10px !important;
        color: #eee !important;
        padding: 0.7rem 1rem !important;
        font-size: 1rem !important;
        transition: all 0.3s ease-in-out;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #9fbad1 !important;
        box-shadow: 0 0 10px rgba(159, 186, 209, 0.4) !important;
        color: #fff !important;
        outline: none !important;
    }

    /* Buttons */
    .btn {
        border-radius: 25px;
        padding: 0.6rem 1.6rem;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease-in-out;
        font-size: 0.9rem;
    }

    .btn-login {
        background: #9fbad1;
        color: #111;
    }
    .btn-login:hover {
        background: #89a6c0;
    }

    .btn-secondary-custom {
        background: #3e4e59;
        color: #ddd;
    }
    .btn-secondary-custom:hover {
        background: #2f3c44;
    }

    /* Alerts */
    .alert {
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        font-size: 0.9rem;
    }
    .alert-danger {
        background: rgba(200, 60, 60, 0.85);
        color: #fff;
    }
    .alert-success {
        background: rgba(60, 160, 100, 0.85);
        color: #fff;
    }

    /* Action buttons */
    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        margin-top: 1rem;
    }

    /* Responsive */
    @media (max-width: 650px) {
        .glass-card {
            padding: 1.5rem;
        }
        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .btn {
            width: 100%;
        }
    }
</style>

<div class="auth-wrapper">
    <div class="glass-card">
        <div class="glass-card-header">{{ __('Login') }}</div>

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

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Username') }}</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                           name="name" placeholder="Enter Username" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required>
                    @error('password')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-login">{{ __('Login') }}</button>
                    <a href="{{ route('custom.password.recover') }}" class="btn btn-secondary-custom">{{ __('Forgot Password') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary-custom">{{ __('Register') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


