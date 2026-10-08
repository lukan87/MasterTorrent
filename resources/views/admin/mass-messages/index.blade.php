@extends('layouts.admin')
@section('admin-content')
<div class="mm-page">
@include('admin.mass-messages._navigation')
<header class="mm-header">
    <div><div class="mm-eyebrow">COMMUNICATION CENTER</div><h1>Mass Messages</h1><p>Your broadcasts, from the first delivery to the last read.</p></div>
    <a class="btn btn-success" href="{{ route('admin.users.mass-messages.create') }}"><i class="bi bi-plus-lg me-2"></i>New broadcast</a>
</header>
<div class="mm-stats">
    @foreach([['broadcasts', 'Broadcasts', 'broadcast'], ['delivered', 'Delivered copies', 'send-check'], ['read', 'Read copies', 'check2-all'], ['pending', 'Pending copies', 'hourglass-split']] as [$key, $label, $icon])
    <div class="mm-stat"><i class="bi bi-{{ $icon }}"></i><span>{{ $label }}</span><strong>{{ number_format($stats[$key]) }}</strong></div>
    @endforeach
</div>
<section class="mm-panel">
    <form method="GET" class="mm-filters">
        <div class="mm-search"><label for="broadcastSearch">Search broadcasts</label><input class="form-control" id="broadcastSearch" name="search" value="{{ request('search') }}" placeholder="Subject, message or staff member"></div>
        <div><label for="broadcastStatus">Delivery status</label><select class="form-select" id="broadcastStatus" name="status"><option value="">All statuses</option>@foreach(['queued', 'sending', 'sent', 'failed', 'deleting'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div><label for="broadcastSender">Sent as</label><select class="form-select" id="broadcastSender" name="sender"><option value="">All senders</option><option value="system" @selected(request('sender') === 'system')>System</option><option value="user" @selected(request('sender') === 'user')>Staff account</option></select></div>
        <button class="btn btn-outline-success">Filter</button><a class="btn btn-outline-secondary" href="{{ route('admin.users.mass-messages.index') }}">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="table mm-table align-middle mb-0">
            <thead><tr><th>Broadcast</th><th>Initiated by / sent as</th><th>Date</th><th>Delivery</th><th>Read</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>@forelse($broadcasts as $broadcast)
            <tr>
                <td><a class="mm-subject" href="{{ route('admin.users.mass-messages.show', $broadcast) }}">{{ $broadcast->subject }}</a><div class="mm-muted mm-excerpt">{{ Str::limit($broadcast->body, 85) }}</div><span class="mm-badge mm-{{ $broadcast->status }}">{{ ucfirst($broadcast->status) }}</span></td>
                <td><strong>{{ $broadcast->actor_name }}</strong><div class="mm-muted"><i class="bi bi-{{ $broadcast->send_as_system ? 'robot' : 'person' }} me-1"></i>{{ $broadcast->send_as_system ? 'System' : $broadcast->sender_name }}</div></td>
                <td class="text-nowrap"><time datetime="{{ $broadcast->created_at->toIso8601String() }}">{{ $broadcast->created_at->format('d M Y') }}</time><div class="mm-muted">{{ $broadcast->created_at->format('H:i') }} UTC</div></td>
                <td><strong>{{ number_format($broadcast->delivered_count) }}</strong><span class="mm-muted"> / {{ number_format($broadcast->deliveries_count) }}</span><div class="mm-muted">{{ number_format($broadcast->pending_count) }} pending · {{ number_format($broadcast->skipped_count) }} skipped</div></td>
                <td><strong>{{ number_format($broadcast->read_count) }}</strong><div class="mm-meter" role="progressbar" aria-label="Read copies" aria-valuemin="0" aria-valuemax="{{ max(1, $broadcast->delivered_count) }}" aria-valuenow="{{ $broadcast->read_count }}"><span style="width: {{ $broadcast->delivered_count ? round(100 * $broadcast->read_count / $broadcast->delivered_count) : 0 }}%"></span></div></td>
                <td><div class="d-flex gap-2 justify-content-end"><a class="btn btn-sm btn-outline-info" href="{{ route('admin.users.mass-messages.show', $broadcast) }}">View</a>
                @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
                <form method="POST" action="{{ route('admin.users.mass-messages.destroy', $broadcast) }}" data-mm-confirm="Delete this broadcast and all its recipient messages? Pending deliveries will be stopped.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Delete broadcast {{ $broadcast->id }}"><i class="bi bi-trash"></i></button></form>
                @endif</div></td>
            </tr>
            @empty<tr><td colspan="6"><div class="mm-empty"><i class="bi bi-broadcast"></i><h2>{{ request()->hasAny(['search', 'status', 'sender']) ? 'No matching broadcasts' : 'Your next announcement starts here' }}</h2><p>Create a broadcast to track delivery and read status in one place.</p><a href="{{ route('admin.users.mass-messages.create') }}" class="btn btn-success">Compose a message</a></div></td></tr>@endforelse</tbody>
        </table>
    </div>
    <div class="mm-panel-footer">{{ $broadcasts->links('pagination::bootstrap-5') }}</div>
</section>
<p class="mm-footnote">Broadcast tracking begins with messages sent from this page. <a href="{{ route('admin.messages.index', ['search' => 'Mass Message']) }}">View earlier mass-message copies</a>. Earlier messages do not record the staff member behind a System sender.</p>
</div>
@endsection
