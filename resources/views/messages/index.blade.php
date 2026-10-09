@extends('layouts.app')

@section('content')

<div data-messenger data-index-url="{{ route('messages.index') }}" data-draft-user="{{ auth()->id() }}" class="messenger-app {{ $activeConversation ? 'has-active-chat' : '' }}">
    @if($errors->any())
        <div class="ms-flash ms-flash-error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- SIDEBAR --}}
    <aside class="messenger-sidebar">

        <div class="ms-sidebar-header">
            <h6 class="ms-title"><i class="bi bi-chat-dots-fill me-2"></i>Messages</h6>
            <a href="{{ route('messages.create') }}" class="ms-compose-btn" title="New Message">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>

        <input type="text" class="ms-search" id="conversationSearch" placeholder="Search conversations..." aria-label="Search conversations">

        <div data-conversation-results style="display:contents">
@include('messages.sidebar')
</div>
    </aside>

    {{-- CHAT PANE --}}
    <section class="messenger-chat">
        @include('messages.pane')
    </section>
</div>
<div id="messengerNotice" class="ms-notice" role="status" aria-live="polite" hidden></div>
<dialog id="messageDialog" class="ms-dialog" aria-labelledby="messageDialogTitle">
    <form id="messageActionForm">
        <h5 id="messageDialogTitle"></h5>
        <p id="messageDialogDescription"></p>
        <textarea id="messageEditBody" aria-label="Edit message text" maxlength="5000" rows="6"></textarea>
        <p id="messageActionError" role="alert" class="ms-dialog-error"></p>
        <div class="ms-dialog-actions">
            <button type="button" id="messageActionCancel">Cancel</button>
            <button type="submit" id="messageActionConfirm">Save changes</button>
        </div>
    </form>
</dialog>
<link rel="stylesheet" href="{{ asset('css/messenger.css') }}?v={{ filemtime(public_path('css/messenger.css')) }}">

@vite('resources/js/messenger-browser.js')
@endsection

@push('scripts')
<script>
function insertTag(openTag, closeTag){
    var ta = document.getElementById("body");
    if(!ta) return;
    var s = ta.selectionStart, e = ta.selectionEnd;
    var sel = ta.value.substring(s, e);
    ta.value = ta.value.substring(0,s) + openTag + sel + closeTag + ta.value.substring(e);
    ta.dispatchEvent(new Event('input', { bubbles: true }));
    ta.focus();
}
function addSmile(code){
    var ta = document.getElementById("body");
    if(!ta) return;
    ta.value += " " + code;
    ta.dispatchEvent(new Event('input', { bubbles: true }));
    ta.focus();
}
</script>
<script src="{{ asset('js/messenger.js') }}?v={{ filemtime(public_path('js/messenger.js')) }}" defer></script>
@endpush
