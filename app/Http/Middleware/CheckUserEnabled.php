<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->enabled === 'no') {
            Auth::logout();  // Optionally log the user out if they are not enabled
            return redirect()->route('login'); // Redirect to login page
        }

        return $next($request);
    }
}
