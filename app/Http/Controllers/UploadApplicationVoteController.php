<?php

namespace App\Http\Controllers;

use App\Services\UploadApplicationService;
use Illuminate\Http\Request;

class UploadApplicationVoteController extends Controller
{
    public function __construct(private UploadApplicationService $applications)
    {
        $this->middleware('auth');
    }

    public function vote(Request $request, int $id)
    {
        $this->applications->authorizeReviewer($request->user());
        $data = $request->validate(['vote' => 'required|in:approve,reject']);
        $application = $this->applications->vote($request->user(), $id, $data['vote']);

        return back()->with('success', $application->isActive()
            ? 'Vote recorded. You may change your vote until a final decision is made.'
            : 'Vote recorded. The application was '.$application->status.' and the applicant was notified by private message.');
    }
}
