<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class InvalidateSessionIfSecurityVersionChanged
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

$dbVersion = (int) ($user->security_version ?? 1);
$sessionVersion = (int) $request->session()->get('sv', 0);

// daca sesiunea e veche si nu are sv, o initializam (nu logout)
if ($sessionVersion === 0) {
    $request->session()->put('sv', $dbVersion);
    return $next($request);
}

if ($sessionVersion !== $dbVersion) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')
        ->withErrors('Your session has expired. Please log in again.');
}


        return $next($request);
    }
}
