@if($history && auth()->check() && ((int) auth()->id() === (int) $history->user_id || auth()->user()->user_class >= \App\Models\UserClass::MODERATOR))
<div class="snatch-announce">
    <i class="bi bi-broadcast" aria-hidden="true"></i>
    <span>Last announce</span>
    @if($history->last_event_at)
        <time datetime="{{ $history->last_event_at->toIso8601String() }}" title="{{ $history->last_event_at->format('Y-m-d H:i:s T') }}">{{ $history->last_event_at->format('Y-m-d H:i') }}</time>
        <span class="snatch-muted">{{ $history->last_event_at->diffForHumans() }}</span>
        <span class="snatch-event">{{ $history->last_event ? ucfirst($history->last_event) : 'Periodic update' }}</span>
    @else
        <span class="snatch-muted">No announce recorded</span>
    @endif
</div>
@endif
