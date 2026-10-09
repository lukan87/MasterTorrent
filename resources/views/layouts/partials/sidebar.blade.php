@php
    $canUpload = Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes';
    $sidebarGroups = [
        ['label' => 'Discover', 'links' => [
            ['route' => 'home', 'patterns' => ['home'], 'icon' => 'bi-house-door', 'label' => 'Home'],
            ['route' => 'torrents.index', 'patterns' => ['torrents.index', 'torrents.show', 'torrents.snatched'], 'icon' => 'bi-search', 'label' => 'Browse'],
            ['route' => 'torrents.adult', 'patterns' => ['torrents.adult'], 'icon' => 'bi-fire', 'label' => 'XXX'],
            ['route' => $canUpload ? 'torrents.create' : 'uploadapps.create', 'patterns' => $canUpload ? ['torrents.create', 'torrents.upload*'] : ['uploadapps.*'], 'icon' => 'bi-cloud-arrow-up', 'label' => $canUpload ? 'Upload' : 'Uploader application'],
            ['route' => 'requests.index', 'patterns' => ['requests.*'], 'icon' => 'bi-journal-plus', 'label' => 'Requests'],
            ['route' => 'seedboxes.index', 'patterns' => ['seedboxes.*'], 'icon' => 'bi-hdd-network', 'label' => 'Seedboxes'],
        ]],
        ['label' => 'Community', 'links' => [
            ['route' => 'forum.index', 'patterns' => ['forum.*'], 'icon' => 'bi-chat-square-text', 'label' => 'Forums'],
            ['route' => 'rules', 'patterns' => ['rules'], 'icon' => 'bi-shield-check', 'label' => 'Rules'],
            ['route' => 'team.index', 'patterns' => ['team.*'], 'icon' => 'bi-people', 'label' => 'Team'],
            ['route' => 'tickets.index', 'patterns' => ['tickets.*'], 'icon' => 'bi-ticket-detailed', 'label' => 'Support'],
        ]],
    ];
    $userSeedboxes = Auth::check() ? \App\Models\Seedbox::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name']) : collect();
    if (Auth::user()->user_class >= \App\Models\UserClass::MODERATOR) {
        $adminLinks = [
            ['route' => 'admin.index', 'patterns' => ['admin.*', 'happyhour.*'], 'icon' => 'bi-gear', 'label' => 'Admin panel'],
        ];
        if (Auth::user()->user_class >= \App\Models\UserClass::ADMIN) {
            $adminLinks[] = ['route' => 'contactstaff.index', 'patterns' => ['contactstaff.*'], 'icon' => 'bi-person-lines-fill', 'label' => 'Contact staff requests'];
        }
        $sidebarGroups[] = ['label' => 'Administration', 'links' => $adminLinks];
    }
@endphp

