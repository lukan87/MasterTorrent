@extends('layouts.app')
@section('title', 'Your Support Requests')
@section('content')
@vite('resources/js/contact-browser.js')
<div class="contact-support" data-contact-app data-draft-user="{{ auth()->id() ?? 'guest' }}" data-contact-updates="{{ route('contact.conversations') }}">
    @include('contact.partials.hero', [
        'icon' => 'bi-chat-square-text',
        'eyebrow' => 'FileIPlay support',
        'heading' => 'Your conversations',
        'description' => 'New replies appear automatically while this page is open.',
        'actionUrl' => route('contact.create'),
        'actionLabel' => 'Start a new request',
    ])
    @include('contact.partials.feedback')
    @forelse($contacts as $contact)
        <section class="contact-panel mb-3" data-contact-thread="{{ $contact->id }}">
            <header class="contact-thread-heading"><div><span class="contact-eyebrow">Request #{{ $contact->id }}</span><h2>{{ $contact->subject }}</h2></div><span class="contact-status" data-contact-status data-resolved="{{ $contact->resolved ? 'true' : 'false' }}">{{ $contact->resolved ? 'Resolved' : 'Open' }}</span></header>
            <div class="contact-conversation" data-contact-messages>@include('contact.partials.messages', ['messages' => $contact->messages])</div>
            <p class="contact-closed {{ $contact->resolved ? '' : 'd-none' }}" data-contact-closed>This conversation has been resolved by staff.</p>
            <form method="POST" action="{{ route('contact.reply', $contact->id) }}" data-contact-submit data-contact-composer class="{{ $contact->resolved ? 'd-none' : '' }}">@csrf
                <label for="reply-{{ $contact->id }}">Your reply</label><textarea id="reply-{{ $contact->id }}" class="form-control" name="message" rows="4" minlength="3" maxlength="20000" required></textarea><button type="submit" class="btn btn-primary mt-3">Send reply</button>
            </form>
        </section>
    @empty
        <div class="contact-panel">No requests found. <a href="{{ route('contact.create') }}">Contact staff</a> to start a conversation.</div>
    @endforelse
</div>
@endsection
