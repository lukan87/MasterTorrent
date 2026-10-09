<nav class="app-header navbar navbar-expand bg-body-info glass sticky-top" aria-label="Main navigation">
<div class="container-fluid">

<ul class="navbar-nav">
    <li class="nav-item">
        <button type="button" class="nav-link navbar-icon-button" data-lte-toggle="sidebar" aria-label="Toggle sidebar" aria-controls="siteSidebar">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
    </li>
</ul>

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
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('tv-calendar.*') ? 'active' : '' }}" href="{{ route('tv-calendar.index') }}" @if(request()->routeIs('tv-calendar.*')) aria-current="page" @endif>
            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>TV Calendar
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
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('tv-calendar.*') ? 'active' : '' }}" href="{{ route('tv-calendar.index') }}" data-bs-toggle="tooltip" title="TV Calendar" aria-label="TV Calendar" @if(request()->routeIs('tv-calendar.*')) aria-current="page" @endif>
            <i class="bi bi-calendar3 fs-4" aria-hidden="true"></i>
        </a>
    </li>
</ul>

<ul class="navbar-nav ms-auto">

<li class="nav-item" data-torrent-loading hidden>
    <span class="nav-link navbar-icon-button" role="status">
        <span class="spinner-border spinner-border-sm text-info" aria-hidden="true"></span>
        <span class="visually-hidden">Loading content…</span>
    </span>
</li>

{{-- Appearance is available on desktop and mobile, beside activity controls. --}}
<li class="nav-item dropdown theme-menu">
    <button type="button" id="themeMenuToggle" class="nav-link navbar-icon-button"
            data-bs-toggle="dropdown" aria-expanded="false" aria-controls="themeMenuPanel"
            aria-label="Appearance: Dark" title="Appearance: Dark">
        <i class="bi bi-moon-stars fs-4" data-theme-icon aria-hidden="true"></i>
    </button>
    <ul id="themeMenuPanel" class="dropdown-menu dropdown-menu-end theme-dropdown p-2" aria-labelledby="themeMenuToggle">
        <li><h6 class="dropdown-header">Appearance</h6></li>
        <li><button type="button" class="dropdown-item theme-choice" data-theme-choice="light" aria-pressed="false"><i class="bi bi-sun" aria-hidden="true"></i><span>Light</span><i class="bi bi-check2 theme-choice-check" aria-hidden="true"></i></button></li>
        <li><button type="button" class="dropdown-item theme-choice" data-theme-choice="dark" aria-pressed="true"><i class="bi bi-moon-stars" aria-hidden="true"></i><span>Dark</span><i class="bi bi-check2 theme-choice-check" aria-hidden="true"></i></button></li>
        <li><button type="button" class="dropdown-item theme-choice" data-theme-choice="system" aria-pressed="false"><i class="bi bi-circle-half" aria-hidden="true"></i><span>System / Auto</span><i class="bi bi-check2 theme-choice-check" aria-hidden="true"></i></button></li>
    </ul>
</li>

{{-- Library (mobile <md: offcanvas trigger) --}}
<li class="nav-item d-md-none">
    <button type="button" class="nav-link navbar-icon-button" data-bs-toggle="offcanvas" data-bs-target="#libraryOffcanvas" aria-label="Open library" aria-controls="libraryOffcanvas">
        <i class="bi bi-collection-play fs-4" aria-hidden="true"></i>
    </button>
</li>

{{-- Facebook --}}
<!-- <li class="nav-item">
    <a class="nav-link" href="https://www.facebook.com/" target="_blank">
        <i class="bi bi-facebook fs-4" data-bs-toggle="tooltip" title="Facebook"></i>
    </a>
</li> -->

{{-- RSS --}}
<li class="nav-item d-none d-sm-block">
    <a class="nav-link" href="{{ route('rss.index') }}" aria-label="RSS feeds">
        <i class="bi bi-rss fs-4" data-bs-toggle="tooltip" title="RSS"></i>
    </a>
</li>

@auth

@if(auth()->user()->enabled !== 'no')
<li class="nav-item">
    <a class="nav-link navbar-icon-button" href="{{ route('torznab.setup') }}"
       data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-container="body"
       title="Connect Prowlarr, Radarr and Sonarr"
       aria-label="Prowlarr, Radarr and Sonarr setup instructions">
        <i class="bi bi-plug fs-4" aria-hidden="true"></i>
    </a>
</li>
@endif

