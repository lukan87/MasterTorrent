@extends('layouts.app')
@section('content')
@include('tickets.partials.style')
@php($isStaff = auth()->user()->user_class > 5)
<div class="support" data-ticket-browser data-draft-user="{{ auth()->id() }}">
    <header class="support-header">
        <div><div class="support-eyebrow">Support · TK{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</div><h1>{{ $ticket->title }}</h1><p>Opened {{ $ticket->created_at->format('M j, Y') }} by {{ $ticket->user?->name ?? 'Deleted user' }}</p></div>
        <a class="support-btn" href="{{ route('tickets.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back to tickets</a>
    </header>
    @include('tickets.partials.feedback')
    <div class="support-grid">
        <div>
            <div id="ticket-new-replies" class="support-notice d-none" role="status">New activity in this conversation. <a href="#latest-reply" class="support-btn">View latest message</a></div>
            <section class="support-panel" aria-labelledby="conversation-title"><div class="support-panel-head"><h2 id="conversation-title">Conversation</h2><span class="support-badge" data-ticket-status data-value="{{ $ticket->status }}">{{ $ticket->status }}</span></div>
                <div id="ticket-replies" aria-live="polite" aria-relevant="additions">
                    @include('tickets.replies')
                </div>
            </section>
            @include('tickets.composer')
        </div>
        @include('tickets.details')
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

});
</script>
@push('scripts')
@vite('resources/js/ticket-browser.js')
@endpush
@endsection
