@php
    $latestPeer = Auth::user()->peers()->latest('updated_at')->first();
    $connectable = $latestPeer ? $latestPeer->connectable : false;
@endphp

<style>
.nav-badge {
    position: absolute;
    top: 4px;
    right: 2px;
    font-size: 0.6rem;
    padding: 2px 5px;
    border-radius: 10px;
    line-height: 1;
}

.app-header .nav-link {
    padding: 0.4rem 0.6rem;
}

.user-image{
    width:36px;
    height:36px;
    border-radius:50%;
    border:2px solid rgba(255,255,255,.15);
    object-fit:cover;
}

/* -------- Library offcanvas (mobile) -------- */
#libraryOffcanvas {
    background: linear-gradient(160deg, rgba(22, 32, 51, 0.98), rgba(9, 14, 24, 0.98)) !important;
    border-right: 1px solid var(--ui-border) !important;
}
#libraryOffcanvas .offcanvas-header {
    border-bottom: 1px solid var(--ui-border);
    padding: 1rem 1.25rem;
}
#libraryOffcanvas .offcanvas-body {
    padding: 0.75rem 0;
}
#libraryOffcanvas .offcanvas-title {
    font-weight: 700;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #94a3b8;
}
#libraryOffcanvas .library-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    color: #e2e8f0;
    font-weight: 500;
    border-radius: 0;
    transition: background-color 120ms ease, color 120ms ease;
}
#libraryOffcanvas .library-link:hover,
#libraryOffcanvas .library-link:focus {
    background: rgba(99, 210, 198, 0.1);
    color: #fff;
}
#libraryOffcanvas .library-link i {
    font-size: 1.2rem;
    width: 1.4rem;
    text-align: center;
    color: #63d2c6;
}

/* -------- Profile dropdown -------- */
.profile-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    left: auto;
    margin-top: 0.125rem;
    min-width: 380px !important;
    max-width: 94vw;
    padding: 0 !important;
    border-radius: 1rem !important;
    border: 1px solid var(--ui-border) !important;
    background: linear-gradient(160deg, rgba(22, 32, 51, 0.98), rgba(9, 14, 24, 0.98)) !important;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55) !important;
}

.profile-cover {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.25rem 1.1rem;
    border-bottom: 1px solid var(--ui-border);
    background:
        radial-gradient(120% 130% at 90% -20%, rgba(99, 210, 198, 0.2), transparent 55%),
        radial-gradient(120% 150% at -10% 120%, rgba(99, 210, 198, 0.1), transparent 50%);
}

.profile-cover-avatar {
    flex: 0 0 auto;
    width: 4.2rem;
    height: 4.2rem;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(99, 210, 198, 0.45);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
    background: #1e293b;
}

