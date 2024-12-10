@extends('layouts.app')

@section('content')

    <div class="row justify-content-center">
        <!-- Profile Sidebar -->
        <div class="col-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $user->name }} - {{ $user->role_name }}</span>

                    <!-- Edit Profile Icon for Admins or Profile Owner -->
                    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                        <a href="{{ route('profile.edit', ['id' => $user->id, 'name' => $user->name]) }}" data-bs-toggle="tooltip" data-bs-title="Edit Profile">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Profile Image -->
                    <div class="text-center mb-3">
                        @if($user->profile_image)
                            <img src="{{ $user->profile_image }}" alt="Profile Image"  style="width: 50%; object-fit: cover;">
                        @else
                            <p>No profile image uploaded.</p>
                        @endif
                    </div>
                    <a href="{{ route('messages.create', ['receiver_id' => $user->id]) }}" class="text-light">
                              <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Send message to {{$user->name}}">Send message</button>
                       </a>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Profile Information') }}</div>

                <div class="card-body">
                    <!-- Email -->
                    <div class="row mb-3">
                        <label for="email" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-envelope" data-bs-toggle="tooltip" data-bs-title="Email"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- Joined Date -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-calendar-check" data-bs-toggle="tooltip" data-bs-title="Joined"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->created_at->format('d-M-Y H:i') }}</p> <!-- Format as needed -->
                        </div>
                    </div>

                    <!-- Upload -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-arrow-up-circle-fill" data-bs-toggle="tooltip" data-bs-title="Uploaded"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ formatBytes($user->uploaded) }}</p> <!-- Format as needed -->
                        </div>
                    </div>
                    <!-- Download -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-arrow-down-circle-fill" data-bs-toggle="tooltip" data-bs-title="Downloaded"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ formatBytes($user->downloaded) }}</p> <!-- Format as needed -->
                        </div>
                    </div>


                    <!-- Additional Info -->
                    <div class="row mb-3">
                        <label for="info" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-info-square" data-bs-toggle="tooltip" data-bs-title="Info"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{!! convertCustomTagsToHtml($user->info) !!}</p>
                        </div>
                    </div>

                    <!-- User IP -->
                    <div class="row mb-3">
                        <label for="IP" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-globe" data-bs-toggle="tooltip" data-bs-title="My IP"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->IP }}</p>
                        </div>
                    </div>

                    <!-- Torrents Uploaded -->
                    <div class="row mb-3">
                        <label for="torrents_uploaded" class="col-md-2 col-form-label text-md-end">
                        <i class="bi bi-file-earmark-arrow-up" data-bs-toggle="tooltip" data-bs-title="Torrents Uploaded"></i>
                        </label>
                        <div class="col-md-10">
                           <a href="{{ route('profile.torrents', ['id' => $user->id, 'name' => $user->name]) }}">
                               {{ $user->torrents()->count() }}  Uploaded Torrents
                           </a>
                        </div>
                    </div>


                     <!-- Display torrents seeded -->
                     <div class="row mb-3">
                        <label for="seeded_torrents" class="col-md-2 col-form-label text-md-end"><i class="bi bi-cloud-arrow-up" data-bs-toggle="tooltip" data-bs-title="Torrents Seeded"></i></label>
                        <div class="col-md-10">
                            <p class="form-control-static">
                            <a href="{{ route('profile.seedingTorrents', ['id' => $user->id, 'name' => $user->name]) }}">
                 Seeding Torrents
            </a>
                            </p>
                        </div>
                    </div>


                    <!-- Additional fields can be added here as needed -->
                </div>
            </div>
        </div>
    </div>





@endsection
