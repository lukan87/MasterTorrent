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
        max-width: 650px;
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

    /* Shared input + select styling */
    .form-control,
    .form-select {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 10px !important;
        color: #eee !important;
        padding: 0.7rem 1rem !important;
        font-size: 1rem !important;
        transition: all 0.3s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: #9fbad1 !important;
        box-shadow: 0 0 10px rgba(159, 186, 209, 0.4) !important;
        outline: none !important;
        color: #fff !important;
    }

    /* Wrapper for select arrow */
    .select-wrapper {
        position: relative;
    }

    .select-wrapper::after {
        content: "▼";
        font-size: 0.7rem;
        color: #bbb;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    /* Dropdown options */
    .form-select option {
        background: #222;
        color: #eee;
        padding: 0.5rem;
    }

    /* Help text */
    small.form-text {
        color: #888;
        font-size: 0.8rem;
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

    .btn-register {
        background: #9fbad1;
        color: #111;
    }
    .btn-register:hover {
        background: #89a6c0;
    }

    .btn-back {
        background: #3e4e59;
        color: #ddd;
    }
    .btn-back:hover {
        background: #2f3c44;
    }

    /* Actions side by side */
    .form-actions {
        display: flex;
        gap: 0.8rem;
        margin-top: 1rem;
        justify-content: flex-start;
        flex-wrap: wrap;
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
        <div class="glass-card-header">{{ __('Register') }}</div>

        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    @error('name')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email Address') }}</label>
                    <input id="email" type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required autocomplete="new-password">
                    @error('password')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control"
                           name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="mb-3">
                    <label for="recovery_code" class="form-label">{{ __('Recovery Code') }}</label>
                    <input id="recovery_code" type="text"
                           class="form-control @error('recovery_code') is-invalid @enderror"
                           name="recovery_code" value="{{ old('recovery_code') }}" required autocomplete="recovery_code">
                    @error('recovery_code')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                    <small class="form-text">
                        Please enter a unique code. This code is used to recover your password. Write it down safely.
                    </small>
                </div>

                @php
                    $timezones = \DateTimeZone::listIdentifiers();
                @endphp
                <div class="mb-3">
                    <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                    <div class="select-wrapper">
                        <select id="timezone" name="timezone"
                                class="form-select @error('timezone') is-invalid @enderror" required>
                            <option value="" disabled selected>Select your timezone</option>
                            @foreach($timezones as $timezone)
                                <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('timezone')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                @if(config('app.invite_only') == true)
                    <div class="mb-3">
                        <label for="invite_code" class="form-label">{{ __('Invite Code') }}</label>
                        <input id="invite_code" type="text"
                               class="form-control @error('invite_code') is-invalid @enderror"
                               name="invite_code" value="{{ old('invite_code') }}" required>
                        @error('invite_code')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                @endif

                <div class="form-actions">
                    <button type="submit" class="btn btn-register">{{ __('Register') }}</button>
                    <a href="{{ route('login') }}" class="btn btn-back">{{ __('Back To Login') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
