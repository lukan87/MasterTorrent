<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShowDetailedErrors
{
    public function handle(Request $request, Closure $next)
    {
        // Only show detailed errors for admins
        if (auth()->check() && auth()->user()->user_class > 7) {
            config(['app.debug' => true]);  // Enable detailed errors
        } else {
            config(['app.debug' => false]); // Disable detailed errors for others
        }

        return $next($request);
    }
}

