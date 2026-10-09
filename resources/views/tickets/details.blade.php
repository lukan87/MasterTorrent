@php($isStaff = auth()->user()->user_class > 5)
        <aside data-ticket-details>
            <section class="support-panel"><div class="support-panel-head"><h2>Ticket details</h2></div><div class="support-panel-body"><dl class="support-details">
                <div><dt>Status</dt><dd><span class="support-badge" data-ticket-status data-value="{{ $ticket->status }}">{{ $ticket->status }}</span></dd></div>
                <div><dt>Priority</dt><dd><span class="support-badge" data-value="{{ $ticket->priority }}">{{ $ticket->priority }}</span></dd></div>
                <div><dt>Category</dt><dd>{{ $ticket->category?->name ?? 'General' }}</dd></div>
                <div><dt>Owner</dt><dd>{{ $ticket->assignedStaff?->name ?? $ticket->claimedBy?->name ?? 'Awaiting assignment' }}</dd></div>
                <div><dt>Last update</dt><dd>{{ $ticket->updated_at->diffForHumans() }}</dd></div>
            </dl></div></section>
            @if($isStaff)
                <section class="support-panel"><div class="support-panel-head"><h2>Staff actions</h2><i class="bi bi-shield-check support-muted" aria-hidden="true"></i></div><div class="support-panel-body">
                    @if(!$ticket->claimed_by && (!$ticket->assigned_to || $ticket->assigned_to == auth()->id()))<form method="POST" action="{{ route('tickets.claim', $ticket->id) }}" class="mb-4">@csrf<button type="submit" class="support-btn w-100">Assign to myself</button></form>@endif
                    <form method="POST" action="{{ route('tickets.status', $ticket->id) }}" class="mb-4">@csrf<label for="status">Update status</label><select id="status" name="status" class="form-select mb-2">@foreach(\App\Models\Ticket::STATUSES as $status)<option @selected(old('status', $ticket->status) === $status)>{{ $status }}</option>@endforeach</select><button type="submit" class="support-btn w-100">Save status</button></form>
                    <form method="POST" action="{{ route('tickets.assign', $ticket->id) }}" class="mb-4">@csrf<label for="staff-id">Assign to staff</label><select id="staff-id" name="staff_id" class="form-select mb-2" required><option value="">Select a team member</option>@foreach($staffMembers as $staff)<option value="{{ $staff->id }}" @selected(old('staff_id', $ticket->assigned_to ?? $ticket->claimed_by) == $staff->id)>{{ $staff->name }}</option>@endforeach</select><button type="submit" class="support-btn w-100">Save assignment</button></form>
                    <form method="POST" action="{{ route($ticket->is_locked ? 'tickets.unlock' : 'tickets.lock', $ticket->id) }}">@csrf<button type="submit" class="support-btn w-100"><i class="bi bi-{{ $ticket->is_locked ? 'unlock' : 'lock' }}" aria-hidden="true"></i>{{ $ticket->is_locked ? 'Unlock conversation' : 'Lock conversation' }}</button><p class="support-help">Locking pauses replies without changing the ticket status.</p></form>
                </div></section>
            @endif
            <section class="support-panel"><div class="support-panel-head"><h2>Activity</h2></div><div class="support-panel-body"><ol class="support-activity">@forelse($ticket->events->sortByDesc('id')->take(10) as $event)<li><strong>{{ $event->user?->name ?? 'System' }}</strong> {{ $event->event }}<time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->diffForHumans() }}</time></li>@empty<li>Ticket opened<time>{{ $ticket->created_at->diffForHumans() }}</time></li>@endforelse</ol></div></section>
        </aside>