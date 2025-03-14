<?php


namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Warning;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Http\Request;


class WarningController extends Controller
{


    public function index(Request $request)
{
    if ($request->user()->user_class < 6) {
        abort(403);
    }

    // Get users who have warnings
    $usersWithWarnings = User::whereHas('warnings')->withCount('warnings')->paginate(25);

    return view('warnings.index', [
        'usersWithWarnings' => $usersWithWarnings,
    ]);
}

    public function show(Request $request, $id, $username = null)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // If username is missing OR incorrect, redirect **only if necessary**
        if ($username !== $user->username) {
            return redirect()->route('warnings.show', ['id' => $user->id, 'username' => $user->username], 301);
        }
    
        // Fetch warnings
        $warnings = Warning::where('user_id', $user->id)
        ->with(['torrenttitle', 'warneduser', 'history'])  // Eager load the 'user' relationship
        ->latest('active')
        ->paginate(25);
        $warningcount = Warning::where('user_id', $user->id)->count();
    
        // Fetch soft deleted warnings
        $softDeletedWarnings = Warning::where('user_id', $user->id)->with(['torrenttitle', 'warneduser'])->latest('created_at')->onlyTrashed()->paginate(25);
        $softDeletedWarningCount = Warning::where('user_id', $user->id)->onlyTrashed()->count();

       // Fetch history for the user's torrents
       $historyData = History::whereIn('torrent_id', $warnings->pluck('torrent')->pluck('id'))
       ->where('user_id', $user->id)
       ->get();

   // Map history data by torrent_id for easy access in the view
   $historyMap = $historyData->keyBy('torrent_id');
    
        return view('warnings.warninglog', [
            'warnings'                => $warnings,
            'warningcount'            => $warningcount,
            'softDeletedWarnings'     => $softDeletedWarnings,
             'softDeletedWarningCount' => $softDeletedWarningCount,
           'historyMap' => $historyMap,
            'user'                    => $user,
        ]);
    }
    
    
    

  
    public function deactivate(Request $request, $id)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    
        $staff = $request->user();
        $warning = Warning::findOrFail($id);
        $warning->expires_on = Carbon::now();
        $warning->active = 0;
        $warning->save();

        // Send Private Message
        $privateMessage = new Message();
        $privateMessage->sender_id = 2;
        $privateMessage->receiver_id = $warning->user_id;
        $privateMessage->subject = 'Hit and Run Warning Deactivated';
        $privateMessage->body = $staff->username.' has decided to deactivate your active warning for torrent '.$warning->torrent.' You lucked out! [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
        $privateMessage->is_read = 0;
        $privateMessage->save();

        return redirect()->route('warnings.show', ['id' => $warning->warneduser->id, 'username' => $warning->warneduser->username])
    ->withSuccess('Warning Was Successfully Deactivated');

    }

   
    public function deactivateAllWarnings(Request $request, $id, $username)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    
        $staff = $request->user();
        $user = User::where('username', '=', $username)->firstOrFail();
    
        $warnings = Warning::where('user_id', '=', $user->id)->get();
    
        foreach ($warnings as $warning) {
            $warning->expires_on = Carbon::now();
            $warning->active = 0;
            $warning->save();
        }
    
        // Send Private Message
        $privateMessage = new Message();
        $privateMessage->sender_id = $staff->id;
        $privateMessage->receiver_id = $user->id;
        $privateMessage->subject = 'All Hit and Run Warning Deactivated';
        $privateMessage->body = $staff->username.' has decided to deactivate all of your active hit and run warnings. You lucked out! [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
        $privateMessage->is_read = 0;
        $privateMessage->save();
    
        return redirect()->route('warnings.show', ['id' => $user->id, 'username' => $user->username])
            ->withSuccess('All Warnings Were Successfully Deactivated');
    }
    

   
    public function deleteWarning(Request $request, $id)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    

        $staff = $request->user();
        $warning = Warning::findOrFail($id);

        // Send Private Message
        $privateMessage = new Message();
        $privateMessage->sender_id = $staff->id;
        $privateMessage->receiver_id = $warning->user_id;
        $privateMessage->subject = 'Hit and Run Warning Deleted';
        $privateMessage->body = $staff->username.' has decided to delete your warning for torrent '.$warning->torrent.' You lucked out! [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
        $privateMessage->is_read = 0;
        $privateMessage->save();

        $warning->deleted_by = $staff->id;
        $warning->save();
        $warning->delete();

        return redirect()->route('warnings.show', ['id' => $warning->warneduser->id, 'username' => $warning->warneduser->username])
        ->withSuccess('Warning Was Successfully Deleted');
    
    }

   
    public function deleteAllWarnings(Request $request,  $id, $username)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    

        $staff = $request->user();
        $user = User::where('username', '=', $username)->firstOrFail();

        $warnings = Warning::where('user_id', '=', $user->id)->get();

        foreach ($warnings as $warning) {
            $warning->deleted_by = $staff->id;
            $warning->save();
            $warning->delete();
        }

        // Send Private Message
        $privateMessage = new Message();
        $privateMessage->sender_id = $staff->id;
        $privateMessage->receiver_id = $user->id;
        $privateMessage->subject = 'All Hit and Run Warnings Deleted';
        $privateMessage->body = $staff->username.' has decided to delete all of your warnings. You lucked out! [color=red][b]THIS IS AN AUTOMATED SYSTEM MESSAGE, PLEASE DO NOT REPLY![/b][/color]';
        $privateMessage->is_read = 0;
        $privateMessage->save();

        return redirect()->route('warnings.show', ['id' => $warning->warneduser->id, 'username' => $warning->warneduser->username])
            ->withSuccess('All Warnings Were Successfully Deleted');
    }

   
    public function restoreWarning(Request $request, $id)
    {
        if ($request->user()->user_class < 6) {
            abort(403);
        }
    

        $staff = $request->user();
        $warning = Warning::withTrashed()->findOrFail($id);
        $warning->restore();

        return redirect()->route('warnings.show', ['id' => $warning->warneduser->id, 'username' => $warning->warneduser->username])
            ->withSuccess('Warning Was Successfully Restored');
    }
}
