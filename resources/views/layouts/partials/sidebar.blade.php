<aside class="app-sidebar glass shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <i class="bi bi-globe2 opacity-75 shadow fs-3"></i>
        <span class="brand-text fw-light fs-3">{{ config('app.name') }}</span>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-1 fs-6">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }} nav-link">
                         <i class="bi bi-house-door-fill"></i>
                        <p>Home</p>
                    </a>
                </li>

                        <li class="nav-item">
                            <a href="{{ route('torrents.index') }}" class="{{ request()->routeIs('torrents.index') ? 'active' : '' }} nav-link">
                                <i class="bi bi-search"></i>
                                <p>Browse</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('torrents.adult') }}" class="{{ request()->routeIs('torrents.adult') ? 'active' : '' }} nav-link">
                            <i class="bi bi-fire"></i>
                                <p>XXX</p>
                            </a>
                        </li>
                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))
                            <li class="nav-item">
                                <a href="{{ route('torrents.create') }}" class="{{ request()->routeIs('torrents.create') ? 'active' : '' }} nav-link">
                                    <i class="bi bi-upload"></i>
                                    <p>Upload</p>
                                </a>
                            </li>

                            @else

                            <li class="nav-item">
                                <a href="{{ route('uploadapps.create') }}" class="{{ request()->routeIs('uploadapps.create') ? 'active' : '' }} nav-link">
                                    <i class="bi bi-upload"></i>
                                    <p>Uploader Application</p>
                                </a>
                            </li>
                        @endif


                        <li class="nav-item">
                            <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.index') ? 'active' : '' }} nav-link">
                                <i class="bi bi-journal-plus"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ route('rss.index') }}" class="nav-link">
                                 <i class="bi bi-rss"></i>
                                <p>Rss Feed</p>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ route('seedboxes.index') }}" class="{{ request()->routeIs('seedboxes.index') ? 'active' : '' }} nav-link" data-bs-toggle="tooltip" title="Connect and manage your seedboxes on LastFiles">
                                  <i class="bi bi-hdd-network"></i>
                                <p>Seedbox</p>
                            </a>
                        </li>

                <hr>
                {{-- <li class="nav-item">
                    <a href="{{ route('shoutbox.index') }}" class="nav-link">
                        <i class="bi bi-chat-left-dots"></i>
                        <p>Chat</p>
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a href="{{ route('forums.index') }}" class="{{ request()->routeIs('forums.index') ? 'active' : '' }} nav-link">
                        <i class="bi bi-book-half"></i>
                        <p>Forums</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }} nav-link">
                        <i class="bi bi-cart-plus"></i>
                        <p>Shop</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('donate') }}" class="{{ request()->routeIs('donate') ? 'active' : '' }} nav-link">
                        <i class="bi bi-cash-coin"></i>
                        <p>Donate</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('rules') }}" class="{{ request()->routeIs('rules') ? 'active' : '' }} nav-link">
                    <i class="bi bi-info-square-fill"></i>
                        <p>Rules</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('team.index') }}" class="{{ request()->routeIs('team.index') ? 'active' : '' }} nav-link">
                    <i class="bi bi-people-fill"></i>
                        <p>Team</p>
                    </a>
                </li>
<li class="nav-item">
<a href="{{ route('tickets.index') }}"
class="{{ request()->routeIs('tickets.index') ? 'active' : '' }} nav-link">

<i class="bi bi-ticket-detailed-fill"></i>

<p>Tickets</p>

<div class="ticket-badges ms-auto">

@if(!empty($newTickets) && $newTickets > 0)
<span class="badge bg-success"
data-bs-toggle="tooltip"
title="New tickets">
{{ $newTickets }}
</span>
@endif

@if(!empty($waitingStaffTickets) && $waitingStaffTickets > 0)
<span class="badge bg-danger"
data-bs-toggle="tooltip"
title="Waiting for staff reply">
{{ $waitingStaffTickets }}
</span>
@endif

@if(!empty($unassignedTickets) && $unassignedTickets > 0)
<span class="badge bg-warning text-dark"
data-bs-toggle="tooltip"
title="Unassigned tickets">
{{ $unassignedTickets }}
</span>
@endif

</div>

