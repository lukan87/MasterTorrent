@extends('layouts.app')

@section('title', config('app.name') . ' - Home')

@section('content')

<div class="home-dashboard">
    {{-- Tracker statistics (VIP and above) --}}
    @auth
        @if(Auth::user()->user_class >= \App\Models\UserClass::VIP)
            @include('partials.stats')
        @endif
    @endauth

    <div class="row g-3 mt-1">
        <div class="col-md-8 home-chat">
            @include('partials.shoutbox')
        </div>
        <div class="col-md-4 home-news-polls">
            @include('partials.news')
            <div class="mt-3">
                <x-poll-list :polls="$polls" />
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-8 home-trending">
            @include('partials.trendingtorrents')
        </div>
        <div class="col-md-4">
            @include('partials.topUsers24h')
        </div>
    </div>

 <div class="row g-3 mt-1">
    @include('partials.randomonline', [
        'randomIcon' => 'bi-film',
        'randomTitle' => 'Random Movies',
        'randomSubtitle' => 'Randomly selected movies – watch online',
        'randomItems' => $randomOnlineMovies,
    ])
    @include('partials.randomonline', [
        'randomIcon' => 'bi-tv',
        'randomTitle' => 'Random Series',
        'randomSubtitle' => 'Randomly selected series – watch online',
        'randomItems' => $randomOnlineSeries,
    ])
 </div>

    



    @include('partials.latest-user-popup')

    @include('partials.disclaimer')


</div>

<style>
.home-dashboard .home-news-polls > div:first-child,
.home-dashboard .home-news-polls > div > div.mt-2 {
    margin-top: 0 !important;
}
.home-dashboard .home-chat > .shoutbox-shell {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
.home-dashboard .row > div {
    min-width: 0;
}
.home-trending .tt-row:not(.tt-scroll) {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(120px, 100%), 1fr));
}
.home-trending .tt-row:not(.tt-scroll) > .tt-col {
    width: auto;
    max-width: none;
    min-width: 0;
}
.home-trending .tt-row.tt-scroll > .tt-col {
    flex: 0 0 130px;
    width: 130px;
    max-width: 130px;
}
</style>

<x-cookie-consent />

@endsection
