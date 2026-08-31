<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminUnlockController extends Controller
{
    public function form()
    {
        return view('admin.unlock');
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

        $allowed = config('admin_lock.allowed_admin_ids', []);
        if (!in_array((int)$user->id, $allowed, true)) {
            abort(403);
        }

        // protectie anti brute-force
        $key = 'admin-unlock:' . $user->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors('Prea multe incercari. Incearca mai tarziu.');
        }

        $codes = config('admin_lock.codes', []);
        $expectedCode = $codes[(int)$user->id] ?? null;

        if (!$expectedCode) {
            abort(403, 'Cod lipsa pentru acest admin.');
        }

        if ($request->input('code') !== $expectedCode) {
            RateLimiter::hit($key, 60);
            return back()->withErrors('Cod invalid.');
        }

        RateLimiter::clear($key);

        // marcheaza sesiunea ca deblocata
        $request->session()->put('admin_unlocked', true);
        $request->session()->put('admin_unlocked_at', now()->timestamp);

        return redirect()->intended('/admin');
    }
}
