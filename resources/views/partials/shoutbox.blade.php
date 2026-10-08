@push('styles')
<link rel="stylesheet" href="{{ asset('css/shoutbox.css') }}?v={{ filemtime(public_path('css/shoutbox.css')) }}">
@endpush

<div class="shoutbox-shell my-4" id="community-chat" data-user="{{ auth()->id() }}" data-highlight-shout="{{ $highlightShoutId ?? '' }}"
     data-older-url="{{ route('shoutbox.older') }}" data-poll-url="{{ url('/shoutbox/poll') }}" data-typing-url="{{ url('/shoutbox/typing') }}"
     data-stop-url="{{ url('/shoutbox/typing-stop') }}" data-typing-users-url="{{ url('/shoutbox/typing-users') }}">

    <div class="shoutbox-card glass shadow-lg">

<div class="shoutbox-wrap">

    {{-- Header --}}

    <div class="shoutbox-header glass d-flex align-items-center gap-3 px-4 py-3 mb-3">

        <div class="shoutbox-icon">

            <i class="bi bi-chat-dots-fill"></i>

        </div>

        <div>

            <h5 class="mb-0 fw-bold">Community Chat - ENGLISH ONLY!!!</h5>

            <small class="text-muted">Live chat · Be respectful · Have fun</small>

        </div>

    </div>

    @include('partials.onlineusers')

    @if(!auth()->user()->chatblock)
    <div class="shoutbox-tools mb-3">
        <label class="flex-grow-1"> <span class="visually-hidden">Search loaded messages and members</span>
            <input id="chat-search" type="search" class="form-control form-control-sm" placeholder="Search loaded messages or members…" maxlength="100">
        </label>
        <select id="chat-filter" class="form-select form-select-sm" aria-label="Filter loaded chat">
            <option value="all">All messages</option>
            <option value="mine">My conversations</option>
            <option value="pinned">Pinned messages</option>
        </select>
        <button id="chat-expand" type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false">Expand</button>
        <span id="chat-connection" class="small text-muted" role="status">Connecting…</span>
    </div>
    <p id="chat-no-results" class="small text-muted" hidden>No loaded messages match this filter.</p>
    @endif
    <div id="chat-feedback" class="small mb-2" role="status" aria-live="polite">{{ !empty($shoutUnavailable) ? 'This shout is no longer available.' : '' }}</div>
    {{-- Messages --}}

    <div id="shoutbox-pinned">
        @include('partials.shoutbox-pinned')
    </div>
    <div class="shoutbox-container-wrap">

    <div class="shoutbox-container glass p-3 mb-3" id="shoutbox-container">

       <div class="chat-history-controls">
           <button id="chat-load-older" type="button"><i class="bi bi-clock-history" aria-hidden="true"></i> Load 10 older messages</button>
           <span id="chat-history-status" class="small text-muted" role="status" aria-live="polite"></span>
       </div>
       <div id="shoutbox-messages">

@include('partials.shoutbox-messages', ['messages' => $messages->where('sticky', false)])

        </div>

            <button id="shoutbox-jump-bottom" class="shoutbox-jump-bottom" data-bs-toggle="tooltip"  title="Show latest messages" aria-label="Show latest messages" style="display:none;">
            <i class="bi bi-chevron-double-down"></i>
            <span id="shoutbox-jump-count" class="jump-count"></span>
        </button>

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

