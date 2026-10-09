@extends('layouts.app')
@section('title', 'Contact Staff')
@section('content')
@vite('resources/js/contact-browser.js')
<div class="contact-support" data-contact-app data-draft-user="{{ auth()->id() ?? 'guest' }}">
    @include('contact.partials.hero', [
        'icon' => 'bi-chat-heart',
        'eyebrow' => 'FileIPlay support',
        'heading' => 'How can we help?',
        'description' => 'Account trouble, a question, or something we should know? Send a message to the staff team.',
    ])
    <div class="contact-layout">
        <aside class="contact-panel contact-guide"><h2>A little detail goes a long way</h2><p>Tell us what happened and include any useful links or error messages. Never share your password.</p><div class="contact-guide-step"><b>1</b><span>Use an email address you can remember.</span></div><div class="contact-guide-step"><b>2</b><span>Give your request a clear subject.</span></div><div class="contact-guide-step"><b>3</b><span>Return here to read replies and continue the conversation.</span></div><a class="btn btn-outline-secondary w-100 mt-3" href="{{ route('contact.check') }}">Check existing requests <i class="bi bi-arrow-right" aria-hidden="true"></i></a><p class="small mt-3 mb-0">Replies appear on this site. Use the same email address when checking your requests.</p></aside>
        <section class="contact-panel" aria-label="New support request">
            @include('contact.partials.feedback')
            <form method="POST" action="{{ route('contact.store') }}" data-contact-submit>
                @csrf
                <div class="row g-3"><div class="col-sm-6"><label for="contact-name">Your name</label><input id="contact-name" class="form-control" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="100" required></div><div class="col-sm-6"><label for="contact-email">Email address</label><input id="contact-email" type="email" class="form-control" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" required></div></div>
                <label for="contact-subject" class="mt-3">Subject</label><input id="contact-subject" class="form-control" name="subject" value="{{ old('subject') }}" placeholder="What do you need help with?" maxlength="150" required>
                <label for="contact-message" class="mt-3">Your message</label><textarea id="contact-message" name="message" class="form-control" rows="7" minlength="10" maxlength="20000" placeholder="Share the details so we can help." required>{{ old('message') }}</textarea>
                <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true"><input type="hidden" name="js_token" data-contact-token><input type="hidden" name="form_time" data-contact-time>
                <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send" aria-hidden="true"></i> Send request</button>
            </form>
        </section>
    </div>
</div>
@endsection
