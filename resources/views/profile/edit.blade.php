@extends('layouts.app')

@section('content')

    <div class="row justify-content-center mt-5">

        <div class="row">

            <div class="col-md-9 order-md-1">
            <div class="card mr-4">
                    <div class="card-header">{{ __('Edit Profile') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update', [$user->id ,$user->name]) }}">
                            @csrf
                            @method('PUT')


                            <div class="row mb-3">
    <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
    <div class="col-md-6">
        @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
        @else
            <input id="name" type="text" class="form-control" name="name" value="{{ $user->name }}" readonly>
        @endif
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>




                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @if (auth()->user()->user_class >= \App\Models\UserClass::MODERATOR && auth()->id() !== $user->id)
<div class="row mb-3">
    <label for="user_class" class="col-md-4 col-form-label text-md-end">{{ __('User Role') }}</label>
    <div class="col-md-6">
        <select name="user_class" id="user_class" class="form-control">
            @foreach (App\Models\UserClass::getClasses() as $classValue => $className)
                <option value="{{ $classValue }}" {{ $user->user_class == $classValue ? 'selected' : '' }}>
                    {{ $className }}
                </option>
            @endforeach
        </select>
    </div>
</div>
@endif



@if ($user->vip_until != NULL)
<div class="row mb-3">
    <label for="vip_until" class="col-md-4 col-form-label text-md-end">Vip Until</label>
    <div class="col-md-6">
    {{$user->vip_until}}
    </div>
</div>

@else
@endif

@if (auth()->user()->user_class === \App\Models\UserClass::OWNER && $user->user_class <= \App\Models\UserClass::VIP)

<div class="row mb-3">
    <label for="vip_until" class="col-md-4 col-form-label text-md-end">Set VIP Duration</label>
    <div class="col-md-6">
    <select id="vip_until" name="vip_until" class="form-control">
        <option value="">-- Select Duration --</option>
        <option value="4 weeks" {{ old('vip_until') == '4 weeks' ? 'selected' : '' }}>4 Weeks</option>
        <option value="6 weeks" {{ old('vip_until') == '6 weeks' ? 'selected' : '' }}>6 Weeks</option>
        <option value="8 weeks" {{ old('vip_until') == '8 weeks' ? 'selected' : '' }}>8 Weeks</option>
        <option value="10 weeks" {{ old('vip_until') == '10 weeks' ? 'selected' : '' }}>10 Weeks</option>
        <option value="12 weeks" {{ old('vip_until') == '12 weeks' ? 'selected' : '' }}>12 Weeks </option>
    </select>
    </div>
</div>
@endif

                            <div class="row mb-3">
                                <label for="info" class="col-md-4 col-form-label text-md-end">{{ __('Info') }}</label>
                                <div class="col-md-6">
                                    <input id="info" type="text" class="form-control @error('info') is-invalid @enderror" name="info" value="{{ old('info', $user->info) }}">
                                    @error('info')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                           @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::ADMIN && Auth::user()->id != $user->id)
    <div class="row mb-3">
        <label for="seedbonus" class="col-md-4 col-form-label text-md-end">{{ __('Seedbonus') }}</label>
        <div class="col-md-6">
            <input id="seedbonus" type="text" class="form-control @error('seedbonus') is-invalid @enderror" name="seedbonus" value="{{ old('seedbonus', $user->seedbonus) }}">
            @error('seedbonus')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
@endif



                            <div class="row mb-3">
                                <label for="profile_image_url" class="col-md-4 col-form-label text-md-end">{{ __('Profile Image URL') }}</label>
                                <div class="col-md-6">
                                    <input id="profile_image_url" type="text" class="form-control @error('profile_image_url') is-invalid @enderror" name="profile_image_url" value="{{ old('profile_image_url', $user->profile_image) }}">
                                    @error('profile_image_url')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-end">{{ __('Current Profile Image') }}</label>
                                <div class="col-md-6">
                                    @if($user->profile_image)
                                        <img src="{{ $user->profile_image }}" alt="Profile Image" class="img-fluid rounded" style="max-width: 150px; max-height: 150px;">
                                    @else
                                        <p class="text-muted">No profile image uploaded.</p>
                                    @endif
                                </div>
                            </div>


                            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                                <div class="row mb-3">
                                    <label for="recovery_code" class="col-md-4 col-form-label text-md-end">{{ __('Recovery Code') }}</label>
                                    <div class="col-md-6">
                                        <input id="recovery_code" type="text" class="form-control @error('recovery_code') is-invalid @enderror" name="recovery_code">
                                        @error('recovery_code')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            @endif


                    </div>
                </div>

            </div>

            @if (Auth::check() && Auth::user()->user_class > \App\Models\UserClass::MODERATOR && Auth::user()->id !== $user->id)
            <div class="col-md-3 order-md-2">

            <div class="card">
                    <div class="card-body">

                        <div class="row mb-3">
    <label for="enabled" class="col-md-4 col-form-label text-md-end">{{ __('Enabled') }}</label>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" id="enabled" class="form-check-input @error('enabled') is-invalid @enderror" name="enabled" value="yes" {{ old('enabled', $user->enabled) === 'yes' ? 'checked' : '' }}>
            <label class="form-check-label" for="enabled">{{$user->enabled}}</label>
        </div>

        @error('enabled')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row mb-3">
    <label for="donor" class="col-md-4 col-form-label text-md-end">{{ __('Donor') }}</label>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" id="donor" class="form-check-input @error('donor') is-invalid @enderror" name="donor" value="yes" {{ old('donor', $user->donor) === 'yes' ? 'checked' : '' }}>
            <label class="form-check-label" for="donor">{{$user->donor}}</label>
        </div>

        @error('donor')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row mb-3">
    <label for="uploadpos" class="col-md-4 col-form-label text-md-end">{{ __('Uploader') }}</label>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" id="uploadpos" class="form-check-input @error('uploadpos') is-invalid @enderror" name="uploadpos" value="yes" {{ old('uploadpos', $user->uploadpos) === 'yes' ? 'checked' : '' }}>
            <label class="form-check-label" for="uploadpos">{{$user->uploadpos}}</label>
        </div>

        @error('uploadpos')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="row mb-3">
    <label for="downloadpos" class="col-md-4 col-form-label text-md-end">{{ __('Downloader') }}</label>
    <div class="col-md-6">
        <div class="form-check">
            <input type="checkbox" id="downloadpos" class="form-check-input @error('downloadpos') is-invalid @enderror" name="downloadpos" value="yes" {{ old('downloadpos', $user->downloadpos) === 'yes' ? 'checked' : '' }}>
            <label class="form-check-label" for="downloadpos">{{$user->downloadpos}}</label>
        </div>

        @error('downloadpos')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>




                    </div>
                </div>

            </div>
            @endif
        </div>
        <div class="row mt-5">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Update Profile') }}
                                    </button>
                                </div>
                            </div>
                        </form>
    </div>

@endsection
