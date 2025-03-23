<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <i class="bi bi-globe-europe-africa opacity-75 shadow"></i>
        <span class="brand-text fw-light">My Site</span>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link">
                    <i class="bi bi-house-door-fill"></i>
                        <p>Home</p>
                    </a>
                </li>
                <li class="nav-item menu-open">
                    <a href="#" class="nav-link active">
                        <i class="bi bi-file-font-fill"></i>
                        <p>
                            Torrents
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('torrents.index') }}" class="nav-link">
                                <i class="bi bi-card-list"></i>
                                <p>Browse</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('torrents.adult') }}" class="nav-link">
                            <i class="bi bi-fire"></i>
                                <p>XXX</p>
                            </a>
                        </li>
                        @if (Auth::check() && (Auth::user()->user_class >= \App\Models\UserClass::UPLOADER || Auth::user()->uploadpos === 'yes'))
                            <li class="nav-item">
                                <a href="{{ route('torrents.create') }}" class="nav-link">
                                    <i class="bi bi-upload"></i>
                                    <p>Upload</p>
                                </a>
                            </li>

                            @else

                            <li class="nav-item">
                                <a href="{{ route('uploadapps.create') }}" class="nav-link">
                                    <i class="bi bi-upload"></i>
                                    <p>Uploader Application</p>
                                </a>
                            </li>
                        @endif


                        <li class="nav-item">
                            <a href="{{ route('requests.index') }}" class="nav-link">
                                <i class="bi bi-journal-plus"></i>
                                <p>Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('rss.index') }}" class="nav-link">
                                 <i class="bi bi-rss"></i>
                                <p>Rss Feed</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <hr>
                <li class="nav-item">
                    <a href="{{ route('shoutbox.index') }}" class="nav-link">
                        <i class="bi bi-chat-left-dots"></i>
                        <p>Chat</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('overforums.index') }}" class="nav-link">
                        <i class="bi bi-book-half"></i>
                        <p>Forums</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('shop') }}" class="nav-link">
                        <i class="bi bi-cart-plus"></i>
                        <p>Shop</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('donate') }}" class="nav-link">
                        <i class="bi bi-cash-coin"></i>
                        <p>Donate</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('rules') }}" class="nav-link">
                    <i class="bi bi-info-square-fill"></i>
                        <p>Rules</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/staff" class="nav-link">
                    <i class="bi bi-people-fill"></i>
                        <p>Staff</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tickets.index') }}" class="nav-link">
                        <i class="bi bi-ticket-detailed-fill"></i>
                        <p>Tickets</p>
                    </a>
                </li>
                <hr>
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
                            <a href="{{ !Route::is('movies.index') ? route('movies.index') : '#' }}" class="nav-link">
                                <i class="nav-icon bi bi-film"></i>
                                <p>Movies</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ !Route::is('series.index') ? route('series.index') : '#' }}" class="nav-link">
                                <i class="nav-icon bi bi-tv"></i>
                                <p>Series</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('collections.index') }}" class="nav-link">
                                <i class="nav-icon bi bi-collection-play"></i>
                                <p>Collections</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @if (Auth::check() && Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                    <li class="nav-header">Administration</li>
                    <li class="nav-item">
                        <a href="{{ route('admin.index') }}" class="nav-link">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>Admin Panel</p>
                        </a>
                    </li>
                @endif
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
 <!--end::Sidebar--> <!--begin::App Main-->
