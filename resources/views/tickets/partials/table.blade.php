<div class="table-responsive">
<table class="support-table">
    <thead><tr><th scope="col">Ticket</th><th scope="col">Status</th><th scope="col" class="support-secondary">Priority</th><th scope="col" class="support-secondary">Owner</th><th scope="col" class="support-secondary">Updated</th></tr></thead>
    <tbody>
    @forelse($tickets as $ticket)
        <tr>
            <td style="min-width:220px;max-width:540px">
                <span class="support-ticket-id">TK{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }} · {{ $ticket->category?->name ?? 'General' }}</span>
                <a class="support-subject" href="{{ route('tickets.show', ['id' => $ticket->id, 'slug' => $ticket->slug]) }}">{{ $ticket->title }}</a>
                <small>{{ $ticket->user?->name ?? 'Deleted user' }} · {{ $ticket->responses_count }} {{ $ticket->responses_count == 1 ? 'message' : 'messages' }}@if($ticket->is_locked) · <i class="bi bi-lock" aria-hidden="true"></i> Locked @endif</small>
            </td>
            <td><span class="support-badge" data-value="{{ $ticket->status }}">{{ $ticket->status }}</span></td>
            <td class="support-secondary"><span class="support-badge" data-value="{{ $ticket->priority }}">{{ $ticket->priority }}</span></td>
            <td class="support-secondary"><span class="{{ !$ticket->assignedStaff && !$ticket->claimedBy ? 'support-muted' : '' }}">{{ $ticket->assignedStaff?->name ?? $ticket->claimedBy?->name ?? 'Unassigned' }}</span></td>
            <td class="support-secondary"><time class="support-muted" datetime="{{ $ticket->updated_at->toIso8601String() }}" title="{{ $ticket->updated_at->format('M j, Y H:i').' UTC' }}">{{ $ticket->updated_at->diffForHumans() }}</time></td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="support-empty"><i class="bi bi-inbox" aria-hidden="true"></i><h3>No tickets found</h3><p>Try adjusting your filters, or open a new support request.</p><a class="support-btn" href="{{ route('tickets.create') }}">Create a ticket</a></div></td></tr>
    @endforelse
    </tbody>
</table>
</div>
