<div class="shoutbox-shell my-4">

    <div class="shoutbox-card glass shadow-lg">

<div class="shoutbox-wrap">

    {{-- Header --}}

    <div class="shoutbox-header glass d-flex align-items-center gap-3 px-4 py-3 mb-3">

        <div class="shoutbox-icon">

            <i class="bi bi-chat-dots-fill"></i>

        </div>

        <div>

            <h5 class="mb-0 fw-bold">Community Chat</h5>

            <small class="text-muted">Live chat · Be respectful · Have fun</small>

        </div>

    </div>

    {{-- Messages --}}

    <div class="shoutbox-container glass p-3 mb-3">

       <div id="shoutbox-messages">

@if(auth()->check() && auth()->user()->chatblock)

@php

    $systemUser = \App\Models\User::find(2);

    $classColor = \App\Models\UserClass::getClassColor($systemUser->user_class ?? 0);

@endphp

<div class="message system" data-id="system">

    <img class="avatar"

         src="{{ $systemUser->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

    <div class="bubble" style="--accent: {{ $classColor }}">

        <div class="header d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">

                <span class="username"

                      style="color: {{ $classColor }}">

                    {{ $systemUser->name ?? 'System' }}

                </span>

            </div>

            <span class="badge time-badge">

                <i class="bi bi-shield-lock me-1"></i>

                System

            </span>

        </div>

        <div class="content fs-5">

            ⚠️ Unable to post or see chat messages.  

            Your chat access has been restricted by staff.

        </div>

    </div>

</div>

@else

{{-- NORMAL CHAT MESSAGES --}}

@foreach($messages as $message)

            @php

                $classColor = \App\Models\UserClass::getClassColor($message->user->user_class);

            @endphp



              <div class="message {{ auth()->id() === $message->user_id ? 'own' : '' }}" data-id="{{ $message->id }}">

                <img class="avatar"

                     src="{{ $message->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

                <div class="bubble" style="--accent: {{ $classColor }}">

                   <div class="header d-flex align-items-center justify-content-between">

                {{-- LEFT SIDE: Username + Actions --}}

               <div class="d-flex align-items-center gap-2">

           {{-- Username --}}

           <a href="{{ route('profile.show', $message->user->id) }}"

           class="username d-inline-flex align-items-center gap-2"

           style="color: {{ $classColor }}"

           data-bs-toggle="tooltip"

           title="{{ $message->user->role_name }}">

            <span>{{ $message->user->name }}</span>

            <span class="role-badge role-{{ Str::slug($message->user->role_name) }}">

                @switch($message->user->role_name)

                    @case('Web Developer') <i class="bi bi-code-slash fs-5"></i> @break

                    @case('Owner') <i class="bi bi-emoji-sunglasses-fill"></i> @break

                    @case('Admin') <i class="bi bi-shield-fill-check"></i> @break

                    @case('Moderator') <i class="bi bi-shield-lock-fill"></i> @break

                    @case('VIP') <i class="bi bi-gem"></i> @break

                    @case('Elite User') <i class="bi bi-stars"></i> @break

                    @case('Special User') <i class="bi bi-lightning-fill"></i> @break

                    @case('Uploader') <i class="bi bi-cloud-arrow-up-fill"></i> @break

                    @default <i class="bi bi-person-fill"></i>

                @endswitch

            </span>

         </a>

          {{-- Actions (NOW INLINE) --}}

          <div class="actions-inline d-flex align-items-center gap-1">

            {{-- Reply --}}

            @php

$isSystem = $message->user_id == 2;

@endphp

@if(!$isSystem)

<button class="btn-icon"

        onclick="toggleReplyForm({{ $message->id }})"

        data-bs-toggle="tooltip"

        title="Reply">

    <i class="bi bi-reply fs-5"></i>

</button>

@endif

            @if(auth()->id() === $message->user_id || Auth::user()->user_class >= \App\Models\UserClass::ADMIN)

                {{-- Edit --}}

                <button type="button"

                        class="btn-icon warn"

                        data-bs-toggle="tooltip"

                        title="Edit"

                        onclick="openEdit({{ $message->id }})">

                    <i class="bi bi-pencil fs-5"></i>

                </button>

                {{-- Delete --}}

                <form action="{{ route('shoutbox.destroy', $message->id) }}"

                      method="POST"

                      class="shoutbox-delete-form d-inline"

                      data-id="{{ $message->id }}">

                    @csrf

                    @method('DELETE')

                    <button class="btn-icon danger fs-5"

                            data-bs-toggle="tooltip"

                            title="Delete"

                            onclick="return confirm('Delete this shout?')">

                        <i class="bi bi-trash"></i>

                    </button>

                </form>

            @endif

              </div>

            </div>

                 {{-- RIGHT SIDE: Time --}}

               <span class="badge time-badge">

               <i class="bi bi-clock me-1 fs-6"></i>

              <span class="timestamp-text">

                     {{ $message->created_at->format('Y-m-d H:i') }}

                    </span>

                </span>

                    </div>

                    <div class="content fs-6">

                        {!! convertCustomTagsToHtml($message->message) !!}

                    </div>

                                  {{-- EDIT FORM (MESSAGE) --}}

                        <div class="edit-form mt-2" id="edit-form-{{ $message->id }}" style="display:none;">

                      <form class="shoutbox-edit-form"

                        data-id="{{ $message->id }}"

                            action="{{ route('shoutbox.update', $message->id) }}"

                     method="POST">

                       @csrf

                     @method('PUT')

                     <textarea name="content"

                  class="edit-textarea"

                  rows="3"

                  required>{{ $message->message }}</textarea>

                <div class="d-flex gap-2 mt-2">

                     <button class="btn btn-sm btn-success">Save</button>

                       <button type="button"

                    class="btn btn-sm btn-secondary"

                    onclick="closeEdit({{ $message->id }})">

                Cancel

                  </button>

                </div>

                 </form>

               </div>



{{-- Reply form --}}

