@extends('layouts.app')

@section('content')

<div class="messenger-app">

    {{-- SIDEBAR --}}
    <aside class="messenger-sidebar">

        <div class="ms-sidebar-header">
            <h6 class="ms-title"><i class="bi bi-chat-dots-fill me-2"></i>Messages</h6>
            <a href="{{ route('messages.create') }}" class="ms-compose-btn" title="New Message">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>

        <input type="text" class="ms-search" id="conversationSearch" placeholder="Search conversations...">

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
                                 alt="{{ $other->name }}" class="ms-avatar">
                            @if($unread > 0)
                                <span class="ms-unread-dot"></span>
                            @endif
                        </div>
                        <div class="ms-conv-body">
                            <div class="ms-conv-name">{{ $other->name ?? 'Unknown' }}</div>
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
                $isSystemConversation = $activeOther->id == $systemUserId;
            @endphp

            <div class="ms-chat-header">
                <a href="{{ route('conversations.show', $activeConversation->id) }}" class="ms-chat-user">
                    <img src="{{ $activeOther->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}"
                         alt="{{ $activeOther->name }}" class="ms-avatar-sm">
                    <span class="ms-chat-name">{{ $activeOther->name }}</span>
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

            <div class="ms-chat-body" id="chatBody">
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
                        <div class="ms-msg-bubble" data-id="{{ $message->id }}">
                            <div class="ms-msg-content">
                                {!! convertCustomTagsToHtml($message->body) !!}
                            </div>
                            <div class="ms-msg-footer">
                                @if($message->sender_id == Auth::id())
                                    <button class="ms-msg-action edit-msg" data-id="{{ $message->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="ms-msg-action delete-msg" data-id="{{ $message->id }}" title="Delete"><i class="bi bi-trash3"></i></button>
                                @endif
                                <span class="ms-msg-time">{{ $message->created_at->format('d M Y . H:i') }}</span>
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
                        <textarea name="body" id="body" placeholder="Write a reply..." required rows="1"></textarea>
                        <button type="submit" class="ms-send-btn" title="Send">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            @endif
        @else
            <div class="ms-empty-chat">
                <i class="bi bi-chat-square-text"></i>
                <h5>Select a conversation</h5>
                <p>Choose a conversation from the sidebar or start a new one.</p>
            </div>
        @endif
    </section>
</div>