</a>
</li>
                <hr>
                 @if(Auth::user()->user_class >= \App\Models\UserClass::USER)
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-globe"></i>
                        <p>
                            Online
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ !Route::is('movies.index') ? route('movies.index') : '#' }}" class="{{ request()->routeIs('movies.index') ? 'active' : '' }} nav-link">
                                <i class="nav-icon bi bi-film"></i>
                                <p>Movies</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ !Route::is('series.index') ? route('series.index') : '#' }}" class="{{ request()->routeIs('series.index') ? 'active' : '' }} nav-link">
                                <i class="nav-icon bi bi-tv"></i>
                                <p>Series</p>
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a href="{{ route('collections.index') }}" class="nav-link">
                                <i class="nav-icon bi bi-collection-play"></i>
                                <p>Collections</p>
                            </a>
                        </li> -->
                    </ul>
                </li>
                @endif
             
    @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
        <li class="nav-header">Administration</li>
        <li class="nav-item">
            <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.index') ? 'active' : '' }} nav-link">
                <i class="nav-icon bi bi-gear"></i>
                <p>Admin Panel</p>
            </a>
        </li>
        @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
        <li class="nav-item">
            <a href="{{ route('contactstaff.index') }}" class="{{ request()->routeIs('contactstaff.index') ? 'active' : '' }} nav-link">
                <i class="bi bi-person-lines-fill"></i>
                <p>Contact Staff Requests</p>
            </a>
        </li>
        @endif
    @endif

    


            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
 <!--end::Sidebar--> <!--begin::App Main-->
<style>

/* Sidebar container */

.app-sidebar{
    backdrop-filter: blur(12px);
    border-right:1px solid #1f2937;
    box-shadow:
        4px 0 20px rgba(0,0,0,0.4),
        inset -1px 0 0 rgba(255,255,255,0.03);
}
/* Brand */

.sidebar-brand{
    display:flex;
    align-items:center;
    gap:10px;
    padding:16px 18px;
    border-bottom:1px solid #1f2937;
    font-weight:500;
    letter-spacing:.3px;
}

/* Menu spacing */

.sidebar-menu{
    padding-top:10px;
}

/* Links */

.sidebar-menu .nav-link{
    display:flex;
    align-items:center;
    gap:3px;
    color:#cbd5e1;
    border-radius:8px;
    margin:1px 1px;
   
    font-size:14px;
    transition:all .2s ease;
    position:relative;
}

.sidebar-menu .nav-link.active::before{
    content:"";
    position:absolute;
    left:-4px;
    top:6px;
    bottom:6px;
    width:4px;
    border-radius:4px;
    background:#72b3aa;
}

/* Icon alignment */

.sidebar-menu .nav-link i{
    width:20px;
    text-align:center;
    font-size:16px;
    opacity:.9;
}

/* Hover */

.sidebar-menu .nav-link:hover{
    background:#1e293b;
    color:#fff;
    transform:translateX(3px);
}

/* Active */

.sidebar-menu .nav-link.active{
    background:linear-gradient(135deg,#505872,#2d374b);
    color:#fff !important;
    font-weight:500;
    box-shadow:0 4px 10px rgba(0,0,0,.35);
}

/* Active icon */

.sidebar-menu .nav-link.active i{
    color:#fff;
}

/* Tree menu */

.nav-treeview{
    margin-left:8px;
}

/* Tree item */

.nav-treeview .nav-link{
    font-size:13px;
    padding:8px 10px;
}

/* Active tree */

.nav-treeview .nav-link.active{
    background:#1e293b;
    border-left:3px solid #4f7cff;
    padding-left:14px;
}

/* Section header */

.nav-header{
    color:#64748b;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
    padding:14px 18px 6px;
}

/* Separator */

.sidebar-menu hr{
    border-color:#1f2937;
    margin:14px 14px;
}

/* Scroll */

.sidebar-wrapper{
    overflow-y:auto;
    height:calc(100vh - 70px);
}

/* Scrollbar */

.sidebar-wrapper::-webkit-scrollbar{
    width:6px;
}

.sidebar-wrapper::-webkit-scrollbar-thumb{
    background:#1e293b;
    border-radius:10px;
}

.ticket-badges{
display:flex;
gap:4px;
margin-left:auto;
align-items:center;
}

.ticket-badges .badge{
font-size:11px;
padding:4px 6px;
border-radius:6px;
}

@keyframes badgePulse{
0%{opacity:1}
50%{opacity:.45}
100%{opacity:1}
}

.badge.bg-danger{
animation:badgePulse 1.5s infinite;
}
    
</style>