<div class="shoutbox-shell my-4" id="community-chat" data-user="{{ auth()->id() }}"
     data-poll-url="{{ url('/shoutbox/poll') }}" data-typing-url="{{ url('/shoutbox/typing') }}"
     data-stop-url="{{ url('/shoutbox/typing-stop') }}" data-typing-users-url="{{ url('/shoutbox/typing-users') }}">

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
    <div id="chat-feedback" class="small mb-2" role="status" aria-live="polite"></div>
    {{-- Messages --}}

    <div id="shoutbox-pinned">
        @include('partials.shoutbox-pinned')
    </div>
    <div class="shoutbox-container-wrap">

    <div class="shoutbox-container glass p-3 mb-3" id="shoutbox-container">

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
        <label class="form-check-label text-light" for="sticky-checkbox">
            <i class="bi bi-pin-fill"></i> Sticky
        </label>
    </div>
    @endif


</div>





        <div class="chat-compose-actions">
            <small id="chat-compose-hint" class="text-muted">Enter to send · Shift+Enter for a new line</small>
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

    bottom: 3px;

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

/* === JUMP-TO-BOTTOM BUTTON === */
.shoutbox-container-wrap {
    position: relative;
}

.shoutbox-jump-bottom {
    position: absolute;
    bottom: 16px;
    right: 16px;
    z-index: 10;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--ui-border);
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.92));
    color: var(--ui-accent);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,.35);
    transition: opacity .2s, transform .2s;
}

.shoutbox-jump-bottom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 22px rgba(0,0,0,.45);
}

.jump-count {
    display: none;
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    background: var(--ui-accent);
    color: #000;
    font-size: 11px;
    font-weight: 700;
    line-height: 20px;
    text-align: center;
    padding: 0 5px;
}

.shoutbox-jump-bottom.has-count .jump-count {
    display: block;
}

/* === EMPTY STATE === */
.shoutbox-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    gap: 10px;
    color: rgba(255,255,255,.5);
}

/* === MESSAGE GROUPING === */
.message.grouped {
    margin-top: -6px;
}

.message.grouped .avatar {
    visibility: hidden;
}

.message.grouped .header .username,
.message.grouped .header .actions-inline {
    display: none;
}

.message.grouped .bubble {
    padding-top: 4px;
}

.message.grouped .time-badge {
    opacity: 0;
    transition: opacity .15s;
}

.message:hover .time-badge,
.message.grouped:hover .time-badge {
    opacity: 1;
}