<form id="reply-form-{{ $message->id }}"

      action="{{ route('shoutbox.reply', $message->id) }}"

      method="POST"

      class="reply-form shoutbox-reply-form"

      data-parent="{{ $message->id }}"

      style="display: none;">

    @csrf

    <textarea name="content"

              class="reply-textarea"

              placeholder="Reply..."

              required></textarea>

    <div class="reply-actions">

        <button type="submit" class="btn btn-sm btn-success">

            <i class="bi bi-send me-1"></i> Send

        </button>

        <button type="button"

                class="btn btn-sm btn-outline-light"

                onclick="toggleReplyForm({{ $message->id }})">

            Cancel

        </button>

    </div>

</form>





                    {{-- Replies --}}

                    @if($message->replies->count())

                        <div class="replies mt-3">

                            @foreach($message->replies->sortByDesc('created_at') as $reply)

                                @php

                                    $replyColor = \App\Models\UserClass::getClassColor($reply->user->user_class);

                                @endphp

                                <div class="reply-card">

                                    <img class="avatar-sm"

                                         src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

                                    <div class="reply-bubble glass" style="--accent: {{ $replyColor }}">

                                       <div class="reply-header d-flex align-items-center justify-content-between">

    {{-- Left: username + role --}}

<strong class="reply-username d-inline-flex align-items-center gap-2">

    <a href="{{ route('profile.show', $reply->user->id) }}"

       class="d-inline-flex align-items-center gap-2 text-decoration-none"

       style="color: {{ $replyColor }}"

       data-bs-toggle="tooltip"

       title="{{ $reply->user->role_name }}">

        {{-- Username --}}

        <span>{{ $reply->user->name }}</span>

        {{-- Role badge --}}

        <span class="role-badge role-{{ Str::slug($reply->user->role_name) }}">

            @switch($reply->user->role_name)

                @case('Owner')

                    <i class="bi bi-emoji-sunglasses-fill"></i>

                    @break

                @case('Admin')

                    <i class="bi bi-shield-fill-check"></i>

                    @break

                @case('Web Developer')

                    <i class="bi bi-code-slash"></i>

                    @break

                @case('Moderator')

                    <i class="bi bi-shield-lock-fill"></i>

                    @break

                @case('VIP')

                    <i class="bi bi-gem"></i>

                    @break

                @case('Elite User')

                    <i class="bi bi-stars"></i>

                    @break

                @case('Special User')

                    <i class="bi bi-lightning-fill"></i>

                    @break

                @case('Uploader')

                    <i class="bi bi-cloud-arrow-up-fill"></i>

                    @break

                @default

                    <i class="bi bi-person-fill"></i>

            @endswitch

        </span>

    </a>

</strong>



    {{-- Right: meta + actions --}}

    <div class="reply-meta d-flex align-items-center gap-2">

<span class="badge time-badge time-badge-sm">

    <span class="timestamp-text">

        {{ $reply->created_at->format('Y-m-d H:i') }}

    </span>

    @if(

        auth()->user()->user_class >= \App\Models\UserClass::MODERATOR &&

        $reply->updated_at &&

        $reply->updated_at->gt($reply->created_at)

    )

        <span class="edited-badge ms-1"

              data-bs-toggle="tooltip"

              title="Edited at {{ $reply->updated_at->format('Y-m-d H:i') }}">

            (edited)

        </span>

    @elseif(auth()->user()->user_class >= \App\Models\UserClass::MODERATOR)

        <span class="edited-badge ms-1 d-none"

              data-bs-toggle="tooltip">

            (edited)

        </span>

    @endif

</span>







        {{-- Edit --}}

        @if(

            auth()->id() === $reply->user_id ||

            Auth::user()->user_class >= \App\Models\UserClass::MODERATOR

        )

            <button type="button"

        class="btn-icon warn"

        data-bs-toggle="tooltip"

        title="Edit reply"

        onclick="openReplyEdit({{ $reply->id }})">

    <i class="bi bi-pencil"></i>

</button>

        @endif

        {{-- Delete --}}

        @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)

           <form action="{{ route('shoutbox.destroy', $reply->id) }}"

      method="POST"

      class="reply-delete-form d-inline"

      data-bs-toggle="tooltip" title="Delete message"

      data-id="{{ $reply->id }}">

    @csrf

    @method('DELETE')

    <button type="button"

            class="btn-icon danger"

            title="Delete reply"

            onclick="handleReplyDelete(event, this.form)">

        <i class="bi bi-trash"></i>

    </button>

</form>

        @endif

    </div>

</div>



                                        <div class="reply-content-wrapper" data-id="{{ $reply->id }}">

    {{-- DISPLAY --}}

    <div class="reply-content">

        {!! convertCustomTagsToHtml($reply->message) !!}

    </div>

    {{-- EDIT FORM (SAME CLASS AS MESSAGE EDIT) --}}

    <div class="edit-form mt-2" id="reply-edit-form-{{ $reply->id }}" style="display:none;">

        <form class="shoutbox-edit-form"

              data-id="{{ $reply->id }}"

              action="{{ route('shoutbox.update', $reply->id) }}"

              method="POST">

            @csrf

            @method('PUT')

            <textarea name="content"

                      class="edit-textarea"

                      rows="3"

                      required>{{ $reply->message }}</textarea>

            <div class="d-flex gap-2 mt-2">

                <button class="btn btn-sm btn-success">Save</button>

                <button type="button"

                        class="btn btn-sm btn-secondary"

                        onclick="closeReplyEdit({{ $reply->id }})">

                    Cancel

                </button>

            </div>

        </form>

    </div>

</div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </div>

            </div>

        @endforeach

        @endif

        </div>



    </div>

    {{-- Emoji --}}

    <!-- @include('shoutbox.partials.emoji') -->

    {{-- Input --}}

    @if(!auth()->user()->chatblock)

    <form id="shoutbox-form"

          action="{{ route('shoutbox.store') }}"

          method="POST"

          class="chat-input glass"

           @if(Route::is('home')) onsubmit="return false;" @endif>

        @csrf

       <div class="chat-input-wrapper">

       <div id="typing-indicator" class="typing-indicator">

    <span id="typing-users"></span>

    <span class="typing-dots">

        <span></span>

        <span></span>

        <span></span>

    </span>

