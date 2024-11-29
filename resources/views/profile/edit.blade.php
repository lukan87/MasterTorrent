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
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
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
            <input type="checkbox" id="uploadpos" class="form-check-input @error('uploadpos') is-invalid @enderror" name="uploadpos" value="yes" {{ old('uploadpos', $user->donor) === 'yes' ? 'checked' : '' }}>
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
            <input type="checkbox" id="downloadpos" class="form-check-input @error('downloadpos') is-invalid @enderror" name="downloadpos" value="yes" {{ old('downloadpos', $user->donor) === 'yes' ? 'checked' : '' }}>
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
