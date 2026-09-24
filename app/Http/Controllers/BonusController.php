<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserTimeline;
use App\Models\Message;
use App\Models\History;
use App\Services\HitRun\HitRunAmnestyService;


class BonusController extends Controller
{

    public function showShop()
{
    $user = Auth::user();
    if (!$user) {
        return redirect()->route('login')->with('error', 'You cannot access this page unless you are a member!');
    }

    // Get the currently active Happy Hour
    $happyHour = \App\Models\HappyHour::where('active', true)
        ->latest('start_at')
        ->first();

    return view('bonus.shop', compact('user', 'happyHour'));
}


    public function buyUpload(Request $request)
    {
        $user = Auth::user();

        $amount = $request->input('amount');

        // Prices are defined centrally in config/seedbonus.php
        $uploadPricing = config('seedbonus.shop.upload');
        $cost = $uploadPricing[$amount] ?? null;

        if ($cost === null) {
            return redirect()->back()->with('error', 'Invalid selection.');
        }

        $uploadAmount = $amount * 1024 * 1024 * 1024;

        if ($user->seedbonus < $cost) {
            return redirect()->back()->with('error', 'Not enough points.');
        }

        $user->uploaded += $uploadAmount;
        $user->seedbonus -= $cost;
        $user->save();

        
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
    $cost = config('seedbonus.shop.vip', 30000);
    if ($user->seedbonus < $cost) {
        return redirect()->back()->with('error', 'Not enough points.');
    }

    $user->seedbonus -= $cost;

    
    $currentVipUntil = $user->vip_until ? Carbon::parse($user->vip_until) : now();
    $newVipUntil = $currentVipUntil->greaterThanOrEqualTo(now())
        ? $currentVipUntil->addYear()
        : now()->addYear();

    $user->vip_until = $newVipUntil;

    
    $user->user_class = 3;
    $user->slots += config('seedbonus.shop.vip_slots', 10);
    $user->invites += config('seedbonus.shop.vip_invites', 5);
    $user->hit_and_run_count = 0;

    $user->save();

    History::where('user_id', $user->id)
        ->where('hitrun', 1)
        ->delete();

   
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Bought VIP status for 1 year (until {$newVipUntil->toDateString()}) for {$cost} seedbonus points.",
    ]);

    return redirect()->back()->with('success', 'You are now a VIP for one year! You have received 10 additional download slots and 5 invites!');
}


public function buySeedtime(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login')->with('error', 'You must be logged in to buy seedtime.');
    }

    $torrentId = $request->input('torrent_id');
    $seedtimeCost = config("seedbonus.shop.seedtime", 1000); 
    $additionalSeedtime = config("seedbonus.shop.seedtime_added", 86400); 

    
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points to buy seedtime.');
    }

    
    $history = \DB::table('history')
        ->where('user_id', $user->id)
        ->where('torrent_id', $torrentId)
        ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'Torrent history record not found.');
    }

    // Remember whether this purchase is clearing an already-flagged Hit & Run
    // so we can keep the per-user counter (and the 20 limit) in sync.
    $wasHitRun = !empty($history->hitrun);

    
    $user->seedbonus -= $seedtimeCost;
    $user->save();

    \DB::table('history')
        ->where('id', $history->id)
        ->update([
            'prewarned_at' => NULL,
            'seedtime' => $additionalSeedtime, 
            'hitrun' => false,
            'hitrun_removed_at' => $wasHitRun ? now() : ($history->hitrun_removed_at ?? null),
            'updated_at' => now(),
        ]);

    // If this purchase cleared an already-flagged Hit & Run, decrement the user's
    // counter exactly like the other clear paths so they can get under the 20 limit
    // and have their downloads restored.
    if ($wasHitRun) {
        $freshUser = $user->fresh();
        if ($freshUser && $freshUser->hit_and_run_count > 0) {
            $freshUser->decrement('hit_and_run_count');
        }
    }

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
    $seedtimeCost = config("seedbonus.shop.remove_hnr", 5000); 
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points.');
    }
    $additionalSeedtime = 86400; 

   
    if ($user->seedbonus < $seedtimeCost) {
        return redirect()->back()->with('error', 'Not enough points to buy seedtime.');
    }

    
    $history = \DB::table('history')
        ->where('user_id', $user->id)
        ->where('torrent_id', $torrentId)
        ->where('hitrun', true)
        ->first();

    if (!$history) {
        return redirect()->back()->with('error', 'Torrent history record not found.');
    }

    
    $user->seedbonus -= $seedtimeCost;
    $user->hit_and_run_count = max(0, $user->hit_and_run_count - 1); 
    $user->save();

    \DB::table('history')
        ->where('id', $history->id)
        ->update([
            'prewarned_at' => NULL,
            'seedtime' => $additionalSeedtime, 
            'hitrun' => false, 
            'updated_at' => now(),
        ]);


       

       
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => '2',
        'comment' => "Removed HitAndRun for torrent ID: {$torrentId} at the cost of {$seedtimeCost} seedbonus points.",
    ]);

    Message::create([
        'sender_id' => 2,
        'receiver_id' => $user->id,
        'subject' => 'Hit&Run removed',
        'body' => "You have successfully removed the hitandrun for torrent ID: {$torrentId}!",
    ]);

    return redirect()->back()->with('success', 'You successfully removed the hit&run for torrent ID:' . $torrentId);
}


