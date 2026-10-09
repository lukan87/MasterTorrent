<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\UserClass;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['index', 'show', 'answer', 'resolve']);
    }

    private function staffOnly(): void
    {
        abort_unless(Auth::check() && Auth::user()->user_class >= UserClass::ADMIN, 403);
    }

    private function json(array $data)
    {
        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    public function index(Request $request)
    {
        $this->staffOnly();
        $contacts = Contact::withCount('messages')->latest('id')->paginate(25);
        if ($request->expectsJson()) {
            return $this->json(['html' => view('contactstaff.partials.list', compact('contacts'))->render()]);
        }
        return view('contactstaff.index', compact('contacts'));
    }

    public function show(Request $request, $id)
    {
        $this->staffOnly();
        $contact = Contact::findOrFail($id);
        if ($request->expectsJson()) return $this->activity($request, collect([$contact]));
        $contact->load(['messages' => fn ($query) => $query->with('staff:id,name')->orderBy('id')]);
        return view('contactstaff.show', compact('contact'));
    }

    public function create()
    {
        return view('contact.create');
    }

    public function store(Request $request)
    {
        abort_if($request->filled('website') || !$request->filled('js_token'), 403);
        $formTime = $request->input('form_time');
        if (!is_numeric($formTime) || (time() * 1000 - (float) $formTime) < 3000) {
            throw ValidationException::withMessages(['message' => 'Please take a moment to complete the form before sending.']);
        }
        $key = 'contact-form-'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['message' => 'Too many messages sent. Please wait a few minutes.']);
        }
        $data = $request->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|max:150',
            'subject' => 'required|string|max:150', 'message' => 'required|string|min:10|max:20000',
        ]);
        RateLimiter::hit($key, 300);
        $contact = DB::transaction(function () use ($data, $request) {
            $contact = Contact::create($data + ['ip' => $request->ip()]);
            $contact->messages()->create(['sender_type' => 'guest', 'message' => $data['message'], 'ip' => $request->ip()]);
            return $contact;
        });
        $request->session()->put('contact.email', $contact->email);
        $this->notifyStaff($contact, 'New Contact Request');
        if ($request->expectsJson()) return $this->json(['message' => 'Your request has been sent.', 'redirect' => route('contact.conversations')]);
        return redirect()->route('contact.conversations')->with('success', 'Your request has been sent.');
    }

    public function answer(Request $request, $id)
    {
        $this->staffOnly();
        $data = $request->validate(['reply' => 'required|string|min:3|max:20000']);
        DB::transaction(function () use ($id, $data) {
            $contact = Contact::lockForUpdate()->findOrFail($id);
            if ($contact->resolved) throw ValidationException::withMessages(['reply' => 'This conversation has been resolved.']);
            $contact->messages()->create(['sender_type' => 'staff', 'staff_id' => Auth::id(), 'message' => $data['reply']]);
        });
        return $request->expectsJson() ? $this->json(['message' => 'Reply sent.']) : back()->with('success', 'Reply sent.');
    }

    public function check()
    {
        return view('contact.check');
    }

    public function viewReply(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:150']);
        if (!Contact::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'No requests found. Use the email address you entered when contacting staff.']);
        }
        $request->session()->put('contact.email', $data['email']);
        if ($request->expectsJson()) return $this->json(['redirect' => route('contact.conversations')]);
        return redirect()->route('contact.conversations');
    }

    public function conversations(Request $request)
    {
        $email = $request->session()->get('contact.email');
        if (!$email) {
            if ($request->expectsJson()) abort(403);
            return redirect()->route('contact.check');
        }
        $contacts = Contact::where('email', $email)->latest('id')->get();
        if ($request->expectsJson()) return $this->activity($request, $contacts);
        $contacts->load(['messages' => fn ($query) => $query->with('staff:id,name')->orderBy('id')]);
        return view('contact.replies', compact('contacts'));
    }

    private function activity(Request $request, $contacts)
    {
        $data = $request->validate(['after' => 'nullable|integer|min:0']);
        $messages = ContactMessage::whereIn('contact_id', $contacts->pluck('id'))
            ->where('id', '>', $data['after'] ?? 0)->with('staff:id,name')->orderBy('id')->limit(100)->get();
        return $this->json([
            'html' => view('contact.partials.messages', compact('messages'))->render(),
            'contacts' => $contacts->map(fn ($contact) => ['id' => $contact->id, 'resolved' => (bool) $contact->resolved])->values(),
        ]);
    }

    public function resolve(Request $request, $id)
    {
        $this->staffOnly();
        DB::transaction(function () use ($id) {
            $contact = Contact::lockForUpdate()->findOrFail($id);
            $contact->resolved = true;
            $contact->save();
        });
        return $request->expectsJson() ? $this->json(['message' => 'Conversation resolved.']) : back()->with('success', 'Conversation resolved.');
    }

    public function guestReply(Request $request, $id)
    {
        $data = $request->validate(['message' => 'required|string|min:3|max:20000']);
        $email = $request->session()->get('contact.email');
        abort_unless($email, 403);
        $contact = DB::transaction(function () use ($id, $email, $data, $request) {
            $contact = Contact::where('email', $email)->lockForUpdate()->findOrFail($id);
            if ($contact->resolved) throw ValidationException::withMessages(['message' => 'This conversation has been resolved.']);
            $key = 'contact-reply-'.$request->ip();
            if (RateLimiter::tooManyAttempts($key, 10)) {
                throw ValidationException::withMessages(['message' => 'Please wait a few minutes before replying again.']);
            }
            RateLimiter::hit($key, 300);
            $contact->messages()->create(['sender_type' => 'guest', 'message' => $data['message'], 'ip' => $request->ip()]);
            return $contact;
        });
        $this->notifyStaff($contact, 'Contact Reply');
        return $request->expectsJson() ? $this->json(['message' => 'Reply sent.']) : back()->with('success', 'Reply sent.');
    }

    private function notifyStaff(Contact $contact, string $subject): void
    {
        User::whereIn('user_class', [UserClass::ADMIN, UserClass::OWNER, UserClass::WEB_DEVELOPER])
            ->select('id')->each(function ($admin) use ($contact, $subject) {
                SystemMessageService::send(2, $admin->id, $subject,
                    "A guest contacted staff.\n\nEmail: {$contact->email}\nSubject: {$contact->subject}\n\nOpen conversation:\n".route('contactstaff.show', $contact->id));
            });
    }
}
