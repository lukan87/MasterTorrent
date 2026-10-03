<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailPreferenceController extends Controller
{
    public function update(Request $request)
    {
        $request->validate(['subscribed' => ['required', 'boolean']]);

        // Preferences always belong to the signed-in member, never a supplied ID.
        $user = $request->user();
        $user->subscribed = $request->boolean('subscribed');
        $user->save();

        $message = $user->subscribed
            ? 'You subscribed to our email service. You will receive marketing and news emails from us.'
            : 'You unsubscribed from our email service. You will not receive any marketing or news emails about our site.';

        if ($request->expectsJson()) {
            return response()->json(['subscribed' => (bool) $user->subscribed, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
