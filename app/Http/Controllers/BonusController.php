<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserTimeline;
use App\Models\Message;


class BonusController extends Controller
{

    public function showShop()
{

    $user = Auth::user();
         if (!$user) {
        return redirect()->route('login')->with('error', 'You cannot access this page unless you are a member !');
    }
    return view('bonus.shop'); // Ensure this points to the correct view path
}

    public function buyUpload(Request $request)
    {
        $user = Auth::user();

        $amount = $request->input('amount');

        switch ($amount) {
            case '10':
                $cost = 500; // points for 10 GB
                $uploadAmount = 10 * 1024 * 1024 * 1024; // 10 GB in bytes
                break;
            case '25':
                $cost = 1000; // points for 25 GB
                $uploadAmount = 25 * 1024 * 1024 * 1024; // 25 GB in bytes
                break;
            case '100':
                $cost = 5000; // points for 100 GB
                $uploadAmount = 100 * 1024 * 1024 * 1024; // 100 GB in bytes
                break;
            default:
                return redirect()->back()->with('error', 'Invalid selection.');
        }

        if ($user->seedbonus < $cost) {
            return redirect()->back()->with('error', 'Not enough points.');
        }

        $user->uploaded += $uploadAmount;
        $user->seedbonus -= $cost;
        $user->save();

        // Log the action in UserTimeline
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Bought {$amount} GB of upload for {$cost} seedbonus points.",
    ]);

        return redirect()->back()->with('success', "You bought {$amount} GB of upload !");
    }


    public function buyVip(Request $request)
{
    $user = Auth::user();
    $cost = 100000; // Cost of VIP promotion

    if ($user->seedbonus < $cost) {
        return redirect()->back()->with('error', 'Not enough points.');
    }

    $user->seedbonus -= $cost;

    // Set or extend VIP status
    $currentVipUntil = $user->vip_until ? Carbon::parse($user->vip_until) : now();
    $newVipUntil = $currentVipUntil->greaterThanOrEqualTo(now())
        ? $currentVipUntil->addYear()
        : now()->addYear();

    $user->vip_until = $newVipUntil;

    // Set the user's class to VIP (3)
    $user->user_class = 3;

    $user->save();

    // Log the action in UserTimeline with VIP expiration date
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Bought VIP status for 1 year (until {$newVipUntil->toDateString()}) for {$cost} seedbonus points.",
    ]);

    return redirect()->back()->with('success', 'You are now a VIP for one year!');
}


public function buySeedtime(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'You must be logged in to buy seedtime.');
    }

    $torrentId = $request->input('torrent_id');
    $seedtimeCost = 5000; // Cost in points for buying seedtime
    $additionalSeedtime = 86400; // 1 day in seconds

    // Check if user has enough seedbonus points
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points to buy seedtime.');
    }

    // Find the torrent in history to update seedtime
    $history = \DB::table('history')
        ->where('user_id', $user->id)
        ->where('torrent_id', $torrentId)
        ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'Torrent history record not found.');
    }

    // Deduct points and update seedtime
    $user->seedbonus -= $seedtimeCost;
    $user->save();

    \DB::table('history')
        ->where('id', $history->id)
        ->update([
            'prewarned_at' => NULL,
            'seedtime' => $additionalSeedtime, // Update seedtime to 86400 seconds
            'updated_at' => now(),
        ]);

        // Log the action in UserTimeline
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Bought full seedtime for torrent ID: {$torrentId} at the cost of {$seedtimeCost} seedbonus points.",
    ]);

    return redirect()->back()->with('success', 'You successfully updated the seedtime to the site required needs!');
}

public function removeHNR(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'You must be logged in to remove a hit&run.');
    }

    $torrentId = $request->input('torrent_id');
    $seedtimeCost = 5000; // Cost in points for buying seedtime
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points.');
    }
    $additionalSeedtime = 86400; // 1 day in seconds

    // Check if user has enough seedbonus points
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points to buy seedtime.');
    }

    // Find the torrent in history to update seedtime
    $history = \DB::table('history')
        ->where('user_id', $user->id)
        ->where('torrent_id', $torrentId)
        ->where('hitrun', true)
        ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'Torrent history record not found.');
    }

    // Deduct points and update seedtime
    $user->seedbonus -= $seedtimeCost;
    $user->hit_and_run_count = max(0, $user->hit_and_run_count - 1); // Ensure count does not go below 0
    $user->save();

    \DB::table('history')
        ->where('id', $history->id)
        ->update([
            'prewarned_at' => NULL,
            'seedtime' => $additionalSeedtime, // Update seedtime to 86400 seconds
            'hitrun' => false, // Update seedtime to 86400 seconds
            'updated_at' => now(),
        ]);


       

        // Log the action in UserTimeline
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Removed HitAndRun for torrent ID: {$torrentId} at the cost of {$seedtimeCost} seedbonus points.",
    ]);

    Message::create([
        'sender_id' => 2,
        'receiver_id' => $user->id,
        'subject' => 'Hit&Run removed',
        'body' => "You have successfully removed the hitandrun!",
    ]);

    return redirect()->back()->with('success', 'You successfully removed the hit&run for torrent ID:' . $torrentId);
}




}

