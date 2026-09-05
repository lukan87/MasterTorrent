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

.nav-link {
    padding: 0.4rem 0.6rem;
}

.user-image{
    width:36px;
    height:36px;
    border-radius:50%;
    border:2px solid rgba(255,255,255,.15);
    object-fit:cover;
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

<ul class="navbar-nav ms-auto">

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
<li class="nav-item position-relative">
    <a class="nav-link position-relative"
       href="{{ route('profile.tokens', ['id' => Auth::user()->id, 'name' => Auth::user()->name]) }}">

        <i class="bi bi-grid-1x2 fs-4" data-bs-toggle="tooltip" title="Tokens"></i>

        <span class="nav-badge badge {{ Auth::user()->slots > 0 ? 'bg-success' : 'bg-danger' }}">
            {{ Auth::user()->slots }}
        </span>
    </a>
</li>

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
                <a href="{{ route('messages.show', $last->id) }}" class="dropdown-item">
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

        <a href="/messages/inbox" class="dropdown-item dropdown-footer">
            See All Messages
        </a>
    </div>
</li>

@endauth

{{-- PROFILE (UNCHANGED) --}}
<li class="nav-item dropdown user-menu">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle shadow">
        <span class="d-none d-md-inline">
            <span style="color: {{ \App\Models\UserClass::getClassColor(Auth::user()->user_class) }}">
                {{ Auth::user()->name }} {{ auth()->user()->seeder_icon }}
            </span>
                         @if(Auth::user()->warned)
                        <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title=" Warned Until: {{ Auth::user()->warned_until->format('Y-m-d H:i') }}"></i>
                         @endif
                         @if(Auth::user()->donor === 'yes')
                        <i class="bi bi-star-fill text-success" data-bs-toggle="tooltip" title="Donor"></i>
                         @endif

                    </span> </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end" style="min-width: 400px;"> <!--begin::User Image-->
                            <li class="user-header"> <img src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" class="user-image rounded-circle shadow" alt="User Avatar">
                                <p>
                                {{ Auth::user()->name }} - {{ Auth::user()->role_name }}
                                    <small>Member since {{ Auth::user()->created_at }}</small>
                                    @if(Auth::user()->warned_until)
                                    <br>
                                    <small class="text-info">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Warned Until: {{ Auth::user()->warned_until->format('Y-m-d H:i') }}
                                    </small>
                                @endif

                                </p>
                            </li> <!--end::User Image--> <!--begin::Menu Body-->
                            <li class="user-body"> <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center fs-5" data-bs-toggle="tooltip" title="Uploaded"> <small><a href="#"><i class="bi bi-file-arrow-up text-success"></i> {{ \App\Helpers\FormatHelper::formatSize(Auth::user()->uploaded) }}</a></small> </div>
                                    <div class="col-4 text-center fs-5" data-bs-toggle="tooltip" title="Downloaded"> <small><a href="#"><i class="bi bi-file-arrow-down text-info"></i> {{ \App\Helpers\FormatHelper::formatSize(Auth::user()->downloaded) }}</a></small> </div>
                                    <div class="col-4 text-center fs-5">
                                        <small>
                                            <a href="/shop" data-bs-toggle="tooltip" title="Seedbonus">
                                            <i class="bi bi-piggy-bank fs-5 text-warning"></i>
                                             {{ Auth::user()->seedbonus }}
                                            </a>
                                        </small>
                                    </div>
                                </div> <!--end::Row-->
                                <div class="row">
                                <div class="col-4 text-center fs-5" data-bs-toggle="tooltip" title="Upload/Download Ratio">
                                     <small>
                                          <i class="bi bi-speedometer2"></i>
                                            @if(Auth::user()->downloaded > 0)
                                               {{ number_format(Auth::user()->uploaded / Auth::user()->downloaded, 2) }}
                                            @else
                                               &#8734; <!-- Displays infinity symbol if downloaded is 0 -->
                                            @endif
                                     </small>
                                </div>
                                <div class="col-4 text-center fs-5" data-bs-toggle="tooltip" title="Seeding">
                                <a href="{{ route('snatch.seeding') }}">
                                <i class="bi bi-cloud-arrow-up-fill text-success"></i>
                                     <small>
                                     {{ $seedingCount }}
                                     </small>
                                </a>
                                </div>
                                <div class="col-4 text-center fs-5" data-bs-toggle="tooltip" title="Leeching">
                                <a href="{{ route('snatch.leeching') }}">
                                <i class="bi bi-cloud-arrow-down-fill text-danger"></i>
                                     <small>
                                     {{ $leechingCount }}
                                     </small>
                                </a>
                                </div>
                                </div>
                                <div class="row">
                                    <div class="col-4 text-center fs-5"> <small><a href="{{ route('snatch.needToSeed') }}" data-bs-toggle="tooltip" title="Need to seed"><i class="bi bi-exclamation-triangle-fill" style="color: red;"></i> Need to Seed</a></small> </div>
                                    <div class="col-4 text-center fs-5"> <small><a href="{{ route('snatch.snatchlist') }}" data-bs-toggle="tooltip" title="Snatchlist"><i class="bi bi-file-arrow-down text-info fs-5"></i> Snatch List</a></small> </div>
                                    <div class="col-4 text-center fs-5">
                                        <small>
                                            <a href="{{ route('snatch.hitAndRun') }}" data-bs-toggle="tooltip" title="Hit&Run's">
                                            <i class="bi bi-person-exclamation" style="color: red;"></i>
                                             HNR's: {{ Auth::user()->hit_and_run_count }}
                                            </a>
                                        </small>
                                    </div>
                                </div> <!--end::Row-->
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
