@extends('layouts.app')
@section('content')
@include('tickets.partials.style')
<div class="support">
    <header class="support-header">
        <div><div class="support-eyebrow">FileiPlay support</div><h1>{{ auth()->user()->user_class > 5 ? 'Support workspace' : 'How can we help?' }}</h1><p>{{ auth()->user()->user_class > 5 ? 'Manage requests, follow up with members, and keep your queue moving.' : 'Create a request, follow its progress, and talk with our support team.' }}</p></div>
        <a class="support-btn support-btn-primary" href="{{ route('tickets.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i>New ticket</a>
    </header>
    @include('tickets.partials.feedback')
    @include('tickets.partials.nav')
    <div class="support-stats">
        @foreach(['total' => 'Total tickets', 'active' => 'Active requests', 'waiting' => (auth()->user()->user_class > 5 ? 'Awaiting staff reply' : 'Awaiting your reply'), 'resolved' => 'Resolved & closed'] as $key => $label)
            <div class="support-stat"><span>{{ $label }}</span><strong>{{ number_format($stats[$key]) }}</strong></div>
        @endforeach
    </div>
    <section class="support-panel" aria-label="Find tickets"><form class="support-panel-body" method="GET" action="{{ route('tickets.index') }}">
        @if(request()->boolean('my'))<input type="hidden" name="my" value="1">@endif
        @if(request()->boolean('unassigned'))<input type="hidden" name="unassigned" value="1">@endif
        <div class="support-filters">
            <div><label for="ticket-search">Search tickets</label><input id="ticket-search" name="keyword" class="form-control" placeholder="Search subject or description…" value="{{ request('keyword') }}" maxlength="255"></div>
            <div><label for="ticket-status">Status</label><select id="ticket-status" name="status" class="form-select"><option value="">All statuses</option>@foreach(\App\Models\Ticket::STATUSES as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
            <div><label for="ticket-priority">Priority</label><select id="ticket-priority" name="priority" class="form-select"><option value="">All priorities</option>@foreach(\App\Models\Ticket::PRIORITIES as $priority)<option @selected(request('priority') === $priority)>{{ $priority }}</option>@endforeach</select></div>
            <div><label for="ticket-category">Category</label><select id="ticket-category" name="category" class="form-select"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        </div>
        <div class="support-filter-footer">
            <div><label for="ticket-id">Ticket number</label><input id="ticket-id" type="number" min="1" name="ticket_id" class="form-control" placeholder="e.g. 123" value="{{ request('ticket_id') }}"></div>
            @if(auth()->user()->user_class > 5)<div><label for="ticket-user">Member</label><input id="ticket-user" name="user" class="form-control" placeholder="Username" value="{{ request('user') }}" maxlength="255"></div>@endif
            <button class="support-btn support-btn-primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i>Apply filters</button>
            <a class="support-btn" href="{{ route('tickets.index', array_filter(['my' => request()->boolean('my') ? 1 : null, 'unassigned' => request()->boolean('unassigned') ? 1 : null])) }}">Reset</a>
        </div>
    </form></section>
    <section class="support-panel" aria-labelledby="queue-title">
        <div class="support-panel-head"><h2 id="queue-title">{{ request()->boolean('my') ? 'Assigned to me' : (request()->boolean('unassigned') ? 'Unassigned tickets' : 'Tickets') }} <span class="support-muted">({{ number_format($tickets->total()) }})</span></h2><small>Latest activity first</small></div>
        @include('tickets.partials.table')
        @if($tickets->hasPages())<div class="support-pagination">{{ $tickets->links() }}</div>@endif
    </section>
</div>
@endsection
