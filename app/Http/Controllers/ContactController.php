<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\UserClass;
use App\Models\Message;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{

public function __construct()
{
    $this->middleware('auth')->only([
        'index',
        'show',
        'answer',
        'resolve'
    ]);
}

/* ===============================
   Staff Permission Check
================================ */

private function staffOnly()
{
    if (!Auth::check() || Auth::user()->user_class < UserClass::ADMIN) {
        abort(403);
    }
}

/* ===============================
   Staff Panel
================================ */

public function index()
{
    $this->staffOnly();

    $contacts = Contact::orderByDesc('created_at')
        ->paginate(50);

    return view('contactstaff.index', compact('contacts'));
}

public function show($id)
{
    $this->staffOnly();

    $contact = Contact::with('messages.staff')->findOrFail($id);

    return view('contactstaff.show', compact('contact'));
}

/* ===============================
   Guest Contact Form
================================ */

public function create()
{
    return view('contact.create');
}

/* ===============================
   Store Guest Message
================================ */

public function store(Request $request)
{

/* Honeypot anti bot */

if ($request->filled('website')) {
    abort(403);
}

/* JS challenge */

if (!$request->filled('js_token')) {
    abort(403);
}

/* Form time check */

$formTime = $request->input('form_time');

if (!$formTime || (time()*1000 - $formTime) < 3000) {

    return back()->withErrors([
        'message' => 'Form submitted too quickly.'
    ]);

}

/* Rate limiter */

$key = 'contact-form-'.$request->ip();

if (RateLimiter::tooManyAttempts($key, 5)) {

    return back()->withErrors([
        'message' => 'Too many messages sent. Please wait a few minutes.'
    ]);

}

RateLimiter::hit($key, 300);

/* Validation */

$request->validate([
    'name' => 'required|max:100',
    'email' => 'required|email|max:150',
    'subject' => 'required|max:150',
    'message' => 'required|min:10'
]);

/* Create conversation */

$contact = Contact::create([
    'name' => $request->name,
    'email' => $request->email,
    'subject' => $request->subject,
    'message' => $request->message,
    'ip' => $request->ip()
]);

/* Save message */

ContactMessage::create([
    'contact_id' => $contact->id,
    'sender_type' => 'guest',
    'message' => $request->message,
    'ip' => $request->ip()
]);

/* Notify staff */

$staff = User::whereIn('user_class', [
    UserClass::ADMIN,
    UserClass::OWNER,
    UserClass::WEB_DEVELOPER
])->get();

$systemId = 2;

foreach ($staff as $admin) {

    $conversation = \App\Models\Conversation::where(function ($q) use ($systemId, $admin) {
        $q->where('user_one', $systemId)
          ->where('user_two', $admin->id);
    })
    ->orWhere(function ($q) use ($systemId, $admin) {
        $q->where('user_one', $admin->id)
          ->where('user_two', $systemId);
    })
    ->first();

    if (!$conversation) {

        $conversation = \App\Models\Conversation::create([
            'user_one' => $systemId,
            'user_two' => $admin->id,
            'subject' => 'Staff Notifications',
            'last_message_at' => now(),
        ]);

    }

    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $systemId,
        'receiver_id' => $admin->id,
        'subject' => 'New Contact Request',
        'body' =>
"A guest contacted staff.\n\n".
"Email: {$contact->email}\n".
"Subject: {$contact->subject}\n\n".
"Open conversation:\n".
config('app.site_url').'/contactstaff/'.$contact->id,
        'is_read' => 0
    ]);

    $conversation->update([
        'last_message_at' => now()
    ]);

}

return back()->with('status', 'Message sent to staff successfully.');

}

/* ===============================
   Staff Reply
================================ */

public function answer(Request $request,$id)
{

$this->staffOnly();

$request->validate([
    'reply'=>'required|min:3'
]);

$contact = Contact::findOrFail($id);

ContactMessage::create([
    'contact_id'=>$contact->id,
    'sender_type'=>'staff',
    'staff_id'=>Auth::id(),
    'message'=>$request->reply
]);

return back()->with('success','Reply sent.');

}

/* ===============================
   Guest Check Reply Page
================================ */

public function check()
{
    return view('contact.check');
}

/* ===============================
   Guest View Replies
================================ */

public function viewReply(Request $request)
{

$request->validate([
    'email' => 'required|email'
]);

$contacts = Contact::where('email', $request->email)
    ->with('messages')
    ->latest()
    ->get();

if ($contacts->isEmpty()) {

    return back()->with('error',
        'Email not found. Please use the same email address used when sending the message.'
    );

}

return view('contact.replies', compact('contacts'));

}

/* ===============================
   Resolve Conversation
================================ */

public function resolve($id)
{

$this->staffOnly();

$contact = Contact::findOrFail($id);

$contact->resolved = true;
$contact->save();

return back()->with('success','Conversation resolved.');

}

/* ===============================
   Guest Reply
================================ */

public function guestReply(Request $request, $id)
{

$request->validate([
    'message' => 'required|min:3'
]);

$contact = Contact::findOrFail($id);

if ($contact->resolved) {
    return back()->with('error','Conversation already resolved.');
}

/* Save reply */

ContactMessage::create([
    'contact_id' => $contact->id,
    'sender_type' => 'guest',
    'message' => $request->message,
    'ip' => $request->ip()
]);

/* Notify staff */

$staff = User::whereIn('user_class', [
    UserClass::ADMIN,
    UserClass::OWNER,
    UserClass::WEB_DEVELOPER
])->get();

$systemId = 2;

foreach ($staff as $admin) {

    $conversation = \App\Models\Conversation::where(function ($q) use ($systemId, $admin) {
        $q->where('user_one', $systemId)
          ->where('user_two', $admin->id);
    })
    ->orWhere(function ($q) use ($systemId, $admin) {
        $q->where('user_one', $admin->id)
          ->where('user_two', $systemId);
    })
    ->first();

    if (!$conversation) {

        $conversation = \App\Models\Conversation::create([
            'user_one' => $systemId,
            'user_two' => $admin->id,
            'subject' => 'Staff Notifications',
            'last_message_at' => now(),
        ]);

    }

    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $systemId,
        'receiver_id' => $admin->id,
        'subject' => 'Contact Reply',
        'body' =>
"A guest contacted staff.\n\n".
"Email: {$contact->email}\n".
"Subject: {$contact->subject}\n\n".
"Open conversation:\n".
config('app.site_url').'/contactstaff/'.$contact->id,
        'is_read' => 0
    ]);

    $conversation->update([
        'last_message_at' => now()
    ]);

}

return back()->with('success','Reply sent.');

}

}