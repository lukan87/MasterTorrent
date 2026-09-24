@extends('layouts.app')

@section('content')

<style>
/* FileIplay Contact Message — forum/admin style */
.contact-message-page {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem 1rem 3rem;
}

.contact-message-title {
    color: #f8fafc;
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
}

.contact-message-card {
    background: linear-gradient(135deg, rgba(22,32,51,.96), rgba(15,23,42,.92));
    border: 1px solid rgba(148,163,184,.18);
    border-radius: .75rem;
    box-shadow: 0 14px 35px rgba(0,0,0,.25);
    overflow: hidden;
}

.contact-message-card .card-header {
    background: rgba(2,6,23,.28);
    border-bottom: 1px solid rgba(148,163,184,.14);
    color: #f8fafc;
    padding: .9rem 1.1rem;
}

.contact-message-card .card-body {
    padding: 1.25rem;
}

.contact-meta {
    color: #cbd5e1;
    font-size: .9rem;
}

.contact-meta strong {
    color: #64748b;
    font-weight: 700;
}

.contact-divider {
    border: 0;
    border-top: 1px solid rgba(148,163,184,.15);
    margin: 1.1rem 0;
}

.section-title {
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 700;
}

.conversation-box {
    display: flex;
    flex-direction: column;
    gap: .8rem;
    max-height: 650px;
    overflow-y: auto;
    padding: .25rem .2rem .5rem;
}

.message-row {
    display: flex;
    width: 100%;
}

.message-row.guest {
    justify-content: flex-end;
}

.message-row.staff {
    justify-content: flex-start;
}

.message-bubble {
    width: fit-content;
    max-width: 72%;
    padding: .8rem .9rem;
    border-radius: .65rem;
    box-shadow: 0 7px 18px rgba(0,0,0,.18);
    border: 1px solid rgba(148,163,184,.14);
}

.message-bubble.guest {
    background: rgba(13,148,136,.13);
    border-color: rgba(45,212,191,.2);
}

.message-bubble.staff {
    background: rgba(30,41,59,.72);
    border-color: rgba(148,163,184,.16);
}

.message-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: .55rem;
    font-size: .78rem;
}

.guest-name {
    color: #2dd4bf;
    font-weight: 700;
}

.staff-name {
    color: #86efac;
    font-weight: 700;
}

.message-time {
    color: #64748b;
    white-space: nowrap;
}

.message-text {
    color: #cbd5e1;
    font-size: .9rem;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.reply-section {
    margin-top: 1.1rem;
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
    transition: border-color .2s ease, box-shadow .2s ease;
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
    min-height: 125px;
}

.action-row {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem;
}

.btn {
    min-height: 41px;
    border-radius: .55rem;
    font-size: .88rem;
    font-weight: 700;
    padding: .6rem .95rem;
    transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.btn-reply {
    background: #0f766e;
    border: 1px solid #14b8a6;
    color: #fff;
    box-shadow: 0 5px 15px rgba(20,184,166,.12);
}

.btn-reply:hover,
.btn-reply:focus {
    background: #0d9488;
    border-color: #2dd4bf;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 7px 18px rgba(20,184,166,.2);
}

.btn-resolve {
    background: rgba(120,53,15,.55);
    border: 1px solid rgba(251,191,36,.38);
    color: #fde68a;
}

.btn-resolve:hover,
.btn-resolve:focus {
    background: rgba(146,64,14,.7);
    border-color: rgba(251,191,36,.6);
    color: #fef3c7;
    transform: translateY(-1px);
}

.resolved-alert {
    background: rgba(6,78,59,.28);
    border: 1px solid rgba(45,212,191,.22);
    border-radius: .55rem;
    color: #a7f3d0;
    font-size: .9rem;
    padding: .8rem .9rem;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .contact-message-page {
        padding: 1rem .75rem 2rem;
    }

    .contact-message-card .card-body {
        padding: .9rem;
    }

    .message-bubble {
        max-width: 88%;
    }

    .message-header {
        align-items: flex-start;
        flex-direction: column;
        gap: .2rem;
    }

    .message-time {
        white-space: normal;
    }

    .action-row {
        flex-direction: column;
    }

    .action-row form,
    .action-row .btn {
        width: 100%;
    }

    .conversation-box {
        max-height: 520px;
    }
}
</style>

<div class="contact-message-page">

    <h3 class="contact-message-title">
        Contact Message
    </h3>

    <div class="contact-message-card">

        <div class="card-header">
            <strong>Subject: {{ $contact->subject }}</strong>
        </div>

        <div class="card-body">

            <div class="row gy-2 mb-3">
                <div class="col-md-4 contact-meta">
                    <strong>Name:</strong> {{ $contact->name }}
                </div>

                <div class="col-md-4 contact-meta">
                    <strong>Email:</strong> {{ $contact->email }}
                </div>

                <div class="col-md-4 contact-meta text-md-end">
                    <strong>Sent:</strong> {{ $contact->created_at->format('d M Y H:i') }}
                </div>
            </div>

            <hr class="contact-divider">

            <h5 class="section-title mb-3">
                Conversation
            </h5>

            <div class="conversation-box">

                @foreach($contact->messages as $msg)

                    @if($msg->sender_type == 'guest')

                        <div class="message-row guest">
                            <div class="message-bubble guest">

                                <div class="message-header">
                                    <strong class="guest-name">Guest</strong>

                                    <span class="message-time">
                                        {{ $msg->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>

                                <div class="message-text">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>

                            </div>
                        </div>

                    @else

                        <div class="message-row staff">
                            <div class="message-bubble staff">

                                <div class="message-header">
                                    <strong class="staff-name">
                                        Staff ({{ $msg->staff->name ?? 'Staff' }})
                                    </strong>

                                    <span class="message-time">
                                        {{ $msg->created_at->format('d M Y H:i') }}
                                    </span>
                                </div>

                                <div class="message-text">
                                    {!! nl2br(e($msg->message)) !!}
                                </div>

                            </div>
                        </div>

                    @endif

                @endforeach

            </div>

            @if(!$contact->resolved)

                <hr class="contact-divider">

                <div class="reply-section">
                    <h4 class="section-title mb-3">
                        Reply to User
                    </h4>

                    <form method="POST" action="{{ route('contactstaff.answer', $contact->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="reply" class="form-label">Reply</label>

                            <textarea
                                id="reply"
                                name="reply"
                                class="form-control"
                                rows="5"
                                placeholder="Write your reply..."
                                required></textarea>
                        </div>

                        <button type="submit" class="btn btn-reply">
                            Reply
                        </button>
                    </form>

                    <form method="POST" action="{{ route('contactstaff.resolve', $contact->id) }}" class="mt-2">
                        @csrf

                        <button type="submit" class="btn btn-resolve">
                            Resolve Conversation
                        </button>
                    </form>
                </div>

            @endif

            @if($contact->resolved)

                <div class="resolved-alert">
                    <strong>This conversation has been resolved.</strong>
                </div>

            @endif

        </div>
    </div>

</div>

<script>
window.addEventListener('load', function () {
    const box = document.querySelector('.conversation-box');

    if (box) {
        box.scrollTop = box.scrollHeight;
    }
});
</script>

@endsection
