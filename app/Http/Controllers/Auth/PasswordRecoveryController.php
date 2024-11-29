<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordRecoveryController extends Controller
{
    // Show the password recovery form
    public function showRecoveryForm()
    {
        return view('auth.recover-password'); // Path to your recovery form view
    }

    // Handle the password recovery request
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'recovery_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the user by email and check recovery code
        $user = User::where('email', $request->email)
            ->whereNotNull('recovery_code') // Check that the recovery code exists
            ->first();

        // Validate user and recovery code
        if (!$user || !Hash::check($request->recovery_code, $user->recovery_code)) {
            return back()->withErrors(['recovery_code' => 'Invalid email or recovery code.']);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('status', 'Password has been updated!');
    }
}
