@extends('layouts.app')

@section('content')

    <div class="container justify-content-center mt-5">

        <div class="row">

            <div class="col-md-12 order-md-1">
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
