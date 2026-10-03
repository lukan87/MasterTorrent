@extends('layouts.app')
@section('content')
@include('requests.partials.styles')
<div class="rq">
    <header class="rq-panel rq-hero">
        <div><div class="rq-kicker">Community requests</div><h1>Edit your request</h1>
        <p class="rq-muted mb-0">Keep the details clear so members can find the right release.</p></div>
        <a class="btn rq-secondary" href="{{ route('requests.index') }}">All requests</a>
    </header>
    @include('requests.partials.feedback')
    <section class="rq-panel">
        <form method="POST" action="{{ route('requests.update', $request->id) }}">
            @csrf
            @method('PUT')
            @include('requests.partials.form')
        </form>
    </section>
</div>
@endsection
