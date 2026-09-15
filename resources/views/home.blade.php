@extends('layouts.app')

@section('title', config('app.name') . ' - Home')

@section('content')

<div class="row">

    {{-- Main News Section --}}
    <div class="col-lg-7 col-md-5 col-sm-6">
        @include('partials.news')
    </div>

    {{-- Polls Section --}}
    <div class="col-lg-5 col-md-7 col-sm-6">
        <x-poll-list :polls="$polls" />
    </div>

 @include('partials.topUsers24h')

 
 @include('partials.trendingtorrents')

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

    @include('partials.shoutbox')

    {{-- Top Torrents Section --}}
    {{-- @include('partials.toptorrents') --}}
    
    {{-- @include('partials.topusers') --}}
    


    {{-- VIP-only Sections --}}
    @auth
        @if(Auth::user()->user_class >= \App\Models\UserClass::VIP)
            @include('partials.onlineusers')
            @include('partials.stats')
        @endif
    @endauth

    @include('partials.latest-user-popup')

    @include('partials.disclaimer')


</div>

<x-cookie-consent />

@endsection
