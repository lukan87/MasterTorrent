<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\User;
use App\Models\UserClass;
use App\Services\InviteService;  // Import the Invite model
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:20', 'unique:users,name'],
            'email' => ['required', 'string', 'email', 'max:30', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'recovery_code' => ['required', 'string', 'min:6'],
            'invite_code' => [config('app.invite_only') ? 'required' : 'nullable', 'string', 'max:255'],
        ]);
    }

    public function register(Request $request, InviteService $invites)
    {
        $this->validator($request->all())->validate();

        $user = $invites->register($request->input('invite_code'), function (?Invite $invite) use ($request) {
            $user = $this->create(
                $request->all(),
                $request->ip(),
                $invite?->inviter_id,
                $invite?->invite_code,
                $request->input('timezone') ?? $request->input('detected_timezone')
            );
            $user->enabled = 'yes';
            $user->downloadpos = 'yes';
            $user->remember_token = null;
            $user->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data, string $ip, $inviterId = null, $inviteCode = null, $timezone = null)
    {

        $passkey = bin2hex(random_bytes(16)); // Generate 32-char unique passkey

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'recovery_code' => Hash::make($data['recovery_code']),
            'user_class' => UserClass::USER,
            'IP' => $ip,
            'acceptpm' => 'yes',
            'title' => '',
            'enabled' => 'no',
            'donor' => 'no',
            'info' => '',
            'passkey' => $passkey,
            'invited_by' => $inviterId,
            'invite_code' => $inviteCode,
            'timezone' => $timezone,
            'subscribed' => isset($data['subscribed']),
        ]);
    }
}
