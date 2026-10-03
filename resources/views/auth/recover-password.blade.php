@extends('layouts.app')

@section('content')

@include('auth.partials.recovery-style')

<div class="auth-wrapper">
    <div class="glass-card">

        <div class="logo-wrapper">
            <div class="app-logo">
                {{ config('app.name') }}
            </div>
        </div>

        <h4 class="text-center recovery-title">
            RECOVER YOUR PASSWORD
        </h4>

        <p class="recovery-subtitle">
            Use this form if you have forgotten your FileIplay password
            or need to create a new one.
        </p>

        @if(config('auth.email_registration'))
            <div class="recovery-info">Enter your account email and we’ll send a secure password reset link. The link expires in {{ config('auth.passwords.users.expire') }} minutes.</div>
        @else
        {{-- Recovery instructions --}}
        <div class="recovery-info">

            <div class="recovery-info-title">
                Password Recovery Instructions
            </div>

            <ol class="recovery-steps">
                <li>
                    Enter the <strong>same email address</strong> where you
                    received the FileIplay account message.
                </li>

                <li>
                    If your account was migrated from the previous FileIplay
                    website, enter
                    <span class="code-box">fileiplay</span>
                    as your recovery code.
                </li>

                <li>
                    Choose a <strong>new password</strong> and enter it again
                    to confirm it.
                </li>

                <li>
                    Click <strong>Reset Password</strong>. You can then log
                    in using your email/username and your new password.
                </li>

                <li>
                    After logging in, go to your <strong>Profile</strong>
                    and change your recovery code to your own private
                    recovery code.
                </li>
            </ol>

            <div class="security-note">
                The temporary recovery code
                <span class="code-box">fileiplay</span>
                is intended for migrated accounts. For your security,
                please replace it with your own recovery code after you
                regain access to your account.
            </div>

        </div>

        @endif

        @if(session('status'))
            <div class="alert alert-success mb-3">
                {{ session('status') }}
            </div>
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

        <form method="POST" action="{{ route(config('auth.email_registration') ? 'password.email' : 'custom.password.update') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">
                    Email Address
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    placeholder="Your registered email address"
                    autocomplete="email"
                    required
                >

                <span class="form-help">
                    Use the email address associated with your FileIplay
                    account. If you received an account email from us,
                    use that email address here.
                </span>
            </div>

            @unless(config('auth.email_registration'))
            <div class="mb-3">
                <label for="recovery_code" class="form-label">
                    Recovery Code
                </label>

                <input
                    id="recovery_code"
                    type="text"
                    name="recovery_code"
                    class="form-control"
                    placeholder="Enter your recovery code"
                    autocomplete="one-time-code"
                    required
                >

                <span class="form-help">
                    Migrated users can use
                    <strong>fileiplay</strong>
                    as the temporary recovery code.
                </span>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    New Password
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your new password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >

                <span class="form-help">
                    Your new password must contain at least 8 characters.
                </span>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                    Confirm New Password
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Enter your new password again"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </div>

            @endunless

            <div class="d-flex gap-3 flex-wrap mt-4">

                <button type="submit" class="btn btn-recover">
                    {{ config('auth.email_registration') ? 'SEND RESET LINK' : 'RESET PASSWORD' }}
                </button>

                <a
                    href="{{ route('login') }}"
                    class="btn btn-secondary-custom"
                >
                    Login
                </a>

                <a
                    href="{{ route('register') }}"
                    class="btn btn-secondary-custom"
                >
                    Register
                </a>

            </div>

        </form>

    </div>
</div>

@endsection