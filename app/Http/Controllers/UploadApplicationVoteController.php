<?php

namespace App\Http\Controllers;

use App\Models\UploadApplication;
use App\Models\UploadApplicationVote;
use App\Services\SystemMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadApplicationVoteController extends Controller
{

    public function vote(Request $request, $id)
    {

        if (Auth::user()->user_class < 7) {
            abort(403);
        }

        $request->validate([
            'vote' => 'required|in:approve,reject'
        ]);

        $application = UploadApplication::findOrFail($id);

        if ($application->status === 'accepted' || $application->status === 'rejected') {
            return back()->with('error','Application already decided.');
        }

        // Store or update vote
        UploadApplicationVote::updateOrCreate(
            [
                'application_id' => $id,
                'user_id' => Auth::id()
            ],
            [
                'vote' => $request->vote
            ]
        );

        // Count votes
        $votesFor = UploadApplicationVote::where('application_id',$id)
            ->where('vote','approve')
            ->count();

        $votesAgainst = UploadApplicationVote::where('application_id',$id)
            ->where('vote','reject')
            ->count();

        $application->update([
            'votes_for' => $votesFor,
            'votes_against' => $votesAgainst,
            'status' => 'voting'
        ]);

        /*
        |---------------------------------------
        | Automatic Decision
        |---------------------------------------
        */

if ($votesFor >= 3) {

    $application->update([
        'status' => 'accepted',
        'reviewed_by' => Auth::id(),
        'decision_at' => now()
    ]);

    /*
    |--------------------------------------------------------------------------
    | Promote user to uploader
    |--------------------------------------------------------------------------
    */

    $user = $application->applicant;
    $user->user_class = 5;
    $user->save();

    /*
    |--------------------------------------------------------------------------
    | Send conversation message
    |--------------------------------------------------------------------------
    */

    SystemMessageService::send(
        config('tracker.system_user_id', 2),
        $application->applicant_id,
        'Uploader Application Accepted',
        "Congratulations! Your uploader application has been accepted.

You are now an uploader. Please follow the upload rules and seed your torrents properly."
    );

}


if ($votesAgainst >= 3) {

    $application->update([
        'status' => 'rejected',
        'reviewed_by' => Auth::id(),
        'decision_at' => now()
    ]);

    /*
    |--------------------------------------------------------------------------
    | Send rejection message
    |--------------------------------------------------------------------------
    */

    SystemMessageService::send(
        config('tracker.system_user_id', 2),
        $application->applicant_id,
        'Uploader Application Rejected',
        "Unfortunately your uploader application has been rejected.

You may review the uploader requirements and apply again after the cooldown period."
    );

}

        return back()->with('success','Vote recorded.');
    }

}