>

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

        <div class="bbcode-buttons" role="group" aria-label="Format chat text">
            <button type="button" data-chat-format="b" title="Bold" aria-label="Bold"><i class="bi bi-type-bold" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="i" title="Italic" aria-label="Italic"><i class="bi bi-type-italic" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="u" title="Underline" aria-label="Underline"><i class="bi bi-type-underline" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="s" title="Strikethrough" aria-label="Strikethrough"><i class="bi bi-type-strikethrough" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="quote" title="Quote" aria-label="Quote"><i class="bi bi-quote" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="code" title="Code" aria-label="Code"><i class="bi bi-code-slash" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="spoiler" title="Spoiler" aria-label="Spoiler"><i class="bi bi-eye-slash" aria-hidden="true"></i></button>
            <button type="button" data-chat-format="list" title="List" aria-label="List"><i class="bi bi-list-ul" aria-hidden="true"></i></button>
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

    @include('partials.chat-formatting-tools')

    <div class="chat-compose-field">
    <textarea id="content"

              name="content" placeholder="Write a message…"

              rows="1"

              aria-label="Your chat message" aria-describedby="chat-compose-hint" maxlength="1000"

              required></textarea>
        <button type="submit" id="chat-send" class="btn btn-success" aria-label="Send message" title="Send message" hidden disabled>
            <i class="bi bi-send-fill" aria-hidden="true"></i>
        </button>
    </div>

    <span class="chat-hint visually-hidden" aria-hidden="true">Say something nice… 👋</span>

    @if(auth()->user()->user_class >= \App\Models\UserClass::ADMIN)
    <div class="form-check mt-2 chat-sticky-control">
        <input class="form-check-input" type="checkbox" name="sticky" id="sticky-checkbox">
        <label class="form-check-label theme-text" for="sticky-checkbox">
            <i class="bi bi-pin-fill"></i> Sticky
        </label>
    </div>
    @endif


</div>





        <div class="chat-compose-actions">
            <small id="chat-compose-hint" class="text-muted">Enter to send · Shift+Enter for a new line · Tag with @username or @&quot;Display Name&quot;</small>
            <span id="chat-draft-status" class="small text-muted" role="status"></span>
<div id="char-counter" class="char-counter">

        <span id="char-count">0</span> / <span id="char-max">1000</span>

    </div>
        </div>
    </form>

    @endif





</div>

 </div>

</div>


{{-- Shared Inline Script (works on both home and shoutbox pages) --}}
<script>
/* =========================================================
   Shoutbox: Placeholder, Counter, BBCode, Emoji,
   Relative Timestamps, Jump-to-Bottom
   ========================================================= */

/* --- Placeholder Rotation --- */
(() => {
    const textarea = document.getElementById('content');
    const hint = document.querySelector('.chat-hint');
    if (!textarea || !hint) return;

    const messages = [
        "Say something nice… \ud83d\udc4b",
        "What's on your mind? \ud83d\udcad",
        "Drop a message in the chat!",
        "Type something friendly \ud83d\ude0a",
        "Join the conversation!",
        "Share your thoughts \ud83d\udca1",
        "Let's chat! \ud83d\ude80"
    ];

    let lastIndex = -1;
    let index;
    do { index = Math.floor(Math.random() * messages.length); } while (index === lastIndex);
    lastIndex = index;
    textarea.placeholder = messages[index];

    textarea.addEventListener('focus', () => hint.classList.add('active'));
    textarea.addEventListener('blur', () => { if (!textarea.value.trim()) hint.classList.remove('active'); });
    textarea.addEventListener('input', () => {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });
})();

/* --- Character Counter --- */
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
        counter.classList.add('visible');
        counter.classList.toggle('warn', len >= WARN_AT);
        counter.classList.toggle('danger', len >= MAX);
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    });

    textarea.addEventListener('blur', () => {
        if (!textarea.value.length) counter.classList.remove('visible');
    });
})();

/* --- BBCode Wrap --- */
function wrapText(before, after) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.substring(start, end);

    if (selected.length > 0) {
        textarea.value = textarea.value.substring(0, start) + before + selected + after + textarea.value.substring(end);
        textarea.selectionStart = start + before.length;
        textarea.selectionEnd = end + before.length;
    } else {
        const insert = before + after;
        textarea.value = textarea.value.substring(0, start) + insert + textarea.value.substring(start);
        const cursorPos = start + before.length;
        textarea.selectionStart = cursorPos;
        textarea.selectionEnd = cursorPos;
    }
    textarea.focus();
    textarea.scrollTop = textarea.scrollHeight;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

/* --- Emoji Insert --- */
function addEmoji(emoji) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    textarea.value = textarea.value.substring(0, start) + emoji + textarea.value.substring(end);
    const cursor = start + emoji.length;
    textarea.selectionStart = cursor;
    textarea.selectionEnd = cursor;
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

</script>




{{-- Styles --}}




@push('scripts')
<script src="{{ asset('js/chat-media.js') }}?v={{ filemtime(public_path('js/chat-media.js')) }}" defer></script>
<script src="{{ asset('js/shoutbox.js') }}?v={{ filemtime(public_path('js/shoutbox.js')) }}" defer></script>
@endpush
