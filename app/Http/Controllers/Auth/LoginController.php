<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected $redirectTo = '/';

    /**
     * Maximum login attempts before banning the user.
     */
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * Ban duration in hours.
     */
    private const BAN_DURATION_HOURS = 12;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle login using either username or email address.
     */
    public function login(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Username or email
        |--------------------------------------------------------------------------
        |
        | Keep the form field named "name" so the existing login Blade does not
        | need to change structurally. The value may now contain either the
        | user's username or their registered email address.
        |
        */

        $login = trim((string) $request->input('name'));

        $user = User::withTrashed()
            ->where(function ($query) use ($login) {
                $query->where('name', $login)
                    ->orWhere('email', $login);
            })
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Account not found
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return back()
                ->withInput($request->only('name'))
                ->withErrors([
                    'name' => 'Invalid credentials. Please check your username/email and password.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Deleted account
        |--------------------------------------------------------------------------
        */

        if ($user->trashed()) {
            $deletedBy = optional($user->deletedBy)->name ?? 'staff';

            return back()
                ->withInput($request->only('name'))
                ->withErrors([
                    'name' =>
                        'This account was deleted ' .
                        $user->deleted_at->diffForHumans() .
                        " by {$deletedBy}. If you believe this was a mistake, " .
                        'please contact the staff team.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Disabled account
        |--------------------------------------------------------------------------
        */

        if ($user->enabled === 'no' || $user->activation_pending) {
            return back()
                ->withInput($request->only('name'))
                ->withErrors([
                    'name' => $user->activation_pending
                        ? 'Please activate your account using the link in your email. You can request a new activation email below.'
                        : 'Your account has been disabled.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Banned account
        |--------------------------------------------------------------------------
        */

        if ($user->isBanned()) {
            return back()
                ->withInput($request->only('name'))
                ->withErrors([
                    'name' =>
                        'Your account is banned until ' .
                        $user->banned_until->format('d-m-Y H:i:s'),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Determine authentication field
        |--------------------------------------------------------------------------
        |
        | We already found the exact user above. Authenticate against that
        | user's username or email depending on what was entered.
        |
        */

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'name';

        /*
        |--------------------------------------------------------------------------
        | Authenticate
        |--------------------------------------------------------------------------
        */

        if (Auth::attempt([
            $field => $login,
            'password' => $request->input('password'),
        ], $request->boolean('remember'))) {

            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Successful login
            |--------------------------------------------------------------------------
            */

            $user->resetFailedAttempts();

            $user->IP = $request->ip();
            $user->save();

            app(\App\Services\AchievementService::class)->recordLoginDay((int) $user->id);

            return $this->redirectAfterLogin($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Failed login
        |--------------------------------------------------------------------------
        */

        $user->incrementFailedAttempts(
            self::MAX_FAILED_ATTEMPTS,
            self::BAN_DURATION_HOURS
        );

        $user->refresh();

        $remainingAttempts = max(
            0,
            self::MAX_FAILED_ATTEMPTS - (int) $user->failed_attempts
        );

        return back()
            ->withInput($request->only('name'))
            ->withErrors([
                'name' =>
                    'Invalid credentials. You have ' .
                    $remainingAttempts .
                    ' attempts remaining.',
            ]);
    }

    protected function redirectAfterLogin(Request $request): RedirectResponse
    {
        $intended = $request->session()->get('url.intended');

        // Repair destinations saved by announcement polling before the auth fix.
        if (is_string($intended) && parse_url($intended, PHP_URL_PATH) === '/announcements-unread-count') {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended($this->redirectTo);
    }

}