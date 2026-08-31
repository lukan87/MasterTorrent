<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailController extends Controller
{

    public function index()
    {
        $emails = EmailLog::with('user')
            ->latest('sent_at')
            ->paginate(50);

        $subscribedCount = User::where('subscribed', true)->count();

    return view('admin.emails.index', compact('emails', 'subscribedCount'));
}


    public function create()
    {
        $templates = EmailTemplate::all();
        $classes = UserClass::getClasses();

        return view('admin.emails.create', compact('templates', 'classes'));
    }


public function send(Request $request)
{
    $request->validate([
        'subject' => 'required',
        'body' => 'required',
        'target' => 'required|in:subscribed,inactive'
    ]);

    $query = User::query()
        ->where('is_junk', false);


    if ($request->target === 'subscribed') {
        $query->where('subscribed', true);
    }
    elseif ($request->target === 'inactive') {
        $query->whereBetween('last_activity', [
            now()->subDays(90),
            now()->subDays(60),
        ]);
    }


    if ($request->filled('user_class')) {
        $query->whereIn('user_class', $request->user_class);
    }

    $users = $query->get();

    // 🚨 safety checks
    if ($users->count() === 0) {
        return back()->withErrors('No users match your filters.');
    }

    if ($users->count() > 5000) {
        return back()->withErrors('Too many users selected.');
    }

    if ($users->count() > 1000 && $request->confirm !== 'SEND') {
        return back()->withErrors('You must confirm large sends.');
    }

 foreach ($users as $user) {

    try {
       
       $body = $this->parse($request->body, $user);
       $subject = trim($this->parse($request->subject, $user));

      // dd($request->subject, $subject);


        Mail::raw($body, function ($message) use ($user, $subject) {
            $message->to($user->email)
                    ->subject($subject);
        });

       
        EmailLog::create([
            'user_id' => $user->id,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now(),
        ]);

    } catch (TransportExceptionInterface $e) {

        $user->update(['is_junk' => true]);

        \Log::warning("Marked as junk: {$user->email}");

        continue;
    }
}

    return redirect()->route('admin.emails.index')
        ->with('success', 'Emails sent!');
}

private function parse($text, $user)
{
    return str_replace(
        ['{name}', '{ name }', '{Name}', '{NAME}', '{email}', '{ email }'],
        [$user->name, $user->name, $user->name, $user->name, $user->email, $user->email],
        $text
    );
}

public function count(Request $request)
{
    if (!$request->target && empty($request->user_class)) {
        return response()->json(['count' => 0]);
    }

    $query = User::query();

    if ($request->target === 'subscribed') {
        $query->where('subscribed', true);
    }

   if ($request->target === 'inactive') {
    $query->whereBetween('last_activity', [
        now()->subDays(90),
        now()->subDays(60),
    ]);
}

    if (!empty($request->user_class)) {
        $query->whereIn('user_class', $request->user_class);
    }

    return response()->json([
        'count' => $query->count()
    ]);
}

public function destroy(EmailLog $email)
{
    $email->delete();

    return back()->with('success', 'Email log deleted.');
}

public function deleteOld(Request $request)
{
    $request->validate([
        'days' => 'required|integer|min:1'
    ]);

    $count = EmailLog::where('sent_at', '<', now()->subDays($request->days))->delete();

    return back()->with('success', "$count old emails deleted.");
}
}