</div>

  {{-- TOOLBAR (EMOJIS + BBCODE) --}}

    <div class="chat-toolbar">

        {{-- BBCode --}}

        <div class="bbcode-buttons">

            <button type="button" onclick="wrapText('[b]','[/b]')" title="Bold">

                <i class="bi bi-type-bold"></i>

            </button>

            <button type="button" onclick="wrapText('[i]','[/i]')" title="Italic">

                <i class="bi bi-type-italic"></i>

            </button>

            <button type="button" onclick="wrapText('[u]','[/u]')" title="Underline">

                <i class="bi bi-type-underline"></i>

            </button>

            <button type="button" onclick="wrapText('[url]','[/url]')" title="Link">

                <i class="bi bi-link-45deg"></i>

            </button>

        </div>

        {{-- EMOJIS --}}

        <div class="emoji-bar">

    <button type="button" class="emoji-btn" onclick="addEmoji('😊')" title="smile happy grin">😊</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('❤️')" title="heart love">❤️</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('👍')" title="thumbs up like">👍</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😉')" title="wink">😉</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😂')" title="laugh lol funny">😂</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😞')" title="sad">😞</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😍')" title="love heart eyes">😍</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😎')" title="cool sunglasses">😎</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😭')" title="cry tears">😭</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😡')" title="angry mad">😡</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😘')" title="kiss">😘</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😲')" title="surprised wow">😲</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('😁')" title="grin happy">😁</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('⭐')" title="star favorite">⭐</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('🔥')" title="fire hot">🔥</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('🏆')" title="trophy win">🏆</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('🎉')" title="party celebrate">🎉</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('👏')" title="clap applause">👏</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('🎊')" title="confetti">🎊</button>

    <button type="button" class="emoji-btn" onclick="addEmoji('🙌')" title="praise hands">🙌</button>

</div>

    </div>

    <textarea id="content"

              name="content"

              rows="1"

              maxlength="1000"

              required></textarea>

    <span class="chat-hint">Say something nice… 👋</span>



    <div id="char-counter" class="char-counter">

        <span id="char-count">0</span> / <span id="char-max">1000</span>

    </div>

</div>



<script>

(() => {

    const messages = [

        "Say something nice… 👋",

        "What’s on your mind? 💭",

        "Drop a thought, a joke, or a vibe ✨",

        "Be kind. Be funny. Be real 💬",

        "Got something to share? We’re listening 👀",

        "Type here… magic happens sometimes 🪄",

        "Press Enter to send • Shift+Enter for a new line ⏎"

    ];

    const textarea = document.getElementById('content');

    const hint = document.querySelector('.chat-hint');

    if (!textarea || !hint) return;

    // Pick a non-repeating random placeholder

    let lastIndex = -1;

    let index;

    do {

        index = Math.floor(Math.random() * messages.length);

    } while (index === lastIndex);

    lastIndex = index;

    hint.textContent = messages[index];

    // Floating hint behavior

    textarea.addEventListener('focus', () => {

        hint.classList.add('active');

    });

    textarea.addEventListener('blur', () => {

        if (!textarea.value.trim()) {

            hint.classList.remove('active');

        }

    });

    textarea.addEventListener('input', () => {

        textarea.style.height = 'auto';

        textarea.style.height = textarea.scrollHeight + 'px';

    });

})();

</script>



<script>

(() => {

    const textarea = document.getElementById('content');

    const counter = document.getElementById('char-counter');

    const countEl = document.getElementById('char-count');

    if (!textarea || !counter || !countEl) return;

    const MAX = textarea.maxLength || 1000;

    const WARN_AT = 800;

    textarea.addEventListener('input', () => {

        const len = textarea.value.length;

        countEl.textContent = len;

        // show immediately

        counter.classList.add('visible');

        counter.classList.toggle('warn', len >= WARN_AT);

        counter.classList.toggle('danger', len >= MAX);

        // auto-grow textarea

        textarea.style.height = 'auto';

        textarea.style.height = textarea.scrollHeight + 'px';

    });

    textarea.addEventListener('blur', () => {

        if (!textarea.value.length) {

            counter.classList.remove('visible');

        }

    });

})();













function wrapText(before, after) {

    const textarea = document.getElementById('content');

    const start = textarea.selectionStart;

    const end = textarea.selectionEnd;

    const selected = textarea.value.substring(start, end);

    // If text is selected → wrap it

    if (selected.length > 0) {

        textarea.value =

            textarea.value.substring(0, start) +

            before + selected + after +

            textarea.value.substring(end);

        // keep selection around wrapped text

        textarea.selectionStart = start + before.length;

        textarea.selectionEnd = end + before.length;

    } else {

        // No selection → insert and place cursor in middle

        const insert = before + after;

        textarea.value =

            textarea.value.substring(0, start) +

            insert +

            textarea.value.substring(start);

        // 👇 THIS is the key part

        const cursorPos = start + before.length;

        textarea.selectionStart = cursorPos;

        textarea.selectionEnd = cursorPos;

    }

    textarea.focus();

    textarea.scrollTop = textarea.scrollHeight;

}

function addEmoji(emoji) {

    const textarea = document.getElementById('content');

    const start = textarea.selectionStart;

    const end = textarea.selectionEnd;

    textarea.value =

        textarea.value.substring(0, start) +

        emoji +

        textarea.value.substring(end);

    const cursor = start + emoji.length;

    textarea.selectionStart = cursor;

    textarea.selectionEnd = cursor;

    textarea.focus();

}



</script>









<style>

    .emoji-bar {

    display: flex;

    gap: 6px;

    overflow-x: auto;

    padding: 6px;

    margin-bottom: 6px;

    scrollbar-width: none;

}

.emoji-bar::-webkit-scrollbar {

    display: none;

}

.emoji-btn {

    background: rgba(255,255,255,.06);

    border: none;

    border-radius: 10px;

    padding: 6px 8px;

    font-size: 1.1rem;

    cursor: pointer;

    transition: all .15s ease;

}

.emoji-btn:hover {

    background: rgba(255,255,255,.15);

    transform: scale(1.15);

}

    .chat-toolbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 8px;

    gap: 10px;

    flex-wrap: wrap;

}

/* BBCode buttons */

.bbcode-buttons button {

    background: rgba(255,255,255,.08);

    border: none;

    color: #fff;

    padding: 6px 8px;

    border-radius: 8px;

    margin-right: 4px;

    transition: .15s;

}