.profile-cover-name {
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-cover-role {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.15rem;
    font-size: 0.78rem;
    color: #9fb0c6;
}

.profile-status-dot {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0.55rem;
    border-radius: 2rem;
    font-size: 0.68rem;
    font-weight: 600;
}

.profile-status-ok {
    color: #6ee7b7;
    background: rgba(16, 185, 129, 0.14);
    border: 1px solid rgba(52, 211, 153, 0.25);
}

.profile-status-bad {
    color: #fca5a5;
    background: rgba(239, 68, 68, 0.14);
    border: 1px solid rgba(248, 113, 113, 0.25);
}

.profile-cover-member {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.35rem;
    font-size: 0.72rem;
    color: #6b7c93;
}

/* Stats */
.profile-stats {
    padding: 0.9rem 1rem 0.5rem;
    border-bottom: 1px solid var(--ui-border);
}

.profile-stat {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.6rem;
    border: 1px solid var(--ui-border);
    border-radius: 0.8rem;
    background: rgba(15, 23, 42, 0.55);
    min-height: 3.3rem;
    transition: background-color 120ms ease, border-color 120ms ease, transform 120ms ease;
}

.profile-stat:hover,
.profile-stat:focus {
    background: rgba(99, 210, 198, 0.09);
    border-color: rgba(99, 210, 198, 0.25);
    transform: translateY(-1px);
    text-decoration: none;
}

.profile-stat-icon {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.55rem;
    font-size: 1.05rem;
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid var(--ui-border);
}

.profile-stat-value {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    line-height: 1.2;
    color: #e8eef7;
}

.profile-stat-label {
    display: block;
    font-size: 0.64rem;
    font-weight: 600;
    line-height: 1.3;
    color: #8194ab;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Quick links */
.profile-links {
    padding: 0.6rem 1rem 0.9rem;
    border-bottom: 1px solid var(--ui-border);
}

.profile-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    padding: 0.55rem 0.25rem;
    border-radius: 0.7rem;
    color: #cbd5e1;
    font-size: 0.72rem;
    font-weight: 500;
    text-decoration: none !important;
    transition: background-color 120ms ease, color 120ms ease, transform 120ms ease;
}

.profile-link i {
    font-size: 1.05rem;
}

.profile-link:hover,
.profile-link:focus {
    background: rgba(99, 210, 198, 0.1);
    color: #fff;
    transform: translateY(-1px);
}

/* Footer */
.profile-footer {
    display: flex;
    gap: 0.6rem;
    padding: 0.85rem 1rem;
    background: rgba(9, 14, 24, 0.6);
}

.profile-btn {
    flex: 1;
    border-radius: 0.65rem;
    font-weight: 600;
    text-decoration: none !important;
}

/* Slide the profile panel in from the side (right) */
.profile-dropdown {
    transform-origin: top right;
}

.profile-dropdown.show {
    animation: profileDropdownIn 0.28s cubic-bezier(0.22, 0.9, 0.25, 1);
}

@keyframes profileDropdownIn {
    from {
        opacity: 0;
        transform: translateX(24px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@media (max-width: 575.98px) {
    .profile-dropdown {
        min-width: 92vw !important;
        max-width: 92vw;
    }
}
</style>

<nav class="app-header navbar navbar-expand bg-body-info glass sticky-top" data-bs-theme="dark">
<div class="container-fluid">

<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#">
            <i class="bi bi-list"></i>
        </a>
    </li>
</ul>

{{-- Live torrent search --}}
<form class="d-none d-lg-flex ms-lg-3 me-3" action="{{ route('torrents.index') }}" method="GET" role="search">
    <div class="input-group input-group-sm header-search">
        <span class="input-group-text" aria-hidden="true">
            <i class="bi bi-search"></i>
        </span>
        <input
            type="text"
            name="keyword"
            class="form-control"
            placeholder="Search torrents…"
            value="{{ request('keyword') }}"
            aria-label="Search torrents">
    </div>
</form>

{{-- Library (desktop lg+: icon + text) --}}
<ul class="navbar-nav d-none d-lg-flex flex-row ms-2">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('library.movies.index') }}">
            <i class="bi bi-film me-1"></i>Movies
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('library.series.index') }}">
            <i class="bi bi-tv me-1"></i>Series
        </a>
    </li>
</ul>

{{-- Library (medium md–lg: icons only) --}}
<ul class="navbar-nav d-none d-md-flex d-lg-none flex-row ms-2">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('library.movies.index') }}" data-bs-toggle="tooltip" title="Movies">
            <i class="bi bi-film fs-4"></i>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('library.series.index') }}" data-bs-toggle="tooltip" title="Series">
            <i class="bi bi-tv fs-4"></i>
        </a>
    </li>
</ul>

<ul class="navbar-nav ms-auto">

{{-- Library (mobile <md: offcanvas trigger) --}}
<li class="nav-item d-md-none">
    <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#libraryOffcanvas">
        <i class="bi bi-collection-play fs-4" data-bs-toggle="tooltip" title="Library"></i>
    </a>
</li>

{{-- Facebook --}}
<li class="nav-item">
    <a class="nav-link" href="https://www.facebook.com/" target="_blank">
        <i class="bi bi-facebook fs-4" data-bs-toggle="tooltip" title="Facebook"></i>
    </a>
</li>

{{-- RSS --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('rss.index') }}">
        <i class="bi bi-rss fs-4" data-bs-toggle="tooltip" title="RSS"></i>
    </a>
</li>

@auth

