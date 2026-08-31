<?php

namespace App\Http\Controllers;

use App\Models\UploadApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SystemMessageService;
use Illuminate\Support\Facades\DB;

class UploadApplicationController extends Controller
{

    public function index()
    {
        if (Auth::user()->user_class >= 7) {

            $applications = UploadApplication::with('applicant')
                ->latest()
                ->paginate(25);

        } else {

            $applications = UploadApplication::where('applicant_id', Auth::id())
                ->latest()
                ->get();
        }

        return view('uploadapps.index', compact('applications'));
    }


public function create()
{
    $user = auth()->user();

    $eligibility = $user->canApplyForUploader();

    if (!$eligibility['allowed']) {
        return redirect()
            ->route('uploadapps.index')
            ->with('error', $eligibility['reason']);
    }

    $cooldown = $user->uploaderApplicationCooldown();

    if ($cooldown['blocked']) {
        return redirect()
            ->route('uploadapps.index')
            ->with('error', "You must wait {$cooldown['days_remaining']} days before applying again.");
    }

    $existing = UploadApplication::where('applicant_id', $user->id)
        ->whereIn('status', ['pending','discussion','voting'])
        ->exists();

    if ($existing) {
        return redirect()
            ->route('uploadapps.index')
            ->with('error','You already have an active application.');
    }

    return view('uploadapps.create');
}


public function store(Request $request)
{
    $request->validate([
        'why_promoted' => 'required|string',
        'experience' => 'required|string',
        'content_plan' => 'required|string',
        'internal_speed' => 'nullable|url',
        'external_speed' => 'nullable|url'
    ]);

    $application = UploadApplication::create([
        'applicant_id' => auth()->id(),
        'why_promoted' => $request->why_promoted,
        'experience' => $request->experience,
        'content_plan' => $request->content_plan,
        'internal_speed' => $request->internal_speed,
        'external_speed' => $request->external_speed,
        'external_sites' => $request->external_sites,
        'scene_access' => $request->scene_access,
        'know_torrents' => $request->know_torrents,
        'understand_seeding' => $request->understand_seeding
    ]);

    /*
    |--------------------------------------------------------------------------
    | Notify Staff
    |--------------------------------------------------------------------------
    */

    $staff = User::where('user_class', '>=', 6)->get();

    foreach ($staff as $staffMember) {

        SystemMessageService::send(
            config('tracker.system_user_id', 2),
            $staffMember->id,
            'New Uploader Application',
            "A new uploader application has been submitted by {$application->applicant->name}.

Please review the application and cast your vote.

Application ID: {$application->id}

View the application here:
" . route('uploadapps.show', $application->id)
        );

    }

    return redirect()
        ->route('uploadapps.index')
        ->with('success', 'Application submitted successfully.');
}


    public function show($id)
    {
        $application = UploadApplication::with([
            'applicant',
            'comments.user',
            'votes.user'
        ])->findOrFail($id);

        if (
            Auth::id() !== $application->applicant_id &&
            Auth::user()->user_class < 7
        ) {
            abort(403);
        }

        return view('uploadapps.show', compact('application'));
    }


   public function accept($id)
{
    $application = UploadApplication::with('applicant')->findOrFail($id);

    if ($application->status === 'accepted' || $application->status === 'rejected') {
        return back()->with('error', 'This application has already been decided.');
    }

    DB::transaction(function () use ($application) {

        $application->update([
            'status' => 'accepted',
            'reviewed_by' => auth()->id(),
            'decision_at' => now()
        ]);

         $user = $application->applicant;
         $user->user_class = 5;
         $user->save();

        SystemMessageService::send(
            2, // system user
            $application->applicant_id,
            'Uploader Application Accepted',
            'Congratulations! Your uploader application has been accepted.

You are now an uploader. Please follow the upload rules and seed your torrents properly.'
        );

    });

    return back()->with('success', 'Application accepted.');
}


public function reject($id)
{
    $application = UploadApplication::with('applicant')->findOrFail($id);

    if ($application->status === 'accepted' || $application->status === 'rejected') {
        return back()->with('error', 'This application has already been decided.');
    }

    DB::transaction(function () use ($application) {

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'decision_at' => now()
        ]);

        SystemMessageService::send(
            2, // system user
            $application->applicant_id,
            'Uploader Application Rejected',
            'Unfortunately your uploader application has been rejected.

You may review the uploader requirements and apply again after the cooldown period.'
        );

    });

    return back()->with('success', 'Application rejected.');
}

}