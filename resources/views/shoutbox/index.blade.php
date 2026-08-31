@extends('layouts.app')

@section('content')

@php
$rules = [
    'RO' => [
        'Folositi un limbaj prietenos față de colegi',
        'Dacă aveți frustrări referitoare la ceea ce se discută pe chat, vă rugăm să comentați într-un mod cât mai amiabil, sau mai bine, nu comentați!',
        'Nu faceți request-uri pe chat. Aveți secțiunea Request. Veți primi warn timp de o săptămână!',
        'Nu folosiți mesaje repetitive sau spam',
        'Discuțiile politice și religioase sunt strict interzise',
        'Respectați deciziile moderatorilor',
    ],
    'EN' => [
        'Use friendly language with other members',
        'If you have frustrations about the chat discussion, please comment in a friendly manner, or better, don\'t!',
        'Do not make requests in the chat. Use the Request section. You will receive a 1-week warning!',
        'Do not use repetitive messages or spam',
        'Political and religious discussions are strictly prohibited',
        'Respect the moderators\' decisions',
    ],
];
@endphp

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

                @foreach($messages as $message)
                @php
                    $classColor = \App\Models\UserClass::getClassColor($message->user->user_class);
                    $isOwn = auth()->id() === $message->user_id;
                @endphp

                <div class="message {{ $isOwn ? 'own' : '' }}">
                    <img class="avatar"
                         src="{{ $message->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

                    <div class="bubble" style="--accent: {{ $classColor }}">
                        <div class="header">
                            <a href="{{ route('profile.show', $message->user->id) }}"
                               class="username"
                               style="color: {{ $classColor }}">
                                {{ $message->user->name }}
                            </a>

                            <span class="badge time-badge">
                                <i class="bi bi-clock me-1"></i>
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>

                        <div class="content">
                            {!! convertCustomTagsToHtml($message->message) !!}
                        </div>

                        <div class="actions">
                            <button type="button"
                                    class="btn-icon"
                                    onclick="toggleReplyForm({{ $message->id }})">
                                <i class="bi bi-reply"></i>
                            </button>

                            @if($isOwn || Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                <a href="{{ route('shoutbox.edit', $message->id) }}"
                                   class="btn-icon warn">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('shoutbox.destroy', $message->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-icon danger"
                                            onclick="return confirm('Delete this shout?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- Reply form --}}
                        <form id="reply-form-{{ $message->id }}"
                              action="{{ route('shoutbox.reply', $message->id) }}"
                              method="POST"
                              class="reply-form"
                              style="display:none">
                            @csrf
                            <textarea name="content"
                                      placeholder="Reply..."
                                      required></textarea>
                            <button class="btn btn-primary btn-sm mt-1">Send</button>
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
                                    <div class="reply-header d-flex justify-content-between align-items-center">
                                        <strong class="reply-username"
                                                style="color: {{ $replyColor }}">
                                            {{ $reply->user->name }}
                                        </strong>

                                        <div class="reply-meta d-flex align-items-center gap-2">
                                            <span class="badge time-badge time-badge-sm">
                                                {{ $reply->created_at->format('H:i') }}
                                            </span>

                                            @if(auth()->id() === $reply->user_id ||
                                                Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                                                <a href="{{ route('shoutbox.edit', $reply->id) }}"
                                                   class="btn-icon warn">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            @if(Auth::user()->user_class >= \App\Models\UserClass::MODERATOR)
                                                <form action="{{ route('shoutbox.destroy', $reply->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-icon danger"
                                                            onclick="return confirm('Delete this reply?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="reply-content">
                                        {!! convertCustomTagsToHtml($reply->message) !!}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>
                </div>
                @endforeach
            </div>

            {{-- Emoji --}}
            @include('shoutbox.partials.emoji')

            {{-- Input --}}
            <form id="shoutbox-form"
                  action="{{ route('shoutbox.store') }}"
                  method="POST"
                  class="chat-input glass">
                @csrf
                <textarea id="content"
                          name="content"
                          placeholder="Say something nice… 👋"
                          required></textarea>
            </form>

            {{-- Rules --}}
            <div class="chat-rules-float">
                <button class="rules-fab"
                        data-bs-toggle="collapse"
                        data-bs-target="#chatRules">
                    <i class="bi bi-shield-check"></i>
                    <span class="rules-label">Rules</span>
                </button>

                <div id="chatRules" class="collapse rules-panel glass">
                    <div class="p-3 row g-3">
                        @foreach($rules as $lang => $list)
                        <div class="col-md-6">
                            <div class="rules-card">
                                <h6 class="fw-bold text-center mb-2">{{ $lang }} Rules</h6>
                                <ul>
                                    @foreach($list as $rule)
                                        <li>{{ $rule }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- Styles --}}
<style>
body {
    background: radial-gradient(circle at top, #2a2a2a, #121212);
    color: #eaeaea;
}

.glass {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.08);
}

.rules-toggle {
    width: 100%;
    background: none;
    border: none;
    padding: 14px;
    color: #fff;
    display: flex;
    align-items: center;
    font-weight: 600;
}

.rules-card {
    background: rgba(0,0,0,.4);
    border-radius: 12px;
    padding: 14px;
}

.rules-card ul {
    padding-left: 18px;
    font-size: .9rem;
}

.rules-warning {
    font-size: .8rem;
    color: #ffc107;
    margin-top: 10px;
}

.shoutbox-container {
    max-height: 650px;
    overflow-y: auto;
}

.message {
    display: flex;
    gap: 12px;
    margin-bottom: 14px;
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
    margin-top: 6px;
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
.btn-icon.warn:hover { color: #ffc107; }
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
    font-size: .9rem;
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
    font-size: .95rem;
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


.chat-rules-float {
    position: fixed;
    bottom: 110px; /* above chat input */
    right: 90px;
    z-index: 1050;
}

/* Floating action button */
.rules-fab {
    display: flex;
    align-items: center;
    gap: 6px;
    background:#b6a46e;
    color: #111;
    border: none;
    border-radius: 999px;
    padding: 10px 14px;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(0,0,0,.35);
    cursor: pointer;
}

.rules-fab:hover {
    background: #a39568;
}

.rules-fab i {
    font-size: 1.2rem;
}

.rules-label {
    font-size: .9rem;
}

/* Floating panel */
.rules-panel {
    position: absolute;
    bottom: 55px;
    right: 0;
    width: 720px;
    max-width: 90vw;
    max-height: 70vh;
    overflow-y: auto;
}

.rules-panel.collapse.show {
    animation: slideUp .25s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* === RESPONSIVE RULES FLOAT (MOBILE FIRST) === */

@media (max-width: 768px) {

    /* Move FAB to safer mobile spot */
    .chat-rules-float {
        right: 16px;
        bottom: 90px;
    }

    /* Make button more compact */
    .rules-fab {
        padding: 10px 12px;
        font-size: .85rem;
    }

    .rules-label {
        display: none; /* icon-only on mobile */
    }

    /* Bottom-sheet style panel */
    .rules-panel {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        max-width: 100%;
        max-height: 75vh;
        border-radius: 18px 18px 0 0;
        padding-bottom: env(safe-area-inset-bottom);
        box-shadow: 0 -20px 60px rgba(0,0,0,.6);
    }

    /* Smooth slide-up animation */
    .rules-panel.collapse.show {
        animation: slideUpMobile .25s ease;
    }

    @keyframes slideUpMobile {
        from {
            opacity: 0;
            transform: translateY(100%);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stack rules vertically */
    .rules-panel .row {
        flex-direction: column;
    }

    .rules-panel .col-md-6 {
        width: 100%;
    }

    /* Improve readability */
    .rules-card {
        font-size: .9rem;
    }
}


/* === SHOUTBOX WRAP === */

.shoutbox-wrap {
    position: relative;
}

.shoutbox-header {
    border-radius: 18px;
    border-left: 4px solid #ffc107;
}

.shoutbox-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #ffc107, #ffda6a);
    color: #111;
    font-size: 1.3rem;
    box-shadow: 0 6px 20px rgba(255,193,7,.35);
}

.shoutbox-body {
    border-radius: 20px;
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
    border: 1px solid #ffc107;
    box-shadow: 0 0 0 2px rgba(255,193,7,.15);
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
    max-width: auto;
    padding: 20px;
    border-radius: 22px;
    position: relative;
}

/* subtle glowing edge */
.shoutbox-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 22px;
    padding: 1px;
    background: linear-gradient(
        135deg,
        rgba(115, 114, 112, 0.4),
        rgba(24, 28, 112, 0.05),
        rgba(31, 29, 25, 0.113)
    );
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    pointer-events: none;
}

/* header separation */
.shoutbox-header {
    background: rgba(40, 40, 40, 0.168);
    border-radius: 18px;
}

/* tighten inner spacing */
.shoutbox-container {
    border-radius: 18px;
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
    background: rgba(255,193,7,.6);
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



</style>


{{-- Reply toggle --}}
<script>
function toggleReplyForm(id) {
    const el = document.getElementById('reply-form-' + id);
    if (el) el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('shoutbox-form');
    const textarea = document.getElementById('content');

    if (!form || !textarea) return;

    textarea.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.submit(); // classic submit → controller redirect
        }
    });
});
</script>


@endsection
