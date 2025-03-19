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
use Illuminate\Support\Facades\Log;

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
        $rules = [
            'name' => ['required', 'string', 'max:20', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:30', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'recovery_code' => ['required', 'string', 'min:6'],
        ];

        // If invite-only mode is enabled, validate the invite code
        if (config('app.invite_only')) {
            $rules['invite_code'] = ['required', 'string', 'exists:invites,invite_code,is_used,false,is_expired,false'];
        }

        return Validator::make($data, $rules);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) // Use Request here
    {
        $this->validator($request->all())->validate();

         // If invite-only mode is enabled, process the invite code
         $inviterId = null;
         if (config('app.invite_only')) {
             // Retrieve the invite details using the invite code
             $invite = Invite::where('invite_code', $request->invite_code)
                             ->where('is_used', false)
                             ->where('is_expired', false)
                             ->first();
 
             // If the invite code is invalid, return an error
             if (!$invite) {
                 return response()->redirectToRoute('register')->withErrors(['invite_code' => 'Invalid or expired invite code.']);
             }
 
             // Mark the invite as used
             $invite->update(['is_used' => true]);
 
             // Store the inviter's ID
             $inviterId = $invite->inviter_id;


              // Send a message to the owner about the deletion
        Message::create([
        'receiver_id' => $inviterId,  // The owner receives the message
        'subject' => 'Invite Used',
        'sender_id' => 2,  // The user deleting the torrent (usually admin or system)
        'body' => 'Your invite code  ' . $invite->invite_code . '  has been used by our new user - ' . $request->name ,
        'is_read' => false,  // Mark as unread initially
    ]);
         }

        // Create user with IP address
    $user = $this->create($request->all(), $request->ip(), $inviterId, $request->invite_code);

        // Log the user in after registration
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect($this->redirectTo);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @param  string  $ip
     * @return \App\Models\User
     */
    protected function create(array $data, string $ip, $inviterId = null, $inviteCode = null)
    {
   
        $passkey = bin2hex(random_bytes(16)); // Generate 32-char unique passkey
       
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'recovery_code' => Hash::make($data['recovery_code']),
            'user_class' => UserClass::USER,
            'IP' => $ip, // Use the IP passed from register method
            'acceptpm' => 'yes', // Set default value
            'title' => '', // Set default value
            'enabled' => 'yes', // Set default value
            'donor' => 'no', // Set default value
            'info' => '', // Set default value
            'passkey' => $passkey, // Set the generated passkey
            'invited_by' => $inviterId, // Store the inviter's ID if available
            'invite_code' => $inviteCode, // Store the actual invite code used
        ]);
    }

}
