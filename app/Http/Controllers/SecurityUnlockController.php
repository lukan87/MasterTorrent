<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class SecurityUnlockController extends Controller
{
    public function form()
    {
        return view('security.unlock');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $protectedIds = config('admin_lock.protected_ids', []);
        if (!in_array((int)$user->id, $protectedIds, true)) {
            abort(403);
        }

        // anti brute-force
        $key = 'sec-unlock:' . (int)$user->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors('Prea multe incercari. Incearca mai tarziu.');
        }

        $codes = config('admin_lock.codes', []);
        $expected = $codes[(int)$user->id] ?? null;

        if (!$expected) {
            abort(403, 'Cod lipsa pentru acest admin.');
        }

        if ($request->input('code') !== $expected) {
            RateLimiter::hit($key, 60);
            return back()->withErrors('Cod invalid.');
        }

        RateLimiter::clear($key);

        $request->session()->put('security_unlocked', true);

        return redirect()->intended('/');
    }
}
