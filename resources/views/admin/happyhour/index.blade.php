@extends('layouts.admin')
@section('title', 'Happy Hours')
@section('admin-content')
@include('admin.happyhour.partials.styles')
<div class="happyhour-workspace">
    <div class="hh-header admin-page-header">
        <div><div class="hh-eyebrow"><i class="bi bi-clock-history" aria-hidden="true"></i> Community rewards</div><h1>Happy Hours</h1><p class="text-muted mb-0">Reward the community. Schedule events and manage automatic promotions.</p></div>
        <a class="btn btn-primary" href="{{ route('happyhour.create') }}">Schedule an event</a>
    </div>
    <div class="row g-3 mb-4">
        <section class="col-lg-6"><div class="card h-100 hh-live-panel"><div class="card-body">
            <h2 class="h5">{{ $current ? 'Live now' : 'No event running' }}</h2>
            @if($current)
                <p class="fs-4 mb-2">{{ $current->theme }}</p>
                <p>{{ $current->upload_multiplier }}× upload credit · {{ $current->free_download ? 'Freeleech enabled' : 'Standard download accounting' }}</p>
                <p class="mb-0">Ends {{ $current->end_at->format('M j, H:i') }} {{ config('app.timezone') }}</p>
            @else
                <p>Members currently receive the usual torrent rewards.</p>
            @endif
            @if($upcoming)<hr><p class="mb-0"><strong>Up next:</strong> {{ $upcoming->theme }} · {{ $upcoming->start_at->format('M j, H:i') }} {{ config('app.timezone') }}</p>@endif
        </div></div></section>
        <section class="col-lg-6"><div class="card h-100 hh-auto-panel"><div class="card-body">
            <h2 class="h5">Automatic events <span class="badge {{ $automatic ? 'bg-success' : 'bg-secondary' }}">{{ $automatic ? 'Enabled' : 'Disabled' }}</span></h2>
            <p>Hourly checks after 07:00, with a 1 in {{ max(1, (int) config('happyhour.random_chance', 3)) }} chance per check. At most one automatic event per day; scheduled events take priority.</p>
            <p>Today's preset: <strong>{{ $theme['name'] }}</strong> · {{ $theme['upload_multiplier'] }}× · {{ $theme['duration_hours'] }} hours{{ $theme['free_download'] ? ' · Freeleech' : '' }}</p>
            <form method="POST" action="{{ route('happyhour.toggleAutomatic') }}">@csrf
                <input type="hidden" name="enabled" value="{{ $automatic ? 0 : 1 }}">
                <button class="btn btn-outline-{{ $automatic ? 'warning' : 'success' }}">{{ $automatic ? 'Disable' : 'Enable' }} automatic events</button>
            </form>
            <small class="d-block mt-2 text-muted">Changing this setting does not stop existing events. Times use {{ config('app.timezone') }}.</small>
        </div></div></section>
    </div>
    <section class="hh-history" aria-labelledby="hh-history-title">
    <div class="hh-eyebrow">Your events</div>
    <h2 id="hh-history-title">Event schedule &amp; history</h2>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Event</th><th>Rewards</th><th>Schedule ({{ config('app.timezone') }})</th><th>Status</th><th>Created by</th><th>Actions</th></tr></thead>
        <tbody>@forelse($happyHours as $hh)
            <tr>
                <td><strong>{{ $hh->theme }}</strong><small class="d-block text-muted">{{ $hh->automatic ? 'Automatic' : 'Manual' }}</small></td>
                <td>{{ $hh->upload_multiplier }}× upload<small class="d-block">{{ $hh->free_download ? 'Freeleech' : 'Standard downloads' }}</small></td>
                <td>{{ $hh->start_at?->format('M j, Y H:i') ?? '—' }}<small class="d-block">to {{ $hh->end_at?->format('M j, Y H:i') ?? '—' }}</small></td>
                <td><span class="badge {{ $hh->status === 'Live' ? 'bg-success' : ($hh->status === 'Scheduled' ? 'bg-primary' : 'bg-secondary') }}">{{ $hh->status }}</span></td>
                <td>{{ $hh->user?->name ?? 'System' }}</td>
                <td>
                    @if(in_array($hh->status, ['Live', 'Scheduled']))
                        <form method="POST" action="{{ route('happyhour.stop', $hh) }}" onsubmit="return confirm('Stop or cancel this event?');">@csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-warning">{{ $hh->status === 'Scheduled' ? 'Cancel' : 'Stop' }}</button>
                        </form>
                    @elseif($hh->automatic && $hh->start_at?->isToday())
                        <small class="text-muted">Available to delete tomorrow</small>
                    @else
                        <form method="POST" action="{{ route('happyhour.destroy', $hh) }}" onsubmit="return confirm('Delete this event from history?');">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty<tr><td colspan="6" class="text-center py-5">No events yet. Schedule your first Happy Hour to reward members.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-3">{{ $happyHours->links('pagination::bootstrap-5') }}</div>
    </section>
</div>
@endsection
