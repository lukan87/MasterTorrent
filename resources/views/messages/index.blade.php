@extends('layouts.app')

@section('content')

<div class="messenger-app {{ $activeConversation ? 'has-active-chat' : '' }}">
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

        <div class="ms-conversation-scroll" id="conversationScroll">
            @forelse($conversations as $conv)
                @php
                    $other = Auth::id() == $conv->user_one ? $conv->userTwo : $conv->userOne;
                    $last  = $conv->lastMessage;
                    $unread = $conv->unread_count ?? 0;
                    $isActive = $activeConversation && $activeConversation->id === $conv->id;
                @endphp
                @if($last)
                    <a href="{{ route('conversations.show', $conv->id) }}"
                       class="ms-conv-item {{ $isActive ? 'active' : '' }}"
                       data-name="{{ strtolower($other->name ?? '') }}">
                        <div class="ms-avatar-wrap">
                            <img src="{{ $other->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                                 alt="{{ $other->name ?? 'Deleted member' }}" class="ms-avatar">
                            @if($unread > 0)
                                <span class="ms-unread-dot"></span>
                            @endif
                        </div>
                        <div class="ms-conv-body">
                            <div class="ms-conv-name">{{ $other->name ?? 'Unknown' }}</div>
                            @include('messages.mass-pill', ['message' => $last])
                            <div class="ms-conv-preview">
                                {{ Str::limit(strip_tags(convertCustomTagsToHtml($last->body)), 45) }}
                            </div>
                        </div>
                        <div class="ms-conv-meta">
                            <span class="ms-conv-time">{{ $last->created_at->diffForHumans() }}</span>
                            @if($unread > 0)
                                <span class="ms-badge">{{ $unread }}</span>
                            @endif
                        </div>
                    </a>
                @endif
            @empty
                <div class="ms-empty-state">
                    <i class="bi bi-chat-square-text"></i>
                    <p>No conversations yet</p>
                </div>
            @endforelse
        </div>

        @if($conversations->hasPages())
            <div class="ms-pagination">
                {{ $conversations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </aside>

    {{-- CHAT PANE --}}
    <section class="messenger-chat">
        @if($activeConversation)
            @php
                $activeOther = Auth::id() == $activeConversation->user_one
                    ? $activeConversation->userTwo
                    : $activeConversation->userOne;
                $lastMsg = $messages->last();
                $systemUserId = 2;
                $isSystemConversation = $activeOther?->id == $systemUserId;
            @endphp

            <div class="ms-chat-header">
                <a href="{{ route('messages.index') }}" class="ms-mobile-back ms-icon-btn" aria-label="Back to conversations"><i class="bi bi-arrow-left"></i></a>
                <a @if($activeOther && !$isSystemConversation) href="{{ route('profile.show', ['id' => $activeOther->id, 'name' => $activeOther->name]) }}" title="View {{ $activeOther->name }}'s profile" @endif class="ms-chat-user">
                    <img src="{{ $activeOther->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                         alt="{{ $activeOther->name ?? 'Deleted member' }}" class="ms-avatar-sm">
                    <span><span class="ms-chat-name">{{ $activeOther->name ?? 'Deleted member' }} @if($activeOther && !$isSystemConversation)<i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>@endif</span><small class="ms-chat-subtitle">Private conversation · Read receipts enabled</small></span>
                </a>
                <div class="ms-chat-actions">
                    <form action="{{ route('messages.destroyConversation', $activeConversation->id) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this entire conversation?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ms-icon-btn" title="Delete conversation">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="ms-chat-body" id="chatBody" data-receipts-url="{{ route('conversations.receipts', $activeConversation) }}">
                @if($hasOlder)
                    <div class="ms-load-older">
                        <a href="{{ route('conversations.show', $activeConversation->id) }}?before={{ $messages->first()->id }}"
                           class="ms-older-link">
                            <i class="bi bi-arrow-up-circle me-1"></i>Load earlier messages
                        </a>
                    </div>
                @endif

                @foreach($messages as $message)
                    @if(
                        $loop->first ||
                        $message->created_at->format('Y-m-d') !== $messages[$loop->index - 1]->created_at->format('Y-m-d')
                    )
                        @php
                            $dayLabel = $message->created_at->isToday()
                                ? 'Today'
                                : ($message->created_at->isYesterday()
                                    ? 'Yesterday'
                                    : $message->created_at->format('d M Y'));
                        @endphp
                        <div class="ms-day-divider"><span>{{ $dayLabel }}</span></div>
                    @endif

                    <div class="ms-msg-row {{ $message->sender_id == Auth::id() ? 'me' : 'them' }}">
                        @if($message->sender_id != Auth::id())
                            <img class="ms-msg-avatar"
                                 src="{{ $message->sender->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="">
                        @endif
                        <div class="ms-msg-bubble" data-id="{{ $message->id }}" data-body="{{ $message->body }}">
                            @include('messages.mass-pill', ['message' => $message, 'showMassActions' => true])
                            <div class="ms-msg-content">{!! convertCustomTagsToHtml($message->body) !!}</div>
                            <div class="ms-msg-footer">
                                @if($message->sender_id == Auth::id())
                                    <button type="button" class="ms-msg-action edit-msg" data-url="{{ route('messages.edit', $message) }}" title="Edit message" aria-label="Edit message"><i class="bi bi-pencil"></i></button>
                                @endif
                                @if(Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                    <button type="button" class="ms-msg-action delete-msg" data-url="{{ route('messages.delete', $message) }}" title="Delete message for both participants" aria-label="Delete message"><i class="bi bi-trash3"></i></button>
                                @endif
                                <span class="ms-msg-time">{{ $message->created_at->format('d M Y · H:i') }}</span>
                                @if($message->sender_id == Auth::id())
                                    <span class="ms-receipt {{ $message->is_read ? 'is-read' : '' }}" data-receipt="{{ $message->id }}" title="{{ $message->is_read ? 'Opened by the recipient' : 'Sent; recipient has not opened it yet' }}">
                                        <i class="bi {{ $message->is_read ? 'bi-check2-all' : 'bi-check2' }}"></i><span>{{ $message->is_read ? 'Read' : 'Sent' }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($message->sender_id == Auth::id())
                            <img class="ms-msg-avatar"
                                 src="{{ Auth::user()->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="">
                        @endif
                    </div>
                @endforeach
            </div>

            @if($isSystemConversation)
                <div class="ms-composer-disabled">
                    <i class="bi bi-shield-lock me-1"></i>System conversations are read-only.
                </div>
            @else
                <form id="chatForm" action="{{ route('messages.storeReply', $activeConversation->id) }}" method="POST">
                    @csrf
                    <div class="ms-composer">
                        <div class="ms-toolbar">
                            <button type="button" onclick="insertTag('[b]','[/b]')" title="Bold"><b>B</b></button>
                            <button type="button" onclick="insertTag('[i]','[/i]')" title="Italic"><i>I</i></button>
                            <button type="button" onclick="insertTag('[u]','[/u]')" title="Underline"><u>U</u></button>
                            <button type="button" onclick="insertTag('[quote]','[/quote]')" title="Quote">&#10077;</button>
                            <button type="button" onclick="insertTag('[code]','[/code]')" title="Code">&lt;/&gt;</button>
                            <button type="button" onclick="insertTag('[url]','[/url]')" title="Link">&#128279;</button>
                            <button type="button" onclick="insertTag('[img]','[/img]')" title="Image">&#128444;</button>
                            <span class="ms-toolbar-sep"></span>
                            <span class="ms-smilies-toggle" id="smiliesToggle" title="Smilies">&#128578;</span>
                        </div>
                        <div class="ms-smilies-panel d-none" id="smiliesPanel">
                            <span onclick="addSmile('&#128578;')">&#128578;</span>
                            <span onclick="addSmile('&#128516;')">&#128516;</span>
                            <span onclick="addSmile('&#128521;')">&#128521;</span>
                            <span onclick="addSmile('&#128523;')">&#128523;</span>
                            <span onclick="addSmile('&#128526;')">&#128526;</span>
                            <span onclick="addSmile('&#128546;')">&#128546;</span>
                            <span onclick="addSmile('&#10084;&#65039;')">&#10084;&#65039;</span>
                            <span onclick="addSmile('&#128293;')">&#128293;</span>
                        </div>
                        <textarea name="body" id="body" placeholder="Write a reply..." required maxlength="5000" aria-label="Message" rows="1">{{ old('body') }}</textarea>
                        <button type="submit" class="ms-send-btn" title="Send">
                            <i class="bi bi-send-fill"></i><span>Send</span>
                        </button>
                        <small class="ms-composer-hint">Enter to send · Shift + Enter for a new line</small>
                    </div>
                </form>
            @endif
        @else
            <div class="ms-empty-chat">
                <i class="bi bi-chat-square-text"></i>
                <h5>Your conversations, together</h5>
                <p>Choose a conversation from the sidebar or start a new one.</p>
            </div>
        @endif
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

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){
    var searchInput = document.getElementById("conversationSearch");
    if(searchInput){
        searchInput.addEventListener("input", function(){
            var val = this.value.toLowerCase();
            document.querySelectorAll(".ms-conv-item").forEach(function(el){
                el.style.display = (el.dataset.name && el.dataset.name.indexOf(val) !== -1) ? "" : "none";
            });
        });
    }
    var toggle = document.getElementById("smiliesToggle");
    var panel  = document.getElementById("smiliesPanel");
    if(toggle && panel){
        toggle.addEventListener("click", function(){ panel.classList.toggle("d-none"); });
    }
    var chat = document.getElementById("chatBody");
    if(!chat) return;
    // Scroll to the last message when the conversation opens, even if
    // media/images are still loading (which grows chat.scrollHeight).
    function jumpToBottom(){ chat.scrollTop = chat.scrollHeight; }
    window.addEventListener("load", jumpToBottom);
    window.addEventListener("resize", jumpToBottom);
    // Re-scroll once each image inside the conversation finishes loading,
    // since those have no fixed height and expand the scroll area.
    chat.querySelectorAll("img").forEach(function(img){
        if(img.complete){ jumpToBottom(); }
        else img.addEventListener("load", jumpToBottom);
    });
    jumpToBottom();
    // Extra safety retries in case anything loads late.
    setTimeout(jumpToBottom, 300);
    setTimeout(jumpToBottom, 1000);
    setTimeout(jumpToBottom, 2000);
    // Textarea listeners only exist in replyable conversations.
    var textarea = document.getElementById("body");
    var form = document.getElementById("chatForm");
    if(textarea && form){
        textarea.addEventListener("input", function(){
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
        });
        textarea.addEventListener("keydown", function(e){
            if(e.key === "Enter" && !e.shiftKey){
                e.preventDefault();
                if(textarea.value.trim() !== "") form.requestSubmit();
            }
        });
    }

});
function insertTag(openTag, closeTag){
    var ta = document.getElementById("body");
    if(!ta) return;
    var s = ta.selectionStart, e = ta.selectionEnd;
    var sel = ta.value.substring(s, e);
    ta.value = ta.value.substring(0,s) + openTag + sel + closeTag + ta.value.substring(e);
    ta.focus();
}
function addSmile(code){
    var ta = document.getElementById("body");
    if(!ta) return;
    ta.value += " " + code;
    ta.focus();
}
</script>
<script src="{{ asset('js/messenger.js') }}?v=1" defer></script>
@endpush
