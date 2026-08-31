<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminUnlockMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = config('admin_lock.allowed_admin_ids', []);
        if (!in_array((int)$user->id, $allowed, true)) {
            abort(403);
        }

        // nu bloca pagina de unlock
        if ($request->is('admin/unlock', 'admin/unlock/*')) {
            return $next($request);
        }

        if (!$request->session()->get('admin_unlocked', false)) {
            return redirect()->route('admin.unlock.form');
        }

        return $next($request);
    }
}
