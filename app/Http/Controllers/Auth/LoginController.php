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
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Maximum login attempts before banning the user.
     */
    const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Ban duration in hours.
     */
    const BAN_DURATION_HOURS = 12;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle login attempts with custom logic for bans and failed attempts.
     */
    public function login(Request $request)
    {
        // Validate the input fields
        $request->validate([
            'name' => 'required|string', // Assuming 'name' is the username field
            'password' => 'required|string',
        ]);

        // Retrieve the user by username
        $user = User::where('name', $request->name)->first();

        // Handle non-existent user
        if (!$user) {
            return redirect()->back()->withErrors(['Invalid credentials. Please check your username and password.']);
        }

        // Check if the account is disabled
        if ($user->enabled === 'no') {
            return redirect()->back()->withErrors(['Your account has been disabled.']);
        }

        // Check if the user is banned
        if ($user->isBanned()) {
            return redirect()->back()->withErrors([
                'Your account is banned until ' . $user->banned_until->format('d-m-Y H:i:s'),
            ]);
        }

        // Attempt to authenticate the user
        if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
            
            // Reset failed attempts and clear any bans on successful login
            $user->resetFailedAttempts();
            $user->IP = $request->ip();
            $user->save();
            return redirect()->intended($this->redirectTo);
        }

        // Increment failed login attempts
        $user->incrementFailedAttempts(self::MAX_FAILED_ATTEMPTS, self::BAN_DURATION_HOURS);

        $remainingAttempts = max(0, self::MAX_FAILED_ATTEMPTS - $user->failed_attempts);

        return redirect()->back()->withErrors([
            'Invalid credentials. You have ' . $remainingAttempts . ' attempts remaining.',
        ]);
    }
}
