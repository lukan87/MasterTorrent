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

        $subscribedCount = User::where('subscribed', true)->where('is_junk', false)->count();

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
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:100000',
            'target' => 'required|in:subscribed,inactive',
        ]);

        $query = $this->recipients($request);

        $users = $query->limit(5001)->get();

        // 🚨 safety checks
        if ($users->count() === 0) {
            return back()->withInput()->withErrors('No users match your filters.');
        }

        if ($users->count() > 5000) {
            return back()->withInput()->withErrors('Too many users selected.');
        }

        if ($users->count() > 1000 && $request->confirm !== 'SEND') {
            return back()->withInput()->withErrors('You must confirm large sends.');
        }

        $sent = 0;
        $failed = 0;
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

                $sent++;
            } catch (TransportExceptionInterface $e) {

                $failed++;
                report($e);

                continue;
            }
        }

        return redirect()->route('admin.emails.index')
            ->with($failed ? 'error' : 'success', "Sent {$sent} email(s); {$failed} failed.");
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
        return response()->json(['count' => $this->recipients($request)->count()]);
    }

    private function recipients(Request $request)
    {
        $request->validate([
            'target' => 'required|in:subscribed,inactive',
            'user_class' => 'nullable|array',
            'user_class.*' => 'integer|distinct|in:'.implode(',', array_keys(UserClass::getClasses())),
        ]);

        return User::query()->where('is_junk', false)
            ->when($request->target === 'subscribed', fn ($query) => $query->where('subscribed', true))
            ->when($request->target === 'inactive', fn ($query) => $query->whereBetween('last_activity', [now()->subDays(90), now()->subDays(60)]))
            ->when($request->filled('user_class'), fn ($query) => $query->whereIn('user_class', $request->input('user_class')));
    }

    public function destroy(EmailLog $email)
    {
        $email->delete();

        return back()->with('success', 'Email log deleted.');
    }

    public function deleteOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $count = EmailLog::where('sent_at', '<', now()->subDays($request->days))->delete();

        return back()->with('success', "$count old emails deleted.");
    }
}
