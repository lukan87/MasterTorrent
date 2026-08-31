@extends('layouts.app')

@section('content')

<div class="container-fluid px-3 px-md-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="fw-bold text-gradient mb-0">
            <i class="bi bi-people-fill me-2"></i>User Management
        </h1>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.comments') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chat-square-text me-1"></i> Comments
            </a>

            <a href="{{ route('uploadapps.index') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-cloud-upload me-1"></i> Upload Apps
            </a>

            <a href="{{ route('warnings.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-exclamation-triangle me-1"></i> Hit & Runs
            </a>
            @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
            <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people me-1"></i>User Mesages
            </a>
            @endif
        </div>
    </div>


    {{-- QUICK STATS ROW --}}
    <div class="row g-3 mb-4">

<div class="row g-3">

    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-people fs-3 text-primary me-3"></i>
                <div>
                    <div class="small text-muted">Total Users</div>
                    <div class="fw-bold">{{ App\Models\User::count() }}</div>
                </div>
            </div>
        </div>
    </div>

     {{-- Warned Users --}}
    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-exclamation-triangle fs-3 text-danger me-3"></i>
                <div>
                    <div class="small text-muted">Warned Users</div>
                    <div class="fw-bold">{{ $warnedUsers}}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Deleted Users --}}
    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-person-x fs-3 text-secondary me-3"></i>
                <div>
                    <div class="small text-muted">Deleted Users</div>
                    <div class="fw-bold">{{ $deletedUsers }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-clock-history fs-3 text-success me-3"></i>
                <div>
                    <div class="small text-muted">Last 24h</div>
                    <div class="fw-bold">{{ $last24Hours }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-calendar-week fs-3 text-info me-3"></i>
                <div>
                    <div class="small text-muted">Last Week</div>
                    <div class="fw-bold">{{ $lastWeek }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-4 col-sm-6">
        <div class="card glass border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-calendar-month fs-3 text-warning me-3"></i>
                <div>
                    <div class="small text-muted">Last Month</div>
                    <div class="fw-bold">{{ $lastMonth }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

    </div>


    <div class="row g-4">

        {{-- MAIN USERS TABLE --}}
        <div class="col-lg-9">

            {{-- SEARCH BAR --}}
<div class="torrent-search card glass border-0 mb-4">
<div class="card-body">

<form method="GET" action="{{ route('admin.users.index') }}">

<div class="row g-3">

{{-- keyword --}}
<div class="col-lg-3 col-md-3 col-sm-6">
<label class="form-label">Search</label>
<input type="text"
name="keyword"
class="form-control"
placeholder="Username / Email / IP"
value="{{ request('keyword') }}">
</div>

{{-- class --}}
<div class="col-lg-3 col-md-3 col-sm-6">
<label class="form-label">Class</label>
<select name="class" class="form-select">
<option value="">All</option>

@foreach($userClasses as $class => $name)
<option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>
{{ $name }}
</option>
@endforeach

</select>
</div>

{{-- warned --}}
<div class="col-lg-3 col-md-3 col-sm-6">
<label class="form-label">Warned</label>
<select name="warned" class="form-select">
<option value="">All</option>
<option value="yes" {{ request('warned')=='yes'?'selected':'' }}>Warned</option>
</select>
</div>

{{-- enabled --}}
<div class="col-lg-3 col-md-3 col-sm-6">
<label class="form-label">Account</label>
<select name="enabled" class="form-select">
<option value="">All</option>
<option value="no" {{ request('enabled')=='no'?'selected':'' }}>Disabled</option>
</select>
</div>

{{-- ratio --}}
<div class="col-lg-3 col-md-3 col-sm-4">
<label class="form-label">Ratio</label>
<select name="ratio" class="form-select">
<option value="">All</option>
<option value="low">Low</option>
</select>
</div>

{{-- inactive --}}
<div class="col-lg-3 col-md-3 col-sm-4">
<label class="form-label">Inactive</label>
<select name="inactive" class="form-select">
<option value="">-</option>
<option value="30">30d</option>
<option value="60">60d</option>
<option value="90">90d</option>
</select>
</div>

{{-- deleted --}}
<div class="col-lg-3 col-md-3 col-sm-4">
<label class="form-label">Deleted</label>
<select name="deleted" class="form-select">
<option value="">All</option>
<option value="no">Active</option>
<option value="only">Deleted</option>
</select>
</div>

</div>

<div class="mt-3 d-flex gap-2">

<button class="btn btn-primary">
<i class="bi bi-search"></i> Search
</button>

<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
<i class="bi bi-x-circle"></i> Reset
</a>

</div>

</form>

</div>
</div>


{{-- USERS LIST --}}
<div class="card glass shadow-sm border-0">

    <div class="card-header bg-transparent fw-semibold">
        <i class="bi bi-people me-2"></i>Users
    </div>

    <div class="card-body p-0">

        @foreach($users as $user)

        <div class="border-bottom py-3 px-3 px-md-4 {{ $user->trashed() ? 'bg-warning bg-opacity-10' : '' }}">

            <div class="row align-items-center g-3">

                {{-- AVATAR + USER --}}
                <div class="col-md-4">

                    <div class="d-flex align-items-center gap-3">

                        <img
                            src="{{ $user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                            class="rounded-circle"
                            width="42"
                            height="42"
                        >

                        <div>

                            <div class="fw-semibold">
                                <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" class="fw-semibold text-decoration-none">

                                {{ $user->name }}

                                @if($user->trashed())
                                    <span class="badge bg-danger ms-2">
                                        Deleted {{ $user->deleted_at->diffForHumans() }}
                                    </span>
                                @endif
                                </a>

                            </div>

                            <div class="small text-muted">
                                {{ $user->IP }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- EMAIL --}}
                <div class="col-md-3 small">

                    <i class="bi bi-envelope me-1 text-muted"></i>

                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                        {{ $user->email }}
                    </a>

                </div>


                {{-- ROLE --}}
                <div class="col-md-2">

                    <span class="badge bg-primary">
                        {{ $user->role_name }}
                    </span>

                </div>


                {{-- LAST ACTIVE --}}
                <div class="col-md-2 small text-muted">

                    <i class="bi bi-clock me-1"></i>
                    {{ $user->updated_at->diffForHumans() }}

                </div>


                {{-- ACTIONS --}}
                <div class="col-md-1 text-end">

                    <div class="d-flex justify-content-end gap-1">

                        {{-- ACTIVE USER ACTIONS --}}
                        @if(!$user->trashed())

                            <a href="{{ url('warnings/' . $user->id) }}"
                               class="btn btn-sm btn-outline-warning"
                               data-bs-toggle="tooltip"
                               title="Warnings">
                                <i class="bi bi-exclamation-triangle"></i>
                            </a>

                            <a href="{{ route('admin.users.show', $user->name) }}"
                               class="btn btn-sm btn-outline-info"
                               data-bs-toggle="tooltip"
                               title="View">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip"
                               title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                        @endif


                        {{-- DELETE / RESTORE --}}
                        @if (Auth::check() && Auth::user()->can_delete == 1)

                            @if(!$user->trashed())

                                <form action="{{ route('admin.users.destroy', $user->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Soft delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            @else

                                <form action="{{ route('admin.users.restore', $user->id) }}"
                                      method="POST">
                                    @csrf

                                    <button class="btn btn-sm btn-success"
                                            onclick="return confirm('Restore this user?')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.users.forceDelete', $user->id) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Permanently delete this user?')">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>

                            @endif

                        @endif

                    </div>

                </div>

            </div>

        </div>

        @endforeach

    </div>

    <div class="card-footer">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>

</div>

        </div>


        {{-- SIDEBAR --}}
        <div class="col-lg-3">

            {{-- MASS MESSAGE --}}
            <div class="card glass shadow-sm border-0">

                <div class="card-header fw-semibold">
                    <i class="bi bi-send me-2"></i>Mass Message
                </div>

                <div class="card-body">

<form id="massMessageForm" action="{{ route('admin.users.sendMassMessage') }}" method="POST">

@csrf

<div class="mb-3">

<label class="form-label">User Classes</label>

<select name="user_class[]" class="form-select select2" multiple required>

@foreach(App\Models\UserClass::getClasses() as $class => $name)

<option value="{{ $class }}">
{{ $name }}
</option>

@endforeach

</select>

</div>

<div class="mb-3">

<label class="form-label">Message</label>

<textarea
name="message"
class="form-control"
rows="9"
required
></textarea>

</div>

<div class="mb-3">

<button type="button" class="btn btn-outline-info w-100" id="previewRecipients">
<i class="bi bi-search me-1"></i>Preview Recipients
</button>

<div id="recipientPreview" class="small text-muted mt-2"></div>

</div>

<button class="btn btn-primary w-100">
<i class="bi bi-send-fill me-1"></i>Send
</button>

</form>
                </div>

            </div>

        </div>

    </div>

</div>

<script>

document.getElementById('previewRecipients').addEventListener('click', function () {

let form = document.getElementById('massMessageForm');
let formData = new FormData(form);

fetch("{{ route('admin.users.mass-message.preview') }}", {
method: "POST",
headers: {
'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
body: formData
})
.then(response => response.json())
.then(data => {

let preview = document.getElementById('recipientPreview');

preview.innerHTML =
'<span class="fw-semibold">Recipients:</span> ' + data.recipients + ' users';

if (data.recipients > 10000) {

preview.innerHTML +=
'<div class="text-danger mt-1">⚠ Large broadcast! Please confirm.</div>';

}

});

});

</script>

@endsection