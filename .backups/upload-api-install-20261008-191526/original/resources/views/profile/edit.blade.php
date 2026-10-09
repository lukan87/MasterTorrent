@extends('layouts.app')

@section('content')

<div class="profile-settings-page">

    {{-- =====================================================
         PROFILE HERO
    ====================================================== --}}
    <div
        class="profile-settings-hero"
        id="coverPreview"
        @if($user->cover)
            style="background-image: url('{{ $user->cover }}');"
        @endif
    >

        <div class="profile-settings-overlay"></div>

        <div class="profile-settings-hero-content">

            <div class="profile-avatar-wrap">

                <img
                    id="profilePreview"
                    src="{{ $user->profile_image ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0f766e&color=fff&size=200' }}"
                    alt="{{ $user->name }}"
                    class="profile-settings-avatar"
                    onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0f766e&color=fff&size=200'"
                >

                <div class="profile-avatar-status">
                    <i class="bi bi-pencil-fill"></i>
                </div>

            </div>


            <div class="profile-settings-user">

                <div class="profile-settings-label">
                    <i class="bi bi-person-gear"></i>
                    PROFILE SETTINGS
                </div>

                <h1>{{ $user->name }}</h1>

                <p>
                    Personalise your FileIplay profile, account details and appearance.
                </p>

            </div>

        </div>

    </div>



    {{-- =====================================================
         FORM
    ====================================================== --}}
    <form
        method="POST"
        action="{{ route('profile.update', [$user->id, $user->name]) }}"
        class="profile-settings-form"
    >

        @csrf
        @method('PUT')


        <div class="settings-layout">


            {{-- =================================================
                 MAIN CONTENT
            ================================================== --}}
            <div class="settings-main">


                {{-- =================================================
                     ACCOUNT
                ================================================== --}}
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>
                            <h2>Account</h2>
                            <p>Your basic FileIplay account information.</p>
                        </div>

                    </div>


                    <div class="settings-card-body">


                        {{-- USERNAME --}}
                        @if (
                            Auth::check() &&
                            Auth::user()->user_class >= \App\Models\UserClass::ADMIN
                        )

                            <div class="settings-field">

                                <label for="name">
                                    Username
                                </label>

                                <div class="settings-input-wrap">

                                    <span class="settings-input-icon">
                                        <i class="bi bi-person"></i>
                                    </span>

                                    @if (Auth::id() !== $user->id)

                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            class="form-control settings-input @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}"
                                            required
                                        >

                                    @else

                                        <input
                                            type="text"
                                            id="name"
                                            class="form-control settings-input"
                                            value="{{ $user->name }}"
                                            readonly
                                        >

                                    @endif

                                </div>


                                @if(Auth::id() === $user->id)

                                    <div class="settings-help">
                                        <i class="bi bi-lock-fill"></i>
                                        Your username cannot be changed here.
                                    </div>

                                @endif


                                @error('name')
                                    <div class="settings-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        @endif



                        {{-- EMAIL --}}
                        <div class="settings-field">

                            <label for="email">
                                Email address
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-envelope"></i>
                                </span>

                                @if (
                                    Auth::check() &&
                                    Auth::user()->user_class >= \App\Models\UserClass::ADMIN
                                )

                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="form-control settings-input @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}"
                                        required
                                    >

                                @else

                                    <input
                                        type="email"
                                        id="email"
                                        class="form-control settings-input"
                                        value="{{ $user->email }}"
                                        readonly
                                    >

                                @endif

                            </div>


                            @if (
                                !Auth::check() ||
                                Auth::user()->user_class < \App\Models\UserClass::ADMIN
                            )

                                <div class="settings-help">
                                    <i class="bi bi-shield-lock"></i>
                                    Contact staff if you need to change your email address.
                                </div>

                            @endif


                            @error('email')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- TIMEZONE --}}
                        <div class="settings-field mb-0">

                            <label for="timezone">
                                Timezone
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-globe2"></i>
                                </span>

                                <select
                                    id="timezone"
                                    name="timezone"
                                    class="form-select settings-input settings-select"
                                >

                                    @foreach(timezone_identifiers_list() as $tz)

                                        <option
                                            value="{{ $tz }}"
                                            {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}
                                        >
                                            {{ $tz }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <div class="settings-help">
                                <i class="bi bi-clock"></i>
                                Used to display dates and times correctly for you.
                            </div>

                        </div>

                    </div>

                </section>



                {{-- =================================================
                     PROFILE APPEARANCE
                ================================================== --}}
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="bi bi-palette2"></i>
                        </div>

                        <div>
                            <h2>Profile Appearance</h2>
                            <p>Customise how your profile looks to other members.</p>
                        </div>

                    </div>


                    <div class="settings-card-body">


                        {{-- PROFILE IMAGE --}}
                        <div class="settings-field">

                            <label for="profilePreviewInput">
                                Profile image
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-person-circle"></i>
                                </span>

                                <input
                                    type="text"
                                    id="profilePreviewInput"
                                    name="profile_image_url"
                                    class="form-control settings-input @error('profile_image_url') is-invalid @enderror"
                                    value="{{ old('profile_image_url', $user->profile_image) }}"
                                    placeholder="https://example.com/avatar.jpg"
                                >

                            </div>

                            <div class="settings-help">
                                <i class="bi bi-info-circle"></i>
                                Paste a direct URL to your profile image.
                            </div>

                            @error('profile_image_url')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- COVER --}}
                        <div class="settings-field">

                            <label for="coverPreviewInput">
                                Cover image
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-image"></i>
                                </span>

                                <input
                                    type="text"
                                    id="coverPreviewInput"
                                    name="cover"
                                    class="form-control settings-input @error('cover') is-invalid @enderror"
                                    value="{{ old('cover', $user->cover) }}"
                                    placeholder="https://example.com/cover.jpg"
                                >

                            </div>

                            <div class="settings-help">
                                <i class="bi bi-info-circle"></i>
                                This image appears across the top of your profile.
                            </div>

                            @error('cover')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- BACKGROUND --}}
                        <div class="settings-field mb-0">

                            <label for="background">
                                Profile background
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-window-fullscreen"></i>
                                </span>

                                <input
                                    type="text"
                                    id="background"
                                    name="background"
                                    class="form-control settings-input @error('background') is-invalid @enderror"
                                    value="{{ old('background', $user->background) }}"
                                    placeholder="https://example.com/background.jpg"
                                >

                            </div>

                            <div class="settings-help">
                                <i class="bi bi-info-circle"></i>
                                Optional background image used on your profile page.
                            </div>

                            @error('background')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </section>



                {{-- =================================================
                     ABOUT ME
                ================================================== --}}
                <section class="settings-card">

                    <div class="settings-card-header">

                        <div class="settings-icon">
                            <i class="bi bi-chat-square-text"></i>
                        </div>

                        <div>
                            <h2>About Me</h2>
                            <p>Tell other members a little about yourself.</p>
                        </div>

                    </div>


                    <div class="settings-card-body">

                        <div class="settings-field mb-0">

                            <label for="infoField">
                                Profile information
                            </label>

                            <textarea
                                id="infoField"
                                name="info"
                                class="form-control settings-input settings-textarea auto-grow @error('info') is-invalid @enderror"
                                rows="4"
                                placeholder="Write something about yourself..."
                            >{{ old('info', $user->info) }}</textarea>

                            <div class="settings-help">
                                <i class="bi bi-eye"></i>
                                This information may be visible to other FileIplay members.
                            </div>

                            @error('info')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </section>

            </div>



            {{-- =================================================
                 RIGHT SIDEBAR
            ================================================== --}}
            <aside class="settings-sidebar">

                {{-- =================================================
                     SECURITY & ADMINISTRATION
                ================================================== --}}
                @if (
                    Auth::check() &&
                    (
                        Auth::user()->user_class >= \App\Models\UserClass::ADMIN ||
                        Auth::user()->name === $user->name
                    )
                )

                    <div class="settings-side-card sidebar-security-card">

                        <div class="sidebar-card-title">

                            <div class="sidebar-security-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>

                            <div>
                                <strong>Security & Administration</strong>
                                <span>Account management</span>
                            </div>

                        </div>


                        {{-- RECOVERY CODE --}}
                        <div class="sidebar-settings-field">

                            <label for="recovery_code">
                                <i class="bi bi-key"></i>
                                Recovery Code
                            </label>

                            <div class="settings-input-wrap">

                                <span class="settings-input-icon">
                                    <i class="bi bi-key"></i>
                                </span>

                                <input
                                    type="text"
                                    id="recovery_code"
                                    name="recovery_code"
                                    class="form-control settings-input sidebar-settings-input"
                                    placeholder="Enter new code"
                                >

                            </div>

                            <div class="sidebar-security-help">
                                <i class="bi bi-lock"></i>
                                Keep your recovery code private.
                            </div>

                        </div>

                        @if(auth()->id() === $user->id)
                        <div class="sidebar-settings-field" id="tracker-passkey">
                            <label><i class="bi bi-key"></i> Tracker Passkey</label>
                            <details class="mb-3">
                                <summary class="text-info">Show tracker passkey</summary>
                                <code class="d-block mt-2 text-break">{{ $user->passkey }}</code>
                            </details>
                            <details @if($errors->getBag('passkey')->any()) open @endif>
                                <summary class="text-warning">Regenerate tracker passkey</summary>
                                <p class="sidebar-security-help mt-2">Your old tracker URLs will stop working. Update your torrent clients after regenerating.</p>
                                <label for="passkey-current-password">Your account password</label>
                                <input type="password" id="passkey-current-password" name="current_password" form="regenerate-passkey-form" autocomplete="current-password" required class="form-control settings-input sidebar-settings-input @if($errors->getBag('passkey')->has('current_password')) is-invalid @endif" aria-describedby="passkey-password-help passkey-password-error">
                                <small id="passkey-password-help" class="sidebar-security-help d-block mt-2">Enter your signed-in account password to confirm this change.</small>
                                <div id="passkey-password-error" class="text-danger mt-2" role="alert">{{ $errors->getBag('passkey')->first('current_password') }}</div>
                                <button type="submit" form="regenerate-passkey-form" class="btn btn-outline-warning mt-3">Confirm regeneration</button>
                            </details>
                        </div>



                        {{-- PASSWORD CHANGE --}}
                        <div class="sidebar-settings-field">
                            <label>Change Password</label>
                            <input type="password" name="current_password" class="form-control settings-input sidebar-settings-input mb-2" placeholder="Current Password">
                            <input type="password" name="new_password" class="form-control settings-input sidebar-settings-input mb-2" placeholder="New Password">
                            <input type="password" name="new_password_confirmation" class="form-control settings-input sidebar-settings-input" placeholder="Confirm New Password">
                        </div>

                        @endif

                                @if((int) auth()->id() === (int) $user->id)
            <section class="settings-panel mb-4 p-3">
                <h2 class="h5">Anonymous publishing</h2>
                <div class="form-check form-switch">
                    <input type="hidden" name="anonymous" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="anonymous-default" name="anonymous" value="1" @checked(old('anonymous', $user->anonymous)) aria-describedby="anonymous-default-help">
                    <label class="form-check-label" for="anonymous-default">Publish anonymously by default</label>
                </div>
                <p id="anonymous-default-help" class="form-text mb-0">Applies to future uploads. You can change the choice on each upload. Other members see Anonymous; you and moderators retain ownership access. Existing uploads are unchanged.</p>
            </section>
        @endif

                    </div>

                @endif

            </aside>

        </div>




        {{-- =================================================
             SAVE BAR
        ================================================== --}}


        <div class="settings-save-bar">

            <div class="save-bar-text">

                <i class="bi bi-cloud-check"></i>

                <div>
                    <strong>Save your changes</strong>
                    <span>Review your settings before updating your profile.</span>
                </div>

            </div>


            <div class="save-bar-actions">

                <a
                    href="{{ url()->previous() }}"
                    class="btn settings-cancel-btn"
                >
                    <i class="bi bi-arrow-left"></i>
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn settings-save-btn"
                >
                    <i class="bi bi-check2-circle"></i>
                    Save Changes
                </button>

            </div>

        </div>

    </form>

    @if(auth()->id() === $user->id)
        <form id="regenerate-passkey-form" method="POST" action="{{ route('profile.passkey.regenerate', $user->id) }}">
            @csrf
            @method('PATCH')
        </form>
    @endif

    @if(auth()->id() === $user->id)
    <!-- Recovery Code Modal -->
    <div class="modal fade" id="recoveryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Verify Recovery Code</h5></div>
                <div class="modal-body">
                    <input type="text" id="verification_recovery_code" name="verification_recovery_code" class="form-control" placeholder="Enter recovery code">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitPasswordChange()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>




