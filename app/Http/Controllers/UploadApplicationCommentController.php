<?php

namespace App\Http\Controllers;

use App\Models\UploadApplicationComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadApplicationCommentController extends Controller
{

    public function store(Request $request, $id)
    {

        $request->validate([
            'comment' => 'required|string'
        ]);

        UploadApplicationComment::create([
            'application_id' => $id,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);

        return back();
    }

}