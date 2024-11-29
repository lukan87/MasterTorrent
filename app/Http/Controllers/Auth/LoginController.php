<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

     /**
     * Override the login attempt method to include ban and failed attempts logic.
     */
    public function login(Request $request)
    {
        // Validate the username and password input fields
        $request->validate([
            'name' => 'required|string',  // Assuming 'name' is your username field
            'password' => 'required|string',
        ]);

        // Retrieve the user by username (name)
        $user = User::where('name', $request->name)->first();

        // If the user doesn't exist
    if (!$user) {
        return redirect()->back()->withErrors([
            'Invalid credentials. Please check your username and password.',
        ]);
    }

          // Check if the account is disabled
    if ($user->enabled === 'no') {
        return redirect()->back()->withErrors([
            'Your account has been disabled.',
        ]);
    }



        // Attempt to log the user in using the username ('name') instead of email
        if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
            // Reset failed attempts and banned_until on successful login
            if ($user) {
                $user->failed_attempts = 0;
                $user->banned_until = null; // Reset the banned time
                $user->save();
            }
            return redirect()->intended($this->redirectPath());
        }

        if ($user && $user->banned_until && Carbon::parse($user->banned_until)->isFuture()) {
            // If the user is banned and the ban time has not expired, deny the login attempt
            return redirect()->back()->withErrors(['Your account is banned until ' . Carbon::parse($user->banned_until)->format('d-m-Y H:i:s')]);
        }

        // If login failed, increment the failed attempts counter
        if ($user) {
            // Increment failed attempts
            $user->failed_attempts++;
            $remainingAttempts = max(0, 5 - $user->failed_attempts);

            // Ban the user if they've reached the max failed attempts
            if ($user->failed_attempts >= 5) {
                $user->banned_until = Carbon::now()->addHours(12);
                $user->save();

                return redirect()->back()->withErrors([
                    'Your account has been banned due to multiple failed login attempts.',
                ]);
            }

            $user->save();
        }

        return redirect()->back()->withErrors([
            'Invalid credentials. You have ' . $remainingAttempts . ' attempts remaining.',
        ]);
    }




}
