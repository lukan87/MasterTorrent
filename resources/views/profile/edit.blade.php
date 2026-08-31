@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <div class="card elite-edit-card">
        <div class="card-header elite-edit-header">
            <i class="bi bi-person-gear me-2"></i>
            Edit Profile
        </div>

        <div class="card-body">

            <form method="POST" action="{{ route('profile.update', [$user->id ,$user->name]) }}">
                @csrf
                @method('PUT')

                {{-- NAME (Admin only) --}}
@if (
    Auth::check() &&
    Auth::user()->user_class >= \App\Models\UserClass::ADMIN
)

<div class="row mb-3">
    <label class="col-md-4 col-form-label text-md-end">Name</label>
    <div class="col-md-6">

        @if (Auth::id() !== $user->id)
            {{-- Admin editing someone else --}}
            <input type="text"
                   class="form-control elite-input @error('name') is-invalid @enderror"
                   name="name"
                   value="{{ old('name', $user->name) }}"
                   required>
        @else
            {{-- Admin editing self --}}
            <input type="text"
                   class="form-control elite-input"
                   value="{{ $user->name }}"
                   readonly>
        @endif

        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror

    </div>
</div>

@endif



                {{-- EMAIL --}}
<div class="row mb-3">
    <label class="col-md-4 col-form-label text-md-end">Email</label>
    <div class="col-md-6">

        @if (
            Auth::check() &&
            Auth::user()->user_class >= \App\Models\UserClass::ADMIN
        )
            {{-- Admin can edit --}}
            <input type="email"
                   class="form-control elite-input @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ old('email', $user->email) }}"
                   required>
        @else
            {{-- Users read-only --}}
            <input type="email"
                   class="form-control elite-input"
                   value="{{ $user->email }}"
                   readonly>
        @endif

        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror

    </div>
</div>


                {{-- PROFILE IMAGE URL --}}
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label text-md-end">Profile Image URL</label>
                    <div class="col-md-6">
                        <input type="text"
                               id="profilePreviewInput"
                               class="form-control elite-input @error('profile_image_url') is-invalid @enderror"
                               name="profile_image_url"
                               value="{{ old('profile_image_url', $user->profile_image) }}">
                        @error('profile_image_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror

                        {{-- @if($user->profile_image)
                            <img id="profilePreview"
                                 src="{{ $user->profile_image }}"
                                 class="elite-small-preview mt-2">
                        @endif --}}
                    </div>
                </div>

                {{-- COVER IMAGE URL --}}
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label text-md-end">Cover Image URL</label>
                    <div class="col-md-6">
                        <input type="text"
                               id="coverPreviewInput"
                               class="form-control elite-input @error('cover') is-invalid @enderror"
                               name="cover"
                               value="{{ old('cover', $user->cover) }}">
                        @error('cover')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror

                        {{-- @if($user->cover)
                            <div id="coverPreview"
                                 class="elite-small-cover mt-2"
                                 style="background-image:url('{{ $user->cover }}')">
                            </div>
                        @endif --}}
                    </div>
                </div>

                {{-- BACKGROUND IMAGE URL --}}
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label text-md-end">Background Image URL</label>
                    <div class="col-md-6">
                        <input type="text"
                               class="form-control elite-input @error('background') is-invalid @enderror"
                               name="background"
                               value="{{ old('background', $user->background) }}">
                        @error('background')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- INFO --}}
                <div class="row mb-3">
    <label class="col-md-4 col-form-label text-md-end">Info</label>
    <div class="col-md-6">
        <textarea
            id="infoField"
            class="form-control elite-input auto-grow"
            name="info"
            rows="3"
        >{{ old('info', $user->info) }}</textarea>
    </div>
</div>


                {{-- TIMEZONE --}}
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label text-md-end">Timezone</label>
                    <div class="col-md-6">
                        <select name="timezone" class="form-control elite-input">
                            @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" {{ $user->timezone === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SEEDBONUS (Admin only, not self) --}}
                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN && Auth::user()->id != $user->id)
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label text-md-end">Seedbonus</label>
                    <div class="col-md-6">
                        <input type="text"
                               class="form-control elite-input"
                               name="seedbonus"
                               value="{{ old('seedbonus', $user->seedbonus) }}">
                    </div>
                </div>
                @endif

                {{-- RECOVERY CODE --}}
                @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                <div class="row mb-4">
                    <label class="col-md-4 col-form-label text-md-end">Recovery Code</label>
                    <div class="col-md-6">
                        <input type="text"
                               class="form-control elite-input"
                               name="recovery_code">
                    </div>
                </div>
                @endif

                {{-- SUBMIT --}}
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-elite px-4">
                            <i class="bi bi-check2-circle me-1"></i>
                            Update Profile
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>

{{-- LIVE PREVIEW --}}
<script>
document.getElementById('profilePreviewInput')?.addEventListener('input', function() {
    let preview = document.getElementById('profilePreview');
    if (preview) preview.src = this.value;
});

document.getElementById('coverPreviewInput')?.addEventListener('input', function() {
    let preview = document.getElementById('coverPreview');
    if (preview) preview.style.backgroundImage = "url('" + this.value + "')";
});
</script>

<script>
const textarea = document.getElementById('infoField');

if (textarea) {
    const autoResize = () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    };

    textarea.addEventListener('input', autoResize);
    window.addEventListener('load', autoResize);
}
</script>


<style>
.elite-edit-card {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 16px;
}

.elite-edit-header {
    background: linear-gradient(135deg, #282c33, #295381);
    color: white;
    font-weight: 600;
}

.elite-input {
    background: #0d1117;
    border: 1px solid #30363d;
    color: #e6edf3;
    border-radius: 8px;
}

.elite-input:focus {
    border-color: #1f6feb;
    box-shadow: 0 0 0 2px rgba(31,111,235,0.3);
    background: #0d1117;
    color: #fff;
}

.elite-small-preview {
    max-width: 120px;
    border-radius: 12px;
    border: 2px solid #30363d;
}

.elite-small-cover {
    height: 100px;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
    border: 1px solid #30363d;
}

.btn-elite {
    background: linear-gradient(135deg, #2ea043, #238636);
    border: none;
    color: white;
    border-radius: 25px;
    transition: 0.2s ease;
}

.btn-elite:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(35,134,54,0.4);
}

.auto-grow {
    resize: none; /* remove manual resize */
    overflow: hidden;
    min-height: 100px;
}

</style>

@endsection
