@extends('layouts.app')

@section('title', config('app.name') . ' - Home')

@section('content')

    <div class="home-dashboard">
        <div class="row g-3 mt-1">
            <div class="col-md-8 home-chat">
                @include('partials.shoutbox')
            </div>
            <div class="col-md-4 home-news-polls">
                @include('partials.news')
                <div class="mt-3">
                    <div data-home-widget="polls" data-widget-url="{{ url()->current() }}">
                        <x-poll-list :polls="$polls" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Legacy Hot poster section, retained for later use.
    <div class="row g-3 mt-1">
        <div class="col-md-8 home-trending">
            <div data-home-widget="trending" data-widget-url="{{ url()->current() }}">
                @include('partials.trendingtorrents')
            </div>
        </div>
    </div>
    --}}

        <div class="row g-3 mt-1 torrent-featured">
            @foreach ($featuredCards as $card)
                <div class="col-md-4" data-torrent-featured="{{ $card['kind'] }}">
                    @include('torrents.partials.featured-card', $card)
                </div>
            @endforeach
            <div class="col-md-4">
                @include('partials.topUsers24h')
            </div>
        </div>

        @if (Auth::user()->user_class >= \App\Models\UserClass::VIP)
            @include('partials.stats')
        @endif

        @include('partials.latest-user-popup')

        @include('partials.disclaimer')


    </div>

    <style>
        .home-dashboard .home-news-polls>div:first-child,
        .home-dashboard .home-news-polls>div>div.mt-2 {
            margin-top: 0 !important;
        }

        .home-dashboard .home-chat>.shoutbox-shell {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .home-dashboard .row>div {
            min-width: 0;
        }

        .home-trending .tt-row:not(.tt-scroll) {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(120px, 100%), 1fr));
        }

        .home-trending .tt-row:not(.tt-scroll)>.tt-col {
            width: auto;
            max-width: none;
            min-width: 0;
        }

        .home-trending .tt-row.tt-scroll>.tt-col {
            flex: 0 0 130px;
            width: 130px;
            max-width: 130px;
        }
    </style>

    <x-cookie-consent />

    @vite(['resources/js/home-widgets.js', 'resources/js/torrent-featured.js'])
@endsection
