@extends('layouts.app')

@section('content')

<div class="container py-5" style="max-width:860px">

    <div class="glass shadow-lg border-0 rounded-4 overflow-hidden mc-card">

        {{-- Header --}}
        <div class="mc-header d-flex align-items-center gap-3 px-4 py-3">
            <span class="mc-header-icon">
                <i class="bi bi-chat-dots-fill"></i>
            </span>
            <div>
                <div class="fw-bold fs-5 text-white">New Message</div>
                <small class="text-muted">
                    @if($recipient)
                        to {{ $recipient->name }}
                    @else
                        start a conversation with a member
                    @endif
                </small>
            </div>

            <a href="{{ route('messages.index') }}" class="mc-back ms-auto">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                {{ session('error') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Recipient picker --}}
            @if(!$recipient)
                <div class="mb-4">
                    <form method="GET" action="{{ route('messages.create') }}" class="mc-recipient-picker">
                        <div class="input-group">
                            <span class="input-group-text mc-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input type="text"
                                   name="username"
                                   class="form-control bg-dark text-light border-secondary"
                                   placeholder="Type a member's name..."
                                   value="{{ old('username') }}">
                            <button class="btn btn-ghost-accent px-4 fw-bold" type="submit">
                                <i class="bi bi-search me-1"></i>Find
                            </button>
                        </div>
                    </form>

                    @if($error)
                        <div class="alert alert-danger mt-3 mb-0 rounded-3">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ $error }}
                        </div>
                    @endif
                </div>
            @endif

            @if($recipient)
            <form action="{{ route('messages.store') }}" method="POST">
                @csrf

                <input type="hidden" name="receiver_id" value="{{ $recipient->id }}">

                {{-- Subject --}}
                <div class="form-floating mb-4">
                    <input type="text"
                           name="subject"
                           id="subject"
                           class="form-control bg-dark text-light border-secondary @error('subject') is-invalid @enderror"
                           placeholder="Subject"
                           value="{{ old('subject') }}">
                    <label for="subject">Subject</label>
                    @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Message --}}
                <div class="mb-4">
                    <div class="bbcode-toolbar mb-2">
                        <button type="button" onclick="insertTag('[b]','[/b]')" title="Bold"><b>B</b></button>
                        <button type="button" onclick="insertTag('[i]','[/i]')" title="Italic"><i>I</i></button>
                        <button type="button" onclick="insertTag('[u]','[/u]')" title="Underline"><u>U</u></button>
                        <button type="button" onclick="insertTag('[quote]','[/quote]')" title="Quote">&#10077;</button>
                        <button type="button" onclick="insertTag('[code]','[/code]')" title="Code">&lt;/&gt;</button>
                        <button type="button" onclick="insertTag('[url]','[/url]')" title="Link">&#128279;</button>
                        <button type="button" onclick="insertTag('[img]','[/img]')" title="Image">&#128444;</button>
                    </div>

                    <textarea name="body"
                              id="body"
                              class="form-control bg-dark text-light border-secondary @error('body') is-invalid @enderror message-box"
                              placeholder="Write your message..."
                              required
                              rows="5">{{ old('body') }}</textarea>

                    @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Smilies --}}
                <div class="smilies mt-2 mb-4">
                    <span onclick="addSmile('&#128578;')">&#128578;</span>
                    <span onclick="addSmile('&#128516;')">&#128516;</span>
                    <span onclick="addSmile('&#128521;')">&#128521;</span>
                    <span onclick="addSmile('&#128523;')">&#128523;</span>
                    <span onclick="addSmile('&#128526;')">&#128526;</span>
                    <span onclick="addSmile('&#128546;')">&#128546;</span>
                    <span onclick="addSmile('&#10084;&#65039;')">&#10084;&#65039;</span>
                    <span onclick="addSmile('&#128293;')">&#128293;</span>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="mc-send px-4 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Send Message
                    </button>
                </div>
            </form>
            @else
                <div class="mc-hint">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Find a member above to start a new private conversation.
                </div>
            @endif

        </div>
    </div>
</div>

<style>
.mc-card{ background:var(--ui-surface-raised); }
.mc-header{ background:linear-gradient(135deg, rgba(99,210,198,.16), rgba(45,110,126,.10)); border-bottom:1px solid var(--ui-border); }
.mc-header-icon{ width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,var(--ui-accent),var(--ui-accent-strong));color:#0b1120;display:flex;align-items:center;justify-content:center;font-size:1.25rem;box-shadow:0 6px 16px rgba(99,210,198,.35); }
.mc-back{ text-decoration:none;color:var(--ui-text-muted);font-size:.85rem;padding:6px 12px;border-radius:8px;transition:.15s; }
.mc-back:hover{ color:var(--ui-accent);background:rgba(99,210,198,.1); }
.mc-recipient-picker .mc-input-icon{ background:rgba(255,255,255,.04);border-color:var(--ui-border);color:var(--ui-accent); }
.btn-ghost-accent{ background:rgba(99,210,198,.12);color:var(--ui-accent);border:1px solid rgba(99,210,198,.3);transition:.15s; }
.btn-ghost-accent:hover{ background:var(--ui-accent);color:#0b1120; }
.form-control{ border-radius:.6rem; transition:all .25s; }
.form-control:focus{ border-color:var(--ui-accent); box-shadow:0 0 0 .2rem rgba(99,210,198,.22); }
.message-box{ min-height:120px; resize:none; }
.bbcode-toolbar{ display:flex; flex-wrap:wrap; gap:6px; }
.bbcode-toolbar button{ background:rgba(255,255,255,.06);border:1px solid var(--ui-border);color:#fff;padding:6px 10px;border-radius:6px;cursor:pointer;transition:.2s;font-size:14px; }
.bbcode-toolbar button:hover{ background:var(--ui-accent);color:#0b1120; }
.smilies span{ cursor:pointer; font-size:20px; margin-right:8px; transition:.2s; }
.smilies span:hover{ transform:scale(1.25); }
.mc-send{ background:linear-gradient(135deg,var(--ui-accent),var(--ui-accent-strong));border:none;color:#0b1120;border-radius:10px;transition:.2s;padding:.6rem 2rem; }
.mc-send:hover{ transform:translateY(-2px); box-shadow:0 8px 24px rgba(99,210,198,.35); }
.mc-hint{ color:var(--ui-text-muted);font-size:.9rem;text-align:center;padding:12px;border:1px dashed var(--ui-border);border-radius:10px; }
@media(max-width:768px){ .card-body{ padding:1.25rem; } }
</style>

<script>
document.addEventListener("DOMContentLoaded", function(){
    var ta = document.querySelector(".message-box");
    if(!ta) return;
    ta.addEventListener("input", function(){
        this.style.height = "auto";
        this.style.height = (this.scrollHeight) + "px";
    });
});
function insertTag(openTag, closeTag){
    var ta = document.getElementById("body");
    if(!ta) return;
    var s = ta.selectionStart, e = ta.selectionEnd;
    ta.value = ta.value.substring(0,s) + openTag + ta.value.substring(s,e) + closeTag + ta.value.substring(e);
    ta.focus();
}
function addSmile(code){
    var ta = document.getElementById("body");
    if(!ta) return;
    ta.value += " " + code;
}
</script>

@endsection
