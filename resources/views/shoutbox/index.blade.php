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

<!-- Chat Rules Dropdown -->
<div class="dropdown mb-3 mt-1">
    <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="chatRulesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Chat Rules
    </button>
    <div class="dropdown-menu dropdown-menu-dark p-3" style="width: 100%; max-height: 500px; overflow-y: auto;">
        <div class="row">
            @foreach($rules as $lang => $list)
                <div class="col-md-6 mb-3">
                    <div class="card bg-dark text-white h-100 shadow-sm border-secondary">
                        <div class="card-header bg-secondary text-center">
                            <h5 class="mb-0">{{ $lang }} Rules</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                @foreach($list as $index => $rule)
                                    <li class="list-group-item bg-transparent text-white border-secondary">
                                        {{ $index + 1 }}. {{ $rule }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer bg-transparent border-secondary">
                            <div class="alert alert-warning mb-0 text-center">
                                <strong>{{ $lang === 'RO' ? 'Atenție:' : 'Warning:' }}</strong>
                                {{ $lang === 'RO' ? 'Nerespectarea acestor reguli poate duce la avertizări sau suspendarea accesului la chat.' : 'Breaking these rules may result in warnings or suspension of chat access.' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Shoutbox -->
<div class="shoutbox-container mt-3">
    @foreach($messages as $message)
        @php
            $classColor = \App\Models\UserClass::getClassColor($message->user->user_class);
        @endphp
        <div class="shoutbox-message d-flex align-items-start mb-3">
            <div class="avatar-container me-3">
                <img src="{{ $message->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="Avatar">
            </div>
            <div class="message-bubble w-100" style="border-left: 4px solid {{ $classColor }}">
                <div class="d-flex justify-content-between align-items-start">
                    <!-- Username -->
                    <span class="username">
                        <a href="{{ route('profile.show', $message->user->id) }}" style="color: {{ $classColor }}">
                            {{ $message->user->name }}
                            @if($message->user->warned)
                                <i class="bi bi-exclamation-triangle-fill text-danger" title="Warned"></i>
                            @endif
                            @if($message->user->donor === 'yes')
                                <i class="bi bi-star-fill text-success" title="Donor"></i>
                            @endif
                        </a>
                    </span>

                    <!-- Timestamp + Actions stacked right -->
                    <div class="d-flex flex-column align-items-end">
                        <span class="timestamp badge bg-secondary ms-1">
                            <b>{{ $message->created_at->format('Y-m-d H:i') }}</b>
                        </span>
                        <div class="message-actions mt-1">
                            @if(auth()->id() === $message->user_id || Auth::user()->user_class >= \App\Models\UserClass::ADMIN)
                                <a href="{{ route('shoutbox.edit', $message->id) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                <form action="{{ route('shoutbox.destroy', $message->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this shout?')">Delete</button>
                                </form>
                            @endif
                            <button class="btn btn-sm btn-outline-primary" onclick="toggleReplyForm({{ $message->id }})">Reply</button>
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div class="message-content mt-2">
                    <b>{!! convertCustomTagsToHtml($message->message) !!}</b>
                </div>

                <!-- Reply Form -->
                <form id="reply-form-{{ $message->id }}" action="{{ route('shoutbox.reply', $message->id) }}" method="POST" class="mt-2" style="display:none;">
                    @csrf
                    <textarea name="content" class="form-control mb-2" placeholder="Type your reply..." required></textarea>
                    <button type="submit" class="btn btn-sm btn-primary">Post Reply</button>
                </form>

                <!-- Replies -->
                @if($message->replies && $message->replies->count() > 0)
                    <div class="replies mt-3 ps-3">
                        @foreach($message->replies->sortByDesc('created_at') as $reply)
                            @php
                                $replyColor = \App\Models\UserClass::getClassColor($reply->user->user_class);
                            @endphp
                            <div class="d-flex align-items-start mb-2">
                                <div class="avatar-container me-2">
                                    <img src="{{ $reply->user->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}" alt="Avatar">
                                </div>
                                <div class="message-bubble w-100 small" style="border-left: 4px solid {{ $replyColor }}">
                                    <div class="d-flex justify-content-between">
                                        <span class="username">
                                            <a href="{{ route('profile.show', $reply->user->id) }}" style="color: {{ $replyColor }}">
                                                {{ $reply->user->name }}
                                                @if($reply->user->warned)
                                                    <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                                @endif
                                                @if($reply->user->donor === 'yes')
                                                    <i class="bi bi-star-fill text-success"></i>
                                                @endif
                                            </a>
                                        </span>
                                        <span class="timestamp">{{ $reply->created_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <div class="message-content mt-1"><b>{{ $reply->message }}</b></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

@include('shoutbox.partials.emoji')

<form id="shoutbox-form" action="{{ route('shoutbox.store') }}" method="POST" class="mt-3 mb-4">
    @csrf
    <textarea name="content" id="content" class="form-control" placeholder="Type your message here..." required onkeydown="submitOnEnter(event)"></textarea>
</form>

<style>
body {
    background: linear-gradient(135deg, #1f1f1f, #535252);
    color: #fff;
    font-family: 'Segoe UI', sans-serif;
}
.card { border-radius: 12px; }
.shoutbox-container { max-height: 750px; overflow-y: auto; }

.message-bubble, .replies .message-bubble {
    background: rgba(0,0,0,0.6);
    border-radius: 10px;
    padding: 10px 12px;
    border-left: 4px solid;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
    transition: transform 0.2s, box-shadow 0.2s;
}

.message-bubble:hover, .replies .message-bubble:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
}

.message-content { font-weight: 500; }
.username a { font-weight: bold; text-decoration: none; }
.avatar-container img { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #444; }
.timestamp { font-size: 0.8rem; color: #aaa; }
.message-actions { display: flex; gap: 5px; margin-top: 5px; }
.replies { border-left: 2px solid #555; }
</style>

<script>
    function insertBBCode(tag) {
        var textarea = document.getElementById('content');
        var selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
        var cursorPos;
        if (selectedText.length > 0) {
            cursorPos = textarea.selectionEnd + tag.length;
        } else {
            cursorPos = textarea.selectionStart + tag.length + 2;
        }
        var currentContent = textarea.value;
        var newContent = currentContent.substring(0, textarea.selectionStart) +
            '[' + tag + ']' + selectedText + '[/' + tag + ']' +
            currentContent.substring(textarea.selectionEnd);
        textarea.value = newContent;
        textarea.setSelectionRange(cursorPos, cursorPos);
        textarea.focus();
    }

    function insertEmoji(emojiCode) {
        var textarea = document.getElementById('content');
        var cursorPos;
        fetch(`/get-emoji/${emojiCode}`)
            .then(response => response.json())
            .then(data => {
                var emoji = data.emoji;
                var selectedText = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
                if (selectedText.length > 0) {
                    cursorPos = textarea.selectionEnd + emoji.length;
                    textarea.value = textarea.value.substring(0, textarea.selectionStart) +
                        emoji + selectedText + emoji +
                        textarea.value.substring(textarea.selectionEnd);
                } else {
                    cursorPos = textarea.selectionStart + emoji.length;
                    textarea.value = textarea.value.substring(0, textarea.selectionStart) +
                        emoji + textarea.value.substring(textarea.selectionEnd);
                }
                textarea.setSelectionRange(cursorPos, cursorPos);
                textarea.focus();
            })
            .catch(error => console.error('Error fetching emoji:', error));
    }

    function submitOnEnter(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            document.getElementById('shoutbox-form').submit();
        }
    }

    document.getElementById('shoutbox-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            // handle success
        })
        .catch(error => console.error('Error:', error));
    });

    function toggleReplyForm(id) {
        var form = document.getElementById('reply-form-' + id);
        form.style.display = (form.style.display === 'none') ? 'block' : 'none';
    }
</script>

@endsection
