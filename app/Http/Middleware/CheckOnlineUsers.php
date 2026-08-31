<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class CheckOnlineUsers
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // Update the user's last activity in the database
            $user = Auth::user();
            $user->last_activity = now(); // Assuming you have a `last_activity` column in users table
            $user->save();
        }

        return $next($request);
    }
}
