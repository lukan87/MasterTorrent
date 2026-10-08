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
    if (Auth::user()->user_class >= \App\Models\UserClass::USER) {
        $sidebarGroups[] = ['label' => 'Watch online', 'links' => [
            ['route' => 'movies.index', 'patterns' => ['movies.*'], 'icon' => 'bi-film', 'label' => 'Online movies'],
            ['route' => 'series.index', 'patterns' => ['series.*'], 'icon' => 'bi-tv', 'label' => 'Online series'],
        ]];
    }
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
                        @php($active = request()->routeIs(...$link['patterns']))
                        <li class="nav-item">
                            <a href="{{ route($link['route']) }}" class="nav-link {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
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
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
