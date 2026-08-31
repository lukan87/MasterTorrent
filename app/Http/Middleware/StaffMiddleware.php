<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403);
        }

        // Example: class >= 5 is staff
        if (auth()->user()->user_class < 6) {
            abort(403, 'Staff only.');
        }

        return $next($request);
    }
}