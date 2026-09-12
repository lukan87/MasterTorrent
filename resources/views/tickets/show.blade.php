@extends('layouts.app')

@section('content')

<div class="container-fluid py-3 ticket-show-page">

    <div class="row g-3">

        {{-- TICKET INFO --}}
        <div class="col-lg-4">

            <div class="ticket-panel">
                <div class="ticket-panel-header">
                    <i class="bi bi-life-preserver"></i>
                    <span>Ticket Info</span>
                </div>

                <div class="ticket-panel-body">

                    <div class="ticket-info-item">
                        <strong>Status:</strong>
                        <span class="badge ticket-status-badge">{{ $ticket->status }}</span>
                    </div>

                    <div class="ticket-info-item">
                        <strong>Priority:</strong>
                        <span>{{ $ticket->priority }}</span>
                    </div>

                    <div class="ticket-info-item">
                        <strong>Category:</strong>
                        <span>{{ $ticket->category->name }}</span>
                    </div>

                    <div class="ticket-info-item">
                        <strong>Creator:</strong>
                        <span>{{ $ticket->user->name }}</span>
                    </div>

                    <div class="ticket-info-item">
                        <strong>Created:</strong>
                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>

                    @if($ticket->assignedStaff)
                        <div class="ticket-info-item">
                            <strong>Assigned Staff:</strong>
                            <span>{{ $ticket->assignedStaff->name }}</span>
                        </div>
                    @endif

                    @if($ticket->claimedBy)
                        <div class="ticket-info-item">
                            <strong>Claimed By:</strong>
                            <span>{{ $ticket->claimedBy->name }}</span>
                        </div>
                    @endif

                    @if(Auth::user()->user_class > 5)

                        <div class="ticket-section-divider"></div>

                        <h6 class="ticket-section-title">
                            <i class="bi bi-shield-check"></i>
                            Staff Controls
                        </h6>

                        <form method="POST" action="{{ route('tickets.claim',$ticket->id) }}">
                            @csrf
                            <button class="ticket-action ticket-action-warning w-100 mb-2">
                                <i class="bi bi-person-check me-1"></i>
                                Claim Ticket
                            </button>
                        </form>

                        <form method="POST" action="{{ route('tickets.status',$ticket->id) }}">
                            @csrf

                            <select name="status" class="form-select ticket-select mb-2">
                                <option value="Open" {{ $ticket->status=='Open'?'selected':'' }}>Open</option>
                                <option value="Waiting Staff" {{ $ticket->status=='Waiting Staff'?'selected':'' }}>Waiting Staff</option>
                                <option value="Waiting User" {{ $ticket->status=='Waiting User'?'selected':'' }}>Waiting User</option>
                                <option value="Resolved" {{ $ticket->status=='Resolved'?'selected':'' }}>Resolved</option>
                                <option value="Closed" {{ $ticket->status=='Closed'?'selected':'' }}>Closed</option>
                            </select>

                            <button class="ticket-action ticket-action-primary w-100">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Update Status
                            </button>
                        </form>

                        <form method="POST" action="{{ route('tickets.lock',$ticket->id) }}" class="mt-2">
                            @csrf
                            <button class="ticket-action ticket-action-danger w-100">
                                <i class="bi bi-lock me-1"></i>
                                Lock Ticket
                            </button>
                        </form>

                    @endif

                    <div class="ticket-section-divider"></div>

                    <h6 class="ticket-section-title">
                        <i class="bi bi-clock-history"></i>
                        Activity
                    </h6>

                    @foreach($ticket->events->sortByDesc('created_at')->take(10) as $event)
                        <div class="ticket-event">
                            <div>
                                <strong>
                                    @if($event->user)
                                        {{ $event->user->name }}
                                    @endif
                                </strong>
                                <span>{{ $event->event }}</span>
                            </div>

                            <small>{{ $event->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>

        {{-- CONVERSATION --}}
        <div class="col-lg-8">

            <div class="ticket-panel">
                <div class="ticket-panel-header">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Conversation</span>
                </div>

                <div class="ticket-panel-body">

                    <div id="new-reply-alert" class="alert ticket-new-reply d-none mb-3">
                        <div>
                            <i class="bi bi-bell me-1"></i>
                            <strong>New reply from staff</strong>
                        </div>

                        <button class="ticket-view-reply" onclick="scrollToReply()">
                            View
                        </button>
                    </div>

                    <div id="ticket-replies">

                        @foreach($ticket->responses as $response)

                            @if(!$response->is_staff_note || Auth::user()->user_class > 5)

                                <div class="ticket-response {{ $loop->first ? 'first-response' : '' }}">

                                    <div class="ticket-response-header">

                                        <div class="ticket-response-user">
                                            <strong>{{ $response->user->name }}</strong>

                                            @if($loop->first)
                                                <span class="badge ticket-author-badge">Author</span>
                                            @endif

                                            @if($response->is_staff_note)
                                                <span class="badge ticket-note-badge">Staff Note</span>
                                            @endif
                                        </div>

                                        <span class="ticket-response-time">
                                            {{ $response->created_at->diffForHumans() }}
                                        </span>

                                    </div>

                                    <div class="ticket-message">
                                        {{ $response->message }}
                                    </div>

                                    @if($response->attachments->count())
                                        <div class="ticket-attachments">
                                            @foreach($response->attachments as $file)
                                                <a href="{{ route('tickets.download',$file->id) }}"
                                                   class="ticket-attachment">
                                                    <i class="bi bi-paperclip"></i>
                                                    {{ $file->file_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>

                            @endif

                        @endforeach

                    </div>

                    @if(!$ticket->is_locked)

                        <div class="ticket-section-divider"></div>

                        <form method="POST"
                              action="{{ route('tickets.reply',$ticket->id) }}"
                              enctype="multipart/form-data">

                            @csrf

                            <textarea
                                name="message"
                                rows="4"
                                class="form-control ticket-textarea mb-3"
                                required></textarea>

                            <div id="typing-indicator" class="typing-indicator d-none"></div>

                            <input
                                type="file"
                                name="attachment[]"
                                multiple
                                class="form-control ticket-file-input mb-3"
                            >

                            @if(Auth::user()->user_class > 5)
                                <div class="form-check ticket-note-check mb-3">
                                    <input
                                        type="checkbox"
                                        name="staff_note"
                                        class="form-check-input"
                                        id="staffNote"
                                    >
                                    <label class="form-check-label" for="staffNote">
                                        Internal Staff Note
                                    </label>
                                </div>
                            @endif

                            <button class="ticket-action ticket-action-primary">
                                <i class="bi bi-reply-fill me-1"></i>
                                Reply
                            </button>

                        </form>

                    @else

                        <div class="ticket-section-divider"></div>

                        <div class="ticket-locked-alert">
                            <i class="bi bi-lock-fill"></i>
                            <span>This ticket is locked.</span>
                        </div>

                        <form method="POST" action="{{ route('tickets.lock',$ticket->id) }}">
                            @csrf

                            <button class="ticket-action ticket-action-success ticket-unlock-btn"
                                @if(Auth::id() == $ticket->user_id)
                                    data-bs-toggle="tooltip"
                                    title="Only unlock if you have the same issue! If unlocking and spamming, you will get a warning!"
                                @endif
                            >
                                <i class="bi bi-unlock me-1"></i>
                                Unlock Ticket
                            </button>
                        </form>

                    @endif

                </div>
            </div>

        </div>

    </div>

</div>

<style>
/* =========================================
   FILEIPLAY SUPPORT TICKET
   Dark glass + teal forum style
========================================= */

.ticket-show-page {
    max-width: 1500px;
}

.ticket-panel {
    position: relative;
    overflow: hidden;
    height: 100%;
    background: linear-gradient(135deg, rgba(22,32,51,.95), rgba(15,23,42,.84));
    border: 1px solid var(--ui-border, rgba(148,163,184,.16));
    border-radius: .85rem;
    box-shadow: 0 10px 28px rgba(0,0,0,.22);
}

.ticket-panel::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--ui-accent, #22d3c5);
}

.ticket-panel-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-height: 44px;
    padding: .7rem .9rem;
    color: #f1f5f9;
    background: rgba(15,23,42,.4);
    border-bottom: 1px solid var(--ui-border, rgba(148,163,184,.16));
    font-size: 14px;
    font-weight: 700;
}

.ticket-panel-header i {
    color: var(--ui-accent, #22d3c5);
}

.ticket-panel-body {
    padding: .95rem;
    color: #cbd5e1;
    font-size: 13px;
}

.ticket-info-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: .75rem;
    padding: .42rem 0;
    border-bottom: 1px solid rgba(148,163,184,.07);
}

.ticket-info-item:last-of-type {
    border-bottom: 0;
}

.ticket-info-item strong {
    color: #94a3b8;
    font-weight: 600;
}

.ticket-info-item > span:not(.badge) {
    color: #e2e8f0;
    text-align: right;
}

.ticket-status-badge {
    color: #67e8f9;
    background: rgba(14,116,144,.22);
    border: 1px solid rgba(34,211,238,.2);
    border-radius: .4rem;
    font-size: 11px;
}

.ticket-section-divider {
    height: 1px;
    margin: 1rem 0;
    background: rgba(148,163,184,.13);
}

.ticket-section-title {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin: 0 0 .65rem;
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 700;
}

.ticket-section-title i {
    color: var(--ui-accent, #22d3c5);
}

.ticket-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: .4rem .7rem;
    border-radius: .5rem;
    font-size: 13px;
    font-weight: 600;
    transition: all .18s ease;
}

.ticket-action-primary {
    color: #061311;
    background: var(--ui-accent, #22d3c5);
    border: 1px solid var(--ui-accent, #22d3c5);
}

.ticket-action-primary:hover {
    color: #061311;
    filter: brightness(1.06);
    transform: translateY(-1px);
}

.ticket-action-warning {
    color: #fde68a;
    background: rgba(120,53,15,.6);
    border: 1px solid rgba(251,191,36,.22);
}

.ticket-action-warning:hover {
    color: #fff;
    background: rgba(146,64,14,.72);
}

.ticket-action-danger {
    color: #fecaca;
    background: rgba(127,29,29,.65);
    border: 1px solid rgba(248,113,113,.22);
}

.ticket-action-danger:hover {
    color: #fff;
    background: rgba(153,27,27,.78);
}

.ticket-action-success {
    color: #bbf7d0;
    background: rgba(20,83,45,.45);
    border: 1px solid rgba(74,222,128,.2);
}

.ticket-action-success:hover {
    color: #fff;
    background: rgba(22,101,52,.58);
}

.ticket-select {
    min-height: 36px;
    color: #e2e8f0 !important;
    background-color: rgba(15,23,42,.72) !important;
    border: 1px solid rgba(148,163,184,.2) !important;
    border-radius: .5rem !important;
    box-shadow: none !important;
    font-size: 13px;
}

.ticket-select:focus {
    border-color: var(--ui-accent,#22d3c5) !important;
    box-shadow: 0 0 0 2px rgba(34,211,197,.08) !important;
}

.ticket-select option {
    color: #e2e8f0;
    background: #0f172a;
}

.ticket-event {
    padding: .5rem .6rem;
    margin-bottom: .45rem;
    background: rgba(15,23,42,.42);
    border: 1px solid rgba(148,163,184,.08);
    border-radius: .5rem;
    line-height: 1.4;
}

.ticket-event strong {
    color: #e2e8f0;
    font-weight: 600;
}

.ticket-event span {
    color: #94a3b8;
    margin-left: .25rem;
}

.ticket-event small {
    display: block;
    margin-top: .2rem;
    color: #64748b;
    font-size: 11px;
}

/* NEW REPLY */
#new-reply-alert {
    position: sticky;
    top: 0;
    z-index: 5;
}

.ticket-new-reply {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .6rem;
    padding: .65rem .75rem;
    color: #a7f3d0;
    background: rgba(6,78,59,.25);
    border: 1px solid rgba(52,211,153,.2);
    border-radius: .55rem;
    font-size: 13px;
}

.ticket-view-reply {
    min-height: 30px;
    padding: .3rem .6rem;
    color: #061311;
    background: #a7f3d0;
    border: 0;
    border-radius: .45rem;
    font-size: 12px;
    font-weight: 600;
}

/* RESPONSES */
.ticket-response {
    padding: .9rem 0;
    border-top: 1px solid rgba(148,163,184,.13);
}

.ticket-response.first-response {
    padding-top: 0;
    border-top: 0;
}

.ticket-response-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .55rem;
}

.ticket-response-user {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-wrap: wrap;
}

.ticket-response-user strong {
    color: #e2e8f0;
    font-size: 13px;
    font-weight: 700;
}

.ticket-response-time {
    color: #64748b;
    font-size: 11px;
    white-space: nowrap;
}

.ticket-author-badge {
    color: #061311;
    background: var(--ui-accent,#22d3c5);
    font-size: 10px;
}

.ticket-note-badge {
    color: #fde68a;
    background: rgba(120,53,15,.65);
    border: 1px solid rgba(251,191,36,.2);
    font-size: 10px;
}

.ticket-message {
    color: #cbd5e1;
    font-size: 13px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
}

.ticket-attachments {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .65rem;
}

.ticket-attachment {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    max-width: 100%;
    padding: .35rem .55rem;
    color: #cbd5e1;
    background: rgba(15,23,42,.6);
    border: 1px solid rgba(148,163,184,.16);
    border-radius: .45rem;
    font-size: 12px;
    text-decoration: none;
}

.ticket-attachment:hover {
    color: var(--ui-accent,#22d3c5);
    border-color: rgba(34,211,197,.3);
}

/* REPLY FORM */
.ticket-textarea,
.ticket-file-input {
    color: #e2e8f0 !important;
    background-color: rgba(15,23,42,.72) !important;
    border: 1px solid rgba(148,163,184,.2) !important;
    border-radius: .55rem !important;
    box-shadow: none !important;
    font-size: 13px !important;
}

.ticket-textarea {
    min-height: 110px;
    resize: vertical;
}

.ticket-textarea::placeholder {
    color: #64748b;
}

.ticket-textarea:focus,
.ticket-file-input:focus {
    color: #f8fafc !important;
    background-color: rgba(15,23,42,.9) !important;
    border-color: var(--ui-accent,#22d3c5) !important;
    box-shadow: 0 0 0 2px rgba(34,211,197,.08) !important;
}

.ticket-file-input {
    min-height: 38px;
}

.ticket-note-check {
    color: #cbd5e1;
    font-size: 13px;
}

.ticket-note-check .form-check-input {
    background-color: rgba(15,23,42,.8);
    border-color: rgba(148,163,184,.3);
}

.ticket-note-check .form-check-input:checked {
    background-color: var(--ui-accent,#22d3c5);
    border-color: var(--ui-accent,#22d3c5);
}

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    color: #94a3b8;
    font-size: 13px;
    transition: opacity .25s ease;
}

.typing-name {
    color: #e2e8f0;
    font-weight: 600;
}

.typing-dots {
    display: inline-flex;
    gap: 3px;
    margin-left: 4px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(2) {
    animation-delay: .2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: .4s;
}

@keyframes typingBounce {
    0%,80%,100% { transform: scale(0); opacity: .4; }
    40% { transform: scale(1); opacity: 1; }
}

.ticket-locked-alert {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .75rem;
    padding: .7rem .8rem;
    color: #fde68a;
    background: rgba(120,53,15,.22);
    border: 1px solid rgba(251,191,36,.2);
    border-radius: .55rem;
    font-size: 13px;
}

.ticket-unlock-btn {
    width: auto;
}

/* MOBILE */
@media (max-width: 768px) {
    .ticket-show-page {
        padding-left: .5rem !important;
        padding-right: .5rem !important;
    }

    .ticket-panel-body {
        padding: .8rem;
    }

    .ticket-info-item {
        align-items: flex-start;
        flex-direction: column;
        gap: .15rem;
    }

    .ticket-info-item > span:not(.badge) {
        text-align: left;
    }

    .ticket-response-header {
        align-items: flex-start;
        flex-direction: column;
        gap: .25rem;
    }

    .ticket-new-reply {
        align-items: flex-start;
        flex-direction: column;
    }

    .ticket-view-reply {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function(){

    let lastReplyCount = {{ $ticket->responses->count() }};
    let ticketReplies = document.getElementById("ticket-replies");
    let alertBox = document.getElementById("new-reply-alert");

    function renderReply(reply){

        let attachments = '';

        if(reply.attachments.length){

            reply.attachments.forEach(file => {

                attachments += `
                    <a href="/tickets/download/${file.id}"
                       class="ticket-attachment me-1">
                        <i class="bi bi-paperclip"></i> ${file.file_name}
                    </a>
                `;

            });

        }

        let badge = reply.is_staff_note
            ? '<span class="badge ticket-note-badge">Staff Note</span>'
            : '';

        return `
            <div class="ticket-response new-reply">

                <div class="ticket-response-header">

                    <div class="ticket-response-user">
                        <strong>${reply.user.name}</strong>
                        ${badge}
                    </div>

                    <span class="ticket-response-time">
                        just now
                    </span>

                </div>

                <div class="ticket-message">
                    ${reply.message}
                </div>

                <div class="ticket-attachments">
                    ${attachments}
                </div>

            </div>
        `;
    }

    window.scrollToReply = function(){

        let el = document.querySelector('.new-reply:last-child');

        if(el){
            el.scrollIntoView({behavior:'smooth'});
        }

        alertBox.classList.add("d-none");
    };

    setInterval(function(){

        fetch("{{ route('tickets.fetchReplies',$ticket->id) }}")
            .then(res => res.json())
            .then(data => {

                if(data.length > lastReplyCount){

                    let newReplies = data.slice(lastReplyCount);

                    newReplies.forEach(reply => {

                        ticketReplies.insertAdjacentHTML(
                            'beforeend',
                            renderReply(reply)
                        );

                    });

                    lastReplyCount = data.length;
                    alertBox.classList.remove("d-none");
                }

            });

    },5000);

    /* --------------------------
       STAFF TYPING
    -------------------------- */

    let textarea = document.querySelector('textarea[name="message"]');

    if(textarea){

        let typingTimer;

        textarea.addEventListener("input", function(){

            clearTimeout(typingTimer);

            fetch("/tickets/{{ $ticket->id }}/typing",{
                method:"POST",
                headers:{
                    'X-CSRF-TOKEN':'{{ csrf_token() }}',
                    'Content-Type':'application/json'
                }
            });

            typingTimer = setTimeout(()=>{},2000);
        });

        setInterval(function(){

            fetch("/tickets/{{ $ticket->id }}/typing-status")
                .then(res=>res.json())
                .then(data=>{

                    let indicator = document.getElementById("typing-indicator");

                    if(!indicator) return;

                    if(data.typing){

                        if(data.user_id != {{ auth()->id() }}){

                            indicator.innerHTML =
                                `<span class="typing-name">${data.name}</span> is typing
                                <span class="typing-dots">
                                    <span></span><span></span><span></span>
                                </span>`;

                            indicator.classList.remove("d-none");
                        }

                    }else{

                        indicator.classList.add("d-none");

                    }

                });

        },3000);

    }

});
</script>

@endsection