{{-- Notifications --}}
<li class="nav-item dropdown position-relative">
    <a class="nav-link position-relative" data-bs-toggle="dropdown">
        <i class="bi bi-bell fs-4"></i>

        @if(auth()->user()->unreadNotifications->count())
            <span class="nav-badge badge bg-danger">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end bg-dark text-light p-2 shadow-lg" style="min-width:320px;">
        @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
            @php $type = $notification->data['type'] ?? null; @endphp

            <li>
                @if($type === 'torrent_deleted')

                    <div class="dropdown-item text-light">
                        <div class="fw-semibold">
                            <i class="bi bi-trash-fill text-danger me-1"></i>
                            Your torrent <strong>{{ $notification->data['torrent_name'] }}</strong> was deleted
                        </div>

                        @if(!empty($notification->data['deleted_by']))
                            <div class="small text-muted">
                                Deleted by {{ $notification->data['deleted_by'] }}
                            </div>
                        @endif

                        @if(!empty($notification->data['reason']))
                            <div class="small text-danger">
                                {{ $notification->data['reason'] }}
                            </div>
                        @endif

                        <div class="small text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                @elseif($type === 'torrent_updated')

                    <div class="dropdown-item text-light">
                        <div class="fw-semibold">
                            <i class="bi bi-pencil-square text-primary me-1"></i>
                            Subscribed torrent <strong>{{ $notification->data['torrent_name'] }}</strong> was updated
                        </div>

                        @if(!empty($notification->data['updated_by']))
                            <div class="small text-muted">
                                Updated by {{ $notification->data['updated_by'] }}
                            </div>
                        @endif

                        <div class="small text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                @else

                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-light text-start bg-transparent border-0 w-100">

                            <div class="fw-semibold">
                                @if($type === 'forum_mention')

    <i class="bi bi-at text-warning me-1"></i>

    <strong>{{ $notification->data['author'] ?? 'Someone' }}</strong>

    mentioned you in

    <em>{{ $notification->data['topic_title'] ?? 'a forum topic' }}</em>

    {{-- Forum Like --}}
@elseif($type === 'forum_like')

    <i class="bi bi-heart-fill text-danger me-1"></i>

    <strong>{{ $data['author'] ?? 'Someone' }}</strong>
    liked your post

    @if(!empty($data['topic_title']))
        <em class="d-block mt-1">
            {{ $data['topic_title'] }}
        </em>
    @endif

@elseif($type === 'forum_reply')
                                    <i class="bi bi-chat-dots-fill text-info me-1"></i>
                                    <strong>{{ $notification->data['author'] }}</strong>
                                    replied to <em>{{ $notification->data['topic_title'] }}</em>
                                @elseif(isset($notification->data['author']))
                                    <i class="bi bi-chat-dots-fill text-info me-1"></i>
                                    <strong>{{ $notification->data['author'] }}</strong>
                                    replied to your post
                                @else
                                    <i class="bi bi-bell-fill text-warning me-1"></i>
                                    New activity
                                @endif
                            </div>

                            <div class="small text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>

                        </button>
                    </form>

                @endif
            </li>
        @empty
            <li class="dropdown-item text-muted">No new notifications</li>
        @endforelse

        <li><hr class="dropdown-divider border-secondary"></li>

        <li>
            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center text-info fw-semibold">
                <i class="bi bi-list-ul me-1"></i> View all notifications
            </a>
        </li>
    </ul>
</li>

{{-- Tokens --}}
<!-- <li class="nav-item position-relative">
    <a class="nav-link position-relative"
       href="{{ route('profile.tokens', ['id' => Auth::user()->id, 'name' => Auth::user()->name]) }}">

        <i class="bi bi-grid-1x2 fs-4" data-bs-toggle="tooltip" title="Tokens"></i>

        <span class="nav-badge badge {{ Auth::user()->slots > 0 ? 'bg-success' : 'bg-info' }}">
            {{ Auth::user()->slots }}
        </span>
    </a>
</li> -->

{{-- Announcements --}}
<li class="nav-item position-relative">
    <a class="nav-link position-relative" href="{{ route('announcements.index') }}">
        <i class="bi bi-megaphone fs-4" data-bs-toggle="tooltip" title="Announcements"></i>

        <span id="announcement-badge" class="nav-badge badge bg-danger d-none"></span>
    </a>
</li>

{{-- Messages --}}
<li class="nav-item dropdown position-relative">
    <a class="nav-link position-relative" data-bs-toggle="dropdown">
        <i class="bi bi-envelope fs-4"></i>

        <span class="nav-badge badge {{ $unreadMessagesCount > 0 ? 'bg-danger' : 'bg-success' }}">
            {{ $unreadMessagesCount }}
        </span>
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        @foreach ($conversations as $conversation)
            @php
                $last = $conversation->lastMessage;
                $other = Auth::id() == $conversation->user_one ? $conversation->userTwo : $conversation->userOne;
            @endphp

            @if($last)
                <a href="{{ route('conversations.show', $conversation->id) }}" class="dropdown-item">
                    <div class="d-flex">

                        <div class="flex-shrink-0">
                            <img src="{{ $other->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                                 class="rounded-circle me-3"
                                 style="width:50px;height:50px;object-fit:cover;">
                        </div>

                        <div class="flex-grow-1" style="max-width:calc(100% - 60px)">
                            <h3 class="dropdown-item-title">
                                {{ $other->name ?? 'Unknown' }}

                                <span class="float-end fs-7 {{ !$last->is_read && $last->receiver_id == Auth::id() ? 'text-danger' : 'text-success' }}">
                                    <i class="bi bi-check-circle"></i>
                                </span>
                            </h3>

                            <p class="fs-7">
                                {{ Str::limit(strip_tags(convertCustomTagsToHtml($last->body)), 60) }}
                            </p>

                            <p class="fs-7 text-secondary">
                                <i class="bi bi-clock-fill me-1"></i>
                                {{ $last->created_at->diffForHumans() }}
                            </p>
                        </div>

                    </div>
                </a>

                <div class="dropdown-divider"></div>
            @endif
        @endforeach

        <a href="{{ route('messages.index') }}" class="dropdown-item dropdown-footer text-center">
            <i class="bi bi-chat-dots-fill me-1"></i>See All Messages
        </a>
    </div>
