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
        radial-gradient(circle at 50% 0%, var(--theme-teal-soft, rgba(66, 217, 208, .07)), transparent 34%),
        linear-gradient(135deg, var(--theme-surface, #070c15) 0%, var(--theme-surface, #0a0f1b) 55%, var(--theme-surface, #060b13) 100%);
    color:  var(--theme-text, #e7edf5);
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
    position: relative;
    text-align: center;
    margin-bottom: 1.6rem;
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

.form-label {
    color: var(--theme-text, #cbd5e1);
    font-size: var(--site-font-body, 13px);
    font-weight: 600;
    margin-bottom: .4rem;
}

.form-control,
.form-select {
    width: 100%;
    min-height: 46px;
    box-sizing: border-box;
    color: var(--theme-text, #e2e8f0) !important;
    background: var(--theme-control, rgba(10,15,27,.78)) !important;
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .2)) !important;
    border-radius: .55rem !important;
    padding: .65rem .85rem !important;
    font-size: var(--site-font-body, 13px);
    box-shadow: none !important;
    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-control:focus,
.form-select:focus {
    color: var(--theme-text, #fff) !important;
    border-color: var(--theme-teal-border, rgba(66, 217, 208, .58)) !important;
    box-shadow: 0 0 0 .2rem var(--theme-shadow, rgba(66, 217, 208, .08)) !important;
    outline: none !important;
}

.form-control::placeholder {
    color: var(--theme-muted, #64748b);
}

.form-select option {
    color: var(--theme-text, #e2e8f0);
    background: var(--theme-control, #0a0f1b);
}

.text-muted {
    color: var(--theme-muted, #94a3b8) !important;
}

.text-info {
    color: var(--theme-teal-text, #42d9d0) !important;
}

.invalid-feedback {
    color: var(--theme-red-text, #fca5a5);
}

.btn-register,
.btn-back {
    min-height: 43px;
    padding: .55rem 1rem;
    border-radius: .55rem;
    font-size: var(--site-font-body, 13px);
    font-weight: 700;
    transition: all .18s ease;
}

.btn-register {
    color: var(--theme-on-action, #062a2b);
    background: var(--theme-teal-action, #42d9d0);
    border: 1px solid var(--theme-teal-border, #42d9d0);
    box-shadow: 0 5px 16px var(--theme-shadow, rgba(66, 217, 208, .12));
}

.btn-register:hover,
.btn-register:focus {
    color: var(--theme-on-action, #031b1c);
    background: var(--theme-teal-action, #67e3dc);
    border-color: var(--theme-teal-border, #67e3dc);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px var(--theme-shadow, rgba(66, 217, 208, .18));
}

.btn-register:disabled {
    opacity: .45;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--theme-text, #cbd5e1);
    background: var(--theme-surface, rgba(10,15,27,.65));
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .2));
    text-decoration: none;
}

.btn-back:hover {
    color: var(--theme-teal-text, #42d9d0);
    background: var(--theme-teal-soft, rgba(66, 217, 208, .07));
    border-color: var(--theme-teal-border, rgba(66, 217, 208, .3));
}

.form-check-input {
    background-color: var(--theme-control, #0a0f1b);
    border-color: var(--theme-border, rgba(148, 163, 184, .3));
}

.form-check-input:checked {
    background-color: var(--theme-teal-soft, #42d9d0);
    border-color: var(--theme-teal-border, #42d9d0);
}

.register-error {
    color: var(--theme-text, #fecaca);
    background: var(--theme-red-soft, rgba(239, 68, 68, .1));
    border: 1px solid var(--theme-red-border, rgba(239, 68, 68, .22));
    border-radius: .55rem;
    padding: .8rem 1rem;
    margin-bottom: 1rem;
    font-size: var(--site-font-body, 13px);
}

.live-error {
    color: var(--theme-red-text, #fca5a5);
    font-size: var(--site-font-body, 13px);
    margin-top: .35rem;
}

.live-success {
    color: var(--theme-green-text, #86efac);
    font-size: var(--site-font-body, 13px);
    margin-top: .35rem;
}

.validation-summary {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    margin-top: .8rem;
}

.validation-item {
    padding: .3rem .55rem;
    border-radius: .4rem;
    color: var(--theme-muted, #94a3b8);
    background: var(--theme-surface, rgba(10,15,27,.6));
    border: 1px solid var(--theme-border, rgba(148, 163, 184, .15));
    font-size: var(--site-font-body, 13px);
}

.validation-item.valid {
    color: var(--theme-green-text, #86efac);
    background: var(--theme-green-soft, rgba(34, 197, 94, .06));
    border-color: var(--theme-green-border, rgba(34, 197, 94, .22));
}

.validation-item.invalid {
    color: var(--theme-red-text, #fca5a5);
    background: var(--theme-red-soft, rgba(239, 68, 68, .06));
    border-color: var(--theme-red-border, rgba(239, 68, 68, .2));
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

        <div class="glass-card">

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

                <div class="app-logo">
                    {{ config('app.name') }}
                </div>

                <div class="logo-subtitle">
                    Create your FileIplay account
                </div>

            </div>

            <form
                id="registration-form"
                method="POST"
                action="{{ route('register') }}"
            >

                @csrf

                {{-- Username --}}
                <div class="mb-3">

                    <label for="register-name" class="form-label">
                        Username
                    </label>

                    <input
                        id="register-name"
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        maxlength="20"
                        required
                        autocomplete="username"
                        placeholder="Choose a username"
                    >

                    <small class="text-muted">
                        Your username must be available before you can register.
                    </small>

                    <div
                        id="name-validation"
                        class="live-error"
                        style="display:none;"
                    ></div>

                    @error('name')
                        <div class="live-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Email --}}
                <div class="mb-3">

                    <label for="register-email" class="form-label">
                        Email
                    </label>

                    <input
                        id="register-email"
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="Enter your email address"
                    >

                    <small class="text-muted">
                        Your email address must be valid and not already registered.
                    </small>

                    <div
                        id="email-validation"
                        class="live-error"
                        style="display:none;"
                    ></div>

                    @error('email')
                        <div class="live-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Password --}}
                <div class="mb-3">

                    <label for="register-password" class="form-label">
                        Password
                    </label>

                    <input
                        id="register-password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Choose a password"
                    >

                    @error('password')
                        <div class="live-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Password Confirmation --}}
                <div class="mb-3">

                    <label for="password_confirmation" class="form-label">
                        Confirm Password
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        required
                        autocomplete="new-password"
                        placeholder="Enter your password again"
                    >

                </div>

                @if(config('auth.email_registration'))
                    <div class="alert alert-success">We’ll email you an activation link. Confirm your email before signing in. If you forget your password, we’ll send you a reset link. Check your spam folder as well</div>
                @else
                {{-- Recovery Code --}}
                <div class="mb-3">

                    <label for="recovery_code" class="form-label">
                        Recovery Code
                    </label>

                    <input
                        id="recovery_code"
                        type="text"
                        name="recovery_code"
                        class="form-control @error('recovery_code') is-invalid @enderror"
                        value="{{ old('recovery_code') }}"
                        minlength="6"
                        maxlength="20"
                        required
                        autocomplete="off"
                        placeholder="Minimum 6 characters"
                    >

                    <small class="text-muted">Use 6–20 characters. Keep this code safe — you will need it if you forget your password.</small>

                    <div
                        id="recovery-validation"
                        class="live-error"
                        style="display:none;"
                    ></div>

                    @error('recovery_code')
                        <div class="live-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                @endif

                {{-- Live validation summary --}}
                <div class="validation-summary">

                    <span
                        id="username-status"
                        class="validation-item"
                    >
                        Username
                    </span>

                    <span
                        id="email-status"
                        class="validation-item"
                    >
                        Email
                    </span>

                    @unless(config('auth.email_registration'))
                    <span
                        id="recovery-status"
                        class="validation-item"
                    >
                        Recovery Code
                    </span>
                    @endunless

                </div>

                {{-- Timezone --}}
                @php
                    $timezones = \DateTimeZone::listIdentifiers();
                @endphp

                <div class="mb-3 mt-3">

                    <label for="timezone" class="form-label">
                        Timezone
                    </label>

                    <select
                        name="timezone"
                        id="timezone"
                        class="form-select @error('timezone') is-invalid @enderror"
                        required
                    >

                        <option value="" disabled {{ old('timezone') ? '' : 'selected' }}>
                            Select timezone
                        </option>

                        @foreach($timezones as $timezone)

                            <option
                                value="{{ $timezone }}"
                                @selected(old('timezone') === $timezone)
                            >
                                {{ $timezone }}
                            </option>

                        @endforeach

                    </select>

                    <small
                        id="detected-timezone"
                        class="text-info d-block mt-2"
                        style="opacity:.8;"
                    ></small>

                    <button
                        type="button"
                        id="use-my-timezone"
                        class="btn btn-sm btn-back mt-2"
                    >
                        Use My Timezone
                    </button>

                    <input
                        type="hidden"
                        name="detected_timezone"
                        id="detected_timezone"
                    >

                    @error('timezone')
                        <div class="live-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Invite Code --}}
                <div class="mb-3">

                    <label for="invite_code" class="form-label">
                        Invite Code
                        {{ config('app.invite_only') ? '' : '(optional)' }}
                    </label>

                    <input
                        id="invite_code"
                        type="text"
                        name="invite_code"
                        maxlength="255"
                        value="{{ old('invite_code', request('invite_code')) }}"
                        class="form-control @error('invite_code') is-invalid @enderror"
                        @required(config('app.invite_only'))
                    >

                    @error('invite_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Email Subscription --}}
                <div class="mb-4">

                    <input
                        type="hidden"
                        name="subscribed"
                        value="0"
                    >

                    @include('partials.email-subscription-switch', [
                        'emailToggleId' => 'registration-email-subscribe',
                        'emailSubscribed' => (bool) old('subscribed', false),
                        'emailHelpId' => 'registration-email-help',
                    ])

                    <p
                        id="registration-email-help"
                        class="small text-muted mt-2 mb-0"
                    >
                        Optional: receive marketing emails, site news and updates
                        from us. You can unsubscribe at any time from your profile.
                    </p>

                    @error('subscribed')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                {{-- Actions --}}
                <div class="d-flex gap-3 mt-3 flex-wrap">

                    <button
                        id="register-button"
                        type="submit"
                        class="btn btn-register"
                        disabled
                    >
                        REGISTER
                    </button>

                    <a
                        href="{{ route('login') }}"
                        class="btn btn-back"
                    >
                        Back To Login
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('registration-form');

    const nameInput = document.getElementById('register-name');
    const emailInput = document.getElementById('register-email');
    const recoveryInput = document.getElementById('recovery_code');

    const nameValidation = document.getElementById('name-validation');
    const emailValidation = document.getElementById('email-validation');
    const recoveryValidation = document.getElementById('recovery-validation');

    const usernameStatus = document.getElementById('username-status');
    const emailStatus = document.getElementById('email-status');
    const recoveryStatus = document.getElementById('recovery-status');

    const registerButton = document.getElementById('register-button');

    if (
        !form ||
        !nameInput ||
        !emailInput ||
        !registerButton
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation State
    |--------------------------------------------------------------------------
    */

    const validation = {
        username: false,
        email: false,
        recovery: !recoveryInput,
        usernameChecking: false,
        emailChecking: false
    };

    /*
    |--------------------------------------------------------------------------
    | Update REGISTER Button
    |--------------------------------------------------------------------------
    */

    function updateRegisterButton() {

        const valid =
            validation.username === true &&
            validation.email === true &&
            validation.recovery === true &&
            validation.usernameChecking === false &&
            validation.emailChecking === false;

        registerButton.disabled = !valid;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Badge
    |--------------------------------------------------------------------------
    */

    function setStatus(element, state, label) {

        if (!element) {
            return;
        }

        element.classList.remove('valid', 'invalid');

        if (state === true) {
            element.classList.add('valid');
            element.textContent = '✓ ' + label;
            return;
        }

        if (state === false) {
            element.classList.add('invalid');
            element.textContent = '✕ ' + label;
            return;
        }

        element.textContent = label;
    }

    /*
    |--------------------------------------------------------------------------
    | Field Message
    |--------------------------------------------------------------------------
    */

    function showMessage(element, message, success = false) {

        if (!element) {
            return;
        }

        element.textContent = message;
        element.className = success
            ? 'live-success'
            : 'live-error';

        element.style.display = 'block';
    }

    function hideMessage(element) {

        if (!element) {
            return;
        }

        element.textContent = '';
        element.style.display = 'none';
    }

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    */

    let usernameRequest = 0;

    nameInput.addEventListener('input', function () {

        usernameRequest++;

        validation.username = false;
        validation.usernameChecking = false;

        hideMessage(nameValidation);
        setStatus(usernameStatus, null, 'Username');

        updateRegisterButton();
    });

    nameInput.addEventListener('blur', async function () {

        const username = nameInput.value.trim();

        const requestId = ++usernameRequest;

        validation.username = false;

        hideMessage(nameValidation);

        if (!username) {

            setStatus(usernameStatus, false, 'Username');
            updateRegisterButton();

            return;
        }

        validation.usernameChecking = true;

        usernameStatus.classList.remove('valid', 'invalid');
        usernameStatus.textContent = 'Checking username...';

        updateRegisterButton();

        try {

            const response = await fetch(
                `/check-username?name=${encodeURIComponent(username)}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to check username');
            }

            const data = await response.json();

            /*
             * Ignore an old response if another check started.
             */
            if (
                requestId !== usernameRequest ||
                nameInput.value.trim() !== username
            ) {
                return;
            }

            if (data.exists) {

                validation.username = false;

                showMessage(
                    nameValidation,
                    'Username already taken'
                );

                setStatus(
                    usernameStatus,
                    false,
                    'Username'
                );

            } else {

                validation.username = true;

                showMessage(
                    nameValidation,
                    'Username available',
                    true
                );

                setStatus(
                    usernameStatus,
                    true,
                    'Username'
                );
            }

        } catch (error) {

            if (requestId !== usernameRequest) {
                return;
            }

            validation.username = false;

            showMessage(
                nameValidation,
                'Unable to check username. Please try again.'
            );

            setStatus(
                usernameStatus,
                false,
                'Username'
            );

        } finally {

            if (requestId === usernameRequest) {
                validation.usernameChecking = false;
                updateRegisterButton();
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    let emailRequest = 0;

    emailInput.addEventListener('input', function () {

        emailRequest++;

        validation.email = false;
        validation.emailChecking = false;

        hideMessage(emailValidation);
        setStatus(emailStatus, null, 'Email');

        updateRegisterButton();
    });

    emailInput.addEventListener('blur', async function () {

        const email = emailInput.value.trim();

        const requestId = ++emailRequest;

        validation.email = false;

        hideMessage(emailValidation);

        if (!email) {

            setStatus(emailStatus, false, 'Email');
            updateRegisterButton();

            return;
        }

        if (!emailInput.checkValidity()) {

            showMessage(
                emailValidation,
                'Please enter a valid email address'
            );

            setStatus(
                emailStatus,
                false,
                'Email'
            );

            updateRegisterButton();

            return;
        }

        validation.emailChecking = true;

        emailStatus.classList.remove('valid', 'invalid');
        emailStatus.textContent = 'Checking email...';

        updateRegisterButton();

        try {

            const response = await fetch(
                `/check-email?email=${encodeURIComponent(email)}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to check email');
            }

            const data = await response.json();

            /*
             * Ignore stale responses.
             */
            if (
                requestId !== emailRequest ||
                emailInput.value.trim() !== email
            ) {
                return;
            }

            if (data.exists) {

                validation.email = false;

                showMessage(
                    emailValidation,
                    'Email already registered'
                );

                setStatus(
                    emailStatus,
                    false,
                    'Email'
                );

            } else {

                validation.email = true;

                showMessage(
                    emailValidation,
                    'Email available',
                    true
                );

                setStatus(
                    emailStatus,
                    true,
                    'Email'
                );
            }

        } catch (error) {

            if (requestId !== emailRequest) {
                return;
            }

            validation.email = false;

            showMessage(
                emailValidation,
                'Unable to check email. Please try again.'
            );

            setStatus(
                emailStatus,
                false,
                'Email'
            );

        } finally {

            if (requestId === emailRequest) {
                validation.emailChecking = false;
                updateRegisterButton();
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Recovery Code
    |--------------------------------------------------------------------------
    */

    function validateRecoveryCode(showEmptyError = false) {
        if (!recoveryInput) {
            validation.recovery = true;
            updateRegisterButton();
            return true;
        }

        const recoveryCode = recoveryInput.value.trim();

        validation.recovery = false;

        hideMessage(recoveryValidation);

        if (!recoveryCode) {

            setStatus(
                recoveryStatus,
                null,
                'Recovery Code'
            );

            if (showEmptyError) {
                showMessage(
                    recoveryValidation,
                    'Recovery code is required.'
                );

                setStatus(
                    recoveryStatus,
                    false,
                    'Recovery Code'
                );
            }

            updateRegisterButton();

            return false;
        }

        if (recoveryCode.length < 6) {

            showMessage(
                recoveryValidation,
                'Recovery code must be at least 6 characters.'
            );

            setStatus(
                recoveryStatus,
                false,
                'Recovery Code'
            );

            updateRegisterButton();

            return false;
        }

        validation.recovery = true;

        showMessage(
            recoveryValidation,
            'Recovery code is valid',
            true
        );

        setStatus(
            recoveryStatus,
            true,
            'Recovery Code'
        );

        updateRegisterButton();

        return true;
    }

    recoveryInput?.addEventListener('input', function () {
        validateRecoveryCode(false);
    });

    recoveryInput?.addEventListener('blur', function () {
        validateRecoveryCode(true);
    });

    /*
    |--------------------------------------------------------------------------
    | Final Submit Protection
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        validateRecoveryCode(true);

        const allLiveChecksValid =
            validation.username === true &&
            validation.email === true &&
            validation.recovery === true &&
            validation.usernameChecking === false &&
            validation.emailChecking === false;

        if (!allLiveChecksValid) {

            event.preventDefault();

            updateRegisterButton();

            if (!validation.username) {
                nameInput.focus();
                return;
            }

            if (!validation.email) {
                emailInput.focus();
                return;
            }

            if (!validation.recovery) {
                recoveryInput.focus();
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Initial State
    |--------------------------------------------------------------------------
    */

    registerButton.disabled = true;

    /*
     * If Laravel returned the form with old values after another
     * validation error, validate the recovery code immediately.
     *
     * Username/email availability must be checked again.
     */
    validateRecoveryCode(false);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Timezone Detection
    |--------------------------------------------------------------------------
    */

    const timezoneSelect = document.getElementById('timezone');
    const detectedText = document.getElementById('detected-timezone');
    const hiddenInput = document.getElementById('detected_timezone');
    const useButton = document.getElementById('use-my-timezone');

    if (
        !timezoneSelect ||
        !detectedText ||
        !hiddenInput ||
        !useButton
    ) {
        return;
    }

    let detectedTimezone = null;

    try {

        detectedTimezone =
            Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (detectedTimezone) {

            detectedText.textContent =
                'Detected timezone: ' + detectedTimezone;

            hiddenInput.value = detectedTimezone;
        }

    } catch (error) {

        detectedText.textContent =
            'Could not detect timezone.';
    }

    useButton.addEventListener('click', function () {

        if (!detectedTimezone) {
            return;
        }

        const option = Array.from(timezoneSelect.options)
            .find(option => option.value === detectedTimezone);

        if (option) {

            timezoneSelect.value = detectedTimezone;

            detectedText.textContent =
                'Timezone selected: ' + detectedTimezone;
        }
    });
});
</script>

@endsection
