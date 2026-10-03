<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ActivateAccount;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function notice()
    {
        return view('auth.activation');
    }

    public function activate(Request $request, string $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('activation.notice')->withErrors([
                'email' => 'This activation link is invalid or has expired. Request a new one below.',
            ]);
        }

        $user = User::find($id);
        if (! $user || ! hash_equals(sha1($user->email), $hash)) {
            return redirect()->route('activation.notice')->withErrors(['email' => 'This activation link is invalid.']);
        }

        // Atomic and idempotent: a used link cannot re-enable a disabled account.
        User::whereKey($id)->where('activation_pending', true)->update([
            'activation_pending' => false,
            'email_verified_at' => now(),
            'enabled' => 'yes',
        ]);

        return redirect()->route('login')->with('status', 'Email confirmed. You can now log in if your account is enabled.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $request->email)->where('activation_pending', true)->first();
        try {
            $user?->notify(new ActivateAccount);
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['email' => 'We could not send the email. Please try again shortly.']);
        }

        return back()->with('status', 'If an account is waiting for activation at this address, we have sent a new link. Check your inbox and spam folder.');
    }
}
