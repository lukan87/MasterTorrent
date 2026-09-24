@extends('layouts.app')

@section('content')

<style>
/* FileIplay Your Conversations — forum style */
.conversations-page {
    width: 100%;
    max-width: 940px;
    margin: 0 auto;
    padding: 2rem 1rem 3rem;
}

.conversations-title {
    color: #f8fafc;
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
}

.conversation-card {
    background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.92));
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .75rem;
    box-shadow: 0 12px 30px rgba(0,0,0,.22);
    overflow: hidden;
    margin-bottom: 1rem;
}

.conversation-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem 1rem;
    background: rgba(2,6,23,.25);
    border-bottom: 1px solid rgba(148,163,184,.14);
}

.conversation-subject {
    color: #f8fafc;
    font-size: .98rem;
    font-weight: 700;
    word-break: break-word;
}

.conversation-body {
    padding: 1rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .3rem .6rem;
    border-radius: .45rem;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
}

.status-resolved {
    color: #a7f3d0;
    background: rgba(6,78,59,.42);
    border: 1px solid rgba(45,212,191,.25);
}

.status-open {
    color: #fde68a;
    background: rgba(120,53,15,.35);
    border: 1px solid rgba(251,191,36,.25);
}

.message {
    background: rgba(2,6,23,.34);
    border: 1px solid rgba(148,163,184,.15);
    border-radius: .6rem;
    padding: .85rem;
    margin-bottom: .75rem;
}

.message:last-child {
    margin-bottom: 1rem;
}

.message-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .55rem;
}

.sender-you {
    color: #2dd4bf;
    font-weight: 700;
    font-size: .9rem;
}

.sender-staff {
    color: #86efac;
    font-weight: 700;
    font-size: .9rem;
}

.message-time {
    color: #64748b;
    font-size: .78rem;
    white-space: nowrap;
}

.message-divider {
    border: 0;
    border-top: 1px solid rgba(148,163,184,.13);
    margin: .55rem 0 .75rem;
}

.message-content {
    color: #cbd5e1;
    font-size: .92rem;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.form-label {
    color: #cbd5e1;
    font-size: .9rem;
    font-weight: 600;
    margin-bottom: .4rem;
}

.form-control {
    background: rgba(2,6,23,.48) !important;
    border: 1px solid rgba(148,163,184,.22) !important;
    border-radius: .55rem !important;
    color: #f8fafc !important;
    padding: .7rem .8rem !important;
    font-size: .92rem;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.form-control::placeholder {
    color: #64748b;
}

.form-control:focus {
    background: rgba(2,6,23,.62) !important;
    border-color: rgba(45,212,191,.75) !important;
    box-shadow: 0 0 0 .18rem rgba(45,212,191,.10) !important;
    outline: none !important;
}

textarea.form-control {
    resize: vertical;
    min-height: 105px;
}

.btn {
    min-height: 41px;
    border-radius: .55rem;
    font-size: .88rem;
    font-weight: 700;
    padding: .6rem .9rem;
    transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.btn-primary {
    background: #0f766e !important;
    border: 1px solid #14b8a6 !important;
    color: #fff !important;
    box-shadow: 0 5px 15px rgba(20,184,166,.12);
}

.btn-primary:hover,
.btn-primary:focus {
    background: #0d9488 !important;
    border-color: #2dd4bf !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 7px 18px rgba(20,184,166,.2);
}

.resolved-message {
    background: rgba(6,78,59,.25);
    border: 1px solid rgba(45,212,191,.2);
    border-radius: .55rem;
    color: #a7f3d0;
    font-size: .88rem;
    line-height: 1.55;
    padding: .8rem .9rem;
    margin-top: .9rem;
}

@media (max-width: 576px) {
    .conversations-page {
        padding: 1rem .75rem 2rem;
    }

    .conversations-title {
        font-size: 1.2rem;
    }

    .conversation-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .conversation-body {
        padding: .8rem;
    }

    .message {
        padding: .75rem;
    }

    .message-meta {
        align-items: flex-start;
        flex-direction: column;
        gap: .2rem;
    }

    .message-time {
        white-space: normal;
    }

    .btn-primary {
        width: 100%;
    }
}
</style>

<div class="conversations-page">

    <h3 class="conversations-title">
        Your Conversations / Conversațiile Tale
    </h3>

    @foreach($contacts as $contact)

        <div class="conversation-card">

            <div class="conversation-header">
                <strong class="conversation-subject">
                    {{ $contact->subject }}
                </strong>

                @if($contact->resolved)
                    <span class="status-badge status-resolved">Resolved</span>
                @else
                    <span class="status-badge status-open">Open</span>
                @endif
            </div>

            <div class="conversation-body">

                @foreach($contact->messages as $msg)

                    <div class="message">

                        <div class="message-meta">
                            @if($msg->sender_type == 'guest')
                                <strong class="sender-you">You / Tu</strong>
                            @else
                                <strong class="sender-staff">Staff</strong>
                            @endif

                            <span class="message-time">
                                {{ $msg->created_at->format('d M Y H:i') }}
                            </span>
                        </div>

                        <hr class="message-divider">

                        <div class="message-content">
                            {!! nl2br(e($msg->message)) !!}
                        </div>

                    </div>

                @endforeach

                @if(!$contact->resolved)

                    <form method="POST" action="{{ route('contact.reply', $contact->id) }}">
                        @csrf

                        <div class="mb-2">
                            <label for="reply-{{ $contact->id }}" class="form-label">
                                Reply / Răspuns
                            </label>

                            <textarea
                                id="reply-{{ $contact->id }}"
                                name="message"
                                class="form-control"
                                rows="4"
                                placeholder="Write a reply / Scrie un răspuns..."
                                required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Send Reply / Trimite Răspuns
                        </button>
                    </form>

                @else

                    <div class="resolved-message">
                        <strong>Conversation resolved.</strong><br>
                        EN: This conversation has been closed by staff.<br>
                        RO: Această conversație a fost închisă de staff.
                    </div>

                @endif

            </div>
        </div>

    @endforeach

</div>

@endsection