</li>

@endauth

{{-- PROFILE DROPDOWN --}}
<li class="nav-item dropdown user-menu">
    <a href="#" class="nav-link dropdown-toggle" id="userMenuToggle">
        <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle shadow">
        <span class="d-none d-md-inline">
            <span style="color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}">
                {{ Auth::user()->name }} {{ auth()->user()->seeder_icon }}
            </span>
            @if(Auth::user()->warned)
                <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title="Warned Until: {{ optional(Auth::user()->warned_until)->format('Y-m-d H:i') }}"></i>
            @endif
            @if(Auth::user()->donor === 'yes')
                <i class="bi bi-star-fill text-warning" data-bs-toggle="tooltip" title="Donor"></i>
            @endif
        </span>
    </a>

    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end profile-dropdown">
        {{-- Cover: avatar, name, role, connection status --}}
        <li class="profile-cover">
            <img
                class="profile-cover-avatar"
                src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                alt="{{ Auth::user()->name }}">
            <div class="min-w-0">
                <div class="profile-cover-name" style="color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}">
                    {{ Auth::user()->name }}
                    @if(Auth::user()->donor === 'yes')
                        <i class="bi bi-star-fill text-warning" data-bs-toggle="tooltip" title="Donor"></i>
                    @endif
                </div>
                <div class="profile-cover-role">
                    <span>{{ Auth::user()->role_name }}</span>
                    <!-- <span class="profile-status-dot {{ $connectable ? 'profile-status-ok' : 'profile-status-bad' }}" data-bs-toggle="tooltip" title="{{ $connectable ? 'Port open — you are connectable' : 'Port closed — not connectable' }}">
                        <i class="bi {{ $connectable ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        {{ $connectable ? 'Connectable' : 'Not Connectable' }}
                    </span> -->
                </div>
                <div class="profile-cover-member">
                    <i class="bi bi-calendar3"></i>
                    Member since {{ Auth::user()->created_at->format('M Y') }}
                    @if(Auth::user()->warned)
                        <span class="badge text-bg-danger ms-1" data-bs-toggle="tooltip" title="Warned until {{ optional(Auth::user()->warned_until)->format('Y-m-d H:i') }}">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Warned
                        </span>
                    @endif
                </div>
            </div>
        </li>

        {{-- Quick stats --}}
        <li class="profile-stats">
            <div class="row g-2">
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('snatch.seeding') }}" data-bs-toggle="tooltip" title="Uploaded traffic">
                        <span class="profile-stat-icon text-success"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                        <span><span class="profile-stat-value">{{ \App\Helpers\FormatHelper::formatSize(Auth::user()->uploaded) }}</span><span class="profile-stat-label">Uploaded</span></span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('snatch.leeching') }}" data-bs-toggle="tooltip" title="Downloaded traffic">
                        <span class="profile-stat-icon text-info"><i class="bi bi-cloud-arrow-down-fill"></i></span>
                        <span><span class="profile-stat-value">{{ \App\Helpers\FormatHelper::formatSize(Auth::user()->downloaded) }}</span><span class="profile-stat-label">Downloaded</span></span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('snatch.snatchlist') }}" data-bs-toggle="tooltip" title="Upload / Download ratio">
                        <span class="profile-stat-icon text-primary"><i class="bi bi-speedometer2"></i></span>
                        <span>
                            <span class="profile-stat-value">
                                @if(Auth::user()->downloaded > 0)
                                    {{ number_format(Auth::user()->uploaded / Auth::user()->downloaded, 2) }}
                                @else
                                    &#8734;
                                @endif
                            </span><span class="profile-stat-label">Ratio</span>
                        </span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('shop') }}" data-bs-toggle="tooltip" title="Seedbonus shop">
                        <span class="profile-stat-icon text-warning"><i class="bi bi-piggy-bank"></i></span>
                        <span><span class="profile-stat-value">{{ number_format(Auth::user()->seedbonus) }}</span><span class="profile-stat-label">Seedbonus</span></span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('snatch.seeding') }}" data-bs-toggle="tooltip" title="Torrents you are seeding">
                        <span class="profile-stat-icon text-success"><i class="bi bi-arrow-up-circle-fill"></i></span>
                        <span><span class="profile-stat-value">{{ $seedingCount }}</span><span class="profile-stat-label">Seeding</span></span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('snatch.leeching') }}" data-bs-toggle="tooltip" title="Torrents you are leeching">
                        <span class="profile-stat-icon text-danger"><i class="bi bi-arrow-down-circle-fill"></i></span>
                        <span><span class="profile-stat-value">{{ $leechingCount }}</span><span class="profile-stat-label">Leeching</span></span>
                    </a>
                </div>
                <div class="col-6">
                    <a class="profile-stat" href="{{ route('profile.tokens', ['id' => Auth::user()->id, 'name' => Auth::user()->name]) }}" data-bs-toggle="tooltip" title="Freeleech Tokens">
                        <span class="profile-stat-icon text-info"><i class="bi bi-grid-1x2"></i></span>
                        <span><span class="profile-stat-value {{ Auth::user()->slots }}">
            {{ Auth::user()->slots }}
        </span><span class="profile-stat-label">Freeleech Tokens</span></span>
                    </a>
                </div>
            </div>
        </li>

        {{-- Quick links --}}
        <li class="profile-links">
            <div class="row g-1">
                <div class="col-4">
                    <a class="profile-link" href="{{ route('invites.index') }}">
                        <i class="bi bi-envelope-plus-fill"></i><span>Invites ({{ Auth::user()->invites }})</span>
                    </a>
                </div>
                <div class="col-4">
                    <a class="profile-link" href="{{ route('snatch.hitAndRun') }}">
                        <i class="bi bi-person-exclamation"></i><span>Hit &amp; Runs ({{ Auth::user()->hit_and_run_count }})</span>
                    </a>
                </div>
                <div class="col-4">
                    <a class="profile-link" href="{{ route('snatch.needToSeed') }}">
                        <i class="bi bi-hourglass-split"></i><span>Needs Seed</span>
                    </a>
                </div>
            </div>
        </li>

        {{-- Footer --}}
        <li class="profile-footer">
            <a href="{{ route('profile.show', ['id' => Auth::user()->id, 'name' => Auth::user()->name]) }}" class="btn btn-outline-info btn-sm profile-btn">
                <i class="bi bi-person-fill me-1"></i>View Profile
            </a>
            <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm profile-btn"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </li>
    </ul>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</li>
                </ul> <!--end::End Navbar Links-->
            </div> <!--end::Container-->
        </nav> <!--end::Header--> <!--begin::Sidebar-->

