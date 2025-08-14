@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h1 class="fw-bold text-gradient mb-0">
            <i class="bi bi-people-fill me-2"></i>User Management
        </h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.comments') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chat-square-text"></i> <span class="d-none d-md-inline">Comments</span>
            </a>
            <a href="{{ route('uploadapps.index') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-cloud-upload"></i> <span class="d-none d-md-inline">Upload Apps</span>
            </a>
            <a href="{{ route('warnings.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-exclamation-triangle"></i> <span class="d-none d-md-inline">Hit & Runs</span>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Main Content Column -->
        <div class="col-lg-8 order-2 order-lg-1">
            <!-- Search Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-2 p-md-3">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Search users..." 
                                   value="{{ request()->input('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel-fill d-md-none"></i>
                                <span class="d-none d-md-inline">Filter</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 py-2 py-md-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table me-2"></i>User Records
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark d-none d-md-table-header-group">
                                <tr>
                                    <th class="ps-3 ps-md-4">ID</th>
                                    <th>User</th>
                                    <th class="d-none d-md-table-cell">Contact</th>
                                    <th>Role</th>
                                    <th class="d-none d-sm-table-cell">Last Active</th>
                                    <th class="text-end pe-3 pe-md-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                <tr>
                                    <td class="ps-3 ps-md-4 fw-semibold d-none d-md-table-cell">{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 me-md-3">
                                                <img src="{{ $user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" 
                                                     class="rounded-circle" alt="{{ $user->name }}">
                                            </div>
                                            <div>
                                                <a href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}" 
                                                   class="fw-semibold text-decoration-none">
                                                    {{ $user->name }}
                                                </a>
                                                <div class="text-muted small d-md-none">{{ $user->email }}</div>
                                                <div class="text-muted small">{{ $user->IP }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $user->role_name }}
                                        </span>
                                    </td>
                                    <td class="d-none d-sm-table-cell">
                                        <span class="small text-muted" data-bs-toggle="tooltip" 
                                              title="{{ $user->updated_at->format('M j, Y g:i a') }}">
                                            {{ $user->updated_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 pe-md-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ url('warnings/' . $user->id) }}" 
                                               class="btn btn-outline-warning" data-bs-toggle="tooltip" 
                                               title="Warnings">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </a>
                                            <a href="{{ route('admin.users.show', $user->name) }}" 
                                               class="btn btn-outline-info" data-bs-toggle="tooltip" 
                                               title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user->id) }}" 
                                               class="btn btn-outline-primary" data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if (Auth::check() && Auth::user()->can_delete == 1)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" 
                                                        onclick="return confirm('Delete this user permanently?')"
                                                        data-bs-toggle="tooltip" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer border-0 py-2 py-md-3">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4 order-1 order-lg-2">
            <!-- Stats Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header border-0 py-2 py-md-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up me-2"></i>Registration Stats
                    </h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="row g-2 g-md-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center p-2 p-md-3 bg-primary bg-opacity-10 rounded">
                                <div class="bg-primary text-white rounded-circle p-2 p-md-3 me-2 me-md-3">
                                    <i class="bi bi-people-fill fs-5 fs-md-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Total Users</h6>
                                    <span class="text-muted small">{{ App\Models\User::count() }} registered</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-2 p-md-3 bg-success bg-opacity-10 rounded">
                                <div class="bg-success text-white rounded-circle p-2 p-md-3 me-2 me-md-3">
                                    <i class="bi bi-clock-history fs-5 fs-md-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Last 24h</h6>
                                    <span class="text-muted small">{{ $last24Hours }} new</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-2 p-md-3 bg-info bg-opacity-10 rounded">
                                <div class="bg-info text-white rounded-circle p-2 p-md-3 me-2 me-md-3">
                                    <i class="bi bi-calendar-week fs-5 fs-md-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Last Week</h6>
                                    <span class="text-muted small">{{ $lastWeek }} new</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center p-2 p-md-3 bg-warning bg-opacity-10 rounded">
                                <div class="bg-warning text-white rounded-circle p-2 p-md-3 me-2 me-md-3">
                                    <i class="bi bi-calendar-month fs-5 fs-md-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Last Month</h6>
                                    <span class="text-muted small">{{ $lastMonth }} new</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mass Message Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header border-0 py-2 py-md-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-send me-2"></i>Mass Message
                    </h5>
                </div>
                <div class="card-body p-2 p-md-3">
                    <form action="{{ route('admin.users.sendMassMessage') }}" method="POST">
                        @csrf
                        <div class="mb-2 mb-md-3">
                            <label class="form-label">Target User Classes</label>
                            <select name="user_class[]" class="form-select select2" multiple>
                                @foreach(App\Models\UserClass::getClasses() as $class => $name)
                                    <option value="{{ $class }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Content</label>
                            <textarea name="message" class="form-control" rows="3" 
                                      placeholder="Type your message here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send-fill me-1"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-gradient {
        background: linear-gradient(90deg, #4e54c8 0%, #8f94fb 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .avatar img {
        width: 36px;
        height: 36px;
        object-fit: cover;
    }
    @media (min-width: 768px) {
        .avatar img {
            width: 40px;
            height: 40px;
        }
    }
    .table-hover tbody tr:hover {
        background-color: rgba(78, 84, 200, 0.05);
    }
    .select2 {
        width: 100% !important;
    }
    /* Better mobile table display */
    @media (max-width: 767.98px) {
        .table-responsive {
            border: 0;
        }
        .table {
            font-size: 0.875rem;
        }
        .btn-group .btn {
            padding: 0.25rem;
        }
    }

    .avatar-placeholder {
    width: 100%;
    height: 100%;
    font-weight: bold;
    font-size: 0.75rem;
    text-transform: uppercase;
}
    .avatar-placeholder::before {
        content: attr(data-initials);
        display: block;
        width: 100%;
        height: 100%;
        line-height: 1.5;
        text-align: center;
        color: #fff;
        background-color: #007bff;
        border-radius: 50%;
    }
    .avatar-placeholder img {
        display: none;
    }
    .avatar-placeholder:hover img {
        display: block;
    }
    .avatar-placeholder:hover::before {
        display: none;
    }
    .avatar-placeholder:hover::after {
        content: attr(data-name);
        display: block;
        width: 100%;
        height: 100%;
        line-height: 1.5;
        text-align: center;
        color: #fff;
        background-color: #007bff;
        border-radius: 50%;
    }
</style>

@push('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialize Select2 for multi-select
        $('.select2').select2({
            placeholder: "Select user classes",
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true
        });
    });
</script>
@endpush
@endsection