<style>
body:has(.messenger-app) .app-main{min-height:auto}
body:has(.messenger-app) .app-content{padding-top:1rem;padding-bottom:1rem}
.messenger-app{display:grid;grid-template-columns:340px 1fr;height:calc(95vh - 12rem);min-height:420px;background:var(--ui-surface);border:1px solid var(--ui-border);border-radius:16px;overflow:hidden;box-shadow:var(--ui-shadow)}
.messenger-sidebar{display:flex;flex-direction:column;background:var(--ui-surface-raised);border-right:1px solid var(--ui-border);overflow:hidden}
.ms-sidebar-header{display:flex;align-items:center;justify-content:space-between;padding:16px 18px 10px}
.ms-title{color:#e5edf7;font-weight:700;font-size:1.05rem;margin:0}
.ms-title i{color:var(--ui-accent)}
.ms-compose-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--ui-accent);color:#0b1120;font-size:.95rem;text-decoration:none;transition:.2s}
.ms-compose-btn:hover{transform:scale(1.08);color:#0b1120}
.ms-search{margin:0 14px 6px;padding:9px 14px;border-radius:10px;border:1px solid var(--ui-border);background:rgba(255,255,255,.04);color:#e5edf7;font-size:.88rem;outline:none;transition:.2s}
.ms-search:focus{border-color:var(--ui-accent)}
.ms-search::placeholder{color:var(--ui-text-muted)}
.ms-conversation-scroll{flex:1;overflow-y:auto;padding:4px 0}
.ms-conversation-scroll::-webkit-scrollbar{width:5px}
.ms-conversation-scroll::-webkit-scrollbar-thumb{background:var(--ui-border);border-radius:10px}
.ms-conv-item{display:flex;align-items:center;gap:10px;padding:11px 16px;text-decoration:none;color:#cdd8e4;border-left:3px solid transparent;transition:.15s}
.ms-conv-item:hover{background:rgba(99,210,198,.07)}
.ms-conv-item.active{background:linear-gradient(135deg,rgba(99,210,198,.14),rgba(45,110,126,.10));border-left-color:var(--ui-accent);color:#fff}
.ms-avatar-wrap{position:relative;flex-shrink:0}
.ms-avatar{width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid var(--ui-border)}
.ms-unread-dot{position:absolute;bottom:1px;right:1px;width:10px;height:10px;background:var(--ui-accent);border:2px solid var(--ui-surface-raised);border-radius:50%}
.ms-conv-body{flex:1;min-width:0}
.ms-conv-name{font-weight:600;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ms-conv-preview{font-size:.8rem;color:var(--ui-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}
.ms-conv-meta{display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0}
.ms-conv-time{font-size:.7rem;color:var(--ui-text-muted)}
.ms-badge{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--ui-accent);color:#0b1120;font-size:.7rem;font-weight:700}
.ms-empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;color:var(--ui-text-muted);text-align:center}
.ms-empty-state i{font-size:2.4rem;margin-bottom:8px}
.ms-pagination{padding:8px 14px;text-align:center}
.ms-pagination .pagination{margin:0}
.messenger-chat{display:flex;flex-direction:column;background:rgba(11,17,32,.55);overflow:hidden}
.ms-chat-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:var(--ui-surface-raised);border-bottom:1px solid var(--ui-border)}
.ms-chat-user{display:flex;align-items:center;gap:10px;text-decoration:none;color:#e5edf7}
.ms-avatar-sm{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--ui-border)}
.ms-chat-name{font-weight:600;font-size:.95rem}
.ms-chat-actions .ms-icon-btn{background:none;border:none;color:var(--ui-text-muted);font-size:1.05rem;padding:4px 8px;border-radius:8px;transition:.15s;cursor:pointer}
.ms-chat-actions .ms-icon-btn:hover{color:#f87171;background:rgba(248,113,113,.1)}
.ms-chat-body{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:6px}
.ms-chat-body::-webkit-scrollbar{width:5px}
.ms-chat-body::-webkit-scrollbar-thumb{background:var(--ui-border);border-radius:10px}
.ms-load-older{text-align:center;padding:6px 0 12px}
.ms-older-link{font-size:.82rem;color:var(--ui-accent);text-decoration:none;padding:6px 16px;border-radius:20px;border:1px solid var(--ui-border);transition:.15s}
.ms-older-link:hover{background:rgba(99,210,198,.1);color:var(--ui-accent)}
.ms-day-divider{display:flex;align-items:center;justify-content:center;padding:12px 0 4px}
.ms-day-divider span{font-size:.72rem;color:var(--ui-text-muted);text-transform:uppercase;letter-spacing:.08em;background:rgba(255,255,255,.05);padding:3px 14px;border-radius:14px}
.ms-msg-row{display:flex;align-items:flex-end;gap:8px;max-width:75%;animation:fadeUp .2s ease}
.ms-msg-row.me{align-self:flex-end;flex-direction:row}
.ms-msg-row.them{align-self:flex-start}
.ms-msg-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover}
.ms-msg-bubble{padding:10px 14px;border-radius:16px;border-bottom-left-radius:4px;background:var(--ui-surface-raised);border:1px solid var(--ui-border);color:#e5edf7;font-size:.9rem;line-height:1.45;word-break:break-word}
.ms-msg-row.me .ms-msg-bubble{background:linear-gradient(135deg,rgba(99,210,198,.22),rgba(45,110,126,.18));border-color:rgba(99,210,198,.25);border-bottom-right-radius:4px;border-bottom-left-radius:16px}
.ms-msg-content{margin-bottom:2px}
.ms-msg-content img{max-width:280px;border-radius:10px}
.ms-msg-footer{display:flex;align-items:center;gap:6px;margin-top:4px}
.ms-msg-row.me .ms-msg-footer{justify-content:flex-end}
.ms-msg-time{font-size:.68rem;color:var(--ui-text-muted)}
.ms-msg-action{background:none;border:none;color:var(--ui-text-muted);font-size:.75rem;padding:1px 4px;border-radius:4px;cursor:pointer;transition:.15s}
.ms-msg-action:hover{color:var(--ui-accent)}
.ms-composer{display:flex;flex-direction:column;gap:0;padding:12px 18px 16px;background:var(--ui-surface-raised);border-top:1px solid var(--ui-border);border-radius:0 0 16px 0}
.ms-toolbar{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px}
.ms-toolbar button{background:rgba(255,255,255,.06);border:1px solid var(--ui-border);color:#e5edf7;width:30px;height:30px;border-radius:7px;font-size:.82rem;display:inline-flex;align-items:center;justify-content:center;transition:.15s;cursor:pointer}
.ms-toolbar button:hover{background:var(--ui-accent);color:#0b1120;border-color:var(--ui-accent)}
.ms-toolbar-sep{width:1px;background:var(--ui-border);margin:0 4px}
.ms-smilies-toggle{cursor:pointer;font-size:1.1rem;padding:0 4px;transition:.15s}
.ms-smilies-toggle:hover{transform:scale(1.2)}
.ms-smilies-panel{display:flex;flex-wrap:wrap;gap:8px;padding:8px 0 10px}
.ms-smilies-panel span{cursor:pointer;font-size:1.3rem;transition:.15s}
.ms-smilies-panel span:hover{transform:scale(1.25)}
.ms-composer textarea{flex:1;background:rgba(255,255,255,.04);border:1px solid var(--ui-border);border-radius:12px;padding:10px 14px;color:#e5edf7;font-size:.88rem;resize:none;outline:none;min-height:44px;max-height:160px;transition:.2s}
.ms-composer textarea:focus{border-color:var(--ui-accent)}
.ms-composer textarea::placeholder{color:var(--ui-text-muted)}
.ms-send-btn{align-self:flex-end;margin-top:8px;width:40px;height:40px;border-radius:50%;border:none;background:var(--ui-accent);color:#0b1120;font-size:1rem;display:flex;align-items:center;justify-content:center;transition:.2s;cursor:pointer}
.ms-send-btn:hover{transform:scale(1.08);box-shadow:0 4px 14px rgba(99,210,198,.35)}
.ms-composer-disabled{padding:14px 20px;background:var(--ui-surface-raised);border-top:1px solid var(--ui-border);color:var(--ui-text-muted);font-size:.85rem;text-align:center}
.ms-empty-chat{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--ui-text-muted);text-align:center;padding:40px}
.ms-empty-chat i{font-size:3rem;margin-bottom:12px;color:var(--ui-accent);opacity:.45}
.ms-empty-chat h5{font-weight:600;color:#e5edf7}
.ms-empty-chat p{font-size:.9rem;margin-top:4px}
.ms-flash{grid-column:1/-1;padding:12px 18px;font-size:.9rem;text-align:center;border-bottom:1px solid var(--ui-border)}
.ms-flash-error{color:#ff9c9c;background:rgba(220,53,69,.12)}
.ms-flash-success{color:var(--ui-accent);background:rgba(99,210,198,.1)}
@keyframes fadeUp{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:900px){.messenger-app{grid-template-columns:1fr;height:calc(100vh - 16rem)}.messenger-sidebar{display:flex}.ms-msg-row{max-width:90%}}
</style>

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
                if(textarea.value.trim() !== "") form.submit();
            }
        });
    }
    document.querySelectorAll(".edit-msg").forEach(function(btn){
        btn.addEventListener("click", function(){
            var id = this.dataset.id;
            var bubble = document.querySelector(".ms-msg-bubble[data-id='" + id + "'] .ms-msg-content");
            if(!bubble) return;
            var oldText = bubble.innerText;
            var newText = prompt("Edit message:", oldText);
            if(!newText) return;
            fetch("/messages/edit/" + id, {
                method:"POST",
                headers:{
                    "X-CSRF-TOKEN":document.querySelector("meta[name='csrf-token']").content,
                    "Content-Type":"application/json"
                },
                body:JSON.stringify({body:newText})
            }).then(function(res){ return res.json(); })
            .then(function(json){
                if(json && json.success){
                    bubble.innerHTML = newText;
                } else {
                    alert((json && json.error) ? json.error : "Could not edit message.");
                }
            });
        });
    });
    document.querySelectorAll(".delete-msg").forEach(function(btn){
        btn.addEventListener("click", function(){
            if(!confirm("Delete message?")) return;
            var id = this.dataset.id;
            var row = this.closest(".ms-msg-row");
            fetch("/messages/delete/" + id, {
                method:"DELETE",
                headers:{ "X-CSRF-TOKEN":document.querySelector("meta[name='csrf-token']").content }
            }).then(function(res){ return res.json(); })
            .then(function(json){
                if(json && json.success){
                    if(row) row.remove();
                } else {
                    alert((json && json.error) ? json.error : "Could not delete message.");
                }
            });
        });
    });
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
@endpush
