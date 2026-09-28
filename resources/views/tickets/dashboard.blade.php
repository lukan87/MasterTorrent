@extends('layouts.app')
@section('content')
@include('tickets.partials.style')
<div class="support">
    <header class="support-header"><div><div class="support-eyebrow">Support operations</div><h1>Team overview</h1><p>A clear view of the queue and the requests that need a response.</p></div><a href="{{ route('tickets.index') }}" class="support-btn support-btn-primary">Open ticket queue <i class="bi bi-arrow-right" aria-hidden="true"></i></a></header>
    @include('tickets.partials.nav')
    <div class="support-stats">
        @foreach(['open' => ['Open requests', 'Open'], 'waiting_staff' => ['Awaiting staff reply', 'Waiting Staff'], 'waiting_user' => ['Awaiting member reply', 'Waiting User'], 'resolved' => ['Resolved', 'Resolved']] as $key => [$label, $status])
            <a href="{{ route('tickets.index', ['status' => $status]) }}" class="support-stat"><span>{{ $label }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span><strong>{{ number_format($stats[$key]) }}</strong></a>
        @endforeach
    </div>
    <div class="support-actions mb-4"><a class="support-btn" href="{{ route('tickets.index', ['unassigned' => 1]) }}">Unassigned <span class="support-badge">{{ $stats['unassigned'] }}</span></a><a class="support-btn" href="{{ route('tickets.index', ['my' => 1]) }}">Assigned to me <span class="support-badge">{{ $stats['my_tickets'] }}</span></a></div>
    <section class="support-panel"><div class="support-panel-head"><h2>Recent activity</h2><small>15 most recently updated tickets</small></div>@include('tickets.partials.table', ['tickets' => $recentTickets])</section>
</div>
@endsection
