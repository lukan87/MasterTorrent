<?php

namespace App\Http\Middleware;

use App\Services\AchievementService;
use Closure;
use Illuminate\Http\Request;

class RecordAchievementVisit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Count deliberate page visits, including remembered sessions; polling is not a login day.
        if ($request->user() && $request->user()->last_login_streak_date !== now()->toDateString() && $request->isMethod('GET') && ! $request->ajax()
            && ! $request->expectsJson() && $response->isSuccessful()
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && ! in_array($request->headers->get('Sec-Fetch-Dest'), ['empty', 'image', 'script', 'style'], true)
            && ! str_contains(strtolower($request->headers->get('Purpose', '').' '.$request->headers->get('Sec-Purpose', '')), 'prefetch')) {
            app(AchievementService::class)->recordLoginDay((int) $request->user()->id);
        }

        return $response;
    }
}
