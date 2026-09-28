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



                        {{-- SEEDBONUS --}}
                        @if (
                            Auth::check() &&
                            Auth::user()->user_class >= \App\Models\UserClass::ADMIN &&
                            Auth::user()->id != $user->id
                        )

                            <div class="sidebar-settings-field">

                                <label for="seedbonus">
                                    <i class="bi bi-stars"></i>
                                    Seedbonus
                                </label>

                                <div class="settings-input-wrap">

                                    <span class="settings-input-icon">
                                        <i class="bi bi-stars"></i>
                                    </span>

                                    <input
                                        type="text"
                                        id="seedbonus"
                                        name="seedbonus"
                                        class="form-control settings-input sidebar-settings-input"
                                        value="{{ old('seedbonus', $user->seedbonus) }}"
                                    >

                                </div>

                                <div class="sidebar-admin-label">
                                    <i class="bi bi-shield-check"></i>
                                    Administrator option
                                </div>

                            </div>

                        @endif



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
                        
                        {{-- PASSWORD CHANGE --}}
                        <div class="sidebar-settings-field">
                            <label>Change Password</label>
                            <input type="password" name="current_password" class="form-control settings-input sidebar-settings-input mb-2" placeholder="Current Password">
                            <input type="password" name="new_password" class="form-control settings-input sidebar-settings-input mb-2" placeholder="New Password">
                            <input type="password" name="new_password_confirmation" class="form-control settings-input sidebar-settings-input" placeholder="Confirm New Password">
                        </div>

                    </div>

                @endif

            </aside>

        </div>
>>>>>>>



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

</div>
>>>>>>>



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
    const currentPass = document.querySelector('input[name="current_password"]').value;
    const newPass = document.querySelector('input[name="new_password"]').value;
    
    if (currentPass && newPass) {
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

    background-color: #0f172a;
    background-size: cover;
    background-position: center;

    border: 1px solid rgba(148, 163, 184, .14);
    border-radius: 18px;

    box-shadow:
        0 20px 50px rgba(0, 0, 0, .28),
        inset 0 1px 0 rgba(255, 255, 255, .03);
}


.profile-settings-hero::before {
    content: "";

    position: absolute;
    inset: 0;

    background:
        radial-gradient(
            circle at 80% 10%,
            rgba(20, 184, 166, .20),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            rgba(2, 6, 23, .35),
            rgba(2, 6, 23, .85)
        );
}


.profile-settings-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            to top,
            rgba(2, 6, 23, .98) 0%,
            rgba(2, 6, 23, .62) 55%,
            rgba(2, 6, 23, .25) 100%
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

    border: 4px solid rgba(255, 255, 255, .92);

    background: #0f172a;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .45),
        0 0 0 5px rgba(20, 184, 166, .16);
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

    color: #fff;

    background: #0f766e;

    border: 3px solid #0f172a;
    border-radius: 50%;

    font-size: .75rem;
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

    color: #5eead4;

    font-size: .72rem;
    font-weight: 800;

    letter-spacing: .12em;
}


.profile-settings-user h1 {
    margin: 0;

    color: #fff;

    font-size: clamp(1.8rem, 3vw, 2.6rem);
    font-weight: 800;

    letter-spacing: -.035em;
}


.profile-settings-user p {
    max-width: 600px;

    margin: 5px 0 0;

    color: #cbd5e1;

    font-size: .9rem;
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
            rgba(17, 24, 39, .96),
            rgba(15, 23, 42, .94)
        );

    border: 1px solid rgba(148, 163, 184, .13);

    border-radius: 14px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .16),
        inset 0 1px 0 rgba(255, 255, 255, .025);
}


.settings-card {
    overflow: hidden;
}


.settings-card-header {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 18px 20px;

    border-bottom: 1px solid rgba(148, 163, 184, .10);

    background: rgba(2, 6, 23, .18);
}


.settings-icon {
    width: 42px;
    height: 42px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;

    color: #2dd4bf;

    background: rgba(20, 184, 166, .10);

    border: 1px solid rgba(45, 212, 191, .17);

    font-size: 1.05rem;
}


.settings-card-header h2 {
    margin: 0;

    color: #f8fafc;

    font-size: .98rem;
    font-weight: 750;
}