{{-- Library offcanvas (mobile) --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="libraryOffcanvas" aria-labelledby="libraryOffcanvasLabel" data-bs-theme="dark">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="libraryOffcanvasLabel">
            <i class="bi bi-collection-play me-1"></i>Library
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <a href="{{ route('library.movies.index') }}" class="library-link">
            <i class="bi bi-film"></i>
            <span>Online Movies</span>
        </a>
        <a href="{{ route('library.series.index') }}" class="library-link">
            <i class="bi bi-tv"></i>
            <span>Online Series</span>
        </a>
    </div>
</div>

<script>
(function () {
    const toggle = document.getElementById('userMenuToggle');
    const menu   = toggle?.closest('.user-menu')?.querySelector('.profile-dropdown');
    if (!toggle || !menu) return;

    let openTimer, closeTimer;
    const OPEN_DELAY  = 140;
    const CLOSE_DELAY = 220;

    function open()  { clearTimeout(closeTimer); openTimer  = setTimeout(() => menu.classList.add('show'), OPEN_DELAY); }
    function close() { clearTimeout(openTimer);  closeTimer = setTimeout(() => menu.classList.remove('show'), CLOSE_DELAY); }

    toggle.addEventListener('mouseenter', open);
    toggle.addEventListener('mouseleave', close);
    menu.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    menu.addEventListener('mouseleave', close);

    toggle.addEventListener('click', e => { e.preventDefault(); menu.classList.toggle('show'); });

    document.addEventListener('keydown', e => { if (e.key === 'Escape') menu.classList.remove('show'); });
    document.addEventListener('click', e => { if (!toggle.contains(e.target) && !menu.contains(e.target)) menu.classList.remove('show'); });
})();
</script>

