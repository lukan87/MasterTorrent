<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showRecoveryForm()
    {
        return view('auth.recover-password');
    }

    public function showResetForm(Request $request, string $token)
    {
        if (! config('auth.email_registration')) {
            return redirect()->route('password.recover');
        }

        return view('auth.email-reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function sendResetLink(Request $request)
    {
        if (! config('auth.email_registration')) {
            return redirect()->route('password.recover');
        }

        $request->validate(['email' => ['required', 'email']]);
        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['email' => 'We could not send the reset email. Please try again shortly.']);
        }

        return back()->with('status', 'If an account exists for this email, a password reset link has been sent. Check your inbox and spam folder.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (config('auth.email_registration')) {
            $request->validate(['token' => ['required', 'string']]);
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                fn (User $user, string $password) => $this->savePassword($user, $password)
            );
            if ($status !== Password::PASSWORD_RESET) {
                return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
            }
        } else {
            $request->validate(['recovery_code' => ['required', 'string']]);
            $user = User::where('email', $request->email)->first();
            if (! $user || ! $user->recovery_code || ! Hash::check($request->recovery_code, $user->recovery_code)) {
                return back()->withInput($request->only('email'))->withErrors(['recovery_code' => 'Invalid email or recovery code.']);
            }
            $this->savePassword($user, $request->password);
        }

        // Resetting a password never activates an account or logs the user in.
        return redirect()->route('login')->with('status', 'Your password has been updated. You can now log in with your new password.');
    }

    private function savePassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
    }
}
