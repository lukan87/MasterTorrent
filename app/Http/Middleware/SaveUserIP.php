<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SaveUserIP
{
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $user->IP = $request->ip(); // Get the IP address
            $user->save(); // Save the updated IP address
        }

        return $next($request);
    }
}

