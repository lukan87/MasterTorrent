<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   // CheckPermission.php
public function handle($request, Closure $next, $permission)
{

    $user = Auth::user();

    // Check if the user is authenticated and has the permission
    if (!$user || !UserClass::userHasPermission($user->user_class, $permission)) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}

}
