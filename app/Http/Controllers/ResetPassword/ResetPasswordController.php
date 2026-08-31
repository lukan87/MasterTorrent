<?php

namespace App\Http\Controllers\ResetPassword;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    // Show the password recovery form
    public function showRecoveryForm()
    {
        return view('auth.recover-password'); // Make sure this view exists
    }

    // Handle the password recovery request
    public function updatePassword(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'recovery_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the user with the provided email
        $user = User::where('email', $request->email)->first();

        // Validate if user exists and recovery code matches
        if (!$user || !Hash::check($request->recovery_code, $user->recovery_code ?? '')) {
            return back()->withErrors(['recovery_code' => 'Invalid email or recovery code.']);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Flash a success message to the session
    session()->flash('status', 'Your password has been updated successfully!');

        return redirect()->route('login')->with('status', 'Password has been updated!');
    }
}
