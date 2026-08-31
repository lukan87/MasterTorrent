<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostmarkController extends Controller
{
    public function bounce(Request $request)
    {
        $email = $request->input('Email');
        $type = $request->input('Type');

        if ($email) {
            DB::table('users')
                ->where('email', $email)
                ->update([
                    'email_bounced' => true,
                    'email_bounce_type' => $type,
                ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
