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

                    @if ($user->vip_until != NULL)
<div class="row mb-3">
    <label for="vip_until" class="col-md-4 col-form-label text-md-end">Vip Until</label>
    <div class="col-md-6">
    {{ \Carbon\Carbon::parse($user->vip_until)->format('d F Y, H:i') }}
    </div>
</div>

@else
@endif
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

                @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                    <!-- Email -->
                    <div class="row mb-3">
                        <label for="email" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-envelope" data-bs-toggle="tooltip" data-bs-title="Email"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->email }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Joined Date -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-calendar-check" data-bs-toggle="tooltip" data-bs-title="Joined"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->created_at->format('d-M-Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Last Seen -->
                    <div class="row mb-3">
                        <label for="last_activity" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-calendar2-heart" data-bs-toggle="tooltip" data-bs-title="Last Seen"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static"> {{ $user->last_activity ? $user->last_activity->format('d-M-Y H:i') : 'Never Active' }}</p>
                        </div>
                    </div>

                    <!-- Seedbonus -->
                    <div class="row mb-3">
                        <label for="seedbonus" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-piggy-bank-fill" data-bs-toggle="tooltip" data-bs-title="Seedbonus"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ $user->seedbonus }}</p>
                        </div>
                    </div>

                    <!-- Upload -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-arrow-up-circle-fill" data-bs-toggle="tooltip" data-bs-title="Uploaded"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}</p>
                        </div>
                    </div>
                    <!-- Download -->
                    <div class="row mb-3">
                        <label for="created_at" class="col-md-2 col-form-label text-md-end">
                            <i class="bi bi-arrow-down-circle-fill" data-bs-toggle="tooltip" data-bs-title="Downloaded"></i>
                        </label>
                        <div class="col-md-10">
                            <p class="form-control-static">{{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}</p>
                        </div>
                    </div>


                    <!-- Additional Info -->
                    @if(!empty($user->info))
<div class="row mb-3">
    <label for="info" class="col-md-2 col-form-label text-md-end">
        <i class="bi bi-info-square" data-bs-toggle="tooltip" data-bs-title="Info"></i>
    </label>
    <div class="col-md-10">
        <p class="form-control-static">{!! convertCustomTagsToHtml($user->info) !!}</p>
    </div>
</div>
@endif

@if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
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

                    @endif


                    <!-- Additional fields can be added here as needed -->
                </div>
            </div>
        </div>
    </div>





@endsection
