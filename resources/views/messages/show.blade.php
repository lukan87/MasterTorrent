@extends('layouts.app')

@section('content')

<div class="container-fluid glass py-3 mt-3">

<div class="messenger">

<!-- SIDEBAR -->

<div class="messenger-sidebar glass">

<h6 class="sidebar-title">Messages</h6>

<div class="conversation-scroll">

@foreach($conversations as $conversation)

@php
$other = Auth::id() == $conversation->user_one
    ? $conversation->userTwo
    : $conversation->userOne;

$last = $conversation->lastMessage;

$currentConversation = $messages->first()->conversation_id ?? null;
@endphp

@if($last)

<a href="{{ route('messages.show', $last->id) }}"
class="conversation-item {{ $conversation->id == $currentConversation ? 'active' : '' }}">

<img src="{{ !empty($other->profile_image) ? asset($other->profile_image) : asset('images/default_avatar/default-avatar.jpg') }}">

<div class="conversation-text">

<div class="name">
{{ $other->name }}
</div>

<div class="preview">
{{ Str::limit(strip_tags(convertCustomTagsToHtml($last->body)),40) }}
</div>

</div>

@if(!$last->is_read && $last->receiver_id == Auth::id())
<span class="badge bg-success ms-auto">New</span>
@endif

</a>

@endif

@endforeach

</div>

</div>

<!-- CHAT AREA -->

<div class="messenger-chat glass">

<!-- HEADER -->

@php

$conversation = $messages->first()->conversation ?? null;

$otherUser = null;

if ($conversation) {

    $otherUser = Auth::id() == $conversation->user_one
        ? $conversation->userTwo
        : $conversation->userOne;

}

@endphp

<div class="chat-header">

@if($otherUser)

<div class="chat-user">

<img src="{{ $otherUser->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">

<a href="{{ route('profile.show', $otherUser->id) }}">
    <span>{{ $otherUser->name }} </span>
</a>

</div>

@endif

</div>

<!-- MESSAGES -->



<div class="chat-body" id="chatBody">

@foreach($messages as $message)

<div class="message-row {{ $message->sender_id == Auth::id() ? 'me' : 'them' }}">

@if($message->sender_id != Auth::id()) <img class="avatar" src="{{ $message->sender->profile_image ?? asset('images/default_avatar/default-avatar.jpg') }}">
@endif

<div class="message-bubble" data-id="{{ $message->id }}">

<div class="message-content">
{!! convertCustomTagsToHtml($message->body) !!}
</div>

<div class="message-actions">

@if($message->sender_id == Auth::id())

<button class="edit-msg" data-id="{{ $message->id }}">
<i class="bi bi-pencil"></i>
</button>

<button class="delete-msg" data-id="{{ $message->id }}">
<i class="bi bi-trash"></i>
</button>

@endif

<span class="time">
{{ $message->created_at->format('d M Y • H:i') }}
</span>

</div>

</div>

@if($message->sender_id == Auth::id()) <img class="avatar" src="{{ Auth::user()->profile_image ?? asset('default-avatar.png') }}">
@endif

</div>

@endforeach

</div>

<!-- INPUT BAR -->

<div class="chat-input">

@php
$lastMessage = $messages->last();
$systemUserId = 2;
$conversation = $messages->first()->conversation ?? null;

$isSystemConversation = $conversation &&
    ($conversation->user_one == $systemUserId || $conversation->user_two == $systemUserId);
@endphp

<div class="input-wrapper">

@if($lastMessage && !$isSystemConversation)

<form action="{{ route('messages.storeReply',$lastMessage) }}"
method="POST"
class="input-form"
id="chatForm">

@csrf

<div class="chat-editor">

<div class="bbcode-toolbar">

<button type="button" onclick="insertTag('[b]','[/b]')"><b>B</b></button>
<button type="button" onclick="insertTag('[i]','[/i]')"><i>I</i></button>
<button type="button" onclick="insertTag('[u]','[/u]')"><u>U</u></button>
<button type="button" onclick="insertTag('[quote]','[/quote]')">❝</button>
<button type="button" onclick="insertTag('[code]','[/code]')"><i class="bi bi-code-slash fs-5"></i></button>
<button type="button" onclick="insertTag('[url]','[/url]')"><i class="bi bi-link-45deg fs-5"></i></button>
<button type="button" onclick="insertTag('[img]','[/img]')"><i class="bi bi-card-image fs-5"></i></button>

</div>

<textarea
name="body"
id="body"
class="chat-textarea"
placeholder="Write a message..."
rows="1"
required
></textarea>


<!-- SMILIES -->

<div class="smilies">

<span onclick="addSmile('🙂')">🙂</span>
<span onclick="addSmile('😄')">😄</span>
<span onclick="addSmile('😉')">😉</span>
<span onclick="addSmile('😛')">😛</span>
<span onclick="addSmile('😎')">😎</span>
<span onclick="addSmile('😢')">😢</span>
<span onclick="addSmile('❤️')">❤️</span>
<span onclick="addSmile('🔥')">🔥</span>

</div>

</div>

