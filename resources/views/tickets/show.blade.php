@extends('layouts.app')
@section('content')
@include('tickets.partials.style')
@php($isStaff = auth()->user()->user_class > 5)
<div class="support">
    <header class="support-header">
        <div><div class="support-eyebrow">Support · TK{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</div><h1>{{ $ticket->title }}</h1><p>Opened {{ $ticket->created_at->format('M j, Y') }} by {{ $ticket->user?->name ?? 'Deleted user' }}</p></div>
        <a class="support-btn" href="{{ route('tickets.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back to tickets</a>
    </header>
    @include('tickets.partials.feedback')
    <div class="support-grid">
        <div>
            <div id="ticket-new-replies" class="support-notice d-none" role="status">New activity in this conversation. <a href="#latest-reply" class="support-btn">View latest message</a></div>
            <section class="support-panel" aria-labelledby="conversation-title"><div class="support-panel-head"><h2 id="conversation-title">Conversation</h2><span class="support-badge" data-value="{{ $ticket->status }}">{{ $ticket->status }}</span></div>
                <div id="ticket-replies" aria-live="polite" aria-relevant="additions">
                    @forelse($ticket->responses as $response)
                        <article class="support-message {{ $response->is_staff_note || $response->is_internal ? 'is-note' : '' }}" data-reply-id="{{ $response->id }}" @if($loop->last) id="latest-reply" @endif>
                            <div class="support-message-head"><div class="support-person"><span class="support-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($response->user?->name ?? '?', 0, 1)) }}</span><div><strong>{{ $response->user?->name ?? 'Deleted user' }}</strong> <span class="support-badge">{{ $response->is_staff_note || $response->is_internal ? 'Internal note' : (($response->user?->user_class ?? 0) > 5 ? 'Support team' : 'Member') }}</span></div></div><time class="support-muted" datetime="{{ $response->created_at->toIso8601String() }}" title="{{ $response->created_at->format('M j, Y H:i').' UTC' }}">{{ $response->created_at->diffForHumans() }}</time></div>
                            <div class="support-message-body">{{ $response->message }}</div>
                            @if($response->attachments->isNotEmpty())<div class="support-attachments">@foreach($response->attachments as $file)<a class="support-attachment" href="{{ route('tickets.download', $file->id) }}"><i class="bi bi-paperclip" aria-hidden="true"></i>{{ $file->file_name }}</a>@endforeach</div>@endif
                        </article>
                    @empty
                        <article class="support-message"><div class="support-message-body">{{ $ticket->description }}</div></article>
                    @endforelse
                </div>
            </section>
            <section class="support-panel" aria-labelledby="reply-title">
                @if(in_array($ticket->status, ['Resolved', 'Closed'], true) && !$isStaff)
                    <div class="support-panel-body">
                        <h2 id="reply-title"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>Ticket {{ strtolower($ticket->status) }}</h2>
                        <p class="support-help mb-3">This ticket has been {{ strtolower($ticket->status) }} by the support team and no longer accepts replies. If you need more help, please create a new ticket.</p>
                        <a class="support-btn support-btn-primary" href="{{ route('tickets.create') }}">Create a new ticket</a>
                    </div>
                @elseif($ticket->is_locked)
                    <div class="support-panel-body"><h2 id="reply-title"><i class="bi bi-lock me-2" aria-hidden="true"></i>Conversation locked</h2><p class="support-help mb-3">Replies are paused. Unlock this ticket to follow up on the same issue. For a different issue, create a new ticket.</p><form method="POST" action="{{ route('tickets.unlock', $ticket->id) }}">@csrf<button class="support-btn" type="submit">Unlock conversation</button></form></div>
                @else
                    <div class="support-panel-head"><h2 id="reply-title">Add a reply</h2><small id="reply-audience">Visible to the member and support team</small></div>
                    <form method="POST" action="{{ route('tickets.reply', $ticket->id) }}" enctype="multipart/form-data" class="support-panel-body">
                        @csrf
                        @if($isStaff)<label class="support-note-toggle mb-3" for="staff-note"><input id="staff-note" type="checkbox" name="staff_note" value="1" @checked(old('staff_note'))>Internal note — visible only to staff</label>@endif
                        <div class="support-field"><label for="message">Message</label><textarea id="message" name="message" class="form-control" rows="6" maxlength="20000" placeholder="Write your reply…" required>{{ old('message') }}</textarea></div>
                        @include('tickets.partials.upload')
                        <button id="send-reply" class="support-btn support-btn-primary" type="submit">Send reply</button>
                    </form>
                @endif
            </section>
        </div>
        <aside>
            <section class="support-panel"><div class="support-panel-head"><h2>Ticket details</h2></div><div class="support-panel-body"><dl class="support-details">
                <div><dt>Status</dt><dd><span class="support-badge" data-value="{{ $ticket->status }}">{{ $ticket->status }}</span></dd></div>
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
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const note = document.getElementById('staff-note');
    const updateAudience = () => {
        document.getElementById('reply-audience').textContent = note.checked ? 'Only staff can see this note' : 'Visible to the member and support team';
        document.getElementById('send-reply').textContent = note.checked ? 'Add internal note' : 'Send reply';
    };
    if (note) { note.addEventListener('change', updateAudience); updateAudience(); }
    const replies = document.getElementById('ticket-replies');
    const seen = new Set(Array.from(replies.querySelectorAll('[data-reply-id]'), element => Number(element.dataset.replyId)));
    let pollingStopped = false;
    const node = (tag, className, text) => {
        const element = document.createElement(tag);
        element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    };
    async function poll() {
        if (document.hidden || pollingStopped) return;
        try {
            const response = await fetch(@json(route('tickets.fetchReplies', $ticket->id)), {headers: {'Accept': 'application/json'}});
            if ([401, 403, 404].includes(response.status)) { pollingStopped = true; return; }
            if (!response.ok) return;
            const data = await response.json();
            if (!Array.isArray(data)) return;
            for (const reply of data) {
                if (seen.has(reply.id)) continue;
                const article = node('article', 'support-message' + (reply.is_staff_note ? ' is-note' : ''));
                article.dataset.replyId = reply.id;
                const head = node('div', 'support-message-head');
                const person = node('div', 'support-person');
                person.append(node('strong', '', reply.user.name), node('span', 'support-badge', reply.is_staff_note ? 'Internal note' : (reply.user.is_staff ? 'Support team' : 'Member')));
                const time = node('time', 'support-muted', new Date(reply.created_at).toLocaleString());
                time.dateTime = reply.created_at;
                head.append(person, time);
                article.append(head, node('div', 'support-message-body', reply.message));
                const attachments = node('div', 'support-attachments');
                for (const file of reply.attachments) {
                    const link = node('a', 'support-attachment', file.file_name);
                    link.href = file.url;
                    attachments.append(link);
                }
                if (reply.attachments.length) article.append(attachments);
                document.getElementById('latest-reply')?.removeAttribute('id');
                article.id = 'latest-reply';
                replies.append(article);
                seen.add(reply.id);
                document.getElementById('ticket-new-replies').classList.remove('d-none');
            }
        } catch (_) { /* Retry on the next poll without disturbing the reply draft. */ }
    }
    async function schedule() { await poll(); if (!pollingStopped) setTimeout(schedule, 10000); }
    setTimeout(schedule, 10000);
});
</script>
@endsection
