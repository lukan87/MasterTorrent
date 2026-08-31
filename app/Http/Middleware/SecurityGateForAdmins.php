<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityGateForAdmins
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        $protectedIds = config('admin_lock.protected_ids', []);
        if (!in_array((int)$user->id, $protectedIds, true)) {
            return $next($request); // restul nu sunt afectati
        }

        // lasa pagina de unlock
        if ($request->is('security/unlock', 'security/unlock/*')) {
            return $next($request);
        }

        // daca nu a introdus codul in sesiunea curenta, nu il lasi sa foloseasca contul
        if (!$request->session()->get('security_unlocked', false)) {
            return redirect()->route('security.unlock.form');
        }

        return $next($request);
    }
}