</form>

@endif

<div class="chat-buttons">

@if(!$isSystemConversation)
<button type="submit" form="chatForm" class="action-btn send-btn">
<i class="bi bi-send-fill"></i>
</button>
@else

<div class="system-message text-center text-muted p-2">
<i class="bi bi-info-circle"></i>
This is a system notification. Replies are disabled.
</div>

@endif

@if($lastMessage)

<form action="{{ route('messages.destroyConversation',$lastMessage->conversation_id) }}" method="POST">

@csrf
@method('DELETE')

<button class="action-btn delete-btn">
<i class="bi bi-trash"></i>
</button>

</form>

@endif

</div>

</div>

</div>

</div>

</div>

</div>

<style>

    .message-content {
    word-break: break-word;
    overflow-wrap: anywhere;
}

/* Only scroll for code */
.message-content pre,
.message-content code {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    background: #111;
    padding: 8px;
    border-radius: 6px;
}

    .conversation-scroll {
    max-height: 70vh;      /* responsive height */
    overflow-y: auto;
    padding-right: 4px;    /* avoids scrollbar overlap */
}

/* Optional: nicer scrollbar */
.conversation-scroll::-webkit-scrollbar {
    width: 6px;
}

.conversation-scroll::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}

.input-wrapper{
display:flex;
align-items:center;
gap:10px;
width:100%;
}

.chat-buttons{
display:flex;
gap:8px;
align-items:center;
}

.input-form{
flex:1;
}

.conversation-item{
display:flex;
align-items:center;
gap:10px;
padding:10px 12px;
border-radius:10px;
cursor:pointer;
text-decoration:none;
color:white;
transition:all .2s ease;
}

.conversation-item:hover{
background:#242424;
transform:translateX(3px);
}

.conversation-item.active{
background:#2e3f50;
box-shadow:inset 3px 0 #5fa8ff;
}

.conversation-item img{
width:38px;
height:38px;
border-radius:50%;
object-fit:cover;
flex-shrink:0;
}

.conversation-text{
flex:1;
display:flex;
flex-direction:column;
overflow:hidden;
min-width:0;
}

.name{
font-weight:600;
font-size:14px;
}

.preview{
font-size:12px;
color:#aaa;
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
max-width:160px;
}

/* ACTION BUTTONS */

.action-btn{
width:42px;
height:42px;
display:flex;
align-items:center;
justify-content:center;
border-radius:8px;
border:none;
cursor:pointer;
}

