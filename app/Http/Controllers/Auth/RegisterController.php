<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserClass;
use Illuminate\Foundation\Auth\RegistersUsers;
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
            'name' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'recovery_code' => ['required', 'string', 'min:6'],
        ]);
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

        // Create user with IP address
        $user = $this->create($request->all(), $request->ip());

        // Log the user in after registration
        auth()->login($user);

        return redirect($this->redirectTo);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @param  string  $ip
     * @return \App\Models\User
     */
    protected function create(array $data, string $ip) // Accept IP here
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
        ]);
    }

}
