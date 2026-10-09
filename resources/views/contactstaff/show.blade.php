@extends('layouts.app')
@section('title', 'Contact Request')
@section('content')
@vite('resources/js/contact-browser.js')
<div class="contact-support contact-support--staff" data-contact-app data-draft-user="{{ auth()->id() ?? 'guest' }}" data-contact-updates="{{ route('contactstaff.show', $contact->id) }}">
    @include('contact.partials.hero', [
        'icon' => 'bi-chat-left-dots',
        'eyebrow' => 'Staff workspace',
        'heading' => 'Request details',
        'description' => 'Read the conversation, send a helpful reply, and resolve the request when everything is settled.',
        'actionUrl' => route('contactstaff.index'),
        'actionLabel' => 'All contact requests',
    ])
    @include('contact.partials.feedback')
    <section class="contact-panel" data-contact-thread="{{ $contact->id }}">
        <header class="contact-thread-heading"><div><span class="contact-eyebrow">Request #{{ $contact->id }}</span><h2>{{ $contact->subject }}</h2></div><span class="contact-status" data-contact-status data-resolved="{{ $contact->resolved ? 'true' : 'false' }}">{{ $contact->resolved ? 'Resolved' : 'Open' }}</span></header>
        <div class="contact-details"><span><b>From</b> {{ $contact->name }}</span><span><b>Email</b> {{ $contact->email }}</span><span><b>Created</b> {{ $contact->created_at->format('d M Y · H:i') }}</span><span><b>IP</b> {{ $contact->ip ?? 'Unknown' }}</span></div>
        <div class="contact-conversation" data-contact-messages>@include('contact.partials.messages', ['messages' => $contact->messages])</div>
        <p class="contact-closed {{ $contact->resolved ? '' : 'd-none' }}" data-contact-closed>This conversation has been resolved.</p>
        <form method="POST" action="{{ route('contactstaff.answer', $contact->id) }}" data-contact-submit data-contact-composer class="{{ $contact->resolved ? 'd-none' : '' }}">@csrf
            <label for="staff-reply">Reply to the guest</label><textarea id="staff-reply" name="reply" class="form-control" rows="4" minlength="3" maxlength="20000" required></textarea><button type="submit" class="btn btn-primary mt-3">Send reply</button>
        </form>
        <form method="POST" action="{{ route('contactstaff.resolve', $contact->id) }}" data-contact-submit data-contact-resolve class="mt-3 {{ $contact->resolved ? 'd-none' : '' }}">@csrf<button type="submit" class="btn btn-outline-secondary">Mark as resolved</button></form>
    </section>
</div>
@endsection