.bbcode-buttons button:hover {

    background: rgba(255,255,255,.18);

}

/* Emoji bar */

.emoji-bar {

    display: flex;

    gap: 6px;

    font-size: 1.2rem;

    cursor: pointer;

}

.emoji-bar span {

    transition: transform .1s ease;

}

.emoji-bar span:hover {

    transform: scale(1.2);

}

.typing-indicator{

    display:flex;

    align-items:center;

    gap:6px;

    font-size: 14px;

    color:rgba(255,255,255,.65);

    margin-bottom:6px;

    min-height:20px;

}

.typing-dots{

    display:inline-flex;

    gap:3px;

}

.typing-dots span{

    width:4px;

    height:4px;

    background:#ccc;

    border-radius:50%;

    opacity:.3;

    animation:typingDots 1.4s infinite;

}

.typing-dots span:nth-child(2){

    animation-delay:.2s;

}

.typing-dots span:nth-child(3){

    animation-delay:.4s;

}

@keyframes typingDots{

    0%{opacity:.2;transform:translateY(0);}

    50%{opacity:1;transform:translateY(-2px);}

    100%{opacity:.2;transform:translateY(0);}

}

    #content {

    width: 100%;

    min-height: 36px;

    padding: 18px 20px;      

    padding-top: 22px; 

    background: rgba(15,15,15,.9);

    color: #fff;

    border-radius: 16px;

    border: 1px solid rgba(255,255,255,.12);

    resize: none;

    font-size: 14px;

    line-height: 1.6;        

    caret-color: var(--ui-accent);

    transition:

        border .2s ease,

        box-shadow .2s ease,

        background .2s ease;

}

   .char-counter {

    position: absolute;

    right: 12px;

    bottom: 8px;

    font-size: .7rem;

    font-weight: 500;

    letter-spacing: .3px;

    color: rgba(255,255,255,.45);

    opacity: 0;

    transform: translateY(4px);

    transition: opacity .2s ease, transform .2s ease, color .2s ease;

    pointer-events: none;

}

.char-counter.visible {

    opacity: 1;

    transform: translateY(0);

}

.char-counter.warn {

    color: rgba(255,193,7,.9);

}

.char-counter.danger {

    color: #ff6b6b;

    font-weight: 600;

}

.chat-input-wrapper {

    position: relative;

}



    </style>

    </form>

    @endif





</div>

 </div>

</div>



{{-- Styles --}}

<style>

body {
    color: #e6edf3;
}

.glass {
    background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
    backdrop-filter: blur(10px);
    border: 1px solid var(--ui-border);
    border-radius: .85rem;
}

.shoutbox-container {

    max-height: 500px;

    overflow-y: auto;

}

.avatar {

    width: 44px;

    height: 44px;

    border-radius: 50%;

}

.bubble {

    flex: 1;

    padding: 12px;

    border-left: 4px solid var(--accent);

}

.header {

    display: flex;

    justify-content: space-between;

}

.username {

    font-weight: 600;

    text-decoration: none;

}

.content {

    margin-top: 3px;

}

.actions {

    display: flex;

    gap: 6px;

    margin-top: 8px;

}

.btn-icon {

    background: none;

    border: none;

    color: #bbb;

}

