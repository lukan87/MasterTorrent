@extends('layouts.admin')

@section('admin-content')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
<div class="mm-page au-page">
    @include('admin.mass-messages._navigation')

    <header class="au-hero">
        <div>
            <div class="mm-eyebrow">COMMUNITY ADMINISTRATION</div>
            <h1>Your community, at a glance.</h1>
            <p>Find members, review account activity, and keep your community running smoothly.</p>
        </div>
        <a class="btn btn-success" href="{{ route('admin.users.mass-messages.create') }}"><i class="bi bi-send me-2"></i>New broadcast</a>
    </header>

    <div class="au-stats">
        @foreach([
            [$totalUsers, 'Member accounts', 'people', 'mint', route('admin.users.index', ['deleted' => 'no']), 'Excludes deleted accounts'],
            [$warnedUsers, 'Warned members', 'exclamation-triangle', 'amber', route('admin.users.index', ['warned' => 'yes', 'deleted' => 'no']), 'Accounts requiring attention'],
            [$deletedUsers, 'Deleted accounts', 'person-x', 'rose', route('admin.users.index', ['deleted' => 'only']), 'Review and restore accounts'],
            [$last24Hours, 'Joined in 24 hours', 'clock-history', 'blue', null, 'New members today'],
            [$lastWeek, 'Joined in 7 days', 'calendar-week', 'violet', null, 'This week’s registrations'],
            [$lastMonth, 'Joined in past month', 'calendar-month', 'mint', null, 'Past month’s registrations'],
        ] as [$count, $label, $icon, $tone, $destination, $caption])
            <div class="au-stat au-{{ $tone }}">
                <div class="au-stat-top"><span>{{ $label }}</span><i class="bi bi-{{ $icon }}"></i></div>
                <strong>{{ number_format($count) }}</strong>
                <div class="au-stat-caption">{{ $caption }}</div>
                @if($destination)<a class="au-stat-link" href="{{ $destination }}" aria-label="View {{ strtolower($label) }}"><i class="bi bi-arrow-up-right"></i></a>@endif
            </div>
        @endforeach
    </div>

    <section class="mm-panel au-directory" aria-labelledby="userDirectoryTitle">
        <div class="au-directory-header">
            <div><div class="mm-eyebrow">MEMBER DIRECTORY</div><h2 id="userDirectoryTitle">User Management <span>{{ number_format($users->total()) }}</span></h2></div>
            <div class="au-quick-links" aria-label="Quick account filters">
                <a href="{{ route('admin.users.index') }}" @if(!request()->filled('deleted') && !request()->filled('warned')) aria-current="page" @endif>All accounts</a>
                <a href="{{ route('admin.users.index', ['warned' => 'yes']) }}" @if(request('warned') === 'yes') aria-current="page" @endif><i class="bi bi-exclamation-triangle"></i> Warned</a>
                <a href="{{ route('admin.users.index', ['deleted' => 'only']) }}" @if(request('deleted') === 'only') aria-current="page" @endif><i class="bi bi-archive"></i> Deleted</a>
            </div>
        </div>

        <form class="au-filter-form" method="GET" action="{{ route('admin.users.index') }}">
            <div class="au-primary-filters">
                <div class="au-search"><label for="userSearch">Find a member</label><div class="au-input-icon"><i class="bi bi-search"></i><input class="form-control" id="userSearch" name="keyword" value="{{ request('keyword') }}" placeholder="Username, email address or IP" maxlength="255"></div></div>
                <div><label for="userClass">User class</label><select class="form-select" id="userClass" name="class"><option value="">All classes</option>@foreach($userClasses as $class => $name)<option value="{{ $class }}" @selected(request()->filled('class') && (string) request('class') === (string) $class)>{{ $name }}</option>@endforeach</select></div>
                <div><label for="userDeleted">Account visibility</label><select class="form-select" id="userDeleted" name="deleted"><option value="">All accounts</option><option value="no" @selected(request('deleted') === 'no')>Existing accounts</option><option value="only" @selected(request('deleted') === 'only')>Deleted accounts</option></select></div>
                <button class="btn btn-success"><i class="bi bi-search me-1"></i>Search</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Reset</a>
            </div>
            <details class="au-advanced" @if(collect(request()->only(['warned', 'enabled', 'ratio', 'inactive', 'ip', 'email', 'seedbonus']))->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty()) open @endif>
                <summary><i class="bi bi-sliders me-2"></i>More filters <span>Account health, activity and contact details</span><i class="bi bi-chevron-down ms-auto"></i></summary>
                <div class="au-advanced-grid">
                    <div><label for="userWarned">Warnings</label><select class="form-select" id="userWarned" name="warned"><option value="">All members</option><option value="yes" @selected(request('warned') === 'yes')>Warned members</option></select></div>
                    <div><label for="userEnabled">Account access</label><select class="form-select" id="userEnabled" name="enabled"><option value="">All accounts</option><option value="no" @selected(request('enabled') === 'no')>Disabled accounts</option></select></div>
                    <div><label for="userRatio">Share ratio</label><select class="form-select" id="userRatio" name="ratio"><option value="">Any ratio</option><option value="low" @selected(request('ratio') === 'low')>Below 0.5</option></select></div>
                    <div><label for="userInactive">Inactive for</label><select class="form-select" id="userInactive" name="inactive"><option value="">Any activity</option>@foreach([30, 60, 90] as $days)<option value="{{ $days }}" @selected((string) request('inactive') === (string) $days)>{{ $days }}+ days</option>@endforeach @if(request()->filled('inactive') && !in_array((int) request('inactive'), [30, 60, 90], true))<option value="{{ request('inactive') }}" selected>{{ request('inactive') }}+ days</option>@endif</select></div>
                    <div><label for="userIp">IP address contains</label><input class="form-control" id="userIp" name="ip" value="{{ request('ip') }}" maxlength="45" placeholder="e.g. 192.168."></div>
                    <div><label for="userEmail">Email contains</label><input class="form-control" id="userEmail" name="email" value="{{ request('email') }}" maxlength="255" placeholder="e.g. @example.com"></div>
                    <div><label for="userBonus">Bonus below</label><input class="form-control" id="userBonus" name="seedbonus" type="number" min="0" step="any" value="{{ request('seedbonus') }}" placeholder="Any amount"></div>
                </div>
            </details>
        </form>

        @php
            $activeFilters = collect(request()->only(['keyword', 'class', 'deleted', 'warned', 'enabled', 'ratio', 'inactive', 'ip', 'email', 'seedbonus']))->filter(fn ($value) => $value !== null && $value !== '');
            $filterLabels = ['keyword' => 'Search', 'class' => 'Class', 'deleted' => 'Visibility', 'warned' => 'Warned', 'enabled' => 'Access', 'ratio' => 'Ratio', 'inactive' => 'Inactive days', 'ip' => 'IP', 'email' => 'Email', 'seedbonus' => 'Bonus below'];
        @endphp
        <div class="au-results-meta">
            <span>{{ $users->total() ? number_format($users->firstItem()).'–'.number_format($users->lastItem()).' of '.number_format($users->total()).' accounts' : '0 accounts found' }}</span>
            @if($activeFilters->isNotEmpty())<div class="au-filter-chips">@foreach($activeFilters as $key => $value)<a href="{{ route('admin.users.index', request()->except($key, 'page')) }}" aria-label="Remove {{ $filterLabels[$key] }} filter">{{ $filterLabels[$key] }}: {{ $key === 'class' ? ($userClasses[$value] ?? $value) : $value }}<i class="bi bi-x"></i></a>@endforeach</div>@else<span>All accounts · ordered by member ID</span>@endif
        </div>
        <div class="au-list-heading" aria-hidden="true"><span>Member</span><span>Contact details</span><span>Account</span><span>Last active</span><span>Actions</span></div>
        <div class="au-members">
            @forelse($users as $user)
                @php $deleted = $user->trashed(); @endphp
                <article class="au-member {{ $deleted ? 'au-member-deleted' : '' }}" aria-label="Account for {{ $user->name }}">
                    <div class="au-identity">
                        <div class="au-avatar"><img src="{{ $user->profile_image ?: asset('images/default_avatar/default-avatar.jpg') }}" alt="" width="48" height="48" loading="lazy">@if(!$deleted && $user->last_activity?->gt(now()->subMinutes(5)))<span class="au-online" title="Active in the last 5 minutes"></span>@endif</div>
                        <div><a class="au-name" href="{{ route('profile.show', ['id' => $user->id, 'name' => $user->name]) }}">{{ $user->name }}</a><div class="mm-muted">Member #{{ $user->id }}</div><div class="au-role">{{ $userClasses[$user->user_class] ?? $user->role_name ?? 'Unknown class' }}</div></div>
                    </div>
                    <div class="au-contact"><a href="mailto:{{ $user->email }}"><i class="bi bi-envelope"></i><span>{{ $user->email }}</span></a><div class="mm-muted"><i class="bi bi-globe2"></i>{{ $user->IP ?: 'No IP recorded' }}</div></div>
                    <div class="au-account"><span class="au-status {{ $deleted ? 'au-status-deleted' : ($user->enabled === 'no' ? 'au-status-disabled' : 'au-status-active') }}"><span></span>{{ $deleted ? 'Deleted' : ($user->enabled === 'no' ? 'Disabled' : 'Enabled') }}</span>@if($user->warned)<span class="au-warning"><i class="bi bi-exclamation-triangle"></i> Warned</span>@endif @if($deleted)<div class="mm-muted mt-1">{{ $user->deleted_at->diffForHumans() }}</div>@endif</div>
                    <div class="au-activity"><span class="au-mobile-label">Last active</span>@if($user->last_activity)<time datetime="{{ $user->last_activity->toIso8601String() }}" title="{{ $user->last_activity->format('d M Y, H:i').' UTC' }}">{{ $user->last_activity->diffForHumans() }}</time><div class="mm-muted">{{ $user->last_activity->format('d M Y') }}</div>@else<span class="mm-muted">No activity recorded</span>@endif</div>
                    <div class="au-actions">
                        @if(!$deleted)
                            <a class="btn btn-sm btn-outline-warning au-icon-action" href="{{ url('warnings/'.$user->id) }}" title="Warnings for {{ $user->name }}" aria-label="Warnings for {{ $user->name }}"><i class="bi bi-exclamation-triangle"></i></a>
                            <a class="btn btn-sm btn-outline-info" href="{{ route('admin.users.show', $user->name) }}"><i class="bi bi-eye me-1"></i>View</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user->id) }}"><i class="bi bi-pencil me-1"></i>Edit</a>
                        @endif
                        @if(Auth::check() && Auth::user()->can_delete == 1)
                            @if(!$deleted)
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" data-mm-confirm="Soft delete {{ $user->name }}? The account can be restored later.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger au-icon-action" title="Delete {{ $user->name }}" aria-label="Delete {{ $user->name }}"><i class="bi bi-trash"></i></button></form>
                            @else
                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" data-mm-confirm="Restore {{ $user->name }}?">@csrf<button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button></form>
                                <form action="{{ route('admin.users.forceDelete', $user->id) }}" method="POST" data-mm-confirm="Permanently delete {{ $user->name }} and their associated data? This cannot be undone.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger au-icon-action" title="Permanently delete {{ $user->name }}" aria-label="Permanently delete {{ $user->name }}"><i class="bi bi-x-circle"></i></button></form>
                            @endif
                        @endif
                    </div>
                </article>
            @empty
                <div class="mm-empty"><i class="bi bi-person-search"></i><h2>No members found</h2><p>Try a different name, account status or activity filter.</p><a class="btn btn-outline-success" href="{{ route('admin.users.index') }}">Clear filters</a></div>
            @endforelse
        </div>
        <footer class="au-directory-footer"><span class="mm-muted">{{ number_format($users->total()) }} matching accounts</span>{{ $users->links('pagination::bootstrap-5') }}</footer>
    </section>
</div>
@endsection