<script>
document.addEventListener('DOMContentLoaded', function () {

    const profileInput = document.getElementById('profilePreviewInput');
    const profilePreview = document.getElementById('profilePreview');

    const coverInput = document.getElementById('coverPreviewInput');
    const coverPreview = document.getElementById('coverPreview');

    const textarea = document.getElementById('infoField');


    /*
    |--------------------------------------------------------------------------
    | Avatar live preview
    |--------------------------------------------------------------------------
    */

    if (profileInput) {

        profileInput.addEventListener('input', function () {

            const url = this.value.trim();

            if (!url) {
                return;
            }

            if (profilePreview) {
                profilePreview.src = url;
            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Cover live preview
    |--------------------------------------------------------------------------
    */

    if (coverInput && coverPreview) {

        coverInput.addEventListener('input', function () {

            const url = this.value.trim();

            if (url) {
                coverPreview.style.backgroundImage = `url("${url}")`;
            } else {
                coverPreview.style.backgroundImage = '';
            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Auto-grow About Me
    |--------------------------------------------------------------------------
    */

    if (textarea) {

        const autoResize = () => {

            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

        };

        textarea.addEventListener('input', autoResize);

        autoResize();

    }

});

function submitPasswordChange() {
    const code = document.getElementById('verification_recovery_code').value;
    const form = document.querySelector('form.profile-settings-form');
    const hiddenField = document.createElement('input');
    hiddenField.type = 'hidden';
    hiddenField.name = 'verification_recovery_code';
    hiddenField.value = code;
    form.appendChild(hiddenField);
    form.submit();
}

document.querySelector('form.profile-settings-form').addEventListener('submit', function(e) {
    const currentPass = this.elements.namedItem('current_password')?.value;
    const newPass = this.elements.namedItem('new_password')?.value;

    if (!@json((bool) config('auth.email_registration')) && currentPass && newPass) {
        e.preventDefault();
        var myModal = new bootstrap.Modal(document.getElementById('recoveryModal'));
        myModal.show();
    }
});
</script>



<style>

/* =========================================================
   PAGE
========================================================= */

.profile-settings-page {
    width: 100%;
    max-width: 1280px;

    margin: 0 auto;

    padding: 2rem 1rem 3rem;
}


/* =========================================================
   HERO
========================================================= */

.profile-settings-hero {
    position: relative;

    min-height: 280px;

    overflow: hidden;

    background-color: var(--theme-surface, #0a0f1b);
    background-size: cover;
    background-position: center;

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .14));
    border-radius: 18px;

    box-shadow:
        0 20px 50px var(--theme-shadow, rgba(0, 0, 0, .28)),
        inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .03));
}


.profile-settings-hero::before {
    content: "";

    position: absolute;
    inset: 0;

    background:
        radial-gradient(
            circle at 80% 10%,
            var(--theme-teal-soft, rgba(20, 184, 166, .20)),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            var(--theme-surface-alt, rgba(1,4,15,.35)),
            var(--theme-surface-alt, rgba(1,4,15,.85))
        );
}


.profile-settings-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            to top,
            rgba(1,4,15,.98) 0%,
            rgba(1,4,15,.62) 55%,
            rgba(1,4,15,.25) 100%
        );
}


.profile-settings-hero-content {
    position: absolute;
    z-index: 2;

    left: 40px;
    right: 40px;
    bottom: 30px;

    display: flex;
    align-items: flex-end;

    gap: 24px;
}


/* =========================================================
   HERO AVATAR
========================================================= */

.profile-avatar-wrap {
    position: relative;

    flex-shrink: 0;
}


.profile-settings-avatar {
    width: 118px;
    height: 118px;

    object-fit: cover;

    border-radius: 50%;

    border: 4px solid var(--theme-border, rgba(255, 255, 255, .92));

    background: var(--theme-surface, #0a0f1b);

    box-shadow:
        0 10px 30px var(--theme-shadow, rgba(0, 0, 0, .45)),
        0 0 0 5px var(--theme-shadow, rgba(20, 184, 166, .16));
}


.profile-avatar-status {
    position: absolute;

    right: 2px;
    bottom: 5px;

    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    color:  var(--theme-text, #fff);

    background: var(--theme-teal-soft, #0f766e);

    border: 3px solid var(--theme-border, #0f172a);
    border-radius: 50%;

    font-size: var(--site-font-small, 13px);
}


/* =========================================================
   HERO USER
========================================================= */

.profile-settings-user {
    padding-bottom: 5px;
}


.profile-settings-label {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    margin-bottom: 7px;

    color: var(--theme-teal-text, #5eead4);

    font-size: var(--site-font-small, 13px);
    font-weight: 800;

    letter-spacing: .12em;
}


.profile-settings-user h1 {
    margin: 0;

    color: var(--theme-text, #fff);

    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 800;

    letter-spacing: -.035em;
}


.profile-settings-user p {
    max-width: 600px;

    margin: 5px 0 0;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-body, 13px);
}


/* =========================================================
   FORM / LAYOUT
========================================================= */

.profile-settings-form {
    margin-top: 24px;
}


.settings-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        330px;

    gap: 24px;

    align-items: start;
}


.settings-main {
    display: flex;
    flex-direction: column;

    gap: 20px;
}


/* =========================================================
   CARDS
========================================================= */

.settings-card,
.settings-side-card {
    background:
        linear-gradient(
            145deg,
            var(--theme-surface, rgba(11,16,25,.96)),
            var(--theme-surface, rgba(10,15,27,.94))
        );

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .13));

    border-radius: 14px;

    box-shadow:
        0 10px 30px var(--theme-shadow, rgba(0, 0, 0, .16)),
        inset 0 1px 0 var(--theme-shadow, rgba(255, 255, 255, .025));
}


.settings-card {
    overflow: hidden;
}


.settings-card-header {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 18px 20px;

    border-bottom: 1px solid var(--theme-border, rgba(148, 163, 184, .10));

    background: var(--theme-surface-alt, rgba(1,4,15,.18));
}


.settings-icon {
    width: 42px;
    height: 42px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;

    color: var(--theme-teal-text, #2dd4bf);

    background: var(--theme-teal-soft, rgba(20, 184, 166, .10));

    border: 1px solid var(--theme-teal-border, rgba(45, 212, 191, .17));

    font-size: 1.05rem;
}


.settings-card-header h2 {
    margin: 0;

    color: var(--theme-text, #f8fafc);

    font-size: var(--site-font-body, 13px);
    font-weight: 750;
}


.settings-card-header p {
    margin: 3px 0 0;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-body, 13px);
}


.settings-card-body {
    padding: 22px;
}


/* =========================================================
   FIELDS
========================================================= */

.settings-field {
    margin-bottom: 22px;
}


.settings-field label {
    display: block;

    margin-bottom: 8px;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}


.settings-input-wrap {
    position: relative;
}


.settings-input-icon {
    position: absolute;
    z-index: 3;

    top: 50%;
    left: 14px;

    transform: translateY(-50%);

    color: var(--theme-muted, #64748b);

    font-size: .9rem;

    pointer-events: none;
}


.settings-input {
    min-height: 45px;

    padding:
        .65rem
        .85rem
        .65rem
        42px !important;

    color: var(--theme-text, #f1f5f9) !important;

    background: var(--theme-control, rgba(1,4,15,.55)) !important;

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .16)) !important;

    border-radius: 9px !important;

    font-size: var(--site-font-body, 13px);

    box-shadow: none !important;

    transition:
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;
}


.settings-input:hover {
    border-color: var(--theme-border, rgba(148, 163, 184, .28)) !important;
}


.settings-input:focus {
    background: var(--theme-control, rgba(1,4,15,.76)) !important;

    border-color: var(--theme-teal-border, rgba(45, 212, 191, .65)) !important;

    box-shadow:
        0 0 0 3px var(--theme-shadow, rgba(20, 184, 166, .08)) !important;
}


.settings-input::placeholder {
    color: var(--theme-muted, #475569) !important;
}


.settings-input[readonly] {
    color: var(--theme-muted, #64748b) !important;

    cursor: not-allowed;

    background: var(--theme-control, rgba(20,27,38,.35)) !important;
}


.settings-select {
    cursor: pointer;
}


.settings-select option {
    color: var(--theme-text, #f1f5f9);

    background: var(--theme-control, #0a0f1b);
}


/* =========================================================
   TEXTAREA
========================================================= */

.settings-textarea {
    min-height: 120px;

    padding: 14px !important;

    resize: none;
    overflow: hidden;

    line-height: 1.6;
}


/* =========================================================
   HELP / ERRORS
========================================================= */

.settings-help {
    display: flex;
    align-items: center;

    gap: 6px;

    margin-top: 7px;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-small, 13px);
}


.settings-help i {
    font-size: .68rem;
}


.settings-error {
    margin-top: 7px;

    color: var(--theme-red-text, #f87171);

    font-size: var(--site-font-small, 13px);
}


/* =========================================================
   SIDEBAR
========================================================= */

.settings-sidebar {
    position: sticky;

    top: 85px;

    display: flex;
    flex-direction: column;

    gap: 16px;
}


.settings-side-card {
    padding: 22px;
}


/* =========================================================
   SECURITY / ADMINISTRATION
========================================================= */

.sidebar-security-card {
    padding: 0;

    overflow: hidden;

    border-color: var(--theme-amber-border, rgba(245, 158, 11, .14));
}


.sidebar-card-title {
    display: flex;
    align-items: center;

    gap: 11px;

    padding: 16px 17px;

    background: var(--theme-surface-alt, rgba(1,4,15,.22));

    border-bottom: 1px solid var(--theme-border, rgba(148, 163, 184, .10));
}


.sidebar-security-icon {
    width: 38px;
    height: 38px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    color: var(--theme-amber-text, #fbbf24);

    background: var(--theme-amber-soft, rgba(245, 158, 11, .09));

    border: 1px solid var(--theme-amber-border, rgba(245, 158, 11, .16));
    border-radius: 9px;
}


.sidebar-card-title strong {
    display: block;

    color: var(--theme-text, #f1f5f9);

    font-size: var(--site-font-body, 13px);
    font-weight: 750;
}


.sidebar-card-title span {
    display: block;

    margin-top: 2px;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-small, 13px);
}


.sidebar-settings-field {
    padding: 15px 17px;

    border-bottom: 1px solid var(--theme-border, rgba(148, 163, 184, .08));
}


.sidebar-settings-field:last-child {
    border-bottom: 0;
}


.sidebar-settings-field label {
    display: flex;
    align-items: center;

    gap: 6px;

    margin-bottom: 7px;

    color: var(--theme-text, #cbd5e1);

    font-size: var(--site-font-small, 13px);
    font-weight: 700;
}


.sidebar-settings-field label i {
    color: var(--theme-amber-text, #fbbf24);

    font-size: .72rem;
}


.sidebar-settings-input {
    min-height: 40px;

    font-size: var(--site-font-body, 13px) !important;
}


.sidebar-admin-label,
.sidebar-security-help {
    display: flex;
    align-items: center;

    gap: 5px;

    margin-top: 7px;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-small, 13px);
}


.sidebar-admin-label {
    color: var(--theme-amber-text, #d97706);
}


/* =========================================================
   SAVE BAR
========================================================= */

.settings-save-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    margin-top: 22px;

    padding: 16px 18px;

    background: var(--theme-surface, rgba(10,15,27,.95));

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .13));
    border-radius: 13px;

    box-shadow:
        0 10px 30px var(--theme-shadow, rgba(0, 0, 0, .18));
}


.save-bar-text {
    display: flex;
    align-items: center;

    gap: 11px;
}


.save-bar-text > i {
    color: var(--theme-teal-text, #2dd4bf);

    font-size: 1.3rem;
}


.save-bar-text strong {
    display: block;

    color: var(--theme-text, #e2e8f0);

    font-size: var(--site-font-body, 13px);
}


.save-bar-text span {
    display: block;

    margin-top: 2px;

    color: var(--theme-muted, #64748b);

    font-size: var(--site-font-small, 13px);
}


.save-bar-actions {
    display: flex;

    gap: 9px;
}


.settings-cancel-btn,
.settings-save-btn {
    min-height: 40px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: .55rem 1rem;

    border-radius: 8px;

    font-size: var(--site-font-body, 13px);
    font-weight: 700;
}


.settings-cancel-btn {
    color: var(--theme-muted, #94a3b8);

    background: var(--theme-surface, rgba(20,27,38,.55));

    border: 1px solid var(--theme-border, rgba(148, 163, 184, .16));
}


.settings-cancel-btn:hover {
    color: var(--theme-text, #fff);

    background: var(--theme-surface, rgba(33,42,55,.7));

    border-color: var(--theme-border, rgba(148, 163, 184, .3));
}


.settings-save-btn {
    color: var(--theme-on-action, #fff);

    background:
        linear-gradient(
            135deg,
            var(--theme-teal-action, #0f766e),
            var(--theme-teal-action, #0d9488)
        );

    border: 1px solid var(--theme-teal-border, #14b8a6);

    box-shadow:
        0 6px 16px var(--theme-shadow, rgba(13, 148, 136, .20));

    transition:
        transform .18s ease,
        background .18s ease,
        border-color .18s ease;
}


.settings-save-btn:hover {
    color: var(--theme-on-action, #fff);

    background:
        linear-gradient(
            135deg,
            var(--theme-teal-action, #0d9488),
            var(--theme-teal-action, #14b8a6)
        );

    border-color: var(--theme-teal-border, #2dd4bf);

    transform: translateY(-1px);
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 991.98px) {

    .settings-layout {
        grid-template-columns: 1fr;
    }


    .settings-sidebar {
        position: static;

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        align-items: start;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767.98px) {

    .profile-settings-page {
        padding: 1rem .7rem 2rem;
    }


    .profile-settings-hero {
        min-height: 310px;

        border-radius: 13px;
    }


    .profile-settings-hero-content {
        left: 20px;
        right: 20px;
        bottom: 24px;

        flex-direction: column;
        align-items: flex-start;

        gap: 14px;
    }


    .profile-settings-avatar {
        width: 92px;
        height: 92px;
    }


    .profile-settings-user h1 {
        font-size: 1.7rem;
    }


    .profile-settings-user p {
        font-size: var(--site-font-body, 13px);
    }


    .settings-card-header {
        padding: 16px;
    }


    .settings-card-body {
        padding: 17px;
    }


    .settings-sidebar {
        grid-template-columns: 1fr;
    }


    .settings-save-bar {
        flex-direction: column;
        align-items: stretch;
    }


    .save-bar-actions {
        width: 100%;
    }


    .settings-cancel-btn,
    .settings-save-btn {
        flex: 1;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 420px) {

    .save-bar-actions {
        flex-direction: column-reverse;
    }


    .settings-cancel-btn,
    .settings-save-btn {
        width: 100%;
    }

}

</style>

@endsection
