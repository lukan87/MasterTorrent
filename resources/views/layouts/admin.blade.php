@extends('layouts.app')

@section('content')
<div class="admin-workspace container-fluid py-3">
    <a class="admin-skip" href="#admin-page">Skip admin navigation</a>
    <nav class="admin-navigation mb-3" aria-label="Administration">
        @php
            $adminLinks = [
                ['admin.index', 'Dashboard', 'admin.index'],
                ['admin.users.index', 'Users', 'admin.users.*'],
                ['admin.torrents.index', 'Torrents', 'admin.torrents.*'],
                ['admin.movies.index', 'Movies', 'admin.movies.*'],
                ['admin.series.index', 'Series', 'admin.series.*'],
                ['admin.messages.index', 'Messages', 'admin.messages.*'],
                ['admin.torrent_logs.index', 'Torrent logs', 'admin.torrent_logs.*'],
                ['happyhour.index', 'Happy hour', 'happyhour.*'],
            ];
        @endphp
        @foreach($adminLinks as [$destination, $label, $pattern])
            <a href="{{ route($destination) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
        {{-- Email administration: WEB_DEVELOPER only --}}
        @if(auth()->user()?->user_class === \App\Models\UserClass::WEB_DEVELOPER)
            <a href="{{ route('admin.emails.index') }}"
               @if(request()->routeIs('admin.emails.*')) aria-current="page" @endif>
                Email
            </a>
        @endif
        @if(auth()->user()?->user_class >= \App\Models\UserClass::ADMIN)
            <a href="{{ route('admin.hitrun_amnesty.index') }}" @if(request()->routeIs('admin.hitrun_amnesty.*')) aria-current="page" @endif>Hit &amp; run amnesty</a>
        @endif
        @can('manage-admin-system')
            <a href="{{ route('admin.systemInfo.index') }}" @if(request()->routeIs('admin.systemInfo.*')) aria-current="page" @endif>System tools</a>
        @endcan
        <a href="{{ url('/') }}">Back to site</a>
    </nav>
    @if($errors->any())
        <div class="alert alert-danger" role="alert" tabindex="-1">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @foreach(['success', 'status', 'info', 'error'] as $notice)
        @if(is_string(session($notice)))
            <div class="alert alert-{{ $notice === 'error' ? 'danger' : 'info' }}" role="{{ $notice === 'error' ? 'alert' : 'status' }}">{{ session($notice) }}</div>
        @endif
    @endforeach
    <section id="admin-page" aria-label="Admin page" tabindex="-1">
        @yield('admin-content')
    </section>
</div>
<style>
.admin-navigation { display:flex; flex-wrap:wrap; gap:.5rem; padding:1rem; background:#0a0f1b; border:1px solid #64748b; border-radius:.75rem; }
.admin-navigation a { display:inline-flex; align-items:center; min-height:44px; padding:.5rem .75rem; color:#e2e8f0; border-radius:.375rem; }
.admin-navigation a:hover, .admin-navigation a[aria-current] { color:#fff; background:#212a37; }
.admin-navigation a[aria-current] { text-decoration:underline; text-underline-offset:5px; }
.admin-workspace :is(a,button,input,select,textarea,summary):focus-visible { outline:3px solid #fbbf24; outline-offset:3px; }
.admin-skip:not(:focus) { position:absolute; width:1px; height:1px; overflow:hidden; clip-path:inset(50%); }
.admin-workspace .table-responsive { overflow-x:auto; }
.admin-workspace .admin-catalog-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
</style>
@endsection
