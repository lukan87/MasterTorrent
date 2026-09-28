<?php

namespace App\Http\Controllers;

use App\Services\UploadApplicationService;
use Illuminate\Http\Request;

class UploadApplicationCommentController extends Controller
{
    public function __construct(private UploadApplicationService $applications)
    {
        $this->middleware('auth');
    }

    public function store(Request $request, int $id)
    {
        $this->applications->authorizeReviewer($request->user());
        $data = $request->validate(['comment' => 'required|string|max:5000']);
        $this->applications->comment($request->user(), $id, $data['comment']);

        return redirect()->route('uploadapps.show', $id)->with('success', 'Staff comment added.');
    }
}
