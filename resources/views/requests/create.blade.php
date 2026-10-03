@extends('layouts.app')
@section('content')
@include('requests.partials.styles')
<div class="rq">
    <header class="rq-panel rq-hero">
        <div><div class="rq-kicker">Community requests</div><h1>What’s missing from your collection?</h1>
        <p class="rq-muted mb-0">Give the community the details they need to help you find it.</p></div>
        <a class="btn rq-secondary" href="{{ route('requests.index') }}">All requests</a>
    </header>
    @include('requests.partials.feedback')
    <section class="rq-panel">
        <form method="POST" action="{{ route('requests.store') }}">
            @csrf
            @include('requests.partials.form')
        </form>
    </section>
</div>
@endsection
