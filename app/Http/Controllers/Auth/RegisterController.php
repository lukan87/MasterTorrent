<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\UserClass;
use App\Models\Invite;  // Import the Invite model
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;


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
        'invite_code' => config('app.invite_only') ? ['required', 'string'] : ['nullable'],
    ]);
}

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
//     public function register(Request $request) // Use Request here
//     {
//         $this->validator($request->all())->validate();

//          // If invite-only mode is enabled, process the invite code
//          $inviterId = null;
//          $invite = null;

//          if (config('app.invite_only')) {
//              // Retrieve the invite details using the invite code
//              $invite = Invite::where('invite_code', $request->invite_code)
//                              ->where('is_used', false)
//                              ->where('is_expired', false)
//                              ->first();
 
//              // If the invite code is invalid, return an error
//              if (!$invite) {
//                  return response()->redirectToRoute('register')->withErrors(['invite_code' => 'Invalid or expired invite code.']);
//              }
 
//              // Mark the invite as used
//              $invite->update(['is_used' => true]);
 
//              // Store the inviter's ID
//              $inviterId = $invite->inviter_id;


//               // Send a message to the owner about the invited user
//         Message::create([
//         'receiver_id' => $inviterId,  // The owner receives the message
//         'subject' => 'Invite Used',
//         'sender_id' => 2,  // The user deleting the torrent (usually admin or system)
//         'body' => 'Your invite code  ' . $invite->invite_code . '  has been used by our new user - ' . $request->name ,
//         'is_read' => false,  // Mark as unread initially
//     ]);
//          }

//         // Create user with IP address
//     $user = $this->create($request->all(), $request->ip(), $inviterId, $request->invite_code, $request->timezone);

// // 🔥 GENERATE VERIFICATION TOKEN
// $token = Str::random(64);

// $user->remember_token  = $token;
// $user->enabled = 'no'; // disable until verified
// $user->save();

// // 🔥 SEND EMAIL (WORKS WITH YOUR POSTMARK SETUP)
// Mail::raw(
//     "Welcome!\n\nClick the link below to verify your account:\n\n" . url("/verify/$token"),
//     function ($message) use ($user) {
//         $message->to($user->email)
//                 ->subject('Verify your account');
//     }
// );

// // ❌ DO NOT AUTO LOGIN
// // \Auth::login($user);

// return redirect('/login')->with('status', 'Check your email to verify your account.');
//     }



public function register(Request $request)
{
    $validator = $this->validator($request->all());

    if ($validator->fails()) {
        return redirect()
            ->route('register')
            ->withErrors($validator)
            ->withInput();
    }

    $inviterId = null;
    $invite = null;

    // Handle invite-only mode
    if (config('app.invite_only')) {
        $invite = Invite::where('invite_code', $request->invite_code)
            ->where('is_used', false)
            ->where('is_expired', false)
            ->first();

        if (!$invite) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'invite_code' => 'Invalid or expired invite code.'
                ])
                ->withInput();
        }

        $inviterId = $invite->inviter_id;
    }

    // Create user
    $user = $this->create(
        $request->all(),
        $request->ip(),
        $inviterId,
        $request->invite_code ?? null,
        $request->input('timezone') ?? $request->input('detected_timezone')
    );

    // Enable the account immediately
    $user->enabled = 'yes';
    $user->downloadpos = 'yes';
    $user->remember_token = null;
    $user->save();

    // Mark invite as used
    if ($invite) {
        $invite->update([
            'is_used' => true
        ]);

        $this->sendSystemMessage(
            $inviterId,
            'Invite Used',
            "
[b][color=green]INVITE USED[/color][/b]

Your invite code [b]{$invite->invite_code}[/b] has been used.

New user: [b]{$request->name}[/b]

Welcome them to the site 👍
"
        );
    }

    // Automatically log the new user in
    Auth::login($user);

    // Regenerate session ID for security
    $request->session()->regenerate();

    return redirect('/');
}



    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @param  string  $ip
     * @return \App\Models\User
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
