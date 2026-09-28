@if(auth()->user()->user_class > 5)
<nav class="support-nav" aria-label="Support navigation">
    <a href="{{ route('tickets.index') }}" @if(!request()->boolean('my') && !request()->boolean('unassigned') && !request()->is('staff/tickets/dashboard')) aria-current="page" @endif>All tickets</a>
    <a href="{{ route('tickets.index', ['my' => 1]) }}" @if(request()->boolean('my')) aria-current="page" @endif>Assigned to me</a>
    <a href="{{ route('tickets.index', ['unassigned' => 1]) }}" @if(request()->boolean('unassigned')) aria-current="page" @endif>Unassigned</a>
    <a href="{{ route('tickets.dashboard') }}" @if(request()->is('staff/tickets/dashboard')) aria-current="page" @endif>Overview</a>
</nav>
@endif
