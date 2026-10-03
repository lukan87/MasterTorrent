@extends('layouts.app')
@section('content')
@include('auth.partials.recovery-style')
<div class="auth-wrapper"><div class="glass-card">
    <div class="logo-wrapper"><div class="app-logo">{{ config('app.name') }}</div></div>
    <h4 class="text-center recovery-title">CHOOSE A NEW PASSWORD</h4>
    <p class="recovery-subtitle">Use a unique password with at least 8 characters. Once saved, return to login to access your account.</p>
    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email" class="form-label">Account email</label>
        <input id="email" name="email" type="email" class="form-control mb-3" value="{{ old('email', $email) }}" autocomplete="email" required>
        <label for="password" class="form-label">New password</label>
        <input id="password" name="password" type="password" class="form-control mb-3" minlength="8" autocomplete="new-password" required>
        <label for="password_confirmation" class="form-label">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control mb-3" minlength="8" autocomplete="new-password" required>
        <button class="btn btn-recover" type="submit">Save new password</button>
    </form>
    <a class="btn btn-secondary-custom mt-3" href="{{ route('login') }}">Back to login</a>
</div></div>
@endsection
