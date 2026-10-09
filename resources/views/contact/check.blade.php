@extends('layouts.app')
@section('title', 'Check Staff Replies')
@section('content')
@vite('resources/js/contact-browser.js')
<div class="contact-support contact-support--narrow" data-contact-app data-draft-user="{{ auth()->id() ?? 'guest' }}">
    @include('contact.partials.hero', [
        'icon' => 'bi-envelope-open',
        'eyebrow' => 'Your support inbox',
        'heading' => 'Pick up the conversation',
        'description' => 'Enter the email address you used when contacting staff to find your requests and replies.',
    ])
    <section class="contact-panel">
        @include('contact.partials.feedback')
        <form method="POST" action="{{ route('contact.replies') }}" data-contact-submit>@csrf
            <label for="reply-email">Email address</label><input id="reply-email" class="form-control" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required>
            <button type="submit" class="btn btn-primary w-100 mt-3">Check replies <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <a class="contact-back" href="{{ route('contact.create') }}">Send a new request</a>
    </section>
</div>
@endsection