public function buyInvites(Request $request)
{
    $user = Auth::user();

  
    $cost = config("seedbonus.shop.invite", 1500); 
    if ($user->seedbonus < $cost) {
        return redirect()->back()->with('error', 'Not enough points to buy an invite.');
    }

    $user->seedbonus -= $cost;
    $user->invites += 1; 
    $user->save();

  
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => 2,
        'comment' => "Bought 1 invite for {$cost} seedbonus points.",
    ]);

    return redirect()->back()->with('success', 'You successfully bought 1 invite!');
}

public function buySlots(Request $request)
{
    $user = Auth::user();

    
    $cost = config("seedbonus.shop.slot", 1000); 

    
    if ($user->seedbonus < $cost) {
        return redirect()->back()->with('error', 'Not enough points to buy a slot.');
    }

    
    $user->seedbonus -= $cost;
    $user->slots += 1; 
    $user->save();

   
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => 2, 
        'comment' => "Bought 1 slot for {$cost} seedbonus points.",
    ]);

    return redirect()->back()->with('success', 'You successfully bought 1 slot!');
}

public function buySurprise(Request $request)
{
    $user = Auth::user();


    $cost = config("seedbonus.shop.surprise", 15000);

  
    if ($user->seedbonus < $cost) {
        return redirect()->back()->with('error', 'Not enough points to buy a surprise.');
    }

    
    $rewards = [
        ['type' => 'upload', 'amount' => 100, 'label' => '100GB Upload'],  
        ['type' => 'upload', 'amount' => 250, 'label' => '250GB Upload'],  
        ['type' => 'upload', 'amount' => 500, 'label' => '500GB Upload'], 
        ['type' => 'vip', 'months' => 1, 'label' => 'VIP for 1 month'], 
        ['type' => 'vip', 'months' => 2, 'label' => 'VIP for 2 months'], 
        ['type' => 'vip', 'months' => 3, 'label' => 'VIP for 3 months'], 
        ['type' => 'invite', 'amount' => 3, 'label' => '3 Invite'],      
        ['type' => 'invite', 'amount' => 6, 'label' => '6 Invites'],     
        ['type' => 'invite', 'amount' => 10, 'label' => '10 Invites'],     
        ['type' => 'slot', 'amount' => 5, 'label' => '5 Slot'],          
        ['type' => 'slot', 'amount' => 10, 'label' => '10 Slots'],        
        ['type' => 'slot', 'amount' => 15, 'label' => '15 Slots'],       
    ];

    $reward = $rewards[array_rand($rewards)];
    $user->seedbonus -= $cost;
    $message = '';

    if ($reward['type'] === 'upload') {
        
        $uploadAmount = $reward['amount'] * 1024 * 1024 * 1024;
        $user->uploaded += $uploadAmount;
        $message = "You won {$reward['label']}!";
    } elseif ($reward['type'] === 'vip') {
        if ($user->user_class <= 3) {
            
            $currentVipUntil = $user->vip_until ? Carbon::parse($user->vip_until) : now();
            $newVipUntil = $currentVipUntil->greaterThanOrEqualTo(now())
                ? $currentVipUntil->addMonths($reward['months'])
                : now()->addMonths($reward['months']);
    
            $user->vip_until = $newVipUntil;
            $user->user_class = 3;
    
            $message = "You won VIP status for {$reward['months']} month(s)! Enjoy!";
        } else {
            
            return $this->buySurprise($request);
        }
    } elseif ($reward['type'] === 'invite') {
        $user->invites += $reward['amount'];
        $message = "You won {$reward['label']}!";
    } elseif ($reward['type'] === 'slot') {
        $user->slots += $reward['amount'];
        $message = "You won {$reward['label']}!";
    }

    
    $user->save();

    
    UserTimeline::create([
        'user_id' => $user->id,
        'staff_id' => 2,
        'comment' => "Bought a surprise and won: {$reward['label']}!",
    ]);

    return redirect()->back()->with('success', $message);
}






    /**
     * Clear the user's OLDEST hit & run. Applies the same 1:1 ratio logic as
     * the amnesty (credits the shortfall) and decreases the H&R counter.
     */
    public function clearOneHnr(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to clear a hit&run.');
        }

        // Charge only if there is actually something to clear.
        $hasHnr = History::where('user_id', $user->id)
            ->where('hitrun', 1)
            ->exists();

        if (!$hasHnr) {
            return redirect()->back()->with('info', 'You have no hit & runs to clear.');
        }

        $cost = config('seedbonus.shop.clear_hnr', 7500);

        if ($user->seedbonus < $cost) {
            return redirect()->back()->with('error', 'Not enough points to clear a hit & run.');
        }

        $user->seedbonus -= $cost;
        $user->save();

        $result = (new HitRunAmnestyService)->clearOldestForUser($user);

        UserTimeline::create([
            'user_id' => $user->id,
            'staff_id' => '2',
            'comment' => "Cleared the oldest hit&run at the cost of {$cost} seedbonus points (upload credited: {$result['shortfall']} bytes).",
        ]);

        if ($result['shortfall'] > 0) {
            Message::create([
                'sender_id' => 2,
                'receiver_id' => $user->id,
                'subject' => 'Hit & Run cleared',
                'body' => "You cleared your oldest hit & run. Your upload was topped up by {$result['shortfall']} bytes so that torrent now counts as a 1:1 ratio!",
            ]);
        }

        return redirect()->back()->with('success', 'Your oldest hit & run was cleared and your ratio restored to 1:1 on that torrent!');
    }

    /**
     * Remove an active warning from the user's account (H&R/reseed related).
     */
    public function buyResetWarning(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to reset your warning.');
        }

        if ($user->warned != 1) {
            return redirect()->back()->with('info', 'You have no active warning to reset.');
        }

        $cost = config('seedbonus.shop.reset_warning', 5000);

        if ($user->seedbonus < $cost) {
            return redirect()->back()->with('error', 'Not enough points to reset your warning.');
        }

        $user->seedbonus -= $cost;
        $user->warned = 0;
        $user->warned_until = null;
        $user->save();

        UserTimeline::create([
            'user_id' => $user->id,
            'staff_id' => '2',
            'comment' => "Reset their active warning at the cost of {$cost} seedbonus points.",
        ]);

        return redirect()->back()->with('success', 'Your warning has been reset. Enjoy your clean slate!');
    }
}
