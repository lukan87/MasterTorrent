@extends('layouts.admin')
@section('admin-content')
<div class="mm-page">
@include('admin.mass-messages._navigation')
<header class="mm-header"><div><div class="mm-eyebrow">NEW BROADCAST</div><h1>A message for your community.</h1><p>Choose your audience, write your message, and decide who it comes from.</p></div><a class="btn btn-outline-secondary" href="{{ route('admin.users.mass-messages.index') }}">Back to history</a></header>
<form id="massMessageForm" method="POST" action="{{ route('admin.users.mass-messages.store') }}" data-preview-url="{{ route('admin.users.mass-messages.preview') }}">
@csrf
<div class="mm-compose-grid">
    <section class="mm-panel mm-padding">
        <div class="mm-step"><span>01</span><h2>Write your message</h2></div>
        <label for="massSubject" class="form-label">Subject</label><input class="form-control mb-4" id="massSubject" name="subject" maxlength="100" value="{{ old('subject') }}" placeholder="What would you like members to know?" required>
        <label for="massBody" class="form-label">Message</label><textarea class="form-control" id="massBody" name="message" rows="13" maxlength="16000" placeholder="Write your announcement…" required>{{ old('message') }}</textarea>
        <div class="mm-muted mt-2">BBCode is supported in members’ inboxes. Up to 16,000 characters.</div>
        <div class="mm-step mt-4"><span>02</span><h2>Choose the sender</h2></div>
        <div class="mm-sender-card"><div class="form-check form-switch"><input class="form-check-input" id="sendAsSystem" name="send_as_system" value="1" type="checkbox" @checked(old('send_as_system', false))><label class="form-check-label fw-semibold" for="sendAsSystem">Send as System</label></div><p class="mm-muted mb-0 mt-2">Unchecked: members see {{ auth()->user()->name }} as the sender. Your staff account is recorded in the log either way.</p></div>
    </section>
    <aside class="mm-panel mm-padding">
        <div class="mm-step"><span>03</span><h2>Select your audience</h2></div>
        <p class="mm-muted">All accounts in the selected classes, including inactive accounts. Deleted accounts are excluded.</p>
        <div class="d-flex gap-2 mb-3"><button type="button" class="btn btn-sm btn-outline-secondary" data-mm-select="all">Select all</button><button type="button" class="btn btn-sm btn-outline-secondary" data-mm-select="none">Clear</button></div>
        <fieldset><legend class="visually-hidden">Recipient user classes</legend><div class="mm-audience">
        @foreach($userClasses as $class => $name)
            <label class="mm-class"><input class="form-check-input" type="checkbox" name="user_class[]" value="{{ $class }}" @checked(in_array((string) $class, array_map('strval', old('user_class', [])), true))><span>{{ $name }}</span></label>
        @endforeach
        </div></fieldset>
        <div class="mm-preview mt-4"><div class="mm-eyebrow">AUDIENCE PREVIEW</div><div id="recipientPreview" role="status" aria-live="polite">Select classes to preview the recipient count.</div><button type="button" class="btn btn-outline-info w-100 mt-3" id="previewRecipients"><i class="bi bi-search me-2"></i>Preview recipients</button></div>
        <button class="btn btn-success w-100 mt-4" id="sendBroadcast"><i class="bi bi-send me-2"></i>Queue broadcast</button><p class="mm-muted small mt-3 mb-0">You’ll confirm the recipient count before sending. Deliveries run in the background.</p>
    </aside>
</div>
</form>
</div>
@endsection
