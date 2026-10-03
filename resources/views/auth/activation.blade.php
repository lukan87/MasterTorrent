@extends('layouts.app')
@section('content')
@include('auth.partials.recovery-style')
<div class="auth-wrapper"><div class="glass-card">
    <div class="logo-wrapper"><div class="app-logo">{{ config('app.name') }}</div></div>
    <h4 class="text-center recovery-title">CHECK YOUR INBOX</h4>
    <p class="recovery-subtitle">Activate your account with the link in your email. Missing or expired link? Request a fresh one below, and check your spam folder too.</p>
    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif
    <form method="POST" action="{{ route('activation.resend') }}">
        @csrf
        <label for="email" class="form-label">Account email</label>
        <input id="email" name="email" type="email" class="form-control mb-3" value="{{ old('email') }}" autocomplete="email" required autofocus>
        <button class="btn btn-recover" type="submit">Resend activation link</button>
    </form>
    <a class="btn btn-secondary-custom mt-3" href="{{ route('login') }}">Back to login</a>
</div></div>
@endsection