.send-btn{ background:#3a5a7a; color:white; }

.delete-btn{ background:#8b2d2d; color:white; }



/* LAYOUT */

.messenger{
display:flex;
height:75vh;
background:#1c1c1c;
border-radius:10px;
overflow:hidden;
}

.messenger-sidebar{
width:260px;
background:#161616;
border-right:1px solid #2c2c2c;
padding:15px;
}

.sidebar-title{
color:#bbb;
margin-bottom:15px;
}

.conversation-item{
display:flex;
gap:10px;
padding:10px;
border-radius:8px;
cursor:pointer;
}

.conversation-item:hover{
background:#242424;
}

.conversation-item img{
width:36px;
height:36px;
border-radius:50%;
}



/* CHAT */

.messenger-chat{
flex:1;
display:flex;
flex-direction:column;
background:#1e1e1e;
}

.chat-header{
padding:14px 20px;
border-bottom:1px solid #2c2c2c;
}

.chat-user{
display:flex;
align-items:center;
gap:10px;
font-weight:600;
}

.chat-user img{
width:36px;
height:36px;
border-radius:50%;
}



/* MESSAGES */

.chat-body{
flex:1;
overflow-y:auto;
padding:20px;
}

.message-row{
display:flex;
align-items:flex-end;
gap:10px;
margin-bottom:16px;
}

.message-row.me{
justify-content:flex-end;
}

.avatar{
width:36px;
height:36px;
border-radius:50%;
}

.message-bubble{
position:relative;
max-width:65%;
padding:10px 14px;
border-radius:16px;
background:#2a2a2a;
box-shadow:0 2px 6px rgba(0,0,0,0.25);
line-height:1.4;
}

.message-row.me .message-bubble{
background:#3a5a7a;
border-bottom-right-radius:6px;
}

.message-row.me .message-bubble::after{
content:"";
position:absolute;
right:-6px;
bottom:6px;
width:12px;
height:12px;
background:#3a5a7a;
border-bottom-left-radius:12px;
transform:rotate(-45deg);
}

.message-row.them .message-bubble{
border-bottom-left-radius:6px;
}

.message-row.them .message-bubble::after{
content:"";
position:absolute;
left:-6px;
bottom:6px;
width:12px;
height:12px;
background:#2a2a2a;
border-bottom-right-radius:12px;
transform:rotate(45deg);
}

.message-actions{
display:flex;
align-items:center;
gap:8px;
margin-top:4px;
}

.message-actions button{
background:none;
border:none;
color:#aaa;
cursor:pointer;
font-size:12px;
}

.message-actions button:hover{
color:white;
}

.time{
font-size:.7rem;
opacity:.6;
}



/* INPUT */

.chat-input{
display:flex;
align-items:center;
gap:10px;
padding:12px;
border-top:1px solid #2c2c2c;
background:#1a1a1a;
}

.input-form{
display:flex;
flex:1;
gap:8px;
}



/* EDITOR */

.chat-editor{
flex:1;
display:flex;
flex-direction:column;
gap:6px;
}

.chat-textarea{
background:#2a2a2a;
border:none;
color:white;
padding:8px 10px;
border-radius:6px;
resize:none;
min-height:36px;
max-height:140px;
overflow-y:auto;
}



/* BBCODE */

.bbcode-toolbar{
display:flex;
flex-wrap:wrap;
gap:6px;
}

.bbcode-toolbar button{
background:#2c2c2c;
border:none;
color:white;
padding:4px 8px;
border-radius:4px;
cursor:pointer;
font-size:13px;
}

.bbcode-toolbar button:hover{
background:#3a5a7a;
}



/* SMILIES */

.smilies span{
cursor:pointer;
font-size:18px;
margin-right:6px;
transition:.2s;
}

.smilies span:hover{
transform:scale(1.2);
}



/* SCROLLBAR */

.chat-body::-webkit-scrollbar{ width:6px; }

.chat-body::-webkit-scrollbar-thumb{
background:#333;
border-radius:6px;
}


/* =========================
   MOBILE FIX
   ========================= */

@media (max-width: 768px){

.messenger{
flex-direction:column;
height:auto;
}

/* Sidebar full width */
.messenger-sidebar{
width:100%;
border-right:none;
border-bottom:1px solid #2c2c2c;
max-height:200px;
overflow-y:auto;
}

/* Chat area full width */
.messenger-chat{
width:100%;
}

/* Messages bubbles wider */
.message-bubble{
max-width:85%;
}

/* Chat body height fixed so messages visible */
.chat-body{
flex:1;
overflow-y:auto;
padding:20px;
scroll-behavior:smooth;
}

/* Input layout */
.input-wrapper{
flex-direction:column;
align-items:stretch;
}

/* Buttons align nicely */
.chat-buttons{
justify-content:flex-end;
margin-top:6px;
}

/* Toolbar wrap nicely */
.bbcode-toolbar{
flex-wrap:wrap;
}

/* Smilies wrap */
.smilies{
display:flex;
flex-wrap:wrap;
gap:6px;
margin-top:6px;
}

}

</style>

<script>

document.addEventListener("DOMContentLoaded", function(){

const chat = document.getElementById("chatBody");
const textarea = document.getElementById("body");
const form = document.getElementById("chatForm");

/* AUTO SCROLL ONLY IF USER IS AT BOTTOM */

let shouldScroll = true;

chat.addEventListener("scroll", function(){

const threshold = 50;

shouldScroll = (chat.scrollHeight - chat.scrollTop - chat.clientHeight) < threshold;

});

function scrollToBottom(){

if(shouldScroll){
chat.scrollTop = chat.scrollHeight;
}

}

scrollToBottom();



/* AUTO RESIZE */

textarea.addEventListener("input", function(){

this.style.height = "auto";
this.style.height = this.scrollHeight + "px";

});


/* ENTER TO SEND */

textarea.addEventListener("keydown", function(e){

if(e.key === "Enter" && !e.shiftKey){

e.preventDefault();

if(textarea.value.trim() !== ""){
form.submit();
}

}

});


/* EDIT MESSAGE */

document.querySelectorAll(".edit-msg").forEach(btn=>{

btn.addEventListener("click",function(){

let id = this.dataset.id;

let bubble = document.querySelector(`.message-bubble[data-id="${id}"] .message-content`);

if(!bubble) return;

let oldText = bubble.innerText;

let newText = prompt("Edit message:", oldText);

if(!newText) return;

fetch(`/messages/edit/${id}`, {

method:"POST",

headers:{
"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content,
"Content-Type":"application/json"
},

body:JSON.stringify({
body:newText
})

})
.then(res=>res.json())
.then(data=>{

bubble.innerHTML = newText;

});

});

});


/* DELETE MESSAGE */

document.querySelectorAll(".delete-msg").forEach(btn=>{

btn.addEventListener("click",function(){

if(!confirm("Delete message?")) return;

let id = this.dataset.id;

let row = this.closest(".message-row");

fetch(`/messages/delete/${id}`, {

method:"DELETE",

headers:{
"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
}

})
.then(res=>res.json())
.then(data=>{

if(row){
row.remove();
}

});

});

});


});



/* BBCODE */

function insertTag(openTag, closeTag){

const textarea = document.getElementById("body");

const start = textarea.selectionStart;
const end = textarea.selectionEnd;

const selected = textarea.value.substring(start,end);

textarea.value =
textarea.value.substring(0,start) +
openTag + selected + closeTag +
textarea.value.substring(end);

textarea.focus();

}



/* SMILIES */

function addSmile(code){

const textarea = document.getElementById("body");

textarea.value += " " + code;

textarea.focus();

}

</script>

@endsection
