@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <!-- Profile Sidebar -->
        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <span class="fw-medium">
                        {{ $user->name }} - {{ $user->role_name }}  
                        @if($user->warned)
                            <i class="bi bi-exclamation-triangle-fill text-warning ms-1" data-bs-toggle="tooltip" title="User is warned"></i>
                        @endif
                        @if($user->donor == 'yes')
                            <i class="bi bi-gem text-success ms-1" data-bs-toggle="tooltip" title="Donor"></i>
                        @endif
                    </span>

                    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                        <a href="{{ route('profile.edit', ['id' => $user->id, 'name' => $user->name]) }}" class="text-white" data-bs-toggle="tooltip" title="Edit Profile">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    @endif
                </div>

                <div class="card-body text-center">
                    <!-- Profile Image -->
                  <div class="mb-3 position-relative" style="width: 150px; height: 150px; margin: 0 auto;">
    @if($user->profile_image)
        <img src="{{ $user->profile_image }}" alt="Profile Image" 
             class="rounded-circle img-thumbnail w-100 h-100 object-fit-cover profile-image">
        <div class="profile-image-hover"></div>
    @else
        <div class="d-flex align-items-center justify-content-center rounded-circle bg-light w-100 h-100">
            <i class="bi bi-person-fill text-muted" style="font-size: 3rem;"></i>
        </div>
    @endif
</div>

                    @if ($user->vip_until != NULL)
                        <div class="alert alert-success py-2 mb-3">
                            <small class="d-block fw-bold">VIP Until</small>
                            {{ \Carbon\Carbon::parse($user->vip_until)->format('d F Y, H:i') }}
                        </div>
                    @endif

                    @if ($user->warned_until != NULL)
                        <div class="alert alert-warning py-2 mb-3">
                            <small class="d-block fw-bold">Warned Until</small>
                            {{ \Carbon\Carbon::parse($user->warned_until)->format('d F Y, H:i') }}
                        </div>
                    @endif

                    <a href="{{ route('messages.create', ['receiver_id' => $user->id]) }}" class="btn btn-success btn-sm w-100 mb-2">
                        <i class="bi bi-envelope-plus me-1"></i> Send Message
                    </a>

                    @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-info btn-sm w-100">
                            <i class="bi bi-shield-lock me-1"></i> Admin Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-lg-10 col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #2b2e31 0%, #2e373e 100%);">
                    <span class="fw-medium">{{ __('Profile Information') }}</span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- Basic Info Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 fw-semibold">
                                    <i class="bi bi-person-lines-fill me-2"></i>Basic Information
                                </h5>

                             <div class="badge bg-info text-dark fw-bold fs-6 py-2 mb-3">
    <i class="bi bi-calendar-check me-1"></i> Member since {{ $user->created_at->format('M Y') }}
</div>

                                <!-- Email (visible to user/admins) -->
                                @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-envelope me-1"></i> Email
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                            {{ $user->email }}
                                            {{-- @if ($user->email_verified_at)
                                                <span class="badge bg-success ms-2">Verified</span>
                                            @else
                                                <span class="badge bg-warning ms-2">Unverified</span>
                                            @endif --}}
                                        </div>
                                    </div>
                                </div>
                                @endif

                               

                                <!-- Last Seen -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-clock-history me-1"></i> Last Seen
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                            {{ $user->updated_at ? $user->updated_at->format('d-M-Y H:i') : 'Never Active' }}
                                            @if(method_exists($user, 'isOnline') && $user->isOnline())
    <span class="badge bg-success ms-2">Online Now</span>