/* === RELATIVE TIMESTAMP === */
.ts {
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
}
#shoutbox-pinned:empty { display:none; }
#shoutbox-pinned .shoutbox-pinned-panel { max-height:240px; max-height:min(30vh, 240px); overflow-y:auto; margin-bottom:12px; padding:12px; border:1px solid var(--ui-accent); border-radius:.8rem; background:rgba(15,23,42,.95); }
#shoutbox-pinned .message:last-child { margin-bottom:0; }
#shoutbox-container { overflow-anchor:none; }
/* Additional controls follow the existing chat palette. */
.shoutbox-tools, .chat-compose-actions { display:flex; align-items:center; flex-wrap:wrap; gap:.65rem; }
.shoutbox-tools select { width:auto; }
.chat-compose-actions { padding:.75rem; }
#community-chat.chat-expanded .shoutbox-container { height:75vh; max-height:75vh; }
#community-chat .message[hidden] { display:none !important; }
#community-chat .message:focus-within .actions-inline,
#community-chat .message.grouped .actions-inline { display:flex; opacity:1; }
#community-chat .message.grouped .header .username { display:inline-flex; }
#community-chat .message.grouped .time-badge { opacity:1; }
@media (hover:none) { #community-chat .actions-inline { opacity:1; } }
@media (max-width:576px) { .shoutbox-tools label { flex-basis:100%; } }
/* Conversation bubbles: incoming on the left, your messages on the right. */
#shoutbox-messages > .message {
    width: fit-content;
    max-width: 82%;
    margin-right: auto;
    align-items: flex-start;
}
#shoutbox-messages > .message.own {
    flex-direction: row-reverse;
    margin-left: auto;
    margin-right: 0;
}
#shoutbox-messages > .message::before,
#shoutbox-messages > .message > .bubble::after {
    display: none;
}
#shoutbox-messages > .message > .bubble {
    flex: 1 1 auto;
    min-width: 0;
    width: auto;
    border-radius: 4px 16px 16px 16px !important;
    overflow-wrap: anywhere;
}
#shoutbox-messages > .message.own > .bubble {
    background: linear-gradient(135deg, #164c48, #123b38) !important;
    border-radius: 16px 4px 16px 16px !important;
}
#shoutbox-messages .header,
#shoutbox-messages .header > .d-flex,
#shoutbox-messages .reply-header,
#shoutbox-messages .reply-meta {
    flex-wrap: wrap;
    gap: .4rem;
    min-width: 0;
}
#shoutbox-messages .content img,
#shoutbox-messages .reply-content img {
    max-width: 100%;
    height: auto;
}
#shoutbox-messages .content pre,
#shoutbox-messages .reply-content pre {
    max-width: 100%;
    overflow-x: auto;
}
@media (max-width: 576px) {
    #shoutbox-messages > .message { max-width: 94%; gap: 6px; }
    #shoutbox-messages > .message > .avatar { width: 28px; height: 28px; }
    #shoutbox-messages > .message > .bubble { padding: 10px; }
}
/* Keep the composer quiet until the member starts writing. */
#shoutbox-form { padding: 10px; }
#shoutbox-form .chat-toolbar,
#shoutbox-form .chat-compose-actions,
#shoutbox-form .chat-sticky-control { display: none; }
#shoutbox-form:focus-within .chat-toolbar { display: flex; }
#shoutbox-form:focus-within .chat-compose-actions { display: flex; }
#shoutbox-form:focus-within .chat-sticky-control { display: block; }
#shoutbox-form .chat-toolbar { flex-wrap: nowrap; gap: 8px; margin-bottom: 8px; }
#shoutbox-form .bbcode-buttons { display: flex; flex-shrink: 0; gap: 3px; }
#shoutbox-form .bbcode-buttons button { margin: 0; }
#shoutbox-form .emoji-bar { min-width: 0; margin: 0; padding: 2px; gap: 3px; }
#shoutbox-form .emoji-btn { flex-shrink: 0; }
#shoutbox-form .bbcode-buttons button,
#shoutbox-form .emoji-btn { padding: 4px 6px; font-size: 1rem; }
#shoutbox-form .chat-compose-field { position: relative; }
#shoutbox-form #content { display: block; min-height: 44px; max-height: 180px; padding: 10px 12px; overflow-y: auto; }
#shoutbox-form.has-draft #content { padding-right: 58px; }
#shoutbox-form #chat-send {
    position: absolute; right: 6px; bottom: 5px;
    width: 34px; height: 34px; padding: 0; border-radius: 50%;
    display: grid; place-items: center;
}
#shoutbox-form #chat-send[hidden] { display: none !important; }
#shoutbox-form .chat-compose-actions { padding: 6px 2px 0; gap: 6px 12px; }
#shoutbox-form .char-counter { position: static; margin-left: auto; transform: none; }
#shoutbox-form #chat-draft-status:empty { display: none; }
/* Replies form a chronological thread inside each conversation bubble. */
#community-chat .reply-timeline-heading {
    display: flex; align-items: center; gap: 6px;
    padding-top: 10px; margin-bottom: 12px;
    border-top: 1px solid rgba(255,255,255,.1);
    color: #aeb8c4; font-size: 12px; font-weight: 600;
}
#community-chat .reply-timeline {
    gap: 14px;
    margin-left: 5px;
    padding-left: 18px;
    border-left: 2px solid rgba(148,163,184,.3) !important;
}
#community-chat .reply-timeline > .reply-card {
    position: relative;
    display: flex; flex-direction: row; align-items: flex-start;
    width: 100%; max-width: 100%; margin: 0; gap: 8px;
}
#community-chat .reply-timeline > .reply-card::before {
    content: ''; display: block; position: absolute;
    left: -24px; top: 10px; width: 10px; height: 10px;
    border: 2px solid var(--reply-accent, var(--ui-accent));
    border-radius: 50%; background: #142330 !important;
    box-shadow: 0 0 0 3px rgba(15,23,42,.65);
}
#community-chat .reply-timeline > .reply-card.own::before {
    background: var(--ui-accent) !important;
    border-color: var(--ui-accent);
}
#community-chat .reply-timeline .avatar-sm {
    width: 26px; height: 26px; object-fit: cover; margin-top: 2px;
}
#community-chat .reply-timeline .reply-bubble {
    flex: 1; min-width: 0; padding: 7px 10px;
    background: rgba(8,15,28,.3) !important;
    border: 1px solid rgba(255,255,255,.06) !important;
    border-radius: 6px !important; box-shadow: none !important;
    backdrop-filter: none; overflow-wrap: anywhere;
}
#community-chat .reply-timeline .reply-bubble:hover { transform: none; }
#community-chat .reply-timeline .reply-header,
#community-chat .reply-timeline .reply-meta { flex-wrap: wrap; gap: 4px 8px; }
#community-chat .reply-timeline .reply-content { margin-top: 5px; }
#community-chat .reply-you-label {
    font-size: 10px; font-weight: 600; color: var(--ui-accent);
    padding: 1px 5px; border-radius: 4px; background: rgba(45,212,191,.1);
}
@media (max-width: 576px) {
    #community-chat .reply-timeline { padding-left: 13px; }
    #community-chat .reply-timeline > .reply-card { gap: 5px; }
    #community-chat .reply-timeline > .reply-card::before { left: -19px; }
    #community-chat .reply-timeline .avatar-sm { width: 22px; height: 22px; }
    #community-chat .reply-timeline .reply-bubble { padding: 6px 8px; }
}
</style>


@push('scripts')
<script src="{{ asset('js/shoutbox.js') }}?v=3" defer></script>
@endpush
