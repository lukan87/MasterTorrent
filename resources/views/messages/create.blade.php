@extends('layouts.app')

@section('content')

<div class="container py-5" style="max-width:900px">

<div class="card glass shadow-lg border-0 rounded-4 overflow-hidden">


<!-- Header -->
<div class="card-header border-0 d-flex align-items-center gap-3 py-3 px-4 bg-gradient-primary text-white">

    <img src="{{ $recipient->profile_image ?? asset('default-avatar.png') }}"
         class="rounded-circle shadow-sm"
         width="45" height="45">

    <div>
        <div class="fw-bold fs-5">New Message</div>
        <small class="opacity-75">to {{ $recipient->name }}</small>
    </div>

</div>

<div class="card-body p-4">

    {{-- Alerts --}}
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

    <form action="{{ route('messages.store') }}" method="POST">
        @csrf

        <input type="hidden" name="receiver_id" value="{{ $recipient->id }}">

        <!-- Subject -->
        <div class="form-floating mb-4">
            <input type="text"
                   name="subject"
                   id="subject"
                   class="form-control bg-dark text-light border-secondary @error('subject') is-invalid @enderror"
                   placeholder="Subject"
                   value="{{ old('subject') }}">

            <label for="subject">Subject</label>

            @error('subject')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <!-- Message -->
<!-- Message -->
<div class="mb-4">

    <!-- BBCode Toolbar -->
    <div class="bbcode-toolbar mb-2">

        <button type="button" onclick="insertTag('[b]','[/b]')" title="Bold">
            <b>B</b>
        </button>

        <button type="button" onclick="insertTag('[i]','[/i]')" title="Italic">
            <i>I</i>
        </button>

        <button type="button" onclick="insertTag('[u]','[/u]')" title="Underline">
            <u>U</u>
        </button>

        <button type="button" onclick="insertTag('[quote]','[/quote]')" title="Quote">
            ❝
        </button>

        <button type="button" onclick="insertTag('[code]','[/code]')" title="Code">
            &lt;/&gt;
        </button>

        <button type="button" onclick="insertTag('[url]','[/url]')" title="Link">
            🔗
        </button>

        <button type="button" onclick="insertTag('[img]','[/img]')" title="Image">
            🖼
        </button>

    </div>

    <textarea
        name="body"
        id="body"
        class="form-control bg-dark text-light border-secondary @error('body') is-invalid @enderror message-box"
        placeholder="Write your message..."
        required
        rows="5"
    >{{ old('body') }}</textarea>

    @error('body')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror

</div>

<!-- Smilies -->
<div class="smilies mt-2">

    <span onclick="addSmile('🙂')">🙂</span>
    <span onclick="addSmile('😄')">😄</span>
    <span onclick="addSmile('😉')">😉</span>
    <span onclick="addSmile('😛')">😛</span>
    <span onclick="addSmile('😎')">😎</span>
    <span onclick="addSmile('😢')">😢</span>
    <span onclick="addSmile('❤️')">❤️</span>
    <span onclick="addSmile('🔥')">🔥</span>

</div>

        <!-- Send -->
        <div class="d-flex justify-content-end">

            <button class="btn btn-gradient px-4 py-2 fw-bold text-white shadow-sm">

                <i class="bi bi-send-fill me-2"></i>
                Send Message

            </button>

        </div>

    </form>

</div>


</div>

</div>



<style>

/* Glass card */
.glass {
    background: rgba(30,30,30,.85);
    backdrop-filter: blur(10px);
}

/* Gradient header */
.bg-gradient-primary {
    background: linear-gradient(135deg,#4e54c8,#8f94fb);
}

/* Inputs */
.form-control {
    border-radius: .6rem;
    transition: all .25s;
}

.form-control:focus {
    border-color:#6a6dfd;
    box-shadow:0 0 0 0.2rem rgba(106,109,253,.25);
}

/* Message textarea */
.message-box {
    min-height:120px;
    resize:none;
}

/* Button */
.btn-gradient {
    background: linear-gradient(135deg,#6a11cb,#2575fc);
    border:none;
    transition:.2s;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow:0 8px 25px rgba(0,0,0,.4);
}

/* Mobile tweaks */
@media (max-width:768px){

    .card-body {
        padding:1.5rem;
    }

}
/* BBCode toolbar */

.bbcode-toolbar{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
}

.bbcode-toolbar button{
    background:#2c2f3a;
    border:none;
    color:#fff;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    transition:.2s;
    font-size:14px;
}

.bbcode-toolbar button:hover{
    background:#4e54c8;
}

/* Smilies */

.smilies span{
    cursor:pointer;
    font-size:20px;
    margin-right:8px;
    transition:.2s;
}

.smilies span:hover{
    transform:scale(1.2);
}

</style>



<script>

document.addEventListener("DOMContentLoaded", function(){

    const textarea = document.querySelector(".message-box");

    textarea.addEventListener("input", function(){

        this.style.height = "auto";
        this.style.height = (this.scrollHeight) + "px";

    });

});

function insertTag(openTag, closeTag){

    const textarea = document.getElementById("body");

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    const selected = textarea.value.substring(start,end);

    const replacement = openTag + selected + closeTag;

    textarea.value =
        textarea.value.substring(0,start) +
        replacement +
        textarea.value.substring(end);

}

function addSmile(code){

    const textarea = document.getElementById("body");

    textarea.value += " " + code;

}

</script>

@endsection