.settings-card-header p {
    margin: 3px 0 0;

    color: #64748b;

    font-size: .82rem;
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

    color: #cbd5e1;

    font-size: .86rem;
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

    color: #64748b;

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

    color: #f1f5f9 !important;

    background: rgba(2, 6, 23, .55) !important;

    border: 1px solid rgba(148, 163, 184, .16) !important;

    border-radius: 9px !important;

    font-size: .9rem;

    box-shadow: none !important;

    transition:
        border-color .18s ease,
        background .18s ease,
        box-shadow .18s ease;
}


.settings-input:hover {
    border-color: rgba(148, 163, 184, .28) !important;
}


.settings-input:focus {
    background: rgba(2, 6, 23, .76) !important;

    border-color: rgba(45, 212, 191, .65) !important;

    box-shadow:
        0 0 0 3px rgba(20, 184, 166, .08) !important;
}


.settings-input::placeholder {
    color: #475569 !important;
}


.settings-input[readonly] {
    color: #64748b !important;

    cursor: not-allowed;

    background: rgba(30, 41, 59, .35) !important;
}


.settings-select {
    cursor: pointer;
}


.settings-select option {
    color: #f1f5f9;

    background: #0f172a;
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

    color: #64748b;

    font-size: .72rem;
}


.settings-help i {
    font-size: .68rem;
}


.settings-error {
    margin-top: 7px;

    color: #f87171;

    font-size: .74rem;
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

    border-color: rgba(245, 158, 11, .14);
}


.sidebar-card-title {
    display: flex;
    align-items: center;

    gap: 11px;

    padding: 16px 17px;

    background: rgba(2, 6, 23, .22);

    border-bottom: 1px solid rgba(148, 163, 184, .10);
}


.sidebar-security-icon {
    width: 38px;
    height: 38px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #fbbf24;

    background: rgba(245, 158, 11, .09);

    border: 1px solid rgba(245, 158, 11, .16);
    border-radius: 9px;
}


.sidebar-card-title strong {
    display: block;

    color: #f1f5f9;

    font-size: .82rem;
    font-weight: 750;
}


.sidebar-card-title span {
    display: block;

    margin-top: 2px;

    color: #64748b;

    font-size: .67rem;
}


.sidebar-settings-field {
    padding: 15px 17px;

    border-bottom: 1px solid rgba(148, 163, 184, .08);
}


.sidebar-settings-field:last-child {
    border-bottom: 0;
}


.sidebar-settings-field label {
    display: flex;
    align-items: center;

    gap: 6px;

    margin-bottom: 7px;

    color: #cbd5e1;

    font-size: .75rem;
    font-weight: 700;
}


.sidebar-settings-field label i {
    color: #fbbf24;

    font-size: .72rem;
}


.sidebar-settings-input {
    min-height: 40px;

    font-size: .8rem !important;
}


.sidebar-admin-label,
.sidebar-security-help {
    display: flex;
    align-items: center;

    gap: 5px;

    margin-top: 7px;

    color: #64748b;

    font-size: .65rem;
}


.sidebar-admin-label {
    color: #d97706;
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

    background: rgba(15, 23, 42, .95);

    border: 1px solid rgba(148, 163, 184, .13);
    border-radius: 13px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, .18);
}


.save-bar-text {
    display: flex;
    align-items: center;

    gap: 11px;
}


.save-bar-text > i {
    color: #2dd4bf;

    font-size: 1.3rem;
}


.save-bar-text strong {
    display: block;

    color: #e2e8f0;

    font-size: .8rem;
}


.save-bar-text span {
    display: block;

    margin-top: 2px;

    color: #64748b;

    font-size: .7rem;
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

    font-size: .78rem;
    font-weight: 700;
}


.settings-cancel-btn {
    color: #94a3b8;

    background: rgba(30, 41, 59, .55);

    border: 1px solid rgba(148, 163, 184, .16);
}


.settings-cancel-btn:hover {
    color: #fff;

    background: rgba(51, 65, 85, .7);

    border-color: rgba(148, 163, 184, .3);
}


.settings-save-btn {
    color: #fff;

    background:
        linear-gradient(
            135deg,
            #0f766e,
            #0d9488
        );

    border: 1px solid #14b8a6;

    box-shadow:
        0 6px 16px rgba(13, 148, 136, .20);

    transition:
        transform .18s ease,
        background .18s ease,
        border-color .18s ease;
}


.settings-save-btn:hover {
    color: #fff;

    background:
        linear-gradient(
            135deg,
            #0d9488,
            #14b8a6
        );

    border-color: #2dd4bf;

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
        font-size: .8rem;
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