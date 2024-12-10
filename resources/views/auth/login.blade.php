@extends('layouts.app')

@section('content')

<style>
    /* Full-screen background with gradient overlay */
    body {
        background: linear-gradient(rgba(20, 20, 20, 0.5), rgba(20, 20, 20, 0.9)),
                    url('https://c88f1126c4.mjedge.net/wtl-content/uploads/2024/01/2023-Watchlist-1200x764.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #ccc; /* Softer text color */
        overflow: hidden; /* Prevents scrollbar */
        margin: 0;
        height: 100vh;
    }

    /* Centered glassmorphic card */
    .glass-card {
        background: rgba(50, 50, 50, 0.7); /* Darker frosted glass effect */
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0px 8px 32px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #eee; /* Light gray text color */
        max-width: 500px;
        width: 100%;
    }

    /* Header style */
    .glass-card-header {
        color: #888; /* Dark gray header */
        font-size: 1.75rem;
        text-align: center;
        font-weight: bold;
    }

    /* Input and form styling */
    .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ddd;
        border: none;
    }

    .form-control:focus {
        border-color: #666; /* Darker border on focus */
        box-shadow: 0px 0px 8px #666;
    }

    /* Button styling with hover effects */
    .btn-primary, .btn-info, .btn-warning {
        border-radius: 20px;
        padding: 0.5rem 1.5rem;
        transition: background-color 0.3s ease;
    }

    .btn-primary {
        background-color: #555;
        color: #ddd;
    }
    .btn-primary:hover {
        background-color: #666;
    }

    .btn-info {
        background-color: #444;
        color: #ddd;
    }
    .btn-info:hover {
        background-color: #555;
    }

    .btn-warning {
        background-color: #333;
        color: #ddd;
    }
    .btn-warning:hover {
        background-color: #444;
    }

    /* Error and success messages */
    .alert {
        background-color: rgba(255, 0, 0, 0.8);
    }
</style>

<div class="d-flex justify-content-center align-items-center" style="height: 100vh; margin: 0;">
    <div class="glass-card">
        <div class="glass-card-header">{{ __('Login') }}</div>

        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Username') }}</label>
                    <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Enter Username" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>

                <!-- Secțiunea pentru suport Facebook -->
<div class="support-facebook mb-3 d-flex align-items-center">
    <i class="fab fa-facebook-f fa-2x text-primary me-2"></i>
    <a href="https://www.facebook.com/Lastfiles" target="_blank" style="font-size: 1.2rem; text-decoration: none; color: #007bff;">Support on Facebook</a>
</div>


                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Login') }}</button>
                    <a class="btn btn-info btn-sm" href="{{ route('register') }}">{{ __('Register') }}</a>
                    <a class="btn btn-warning btn-sm" href="{{ route('custom.password.recover') }}">{{ __('Forgot Your Password?') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