@endif
                                        </div>
                                    </div>
                                </div>

                                <!-- IP (visible to user/admins) -->
                                @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-globe me-1"></i> IP Address
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                            {{ $user->IP }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Torrent Stats Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 fw-semibold">
                                    <i class="bi bi-cloud-arrow-down me-2"></i>Torrent Stats
                                </h5>

                               <div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10 border-primary">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-upload me-1"></i> Uploaded</h6>
                <h3 class="card-title">{{ \App\Helpers\FormatHelper::formatSize($user->uploaded) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info bg-opacity-10 border-info">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-upload me-1"></i> Downloaded</h6>
                <h3 class="card-title">{{ \App\Helpers\FormatHelper::formatSize($user->downloaded) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success bg-opacity-10 border-success">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-arrow-up-right-circle me-1"></i> Ratio</h6>
                <h3 class="card-title">{{ $user->downloaded > 0 ? number_format($user->uploaded / $user->downloaded, 2) : '∞' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning bg-opacity-10 border-warning">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-gem me-1"></i> Seedbonus</h6>
                <h3 class="card-title">{{ $user->seedbonus }}</h3>
            </div>
        </div>
    </div>
</div>



                                <!-- Torrents Uploaded -->
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-collection me-1"></i> Torrents
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                            <a href="{{ route('profile.torrents', ['id' => $user->id, 'name' => $user->name]) }}" class="text-decoration-none">
                                                {{ $user->torrents()->count() }} Uploaded
                                            </a>
                                        </div>
                                    </div>
                                </div>


                                <!-- Slots -->
                                @if (auth()->id() === $user->id || (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-hdd-stack me-1"></i> Slots
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                            <a href="{{ route('profile.tokens', ['id' => $user->id, 'name' => $user->name]) }}" class="text-decoration-none">
                                                {{ $user->slots }} Available
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                 <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label text-muted">
                                        <i class="bi bi-collection me-1"></i> Warnings
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="form-control-plaintext">
                                             <a href="{{ route('warnings.show', ['id' => $user->id, 'username' => $user->username]) }}">Warnings</a>
                                        </div>
                                    </div>
                                </div>

                                  @if (Auth::check() && Auth::id() === $user->id)
    <div class="row mt-3">
    <label class="col-sm-4 col-form-label text-muted">
        <i class="bi bi-trash me-1"></i> Delete
    </label>

    <div class="col-sm-8">
        <form action="{{ route('profile.delete', ['id' => $user->id, 'name' => $user->name]) }}"
              method="POST"
              onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account and all of your data.');">

            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-danger btn-sm w-100">
                <i class="bi bi-trash me-1"></i> Delete My Account
            </button>
        </form>
    </div>
</div>

@endif

                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- Additional Information -->
                            @if(!empty($user->info))
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 fw-semibold">
                                    <i class="bi bi-info-circle me-2"></i>About
                                </h5>
                                <div class="p-3 rounded profile-info-box">
                                    {!! convertCustomTagsToHtml($user->info) !!}
                                </div>
                            </div>
                            @endif

                            <!-- Admin Tools Section -->
                            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN))
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-3 fw-semibold">
                                    <i class="bi bi-tools me-2"></i>Admin Tools
                                </h5>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="{{ route('snatch.snatchlist', ['userId' => $user->id]) }}" class="btn btn-sm w-100 mb-2">
                                            <i class="bi bi-list-check me-1"></i> Snatchlist
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('snatch.seeding', ['userId' => $user->id]) }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                                            <i class="bi bi-upload me-1"></i> Seeding
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('snatch.leeching', ['userId' => $user->id]) }}" class="btn btn-outline-danger btn-sm w-100 mb-2">
                                            <i class="bi bi-download me-1"></i> Leeching
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('snatch.hitAndRun', ['userId' => $user->id]) }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i> H&R ({{$user->hit_and_run_count}})
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('snatch.needToSeed', ['userId' => $user->id]) }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                                            <i class="bi bi-clock-history me-1"></i> Need to Seed
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="{{ route('warnings.show', ['id' => $user->id, 'username' => $user->username]) }}" class="btn btn-outline-danger btn-sm w-100 mb-2">
                                            <i class="bi bi-exclamation-octagon me-1"></i> Warnings
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Timeline Section -->
                            @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::ADMIN || Auth::user()->name === $user->name))
                         <div class="mb-4">
    <h5 class="border-bottom pb-2 mb-3 fw-semibold d-flex align-items-center">
        <i class="bi bi-journal-text me-2"></i>Timeline
        <span class="badge bg-primary ms-auto">{{ $user->timeline->count() }}</span>
    </h5>

    @if ($user->timeline->isEmpty())
        <div class="alert alert-info py-2 mb-0">
            No timeline entries for this user.
        </div>
    @else
        <div class="timeline-container position-relative" style="max-height: 300px; overflow-y: auto;">
            <div class="timeline-line position-absolute start-0 h-100" style="width: 2px; background: #dee2e6; left: 1.5rem;"></div>
            <div class="list-group list-group-flush ps-4">
                @foreach ($user->timeline->sortByDesc('created_at') as $entry)
                <div class="list-group-item list-group-item-action position-relative ps-4 border-0 py-2">
                    <div class="timeline-dot position-absolute rounded-circle bg-primary" 
                         style="width: 12px; height: 12px; left: -5px; top: 1.25rem;"></div>
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div>
                            <p class="mb-1">{!! convertCustomTagsToHtml($entry->comment) !!}</p>
                            <small class="text-muted">
                                {{ $entry->created_at->format('M j, Y H:i') }}
                            </small>
                        </div>
                        @if ($entry->staff)
                            <span class="badge bg-dark ms-2">by {{ $entry->staff->name }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control-plaintext {
        padding: 0.375rem 0;
        line-height: 1.5;
    }
    
    .timeline-container::-webkit-scrollbar {
        width: 5px;
    }
    
    .timeline-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .timeline-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .timeline-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

.profile-image {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.profile-image:hover {
    transform: scale(1.05);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.profile-image-hover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.profile-image-container:hover .profile-image-hover {
    opacity: 1;
}

.btn-admin-tool {
    transition: all 0.2s ease;
}
.btn-admin-tool:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.timeline-dot {
    transition: all 0.3s ease;
}
.list-group-item:hover .timeline-dot {
    transform: scale(1.3);
    background: #0d6efd;
}
.profile-info-box {
    max-height: 450px;      
    overflow-y: auto;    
    overflow-x: hidden;     
    white-space: normal;   
}

</style>

@endsection