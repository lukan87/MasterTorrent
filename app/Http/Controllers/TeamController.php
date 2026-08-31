<?php

namespace App\Http\Controllers;

use App\Models\User; // Assuming staff are users in the 'users' table
use App\Models\UserClass;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        // Fetch users with class >= MODERATOR
        $staff = User::whereIn('user_class', [
            UserClass::OWNER,
            UserClass::ADMIN,
            UserClass::MODERATOR,
            UserClass::UPLOADER,
        ])->whereNotNull('user_class')->get();

        // Return a view with the staff data
        return view('team.index', compact('staff'));
    }
}

