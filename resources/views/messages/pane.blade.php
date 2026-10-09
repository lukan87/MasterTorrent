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
                @include('messages.thread')
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