<aside class="app-sidebar glass" id="siteSidebar" aria-label="Sidebar">
    <div class="sidebar-brand">
        <a class="sidebar-brand-link" href="{{ route('home') }}" aria-label="{{ config('app.name') }} home">
            <img class="sidebar-brand-logo"
                 src="{{ asset('images/logo.png') }}"
                 alt="{{ config('app.name') }}"
                 width="1983" height="793"
                 decoding="async">
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav aria-label="Site navigation">
            <ul class="nav sidebar-menu flex-column">
                @foreach($sidebarGroups as $group)
                    <li class="nav-header">{{ $group['label'] }}</li>
                    @foreach($group['links'] as $link)
                        @php
                            $href = route($link['route'], $link['parameters'] ?? []);
                            $active = isset($link['parameters'])
                                ? request()->url() === $href
                                : request()->routeIs(...$link['patterns']);
                        @endphp
                        <li class="nav-item">
                            @if($link['route'] === 'seedboxes.index' && $userSeedboxes->count() > 1)
                                <div class="sidebar-seedbox-row">
                            @endif
                            <a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
                                <i class="nav-icon bi {{ $link['icon'] }}" aria-hidden="true"></i>
                                <p>{{ $link['label'] }}</p>
                                @if($link['route'] === 'tickets.index')
                                    <span class="ticket-badges ms-auto">
                                        @if(!empty($newTickets) && $newTickets > 0)
                                            <span class="badge bg-success" title="New tickets" aria-label="{{ $newTickets }} new tickets">{{ $newTickets }}</span>
                                        @endif
                                        @if(!empty($waitingStaffTickets) && $waitingStaffTickets > 0)
                                            <span class="badge bg-danger" title="Waiting for staff reply" aria-label="{{ $waitingStaffTickets }} waiting for staff reply">{{ $waitingStaffTickets }}</span>
                                        @endif
                                        @if(!empty($unassignedTickets) && $unassignedTickets > 0)
                                            <span class="badge bg-warning text-dark" title="Unassigned tickets" aria-label="{{ $unassignedTickets }} unassigned tickets">{{ $unassignedTickets }}</span>
                                        @endif
                                    </span>
                                @endif
                            </a>
                            @if($link['route'] === 'seedboxes.index' && $userSeedboxes->count() > 1)
                                @php($seedboxMenuOpen = $userSeedboxes->contains(fn ($box) => request()->url() === route('seedboxes.torrents', $box->id)))
                                <button type="button" class="sidebar-seedbox-toggle" data-bs-toggle="collapse" data-bs-target="#sidebar-seedbox-choices"
                                        aria-controls="sidebar-seedbox-choices" aria-expanded="{{ $seedboxMenuOpen ? 'true' : 'false' }}" aria-label="Choose a seedbox">
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </button>
                                </div>
                                <ul class="sidebar-seedbox-choices collapse {{ $seedboxMenuOpen ? 'show' : '' }}" id="sidebar-seedbox-choices" aria-label="Your seedboxes">
                                    @foreach($userSeedboxes as $box)
                                        @php($boxActive = request()->url() === route('seedboxes.torrents', $box->id))
                                        <li><a href="{{ route('seedboxes.torrents', $box->id) }}" class="sidebar-seedbox-choice {{ $boxActive ? 'active' : '' }}" @if($boxActive) aria-current="page" @endif>
                                            <i class="bi bi-hdd-network" aria-hidden="true"></i><span>{{ $box->name }}</span>
                                            @if($boxActive)<i class="bi bi-check2 ms-auto" aria-hidden="true"></i>@endif
                                        </a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </nav>
    </div>
</aside>

@once
<style>
.sidebar-seedbox-row { display: flex; align-items: center; gap: 4px; }
.sidebar-seedbox-row > .nav-link { flex: 1; min-width: 0; }
.sidebar-seedbox-toggle { display: grid; place-items: center; flex: 0 0 28px; height: 28px; margin-right: 8px; padding: 0; border: 1px solid var(--theme-border, #283445); border-radius: 7px; background: var(--theme-surface-alt, #101a28); color: var(--theme-muted, #9ba6b7); font-size: 11px; }
.sidebar-seedbox-toggle:hover, .sidebar-seedbox-toggle[aria-expanded="true"] { color: var(--theme-teal-text, #83ded1); border-color: var(--theme-teal-border, #83ded14d); }
.sidebar-seedbox-toggle[aria-expanded="true"] i { transform: rotate(180deg); }
.sidebar-seedbox-toggle:focus-visible, .sidebar-seedbox-choice:focus-visible { outline: 2px solid var(--theme-teal-text, #83ded1); outline-offset: 2px; }
.sidebar-seedbox-choices { list-style: none; margin: 0 8px 8px 25px; padding: 0 0 0 10px; border-left: 1px solid var(--theme-border, #283445); }
.sidebar-seedbox-choice { display: flex; align-items: center; gap: 8px; padding: 9px 10px; border-radius: 7px; color: var(--theme-muted, #9ba6b7); font-size: 12px; text-decoration: none; }
.sidebar-seedbox-choice span { min-width: 0; overflow-wrap: anywhere; }
.sidebar-seedbox-choice > i { flex-shrink: 0; }
.sidebar-seedbox-choice:hover, .sidebar-seedbox-choice.active { color: var(--theme-teal-text, #83ded1); background: var(--theme-teal-soft, #83ded114); }
</style>
@endonce
