<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Torrent\UploadPermission;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class UploadApiAccess
{
    public function handle(Request $request, Closure $next, string $scope)
    {
        $user = $request->user();
        abort_unless($request->bearerToken() && $user instanceof User
            && $user->currentAccessToken() instanceof PersonalAccessToken, 401, 'A personal bearer token is required.');
        abort_unless(app(UploadPermission::class)->active($user), 403, 'Account access is restricted.');
        abort_unless($user->tokenCan($scope), 403, 'The token does not permit this action.');

        return $next($request);
    }
}
