<nav class="app-header navbar navbar-expand bg-body sticky-top" data-bs-theme="dark"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"> <i class="bi bi-list"></i> </a> </li>
                    <!-- <li class="nav-item d-md-block"> <a href="/" class="nav-link"><i class="bi bi-house-door-fill" data-bs-toggle="tooltip" title="Home"> Home</i></a> </li> -->
                    <!-- <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link">Contact</a> </li> -->
                </ul> <!--end::Start Navbar Links--> <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto"> <!--begin::Navbar Search-->
                    <!-- <li class="nav-item"> <a class="nav-link" data-widget="navbar-search" href="#" role="button"> <i class="bi bi-search"></i> </a> </li> end::Navbar Search begin::Messages Dropdown Menu -->
                    <li class="nav-item dropdown">
    <a class="nav-link" data-bs-toggle="dropdown" href="#">
        <i class="bi bi-chat-text"></i>
        <span class="navbar-badge badge {{ $unreadMessagesCount > 0 ? 'text-bg-danger' : 'text-bg-success' }}">
    {{ $unreadMessagesCount }}
</span>

    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        @foreach ($messages as $message)
            <a href="{{ route('messages.show', $message) }}" class="dropdown-item">
                <!-- Begin::Message -->
                <div class="d-flex">
    <div class="flex-shrink-0">
        @if($message->sender && $message->sender->profile_image)
            <img src="{{ $message->sender->profile_image }}" alt="User Avatar" class="img-fluid rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
        @else
            <img src="{{ asset('images/default_avatar/default-avatar.jpg') }}" alt="Default Avatar" class="img-fluid rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
        @endif
    </div>
    <div class="flex-grow-1" style="max-width: calc(100% - 60px);"> <!-- 100% - width of the image (50px) + margin (10px) -->
        <h3 class="dropdown-item-title">
            {{ $message->sender->name ?? 'Unknown' }}
            <span class="float-end fs-7 {{ $message->is_read == 0 ? 'text-danger' : 'text-success' }}"><i class="bi bi-star-fill"></i></span>
        </h3>
        <p class="fs-7">{!! convertCustomTagsToHtml(Str::limit(strip_tags($message->body), 50)) !!}</p>

        <p class="fs-7 text-secondary">
            <i class="bi bi-clock-fill me-1"></i> {{ $message->created_at->diffForHumans() }}
        </p>
    </div>
</div>
                <!-- End::Message -->
            </a>
            <div class="dropdown-divider"></div>
        @endforeach

        <a href="/messages/inbox" class="dropdown-item dropdown-footer">See All Messages</a>
    </div>
</li>

<!--end::Messages Dropdown Menu--> <!--begin::Notifications Dropdown Menu-->
                    <!-- <li class="nav-item dropdown"> <a class="nav-link" data-bs-toggle="dropdown" href="#"> <i class="bi bi-bell-fill"></i> <span class="navbar-badge badge text-bg-warning">15</span> </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end"> <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div> <a href="#" class="dropdown-item"> <i class="bi bi-envelope me-2"></i> 4 new messages
                                <span class="float-end text-secondary fs-7">3 mins</span> </a>
                            <div class="dropdown-divider"></div> <a href="#" class="dropdown-item"> <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                <span class="float-end text-secondary fs-7">12 hours</span> </a>
                            <div class="dropdown-divider"></div> <a href="#" class="dropdown-item"> <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                <span class="float-end text-secondary fs-7">2 days</span> </a>
                            <div class="dropdown-divider"></div> <a href="#" class="dropdown-item dropdown-footer">
                                See All Notifications
                            </a>
                        </div>
                    </li> end::Notifications Dropdown Menu begin::Fullscreen Toggle -->

                    <li class="nav-item dropdown user-menu"> <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"> <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle shadow" alt="User Avatar"><span class="d-none d-md-inline">

                         <span style="color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}">
                                            {{ Auth::user()->name }}
                         </span>

                    </span> </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end"> <!--begin::User Image-->
                            <li class="user-header text-bg-secondary"> <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle shadow" alt="User Avatar">
                                <p>
                                {{ Auth::user()->name }} - {{ Auth::user()->role_name }}
                                    <small>Member since {{ Auth::user()->created_at }}</small>

                                </p>
                            </li> <!--end::User Image--> <!--begin::Menu Body-->
                            <li class="user-body"> <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center"> <small><a href="#"><i class="bi bi-file-arrow-up" data-bs-toggle="tooltip" title="Uploaded"></i> {{ \App\Helpers\FormatHelper::formatSize(Auth::user()->uploaded) }}</a></small> </div>
                                    <div class="col-4 text-center"> <small><a href="#"><i class="bi bi-file-arrow-down" data-bs-toggle="tooltip" title="Downloaded"></i> {{ \App\Helpers\FormatHelper::formatSize(Auth::user()->downloaded) }}</a></small> </div>
                                    <div class="col-4 text-center">
                                        <small>
                                            <a href="/shop">
                                            <i class="bi bi-piggy-bank" data-bs-toggle="tooltip" title="Seedbonus"></i>
                                             {{ Auth::user()->seedbonus }}
                                            </a>
                                        </small>
                                    </div>
                                </div> <!--end::Row-->
                                <div class="row">
                                <div class="col-4 text-center">
                                     <small>
                                          <i class="bi bi-speedometer2" data-bs-toggle="tooltip" title="Upload/Download Ratio"></i>
                                            @if(Auth::user()->downloaded > 0)
                                               {{ number_format(Auth::user()->uploaded / Auth::user()->downloaded, 2) }}
                                            @else
                                               &#8734; <!-- Displays infinity symbol if downloaded is 0 -->
                                            @endif
                                     </small>
                                </div>
                                <div class="col-4 text-center">
                                <i class="bi bi-cloud-arrow-up-fill" data-bs-toggle="tooltip" title="Seeding"></i>
                                     <small>
                                     {{ $seedingCount }}
                                     </small>
                                </div>
                                <div class="col-4 text-center">
                                <i class="bi bi-cloud-arrow-down-fill" data-bs-toggle="tooltip" title="Leeching"></i>
                                     <small>
                                     {{ $leechingCount }}
                                     </small>
                                </div>
                                </div>
                            </li> <!--end::Menu Body--> <!--begin::Menu Footer-->
                            <li class="user-footer">
                            <a href="{{ route('profile.show', ['id' => Auth::user()->id, 'name' => Auth::user()->name]) }}" class="btn btn-default btn-flat">Profile</a>

                            <a class="btn btn-default btn-flat float-end" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                            </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

                           </li> <!--end::Menu Footer-->
                        </ul>
                    </li> <!--end::User Menu Dropdown-->
                </ul> <!--end::End Navbar Links-->
            </div> <!--end::Container-->
        </nav> <!--end::Header--> <!--begin::Sidebar-->
