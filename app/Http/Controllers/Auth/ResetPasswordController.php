<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{

    public function showRecoveryForm()
    {
        return view('auth.recover-password'); // Adjust this path as needed
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the user with the matching hashed recovery code
        $user = User::whereNotNull('recovery_code')->first();

        // Check if the user exists and if the recovery code matches
        if (!$user || !Hash::check($request->recovery_code, $user->recovery_code)) {
            return back()->withErrors(['recovery_code' => 'Invalid recovery code.']);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
         // Do not nullify the recovery code; keep it forever
         // $user->recovery_code = null; // Comment this out or remove it
        $user->save();

        return redirect()->route('login')->with('status', 'Password has been updated!');
    }
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';
}
