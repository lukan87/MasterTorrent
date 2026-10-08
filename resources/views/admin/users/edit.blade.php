@extends('layouts.admin')

@section('admin-content')

<div class="container py-4 user-edit-page">

    <form action="{{ route('admin.users.update',$user->id) }}" method="POST">

        @csrf

        @method('PUT')

        <div class="page-header d-flex align-items-center justify-content-between mb-4 admin-page-header">

            <div>

                <h2 class="page-title mb-1">Edit User</h2>

                <div class="page-subtitle">Manage account settings, permissions and user information.</div>

            </div>

            <span class="user-id-badge">ID #{{ $user->id }}</span>

        </div>

        {{-- ACCOUNT INFORMATION --}}

        <div class="admin-card mb-4">

            <div class="admin-card-header">

                <i class="bi bi-person-vcard me-2"></i>Account Information

            </div>

            <div class="admin-card-body">

                <div class="row g-3">

                    <div class="col-md-4">

                        <label for="admin-field-1" class="form-label">Name</label>

                        <input id="admin-field-1" type="text" name="name" class="form-control elite-input"

                               value="{{ old('name',$user->name) }}">

                    </div>

                    <div class="col-md-4">

                        <label for="admin-field-2" class="form-label">Email</label>

                        <input id="admin-field-2" type="email" name="email" class="form-control elite-input"

                               value="{{ old('email',$user->email) }}">

                    </div>

                    <div class="col-md-4">

                        <label for="admin-field-3" class="form-label">Profile Image URL</label>

                        <input id="admin-field-3" type="url" name="profile_image" class="form-control elite-input"

                               value="{{ old('profile_image',$user->profile_image) }}">

                    </div>

                    <div class="col-md-4">

                        <label for="admin-field-4" class="form-label">Recovery Code</label>

                        <input id="admin-field-4" type="text" class="form-control elite-input" name="recovery_code">

                    </div>

                </div>

            </div>

        </div>

        @if (auth()->id() !== $user->id)

            {{-- USER STATUS --}}

            <div class="admin-card mb-4">

                <div class="admin-card-header">

                    <i class="bi bi-toggle-on me-2"></i>User Status

                </div>

                <div class="admin-card-body">

                    <div class="row g-4">

                        @foreach (['enabled','downloadpos','uploadpos','donor'] as $field)

                            <div class="col-lg-3 col-md-4 col-sm-6">

                                <label class="form-label fw-semibold">{{ ucfirst($field) }}</label>

                                <div class="btn-group w-100">

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="yes"

                                           id="{{ $field }}_yes"

                                           {{ old($field,$user->$field)=='yes' ? 'checked':'' }}>

                                    <label class="btn btn-outline-success" for="{{ $field }}_yes">Yes</label>

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="no"

                                           id="{{ $field }}_no"

                                           {{ old($field,$user->$field)=='no' ? 'checked':'' }}>

                                    <label class="btn btn-outline-danger" for="{{ $field }}_no">No</label>

                                </div>

                            </div>

                        @endforeach

                        @foreach(['is_immune','is_freeleech'] as $field)

                            <div class="col-lg-3 col-md-4 col-sm-6">

                                <label class="form-label fw-semibold">

                                    {{ ucfirst(str_replace('_',' ',$field)) }}

                                </label>

                                <div class="btn-group w-100">

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="1"

                                           id="{{ $field }}_yes"

                                           {{ old($field,$user->$field)==1 ? 'checked':'' }}>

                                    <label class="btn btn-outline-success" for="{{ $field }}_yes">Yes</label>

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="0"

                                           id="{{ $field }}_no"

                                           {{ old($field,$user->$field)==0 ? 'checked':'' }}>

                                    <label class="btn btn-outline-danger" for="{{ $field }}_no">No</label>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

            {{-- COMMUNICATION BLOCKS --}}

            <div class="admin-card mb-4">

                <div class="admin-card-header">

                    <i class="bi bi-chat-square-dots me-2"></i>Communication Blocks

                </div>

                <div class="admin-card-body">

                    <div class="row g-4">

                        @foreach(['chatblock'=>'Chat Block','commentblock'=>'Comment Block','forumblock'=>'Forum Block'] as $field=>$label)

                            <div class="col-md-4">

                                <label class="form-label fw-semibold">{{ $label }}</label>

                                <div class="btn-group w-100">

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="1"

                                           id="{{ $field }}_yes"

                                           {{ old($field,$user->$field)==1 ? 'checked':'' }}>

                                    <label class="btn btn-outline-danger" for="{{ $field }}_yes">Blocked</label>

                                    <input type="radio" class="btn-check" name="{{ $field }}" value="0"

                                           id="{{ $field }}_no"

                                           {{ old($field,$user->$field)==0 ? 'checked':'' }}>

                                    <label class="btn btn-outline-success" for="{{ $field }}_no">Allowed</label>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif

        {{-- WARNINGS --}}

        @if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR

             && auth()->id() !== $user->id

             && auth()->user()->user_class > $user->user_class)

            <div class="admin-card mb-4">

                <div class="admin-card-header">

                    <i class="bi bi-exclamation-triangle me-2"></i>Warnings

                </div>

                <div class="admin-card-body">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">Warned</label>

                            <div class="btn-group w-100">

                                <input type="radio" class="btn-check" name="warned" value="1"

                                       id="warn_yes"

                                       {{ old('warned',$user->warned)==1?'checked':'' }}>

                                <label class="btn btn-outline-danger" for="warn_yes">Yes</label>

                                <input type="radio" class="btn-check" name="warned" value="0"

                                       id="warn_no"

                                       {{ old('warned',$user->warned)==0?'checked':'' }}>

                                <label class="btn btn-outline-success" for="warn_no">No</label>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <label for="admin-field-5" class="form-label">Warned Until</label>

                            <input id="admin-field-5" type="date" name="warned_until" class="form-control elite-input"

                                   value="{{ old('warned_until',optional($user->warned_until)->format('Y-m-d')) }}">

                        </div>

                        <div class="col-md-12">

                            <label for="admin-field-6" class="form-label">Warning Reason</label>

                            <textarea id="admin-field-6" name="warned_reason" class="form-control elite-input"

                                      rows="3">{{ old('warned_reason',$user->warned_reason) }}</textarea>

                        </div>

                    </div>

                </div>

            </div>

        @endif

        {{-- USER ROLE --}}

        @if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR

             && auth()->id() !== $user->id

             && auth()->user()->user_class > $user->user_class)

            @php

                $allClasses = \App\Models\UserClass::getClasses();

                $promotionCeiling = [

                    \App\Models\UserClass::MODERATOR => \App\Models\UserClass::SUPERUSER,

                    \App\Models\UserClass::ADMIN => \App\Models\UserClass::MODERATOR,

                    \App\Models\UserClass::OWNER => \App\Models\UserClass::ADMIN,

                    \App\Models\UserClass::WEB_DEVELOPER => \App\Models\UserClass::OWNER,

                ];

                $authClass = auth()->user()->user_class;

                $maxClass = $promotionCeiling[$authClass] ?? null;

                $availableClasses = collect($allClasses)->filter(fn($label,$class)=>$class <= $maxClass);

            @endphp

            @if($availableClasses->isNotEmpty())

                <div class="admin-card mb-4">

                    <div class="admin-card-header">

                        <i class="bi bi-shield-lock me-2"></i>User Role

                    </div>

                    <div class="admin-card-body">

                        <select name="user_class" class="form-select elite-input">

                            @foreach($availableClasses as $classValue=>$className)

                                <option value="{{ $classValue }}"

                                    {{ $user->user_class == $classValue ? 'selected':'' }}>

                                    {{ $className }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            @endif

        @endif

        {{-- VIP --}}

        @if (auth()->user()->user_class >= \App\Models\UserClass::OWNER && auth()->id() !== $user->id)

            <div class="admin-card mb-4">

                <div class="admin-card-header">

                    <i class="bi bi-star me-2"></i>VIP Settings

                </div>

                <div class="admin-card-body">

                    @if($user->vip_until)

                        <div class="current-value mb-3">

                            <span>Current VIP Until</span>

                            <strong>{{ $user->vip_until }}</strong>

                        </div>

                    @endif

                    <select id="vip_until" name="vip_until" class="form-select elite-input">

                        <option value="">-- Select Duration --</option>

                        <option value="4 weeks">4 Weeks</option>

                        <option value="6 weeks">6 Weeks</option>

                        <option value="8 weeks">8 Weeks</option>

                        <option value="10 weeks">10 Weeks</option>

                        <option value="12 weeks">12 Weeks</option>

                        <option value="remove">Remove VIP</option>

                    </select>

                </div>

            </div>

        @endif

        @if (auth()->id() !== $user->id)

            {{-- USER STATISTICS --}}

            <div class="admin-card mb-4">

                <div class="admin-card-header">

                    <i class="bi bi-bar-chart me-2"></i>User Statistics

                </div>

                <div class="admin-card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <label for="admin-field-7" class="form-label">Uploaded (GB)</label>

                            <input id="admin-field-7" type="number" name="uploaded" class="form-control elite-input"

                                   value="{{ old('uploaded',floor($user->uploaded/(1024**3))) }}">

                        </div>

                        <div class="col-md-4">

                            <label for="admin-field-8" class="form-label">Downloaded (GB)</label>

                            <input id="admin-field-8" type="number" name="downloaded" class="form-control elite-input"

                                   value="{{ old('downloaded',floor($user->downloaded/(1024**3))) }}">

                        </div>

                        <div class="col-md-4">

                            <label for="admin-field-9" class="form-label">Seedbonus</label>

                            <input id="admin-field-9" type="number" step="0.01" name="seedbonus" class="form-control elite-input"

                                   value="{{ old('seedbonus',$user->seedbonus) }}">

                        </div>

                        <div class="col-md-6">

                            <label for="admin-field-10" class="form-label">Invites</label>

                            <input id="admin-field-10" type="number" name="invites" class="form-control elite-input"

                                   value="{{ old('invites',$user->invites) }}">

                        </div>

                        <div class="col-md-6">

                            <label for="admin-field-11" class="form-label">Slots</label>

                            <input id="admin-field-11" type="number" name="slots" class="form-control elite-input"

                                   value="{{ old('slots',$user->slots) }}">

                        </div>

                    </div>

                </div>

            </div>

        @endif

        {{-- USER BIO --}}

        <div class="admin-card mb-4">

            <div class="admin-card-header">

                <i class="bi bi-person-lines-fill me-2"></i>User Info

            </div>

            <div class="admin-card-body">

                <textarea name="info" class="form-control elite-input" rows="6">{{ old('info',$user->info) }}</textarea>

            </div>

        </div>

        <div class="form-actions d-flex justify-content-between align-items-center mt-4">

            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary action-btn">

                <i class="bi bi-arrow-left me-1"></i>Back

            </a>

            <button type="submit" class="btn btn-success action-btn px-4">

                <i class="bi bi-check2-circle me-1"></i>Save Changes

            </button>

        </div>

    </form>

</div>


<style>
/* FileIplay Admin — enhanced visual layer */
.user-edit-page {
    max-width: 1240px;
}

.user-edit-page::before {
    content: "";
    position: fixed;
    inset: 70px 0 0;
    pointer-events: none;
    z-index: -1;
    background:
        radial-gradient(circle at 12% 8%, var(--theme-teal-soft, rgba(45,212,191,.055)), transparent 28%),
        radial-gradient(circle at 88% 20%, var(--theme-teal-soft, rgba(20,184,166,.035)), transparent 25%);
}

.page-header {
    position: relative;
    padding: .2rem 0 1rem;
    border-bottom: 1px solid var(--theme-border, rgba(148,163,184,.12));
}

.page-title {
    display: flex;
    align-items: center;
    gap: .55rem;
    margin: 0;
    color: var(--theme-text, #f8fafc);
    font-size: 1.55rem;
    font-weight: 750;
    letter-spacing: -.2px;
}

.page-title::before {
    content: "";
    width: 4px;
    height: 27px;
    border-radius: 4px;
    background: var(--theme-teal-soft, #2dd4bf);
    box-shadow: 0 0 12px var(--theme-shadow, rgba(45,212,191,.25));
}

.page-subtitle {
    margin-left: .6rem;
    color: var(--theme-muted, #8291a7);
    font-size: var(--site-font-body, 13px);
}

.user-id-badge {
    border-color: var(--theme-teal-border, rgba(45,212,191,.30));
    background: linear-gradient(135deg, var(--theme-teal-soft, rgba(45,212,191,.10)), var(--theme-surface, rgba(10,15,27,.35)));
    box-shadow: inset 0 0 14px var(--theme-shadow, rgba(45,212,191,.025));
}

.admin-card {
    position: relative;
    border-color: var(--theme-border, rgba(148,163,184,.15));
    background:
        linear-gradient(145deg, var(--theme-surface, rgba(16,23,36,.97)), var(--theme-surface, rgba(8,13,23,.96)));
    box-shadow:
        0 10px 28px var(--theme-shadow, rgba(0,0,0,.18)),
        inset 0 1px 0 var(--theme-shadow, rgba(255,255,255,.018));
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.admin-card:hover {
    border-color: var(--theme-teal-border, rgba(45,212,191,.22));
    box-shadow:
        0 12px 30px var(--theme-shadow, rgba(0,0,0,.22)),
        inset 0 1px 0 var(--theme-shadow, rgba(255,255,255,.025));
}

.admin-card-header {
    position: relative;
    min-height: 44px;
    padding: .7rem .9rem .7rem 1rem;
    background: var(--theme-surface-alt, rgba(1,4,15,.30));
}

.admin-card-header::after {
    content: "";
    position: absolute;
    left: 0;
    top: 9px;
    bottom: 9px;
    width: 3px;
    border-radius: 0 3px 3px 0;
    background: var(--theme-teal-soft, #2dd4bf);
    box-shadow: 0 0 9px var(--theme-shadow, rgba(45,212,191,.18));
}

.admin-card-header i {
    font-size: .95rem;
    opacity: .95;
}

.admin-card-body {
    padding: 1rem;
}

.form-label {
    letter-spacing: .05px;
}

.elite-input,
.form-select {
    min-height: 40px;
    border-color: var(--theme-border, rgba(148,163,184,.20));
    background: var(--theme-control, rgba(5,10,19,.82));
    transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
}

.elite-input:hover,
.form-select:hover {
    border-color: var(--theme-border, rgba(148,163,184,.30));
}

.elite-input:focus,
.form-select:focus {
    border-color: var(--theme-teal-border, rgba(45,212,191,.62));
    box-shadow: 0 0 0 .18rem var(--theme-shadow, rgba(45,212,191,.075)), 0 0 18px var(--theme-shadow, rgba(45,212,191,.035));
}

input[type="date"].elite-input {
    color-scheme: dark;
}

.btn-group {
    box-shadow: 0 3px 10px var(--theme-shadow, rgba(0,0,0,.10));
}

.btn-group .btn {
    min-height: 40px;
    border-color: var(--theme-border, rgba(148,163,184,.22));
    transition: all .15s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
}

.btn-check:checked + .btn-outline-success,
.btn-check:checked + .btn-outline-danger {
    box-shadow: inset 0 0 12px var(--theme-shadow, rgba(255,255,255,.025));
}

.current-value {
    border-color: var(--theme-teal-border, rgba(45,212,191,.22));
    background: linear-gradient(135deg, var(--theme-teal-soft, rgba(20,184,166,.08)), var(--theme-surface, rgba(10,15,27,.25)));
}

.form-actions {
    position: sticky;
    bottom: 0;
    z-index: 10;
    margin-left: -.75rem;
    margin-right: -.75rem;
    padding: .75rem;
    border: 1px solid var(--theme-border, rgba(148,163,184,.12));
    border-radius: .7rem;
    background: var(--theme-surface, rgba(5,10,19,.88));
    backdrop-filter: blur(12px);
    box-shadow: 0 -8px 25px var(--theme-shadow, rgba(0,0,0,.16));
}

.action-btn {
    min-height: 40px;
    padding-left: .9rem;
    padding-right: .9rem;
    transition: transform .15s ease, box-shadow .15s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.form-actions .btn-success {
    box-shadow: 0 5px 14px var(--theme-shadow, rgba(34,197,94,.10));
}

@media (max-width: 767.98px) {
    .page-header {
        align-items: flex-start !important;
    }

    .page-title {
        font-size: 1.3rem;
    }

    .user-id-badge {
        font-size: var(--site-font-small, 13px);
    }

    .admin-card-body {
        padding: .8rem;
    }
}

@media (max-width: 575.98px) {
    .page-header {
        flex-direction: column;
    }

    .page-subtitle {
        margin-left: .6rem;
    }

    .user-id-badge {
        align-self: flex-start;
    }

    .form-actions {
        position: static;
        margin-left: 0;
        margin-right: 0;
        background: transparent;
        border: 0;
        box-shadow: none;
        padding: 0;
    }
}
</style>



@endsection