{{-- Notifications --}}
<li class="nav-item dropdown position-relative">
    <button type="button" class="nav-link navbar-icon-button position-relative" data-bs-toggle="dropdown" aria-label="Notifications" aria-expanded="false">
        <i class="bi bi-bell fs-4" aria-hidden="true"></i>

        <span data-notification-count class="nav-badge badge bg-danger" @if(!auth()->user()->unreadNotifications->count()) hidden @endif>{{ auth()->user()->unreadNotifications->count() }}</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end navbar-activity-dropdown theme-surface theme-text p-2 shadow-lg">
        @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
            @php $type = $notification->data['type'] ?? null; @endphp

            <li>
                @if($type === 'torrent_deleted')

                    <div class="dropdown-item theme-text">
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

                    <div class="dropdown-item theme-text">
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
                        <button type="submit" class="dropdown-item theme-text text-start bg-transparent border-0 w-100">

                            <div class="fw-semibold">
                                @if($type === 'achievement_unlocked')
                                    <i class="bi bi-trophy-fill text-warning me-1" aria-hidden="true"></i>
                                    <strong class="text-wrap">{{ $notification->data['title'] ?? 'Achievement unlocked' }}</strong>
                                @elseif($type === 'request_filled')
                    @include('notifications.request-filled', ['data' => $notification->data])
                @elseif(in_array($type, ['torrent_comment', 'torrent_reaction'], true))
                                    @include('notifications.torrent-activity', ['data' => $notification->data])
                                @elseif($type === 'forum_mention')

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
    <a class="nav-link position-relative" href="{{ route('announcements.index') }}" aria-label="Announcements">
        <i class="bi bi-megaphone fs-4" data-bs-toggle="tooltip" title="Announcements"></i>

        <span id="announcement-badge" class="nav-badge badge bg-danger d-none"></span>
    </a>
</li>

{{-- Messages --}}
<li class="nav-item dropdown position-relative">
    <button type="button" class="nav-link navbar-icon-button position-relative" data-bs-toggle="dropdown" aria-label="Messages" aria-expanded="false">
        <i class="bi bi-envelope fs-4" aria-hidden="true"></i>

        <span data-message-count class="nav-badge badge {{ $unreadMessagesCount > 0 ? 'bg-danger' : 'bg-success' }}">
            {{ $unreadMessagesCount }}
        </span>
    </button>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end navbar-activity-dropdown">
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

                        <div class="flex-grow-1 navbar-message-content">
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
    <button type="button" class="nav-link dropdown-toggle" id="userMenuToggle" aria-expanded="false" aria-controls="userMenuPanel" aria-label="Account menu for {{ Auth::user()->name }}">
        <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle" alt="">
        <span class="navbar-account-name d-none d-md-inline">
            <span style="--member-color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}; color: var(--member-color)">
                {{ Auth::user()->name }} {{ auth()->user()->seeder_icon }}
            </span>
            @if(Auth::user()->warned)
                <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title="Warned Until: {{ optional(Auth::user()->warned_until)->format('Y-m-d H:i') }}"></i>
            @endif
            @if(Auth::user()->donor === 'yes')
                <i class="bi bi-star-fill text-warning" data-bs-toggle="tooltip" title="Donor"></i>
            @endif
        </span>
        <i class="bi bi-chevron-down account-chevron" aria-hidden="true"></i>
    </button>

    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end profile-dropdown" id="userMenuPanel" aria-labelledby="userMenuToggle">
        {{-- Cover: avatar, name, role, connection status --}}
        <li class="profile-cover">
            <img
                class="profile-cover-avatar"
                src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                alt="{{ Auth::user()->name }}">
            <div class="profile-cover-info">
                <div class="profile-cover-name" style="--member-color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}; color: var(--member-color)">
                    {{ Auth::user()->name }}
                    @if(Auth::user()->donor === 'yes')
                        <i class="bi bi-star-fill text-warning" data-bs-toggle="tooltip" title="Donor"></i>
                    @endif
                </div>
                <div class="profile-cover-role">
                    <span>{{ Auth::user()->role_name }}</span>
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
                        <i class="bi bi-person-fill-add"></i><span>Invites ({{ Auth::user()->invites }})</span>
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
<div class="offcanvas offcanvas-start" tabindex="-1" id="libraryOffcanvas" aria-labelledby="libraryOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="libraryOffcanvasLabel">
            <i class="bi bi-collection-play me-1"></i>Library
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <a href="{{ route('library.movies.index') }}" class="library-link">
            <i class="bi bi-film"></i>
            <span>Movies</span>
        </a>
        <a href="{{ route('library.series.index') }}" class="library-link">
            <i class="bi bi-tv"></i>
            <span>Series</span>
        </a>
        <a href="{{ route('tv-calendar.index') }}" class="library-link">
            <i class="bi bi-calendar3"></i>
            <span>TV Calendar</span>
        </a>
        <a href="{{ route('rss.index') }}" class="library-link d-sm-none">
            <i class="bi bi-rss" aria-hidden="true"></i>
            <span>RSS feeds</span>
        </a>
    </div>
</div>

<script src="{{ asset('js/navigation.js') }}?v={{ filemtime(public_path('js/navigation.js')) }}" defer></script>