.btn-icon:hover { color: #fff; }

.btn-icon.warn:hover { color: var(--ui-accent); }

.btn-icon.danger:hover { color: #dc3545; }

.reply-form {

    display: none;

    margin-top: 8px;

}

.reply-form textarea {

    width: 100%;

    background: #111;

    color: #fff;

    border-radius: 8px;

}

.replies {

    display: flex;

    flex-direction: column;

    gap: 10px;

    padding-left: 18px;

    border-left: 2px solid rgba(255,255,255,.08);

}

.reply-card {

    display: flex;

    align-items: flex-start;

    gap: 10px;

}

.reply-bubble {

    flex: 1;

    padding: 12px;

    border-radius: 14px;

    border-left: 4px solid var(--accent);

    background: rgba(0,0,0,.45);

    font-size: 14px;

}

.reply-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

}

.reply-meta {

    display: flex;

    align-items: center;

    gap: 6px;

}

.reply-username {

    font-weight: 600;

    font-size: 14px;

}

.reply-content {

    margin-top: 6px;

    line-height: 1.45;

}

.avatar-sm {

    width: 30px;

    height: 30px;

    border-radius: 50%;

    flex-shrink: 0;

}



.chat-input {

    position: sticky;

    bottom: 10px;

    padding: 12px;

}

.chat-input textarea {

    width: 100%;

    background: #111;

    color: #fff;

    border-radius: 12px;

    resize: none;

}









/* === SHOUTBOX WRAP === */

.shoutbox-wrap {

    position: relative;

}

.shoutbox-header {

    border-radius: 18px;

    border-left: 4px solid var(--ui-accent);

}

.shoutbox-icon {

    width: 42px;

    height: 42px;

    border-radius: 14px;

    display: grid;

    place-items: center;

    background: linear-gradient(135deg, var(--ui-accent), var(--ui-accent-strong));

    color: #111;

    font-size: 1.3rem;

    box-shadow: 0 6px 20px rgba(45, 212, 191, .18);

}

.shoutbox-body {

    border-radius: 10px;

}

/* Make messages feel lighter */

.bubble,

.reply-bubble {

    transition: transform .15s ease, box-shadow .15s ease;

}

.bubble:hover,

.reply-bubble:hover {

    transform: translateY(-1px);

    box-shadow: 0 10px 30px rgba(0,0,0,.35);

}

/* Improve input focus */

.chat-input textarea:focus {

    outline: none;

    border: 1px solid var(--ui-accent);

    box-shadow: 0 0 0 2px rgba(45, 212, 191, .12);

}

/* Modern scrollbar */

.shoutbox-container::-webkit-scrollbar {

    width: 6px;

}

.shoutbox-container::-webkit-scrollbar-thumb {

    background: rgba(255,255,255,.15);

    border-radius: 10px;

}

.shoutbox-container::-webkit-scrollbar-thumb:hover {

    background: rgba(255,255,255,.3);

}

.reply-header {

    font-size: .85rem;

}

.reply-meta .btn-icon {

    padding: 2px;

    line-height: 1;

}

.reply-meta i {

    font-size: .85rem;

}

.reply-meta .timestamp {

    font-size: .75rem;

}

.reply-meta {

    opacity: 0;

    transition: opacity .15s ease;

}

.reply-bubble:hover .reply-meta {

    opacity: 1;

}

/* === MODERN SHOUTBOX CARD === */

.shoutbox-shell {

    display: flex;

    justify-content: center;

}

.shoutbox-card {

    width: 100%;

    max-width: 100%;

    padding: 20px;

    border-radius: 22px;

    position: relative;

}

/* subtle glowing edge */



/* header separation */

.shoutbox-header {

    background: rgba(40, 40, 40, 0.168);

    border-radius: 18px;

}

/* tighten inner spacing */

.shoutbox-container{

    max-height:650px;

    padding:14px !important;

}

/* mobile polish */

@media (max-width: 768px) {

    .shoutbox-card {

        padding: 14px;

    }

}

/* === TIME BADGES === */

.time-badge {

    background: rgba(255,255,255,0.08);

    color: #ddd;

    font-size: .7rem;

    font-weight: 500;

    padding: 4px 8px;

    border-radius: 999px;

    display: inline-flex;

    align-items: center;

    gap: 4px;

    letter-spacing: .3px;

    backdrop-filter: blur(4px);

}

.time-badge-sm {

    font-size: .65rem;

    padding: 3px 7px;

}

/* === HEADER POLISH === */

.header {

    gap: 8px;

}

.header .username {

    display: inline-flex;

    align-items: center;

    gap: 6px;

}

/* === ACTION ICONS === */

.actions .btn-icon,

.reply-meta .btn-icon {

    background: rgba(255,255,255,0.04);

    border-radius: 8px;

    padding: 4px;

    transition: background .15s ease, transform .15s ease;

}

.actions .btn-icon:hover,

.reply-meta .btn-icon:hover {

    background: rgba(255,255,255,0.12);

    transform: scale(1.05);

}

/* === MESSAGE CARD FEEL === */

.message {

    position: relative;

    display: flex;

    gap: 10px;

    margin-bottom: 14px;

}

.message .bubble {

    width: 100%;

    max-width: 100%;

}

.message::before {

    content: '';

    position: absolute;

    left: 20px;

    top: 0;

    bottom: 0;

    width: 1px;

    background: linear-gradient(

        transparent,

        rgba(255,255,255,.06),

        transparent

    );

}

/* === REPLY CARD POLISH === */

.reply-card {

    position: relative;

}

.reply-card::before {

    content: '';

    position: absolute;

    left: -9px;

    top: 12px;

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: rgba(45, 212, 191, .55);

}

/* === INPUT GLOW === */

.chat-input {

    background: linear-gradient(

        to top,

        rgba(0,0,0,.35),

        rgba(255,255,255,.02)

    );

}

/* === SUBTLE ENTRY ANIMATION === */

.message,

.reply-card {

    animation: fadeInUp .2s ease;

}

@keyframes fadeInUp {

    from {

        opacity: 0;

        transform: translateY(4px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}

.reply-meta {

    opacity: 0;

}

.reply-bubble:hover .reply-meta {

    opacity: 1;

}

.actions {

    opacity: 0;

    transition: opacity .15s ease;

}

.bubble:hover .actions {

    opacity: 1;

}

.message.own .bubble {

    background: linear-gradient(

        135deg,

        rgba(76, 98, 123, 0.35),

        rgba(90, 180, 255, 0.25)

    );

    border-radius: 18px 18px 6px 18px; /* iMessage-style tail */

    border-left: none;

    box-shadow:

        inset 0 0 0 1px rgba(255,255,255,.12),

        0 10px 30px rgba(0,0,0,.35);

    position: relative;

    overflow: hidden;

}

.message.own .bubble::after {

    content: '';

    position: absolute;

    top: 0;

    right: 0;

    width: 100%;

    height: 100%;

    background: radial-gradient(

        circle at top right,

        rgba(255,255,255,.25),

        transparent 60%

    );

    pointer-events: none;

}

.message.own .username {

    opacity: .75;

}

/* === OTHER USERS MESSAGE (iMessage-style) === */

.message:not(.own) .bubble {

    background: linear-gradient(

        135deg,

        rgba(15, 14, 14, 0.741),

        rgba(120, 120, 120, 0.14)

    );

    border-radius: 18px 18px 18px 6px; /* opposite tail */

    border-left: 4px solid var(--accent);

    box-shadow:

        inset 0 0 0 1px rgba(255,255,255,.08),

        0 8px 26px rgba(0,0,0,.35);

    position: relative;

    overflow: hidden;

}

.message:not(.own) .bubble::after {

    content: '';

    position: absolute;

    inset: 0;

    background: radial-gradient(

        circle at top left,

        rgba(255,255,255,.18),

        transparent 60%

    );

    pointer-events: none;

}

.message:not(.own) .username {

    opacity: .9;

}

/* === EDIT TEXTAREA (WIDE & MODERN) === */

.edit-textarea {

    width: 100%;

    min-height: 90px;

    padding: 12px 14px;

    border-radius: 14px;

    background: rgba(20, 20, 20, 0.85);

    color: #fff;

    border: 1px solid rgba(255,255,255,.12);

    font-size: 14px;

    line-height: 1.5;

    resize: vertical;

}

/* edge-to-edge feel inside bubble */

.edit-form {

    margin-left: -6px;

    margin-right: -6px;

}

/* focus polish */

.edit-textarea:focus {

    outline: none;

    border-color: var(--ui-accent);

    box-shadow: 0 0 0 2px rgba(45, 212, 191, .12);

}



.user-class-badge {

    background: rgba(214, 212, 212, 0.045);

    color: var(--class-color);

    font-size: 14px;

    font-weight: 600;

    padding: 2px 7px;

    border-radius: 999px;

    letter-spacing: .4px;

    backdrop-filter: blur(4px);

    line-height: 1;

}

/* === BASE ROLE BADGE (REFINED) === */

.role-badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 26px;

    height: 26px;

    border-radius: 50%;

    font-size: .85rem;

    line-height: 1;

    backdrop-filter: blur(8px);

    border: 1px solid rgba(255,255,255,.22);

    background-clip: padding-box;

    box-shadow:

        inset 0 0 0 1px rgba(255,255,255,.06),

        0 6px 18px rgba(0,0,0,.45);

    transition:

        transform .15s ease,

        box-shadow .15s ease,

        filter .15s ease;

}

/* Hover micro-interaction */

.role-badge:hover {

    transform: translateY(-1px) scale(1.08);

    filter: brightness(1.1);

}

/* === ROLE COLORS === */

/* OWNER — crown + royal glow */

.role-owner {

    background: linear-gradient(135deg, #d4af37, #ffec8b);

    color: #2a2100;

    box-shadow:

        0 0 30px rgba(255,215,0,.75),

        0 10px 25px rgba(0,0,0,.45);

}

/* ADMIN — authority red shield */

.role-admin {

    background: linear-gradient(135deg, #ff4d4d, #ff7a7a);

    color: #fff;

    box-shadow:

        0 0 24px rgba(255,77,77,.65),

        0 10px 22px rgba(0,0,0,.4);

}

/* WEB DEV — clean tech cyan */

.role-web-developer {

    background: linear-gradient(135deg, #0dcaf0, #5ee7ff);

    color: #003542;

    box-shadow:

        0 0 22px rgba(13,202,240,.55);

}

/* MODERATOR — calm authority purple */

.role-moderator {

    background: linear-gradient(135deg, #6f42c1, #b088ff);

    color: #fff;

    box-shadow:

        0 0 18px rgba(111,66,193,.5);

}

/* VIP — subtle luxury glow (no seizure 😄) */

.role-vip {

    background: linear-gradient(135deg, var(--ui-accent), #ffe083);

    color: #2b1d00;

    animation: vipGlow 3s ease-in-out infinite;

}

/* ELITE USER — prestige green */

.role-elite-user {

    background: linear-gradient(135deg, #20c997, #6ee7c8);

    color: #083b2d;

}

/* SPECIAL USER — energy orange */

.role-special-user {

    background: linear-gradient(135deg, #fd7e14, #ffb066);

    color: #2a1600;

}

/* UPLOADER — trust green */

.role-uploader {

    background: linear-gradient(135deg, #198754, #4fe3a1);

    color: #06281e;

}

/* DEFAULT USER */

.role-user {

    background: rgba(255,255,255,.14);

    color: #ddd;

}

/* === VIP GLOW (REFINED) === */

@keyframes vipGlow {

    0% {

        box-shadow:

            0 0 14px rgba(45, 212, 191, .18),

            0 8px 20px rgba(0,0,0,.4);

    }

    50% {

        box-shadow:

            0 0 30px rgba(45, 212, 191, .35),

            0 12px 26px rgba(0,0,0,.45);

    }

    100% {

        box-shadow:

            0 0 14px rgba(45, 212, 191, .18),

            0 8px 20px rgba(0,0,0,.4);

    }

}

.edited-badge {

    font-size: .65rem;

    opacity: .7;

    font-style: italic;

    cursor: help;

}



/*Chat*/

/* === MOBILE RULES BOTTOM SHEET FIX === */

@media (max-width: 768px) {

    .rules-panel {

        position: fixed;

        inset: auto 0 0 0;

        max-height: 80vh;

        border-radius: 18px 18px 0 0;

        overflow-y: auto;

        z-index: 1060;

        padding-top: 48px; /* space for header */

    }

    /* Header bar */

    .rules-sheet-header {

        position: absolute;

        top: 0;

        left: 0;

        right: 0;

        height: 44px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: rgba(0,0,0,.35);

        backdrop-filter: blur(10px);

        border-radius: 18px 18px 0 0;

    }

    /* Drag handle */

    .rules-drag {

        width: 42px;

        height: 5px;

        border-radius: 999px;

        background: rgba(255,255,255,.35);

    }

    /* Close button */

    .rules-close {

        position: absolute;

        right: 12px;

        top: 8px;

        background: none;

        border: none;

        color: #fff;

        font-size: 1.1rem;

        opacity: .75;

    }

    .rules-close:hover {

        opacity: 1;

    }

    /* Dim background when open */

    body.rules-open::before {

        content: '';

        position: fixed;

        inset: 0;

        background: rgba(0,0,0,.55);

        z-index: 1055;

    }

}



.actions-inline {

    opacity: 0;

    transition: opacity .15s ease;

}

.bubble:hover .actions-inline {

    opacity: 1;

}

.actions-inline .btn-icon {

    background: transparent;

    padding: 3px;

    border-radius: 6px;

}

.actions-inline .btn-icon:hover {

    background: rgba(255,255,255,.08);

    transform: scale(1.05);

}

/* Smooth edit animation */

.edit-form,

.content {

    transition:

        opacity .18s ease,

        transform .18s ease;

}

.reply-actions {

    display: flex;

    gap: 8px;

    margin-top: 8px;

}

.reply-actions .btn {

    flex: 1;

}

.reply-textarea {

    width: 100%;

    background: rgba(20,20,20,.9);

    color: #fff;

    border-radius: 10px;

    padding: 10px 12px;

    border: 1px solid rgba(255,255,255,.15);

    resize: vertical;

    font-size: 14px;

}

.reply-textarea:focus {

    outline: none;

    border-color: var(--ui-accent);

    box-shadow: 0 0 0 2px rgba(45, 212, 191, .12);

}

</style>

{{-- Scripts --}}

@if(Route::is('home'))

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('shoutbox-form');

    const textarea = document.getElementById('content');

    const messagesBox = document.getElementById('shoutbox-messages');

    if (!form || !textarea || !messagesBox) return;

    let typingTimeout;

    /* =========================

       SEND MESSAGE (ENTER)

    ========================= */

    textarea.addEventListener('keydown', e => {

        if (e.key === 'Enter' && !e.shiftKey) {

            e.preventDefault();

            if (!textarea.value.trim()) return;

            fetch(form.action,{

                method:'POST',

                body:new FormData(form),

                headers:{'X-Requested-With':'XMLHttpRequest'}

            })

            .then(() => {

                textarea.value='';

                reloadMessages();

            })

            .catch(console.error);

        }

    });



    /* =========================

       TYPING INDICATOR

    ========================= */

    textarea.addEventListener('input', () => {

        fetch('/shoutbox/typing',{

            method:'POST',

            headers:{

                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,

                'X-Requested-With':'XMLHttpRequest'

            }

        });

        clearTimeout(typingTimeout);

        typingTimeout = setTimeout(()=>{

            fetch('/shoutbox/typing-stop',{

                method:'POST',

                headers:{

                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,

                    'X-Requested-With':'XMLHttpRequest'

                }

            });

        },2000);

    });



    /* =========================

       RELOAD CHAT

    ========================= */

    function reloadMessages(){

        fetch(window.location.href)

        .then(r=>r.text())

        .then(html=>{

            const dom = new DOMParser().parseFromString(html,'text/html');

            const fresh = dom.querySelector('#shoutbox-messages');

            if(!fresh) return;

            messagesBox.innerHTML = fresh.innerHTML;

            messagesBox.scrollTop = messagesBox.scrollHeight;

        });

    }



});



/* =========================

   DELETE MESSAGE

\========================= */

document.addEventListener('click', e => {

    const form = e.target.closest('.shoutbox-delete-form');

    if(!form) return;

    e.preventDefault();

    if(!confirm('Delete this shout?')) return;

    fetch(form.action,{

        method:'POST',

        body:new FormData(form),

        headers:{'X-Requested-With':'XMLHttpRequest'}

    })

    .then(()=>{

        form.closest('.message')?.remove();

    })

    .catch(console.error);

});



/* =========================

   ESC CLOSE EDIT

\========================= */

document.addEventListener('keydown', e => {

    if(e.key !== 'Escape') return;

    const openForm = document.querySelector('.edit-form[style*="block"]');

    if(!openForm) return;

    const id = openForm.id.replace('edit-form-','');

    closeEdit(id);

});



/* =========================

   REPLY SUBMIT

\========================= */

document.addEventListener('submit', e => {

    const form = e.target.closest('.shoutbox-reply-form');

    if(!form) return;

    e.preventDefault();

    fetch(form.action,{

        method:'POST',

        body:new FormData(form),

        headers:{'X-Requested-With':'XMLHttpRequest'}

    })

    .then(res=>res.json())

    .then(data=>{

        if(!data.html) return;

        const bubble = form.closest('.bubble');

        let replies = bubble.querySelector('.replies');

        if(!replies){

            replies=document.createElement('div');

            replies.className='replies mt-3';

            bubble.appendChild(replies);

        }

        replies.insertAdjacentHTML('afterbegin',data.html);

        form.querySelector('textarea').value='';

        form.style.display='none';

    })

    .catch(console.error);

});



/* =========================

   DELETE REPLY

\========================= */

function handleReplyDelete(e, form){

    e.preventDefault();

    if(!confirm('Delete this reply?')) return;

    fetch(form.action,{

        method:'POST',

        body:new FormData(form),

        headers:{'X-Requested-With':'XMLHttpRequest'}

    })

    .then(()=>{

        const card=form.closest('.reply-card');

        if(!card) return;

        card.style.opacity='0';

        setTimeout(()=>{

            card.remove();

        },200);

    })

    .catch(console.error);

}



/* =========================

   EDIT MESSAGE

\========================= */

function openEdit(id){

    const wrapper=document.querySelector(`.message[data-id="${id}"]`);

    if(!wrapper) return;

    const content=wrapper.querySelector('.content');

    const form=wrapper.querySelector(`#edit-form-${id}`);

    if(!form || !content) return;

    content.style.display='none';

    form.style.display='block';

    const textarea=form.querySelector('.edit-textarea');

    if(textarea){

        textarea.focus();

        textarea.style.height='auto';

        textarea.style.height=textarea.scrollHeight+'px';

    }

}



function closeEdit(id){

    const wrapper=document.querySelector(`.message[data-id="${id}"]`);

    if(!wrapper) return;

    const content=wrapper.querySelector('.content');

    const form=wrapper.querySelector(`#edit-form-${id}`);

    if(!form || !content) return;

    form.style.display='none';

    content.style.display='block';

}



/* =========================

   AUTO EXPAND EDIT TEXTAREA

\========================= */

document.addEventListener('input', e=>{

    if(!e.target.classList.contains('edit-textarea')) return;

    e.target.style.height='auto';

    e.target.style.height=e.target.scrollHeight+'px';

});



/* =========================

   EDIT SUBMIT

\========================= */

document.addEventListener('submit', e=>{

    const form=e.target.closest('.shoutbox-edit-form');

    if(!form) return;

    e.preventDefault();

    fetch(form.action,{

        method:'POST',

        body:new FormData(form),

        headers:{'X-Requested-With':'XMLHttpRequest'}

    })

    .then(r=>r.json())

    .then(data=>{

        if(!data.message) return;

        const id=data.message.id;

        const content=document.querySelector(`.message[data-id="${id}"] .content`);

        if(content){

            content.innerHTML=data.message.message;

            closeEdit(id);

        }

    })

    .catch(console.error);

});



/* =========================

   REPLY EDIT

\========================= */

function openReplyEdit(id){

    document.getElementById('reply-edit-form-'+id).style.display='block';

}

function closeReplyEdit(id){

    document.getElementById('reply-edit-form-'+id).style.display='none';

}



/* =========================

   TOGGLE REPLY FORM

\========================= */

function toggleReplyForm(id){

    const form=document.getElementById('reply-form-'+id);

    if(!form) return;

    const open=form.style.display==='block';

    document.querySelectorAll('.reply-form').forEach(f=>{

        f.style.display='none';

    });

    if(!open){

        form.style.display='block';

        form.querySelector('textarea')?.focus();

    }

}



function loadTypingUsers(){

    fetch('/shoutbox/typing-users')

    .then(r => r.json())

    .then(users => {

        const box = document.getElementById('typing-users');

        const indicator = document.getElementById('typing-indicator');

        if(!users.length){

            box.innerHTML='';

            indicator.style.opacity='0';

            return;

        }

        indicator.style.opacity='1';

        if(users.length === 1){

            box.innerHTML = users[0] + ' is typing';

        }else{

            box.innerHTML = users.join(', ') + ' are typing';

        }

    });

}

setInterval(loadTypingUsers,2000);

</script>

@endpush

@endif

<style>
/* =========================================================
   FileIplay Shoutbox — Forum Style Final Overrides
   ========================================================= */

.shoutbox-shell {
    display: flex;
    justify-content: center;
    width: 100%;
}

.shoutbox-card {
    width: 100%;
    max-width: 100%;
    padding: 18px;
    position: relative;
    background: linear-gradient(135deg, rgba(22, 32, 51, .95), rgba(15, 23, 42, .84));
    border: 1px solid var(--ui-border);
    border-left: 3px solid var(--ui-accent);
    border-radius: .9rem;
    box-shadow: 0 10px 30px rgba(0,0,0,.22);
}

.shoutbox-header {
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84)) !important;
    border: 1px solid var(--ui-border) !important;
    border-left: 3px solid var(--ui-accent) !important;
    border-radius: .8rem !important;
}

.shoutbox-icon {
    width: 40px;
    height: 40px;
    border-radius: .65rem;
    display: grid;
    place-items: center;
    background: rgba(45, 212, 191, .12) !important;
    border: 1px solid rgba(45, 212, 191, .25);
    color: var(--ui-accent) !important;
    font-size: 1.15rem;
    box-shadow: none !important;
}

.shoutbox-header h5 {
    font-size: 14px !important;
}

.shoutbox-header small {
    font-size: 13px !important;
}

.shoutbox-container {
    max-height: 650px;
    overflow-y: auto;
    background: rgba(8, 15, 28, .55) !important;
    border: 1px solid var(--ui-border);
    border-radius: .8rem;
}

.message {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.avatar {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 50%;
    border: 1px solid var(--ui-border);
    flex-shrink: 0;
}

.bubble,
.reply-bubble {
    background: linear-gradient(135deg, rgba(22,32,51,.92), rgba(15,23,42,.80)) !important;
    border: 1px solid var(--ui-border) !important;
    border-left: 3px solid var(--accent) !important;
    border-radius: .75rem !important;
    box-shadow: 0 6px 18px rgba(0,0,0,.18) !important;
}

.message.own .bubble {
    background: linear-gradient(135deg, rgba(20, 62, 70, .48), rgba(15, 35, 49, .82)) !important;
    border-left: 0 !important;
    border-right: 3px solid var(--ui-accent) !important;
    border-radius: .75rem !important;
}

.message:not(.own) .bubble {
    background: linear-gradient(135deg, rgba(22,32,51,.92), rgba(15,23,42,.80)) !important;
}

.username,
.reply-username {
    font-size: 14px !important;
    font-weight: 600;
}

.content,
.reply-content {
    font-size: 14px !important;
    line-height: 1.5;
    color: #e6edf3;
}

.time-badge {
    background: rgba(255,255,255,.055) !important;
    color: #aeb8c4 !important;
    border: 1px solid var(--ui-border);
    font-size: 12px !important;
    padding: 3px 7px;
}

.role-badge {
    width: 23px;
    height: 23px;
    font-size: 12px;
    box-shadow: none !important;
}

.btn-icon {
    color: #9aa7b5;
}

.actions-inline .btn-icon:hover,
.reply-meta .btn-icon:hover {
    background: rgba(45,212,191,.10) !important;
    color: var(--ui-accent) !important;
    transform: none;
}

.btn-icon.warn:hover {
    color: #f6c453 !important;
}

.btn-icon.danger:hover {
    color: #ff6b6b !important;
}

.replies {
    border-left: 1px solid rgba(45,212,191,.22) !important;
}

.reply-card::before {
    background: var(--ui-accent) !important;
}

.reply-textarea,
.edit-textarea,
.chat-input textarea,
#content {
    background: rgba(8, 15, 28, .82) !important;
    color: #e6edf3 !important;
    border: 1px solid var(--ui-border) !important;
    border-radius: .7rem !important;
    font-size: 14px !important;
}

.reply-textarea:focus,
.edit-textarea:focus,
.chat-input textarea:focus,
#content:focus {
    outline: none;
    border-color: var(--ui-accent) !important;
    box-shadow: 0 0 0 2px rgba(45,212,191,.10) !important;
}

.chat-input {
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84)) !important;
    border: 1px solid var(--ui-border) !important;
    border-radius: .8rem !important;
}

.chat-toolbar {
    gap: 8px;
}

.bbcode-buttons button,
.emoji-btn {
    background: rgba(255,255,255,.045) !important;
    color: #b9c4cf !important;
    border: 1px solid var(--ui-border) !important;
    border-radius: .5rem !important;
}

.bbcode-buttons button:hover,
.emoji-btn:hover {
    background: rgba(45,212,191,.10) !important;
    color: var(--ui-accent) !important;
    transform: none;
}

.typing-indicator {
    font-size: 13px !important;
    color: rgba(255,255,255,.55) !important;
}

.typing-dots span {
    background: var(--ui-accent) !important;
}

.chat-hint {
    font-size: 13px !important;
    color: rgba(255,255,255,.42) !important;
}

.char-counter {
    font-size: 12px !important;
}

.reply-actions .btn,
.edit-form .btn {
    font-size: 13px !important;
    border-radius: .5rem;
}

.shoutbox-container::-webkit-scrollbar {
    width: 5px;
}

.shoutbox-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,.12);
    border-radius: 999px;
}

@media (max-width: 768px) {
    .shoutbox-card {
        padding: 12px;
        border-radius: .75rem;
    }

    .shoutbox-header {
        padding: 12px !important;
    }

    .message {
        gap: 8px;
    }

    .avatar {
        width: 36px;
        height: 36px;
    }

    .content,
    .reply-content,
    .username,
    .reply-username {
        font-size: 14px !important;
    }

    .time-badge {
        font-size: 11px !important;
    }

    .role-badge {
        width: 21px;
        height: 21px;
        font-size: 11px;
    }

    .shoutbox-container {
        max-height: 560px;
    }
}
</style>

