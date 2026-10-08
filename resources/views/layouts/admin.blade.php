@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin-mass-messages.css') }}?v={{ filemtime(public_path('css/admin-mass-messages.css')) }}">
<script src="{{ asset('js/admin-mass-messages.js') }}?v={{ filemtime(public_path('js/admin-mass-messages.js')) }}" defer></script>
<div class="admin-workspace admin-theme container-fluid py-3">
    <header class="admin-masthead">
        <a class="admin-brand" href="{{ route('admin.index') }}"><span class="admin-brand-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><span><strong>Administration</strong><small>Community control center</small></span></a>
        <a class="admin-site-link" href="{{ url('/') }}"><i class="bi bi-arrow-up-right" aria-hidden="true"></i>Back to site</a>
    </header>
    <a class="admin-skip" href="#admin-page">Skip admin navigation</a>
    <nav class="admin-navigation mb-3" aria-label="Administration">
        @php
            $adminLinks = [
                ['admin.index', 'Dashboard', 'admin.index', 'grid-1x2'],
                ['admin.users.index', 'Users', 'admin.users.*', 'people'],
                ['admin.torrents.index', 'Torrents', 'admin.torrents.*', 'download'],
                ['admin.movies.index', 'Movies', 'admin.movies.*', 'film'],
                ['admin.series.index', 'Series', 'admin.series.*', 'collection-play'],
                ['admin.messages.index', 'Messages', 'admin.messages.*', 'envelope'],
                ['admin.torrent_logs.index', 'Torrent logs', 'admin.torrent_logs.*', 'clock-history'],
                ['happyhour.index', 'Happy hour', 'happyhour.*', 'gift'],
            ];
        @endphp
        @foreach($adminLinks as [$destination, $label, $pattern, $icon])
            <a href="{{ route($destination) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif><i class="bi bi-{{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
        @endforeach
        {{-- Email administration: WEB_DEVELOPER only --}}
        @if(auth()->user()?->user_class === \App\Models\UserClass::WEB_DEVELOPER)
            <a href="{{ route('admin.emails.index') }}"
               @if(request()->routeIs('admin.emails.*')) aria-current="page" @endif>
                <i class="bi bi-send" aria-hidden="true"></i>Email
            </a>
        @endif
        @if(auth()->user()?->user_class >= \App\Models\UserClass::ADMIN)
            <a href="{{ route('admin.hitrun_amnesty.index') }}" @if(request()->routeIs('admin.hitrun_amnesty.*')) aria-current="page" @endif><i class="bi bi-tools" aria-hidden="true"></i>Hit &amp; run amnesty</a>
        @endif
        @can('manage-admin-system')
            <a href="{{ route('admin.systemInfo.index') }}" @if(request()->routeIs('admin.systemInfo.*')) aria-current="page" @endif><i class="bi bi-cpu" aria-hidden="true"></i>System tools</a>
        @endcan
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
    <section id="admin-page" class="admin-page-content" aria-label="Admin page" tabindex="-1">
        @yield('admin-content')
    </section>
</div>
{{-- Loaded after page styles so the shared admin theme remains authoritative. --}}
<link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
@endsection
