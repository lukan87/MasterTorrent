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
    min-height: 100vh;
    background:
        radial-gradient(circle at 50% 0%, rgba(66, 217, 208, .07), transparent 34%),
        linear-gradient(135deg, #0b1220 0%, #0f172a 55%, #0a111d 100%);
    color: #e7edf5;
}

.register-page {
    width: 100%;
    min-height: calc(100vh - 70px);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem 1rem;
    box-sizing: border-box;
}

.auth-wrapper {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 650px;
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
    position: relative;
    text-align: center;
    margin-bottom: 1.6rem;
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

.form-label {
    color: #cbd5e1;
    font-size: .88rem;
    font-weight: 600;
    margin-bottom: .4rem;
}

.form-control,
.form-select {
    width: 100%;
    min-height: 46px;
    box-sizing: border-box;
    color: #e2e8f0 !important;
    background: rgba(15, 23, 42, .78) !important;
    border: 1px solid rgba(148, 163, 184, .2) !important;
    border-radius: .55rem !important;
    padding: .65rem .85rem !important;
    font-size: .94rem;
    box-shadow: none !important;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.form-control:focus,
.form-select:focus {
    color: #fff !important;
    border-color: rgba(66, 217, 208, .58) !important;
    box-shadow: 0 0 0 .2rem rgba(66, 217, 208, .08) !important;
    outline: none !important;
}

.form-control::placeholder {
    color: #64748b;
}

.form-select option {
    color: #e2e8f0;
    background: #0f172a;
}

.text-muted {
    color: #94a3b8 !important;
}

.text-info {
    color: #42d9d0 !important;
}

.invalid-feedback {
    color: #fca5a5;
}

.btn-register,
.btn-back {
    min-height: 43px;
    padding: .55rem 1rem;
    border-radius: .55rem;
    font-size: .9rem;
    font-weight: 700;
    transition: all .18s ease;
}

.btn-register {
    color: #062a2b;
    background: #42d9d0;
    border: 1px solid #42d9d0;
    box-shadow: 0 5px 16px rgba(66, 217, 208, .12);
}

.btn-register:hover,
.btn-register:focus {
    color: #031b1c;
    background: #67e3dc;
    border-color: #67e3dc;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(66, 217, 208, .18);
}

.btn-register:disabled {
    opacity: .55;
    cursor: not-allowed;
    transform: none;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    background: rgba(15, 23, 42, .65);
    border: 1px solid rgba(148, 163, 184, .2);
    text-decoration: none;
}

.btn-back:hover {
    color: #42d9d0;
    background: rgba(66, 217, 208, .07);
    border-color: rgba(66, 217, 208, .3);
}

.form-check-input {
    background-color: #0f172a;
    border-color: rgba(148, 163, 184, .3);
}

.form-check-input:checked {
    background-color: #42d9d0;
    border-color: #42d9d0;
}

.register-error {
    color: #fecaca;
    background: rgba(239, 68, 68, .1);
    border: 1px solid rgba(239, 68, 68, .22);
    border-radius: .55rem;
    padding: .8rem 1rem;
    margin-bottom: 1rem;
    font-size: .88rem;
}

.live-error {
    color: #fca5a5;
    font-size: .82rem;
    margin-top: .35rem;
}

.live-success {
    color: #86efac;
    font-size: .82rem;
    margin-top: .35rem;
}

@media (max-width: 768px) {
    .register-page {
        align-items: flex-start;
        padding: 1.25rem .65rem 2rem;
    }

    .glass-card {
        padding: 1.35rem;
        border-radius: .7rem;
    }

    .app-logo {
        font-size: 1.65rem;
    }
}

@media (max-width: 480px) {
    .register-page {
        padding: 1rem .45rem 1.5rem;
    }

    .glass-card {
        padding: 1rem;
    }

    .app-logo {
        font-size: 1.5rem;
    }

    .btn-register,
    .btn-back {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .btn-register,
    .btn-back,
    .form-control,
    .form-select {
        transition: none !important;
    }
}
</style>

<div class="register-page">

    <div class="auth-wrapper">

        <div class="glass-card" id="tilt-card">

            @if ($errors->any())
                <div class="register-error">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="logo-wrapper">
                <div class="app-logo">{{ config('app.name') }}</div>
                <div class="logo-subtitle">Create your FileIplay account</div>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                        autocomplete="username"
                    >

                    @error('name')
                        <div class="live-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                        value="{{ old('email') }}"
                        autocomplete="email"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        required
                        autocomplete="new-password"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Recovery Code</label>
                    <input
                        type="text"
                        name="recovery_code"
                        class="form-control"
                        required
                    >
                    <small class="text-muted">Write this down safely.</small>
                </div>

                @php $timezones = \DateTimeZone::listIdentifiers(); @endphp

                <div class="mb-3">
                    <label class="form-label">Timezone</label>

                    <select name="timezone" id="timezone" class="form-select" required>
                        <option disabled selected>Select timezone</option>

                        @foreach($timezones as $timezone)
                            <option value="{{ $timezone }}">{{ $timezone }}</option>
                        @endforeach
                    </select>

                    <small id="detected-timezone"
                           class="text-info d-block mt-2"
                           style="opacity:0.8;"></small>

                    <button type="button"
                            id="use-my-timezone"
                            class="btn btn-sm btn-back mt-2">
                        Use My Timezone
                    </button>

                    <input type="hidden"
                           name="detected_timezone"
                           id="detected_timezone">
                </div>

                <div class="mb-3">
                    <label for="invite_code" class="form-label">Invite Code {{ config('app.invite_only') ? '' : '(optional)' }}</label>
                    <input id="invite_code" type="text" name="invite_code" maxlength="255"
                           value="{{ old('invite_code', request('invite_code')) }}"
                           class="form-control @error('invite_code') is-invalid @enderror"
                           @required(config('app.invite_only'))>
                    @error('invite_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                        <div>
                            <label class="form-label mb-0">Email Notifications</label>
                            <div class="small text-muted">
                                Receive login reminders and important updates
                            </div>
                        </div>

                        <div class="form-check form-switch m-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="subscribed"
                                value="1"
                                checked
                            >
                        </div>

                    </div>
                </div>

                <div class="d-flex gap-3 mt-3 flex-wrap">
                    <button type="submit" class="btn btn-register">
                        REGISTER
                    </button>

                    <a href="{{ route('login') }}" class="btn btn-back">
                        Back To Login
                    </a>
                </div>

            </form>

        </div>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const timezoneSelect = document.getElementById("timezone");
    const detectedText = document.getElementById("detected-timezone");
    const hiddenInput = document.getElementById("detected_timezone");
    const useBtn = document.getElementById("use-my-timezone");

    let detectedTimezone = null;

    try {
        detectedTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (detectedTimezone) {
            detectedText.innerHTML =
                "Detected timezone: <strong>" + detectedTimezone + "</strong>";

            hiddenInput.value = detectedTimezone;
        }

    } catch (e) {
        detectedText.textContent = "Could not detect timezone.";
    }

    useBtn.addEventListener("click", function () {
        if (!detectedTimezone) return;

        const option = timezoneSelect.querySelector(
            `option[value="${detectedTimezone}"]`
        );

        if (option) {
            timezoneSelect.value = detectedTimezone;
        }
    });

});
</script>

<script>
const nameInput = document.querySelector('input[name="name"]');
const registerBtn = document.querySelector('.btn-register');

if (nameInput && registerBtn) {

    nameInput.addEventListener('input', function () {
        let existing = document.getElementById('name-error-live');

        if (existing) existing.remove();

        registerBtn.disabled = false;
    });

    nameInput.addEventListener('blur', function () {

        if (!this.value) return;

        fetch(`/check-username?name=${encodeURIComponent(this.value)}`)
            .then(res => res.json())
            .then(data => {

                let existing = document.getElementById('name-error-live');

                if (existing) existing.remove();

                const div = document.createElement('div');
                div.id = 'name-error-live';
                div.style.fontSize = '13px';
                div.style.marginTop = '5px';

                if (data.exists) {
                    div.className = 'live-error';
                    div.innerText = 'Username already taken';
                    registerBtn.disabled = true;
                } else {
                    div.className = 'live-success';
                    div.innerText = 'Username available';
                }

                nameInput.parentNode.appendChild(div);
            });
    });
}
</script>

<script>
const emailInput = document.querySelector('input[name="email"]');
const registerBtnEmail = document.querySelector('.btn-register');

if (emailInput && registerBtnEmail) {

    emailInput.addEventListener('input', function () {
        let existing = document.getElementById('email-error-live');

        if (existing) existing.remove();

        registerBtnEmail.disabled = false;
    });

    emailInput.addEventListener('blur', function () {

        if (!this.value) return;

        fetch(`/check-email?email=${encodeURIComponent(this.value)}`)
            .then(res => res.json())
            .then(data => {

                let existing = document.getElementById('email-error-live');

                if (existing) existing.remove();

                const div = document.createElement('div');
                div.id = 'email-error-live';
                div.style.fontSize = '13px';
                div.style.marginTop = '5px';

                if (data.exists) {
                    div.className = 'live-error';
                    div.innerText = 'Email already registered';
                    registerBtnEmail.disabled = true;
                } else {
                    div.className = 'live-success';
                    div.innerText = 'Email available';
                }

                emailInput.parentNode.appendChild(div);
            });
    });
}
</script>

@endsection
