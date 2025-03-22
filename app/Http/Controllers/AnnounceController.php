<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Models\Peer;
use App\Models\User;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use App\Services\Bencode;

class AnnounceController extends Controller
{

    public function announce(Request $request, $passkey)
    {

        $agent = $request->server('HTTP_USER_AGENT') ?: "Unknown";

       

        // If Passkey Is Not Provided Exsist Return Error to Client
        if ($passkey == null) {
            Log::notice('Client Attempted To Connect To Announce Without A Passkey');
            return response(Bencode::bencode(['failure reason' => 'Please Call Passkey']), 200, ['Content-Type' => 'text/plain']);
        }

        // If User Client Is Not Sending Required Data Return Error to Client
        if (!$request->has('info_hash') || !$request->has('peer_id') || !$request->has('port') || !$request->has('left') || !$request->has('uploaded') || !$request->has('downloaded')) {
            return response(Bencode::bencode(['failure reason' => 'Bad Data from client']), 200, ['Content-Type' => 'text/plain']);
        }

// Check Passkey Against Users Table
$user = User::where("passkey", '=', $passkey)->first();

// If Passkey Doesnt Exsist Return Error to Client
if (!$user) {
    return response(Bencode::bencode(['failure reason' => 'Passkey is invalid']), 200, ['Content-Type' => 'text/plain']);
}

if ($user->enabled === 'no'){
    return response(Bencode::bencode(['failure reason' => 'Your account has been disabled. Please contact a member of staff !']), 200, ['Content-Type' => 'text/plain']);
}



 // Standard Information Fields
 $event = $request->get('event');
 $hash = bin2hex($request->get('info_hash'));
 $peer_id =$request->get('peer_id');
 $md5_peer_id = md5($peer_id);
 //$ip = $request->ip();
 $port = (int)$request->get('port');
 $left = (float)$request->get('left');
 $uploaded = (float)$request->get('uploaded');
 $real_uploaded = $uploaded;
 $downloaded = (float )$request->get('downloaded');
 $real_downloaded = $downloaded;

 //Extra Information Fields
 $tracker_id = $request->has('trackerid') ? bin2hex($request->get('tracker_id')) : null;
 $compact = ($request->has('compact') && $request->get('compact') == 1) ? true : false;
 $key = $request->has('key') ? bin2hex($request->get('key')) : null;
 $corrupt = $request->has('corrupt') ? $request->get('corrupt') : null;
 $ipv6 = $request->has('ipv6') ? bin2hex($request->get('ipv6')) : null;
 $no_peer_id = ($request->has('no_peer_id') && $request->get('no_peer_id') == 1) ? true : false;

  // If User Download Rights Are Disabled Return Error to Client
  if ($user->downloadpos == 'no') {
    //info('A User With Revoked Download Privileges Attempted To Announce');
    return response(Bencode::bencode(['failure reason' => 'Your download privileges are Revoked']))->withHeaders(['Content-Type' => 'text/plain']);
}

// If User has more that 10 hit and runs Return Error to Client
if ($user->hit_and_run_count > '10' ) {
    //info('A User With Revoked Download Privileges Attempted To Announce');
    return response(Bencode::bencode(['failure reason' => 'You cannot download any torrents as you have more than 10 hit and runs. Contact staff!!!']))->withHeaders(['Content-Type' => 'text/plain']);
}

 // If User Client Is Sending Negitive Values Return Error to Client
 if ($uploaded < 0 || $downloaded < 0 || $left < 0) {
    Log::notice('Client Attempted To Send Data With A Negitive Value');
    return response(Bencode::bencode(['failure reason' => 'Data from client is a negative value']), 200, ['Content-Type' => 'text/plain']);
}


 // Check Info Hash Agaist Torrents Table
 $torrent = Torrent::select(['id', 'status', 'free', 'double', 'times_completed', 'seeders', 'leechers'])->with('peers')->where('info_hash', '=', $hash)->first();

 if (!$torrent) {
    return response(Bencode::bencode(['failure reason' => 'Torrent not found']), 200, ['Content-Type' => 'text/plain']);
}



 $peers = Peer::where('torrent_id', '=', $torrent->id)->where('user_id', '!=', $user->id)->take(50)->get()->toArray();

        $seeders = 0;
        $leechers = 0;

        foreach ($peers as &$p) {
            if ($p['left'] > 0) {
                $leechers++; // Counts the number of leechers
            } else {
                $seeders++; // Counts the number of seeders
            }

            unset(
                $p['id'],
                $p['md5_peer_id'],
                $p['hash'],
                $p['agent'],
                $p['uploaded'],
                $p['downloaded'],
                $p['left'],
                $p['torrent_id'],
                $p['user_id'],
                $p['seeder'],
                $p['created_at'],
                $p['updated_at'],
                $p['client_updated_at']
            );
        }
        
        

       

 // Pull Count On Users Peers Per Torrent
 $limit = Peer::where('hash', '=', $hash)->where('user_id', '=', $user->id)->count();

 // Get The Current Peer

 $client = Peer::where('torrent_id', '=', $torrent->id)->where('peer_id', $peer_id)->where('user_id', '=', $user->id)->first();

 // Flag is tripped if new session is created but client reports up/down > 0
 $ghost = false;

 // Creates a new client if not existing
 if ($client === null && $event == 'completed') {
    return \response(Bencode::bencode(['failure reason' => 'Torrent is complete but no record found.']))->withHeaders(['Content-Type' => 'text/plain']);
}

// Creates a new peer if not existing
if ($client == null) {
    if ($uploaded > 0 || $downloaded > 0) {
        $ghost = true;
        $event = 'started';
    }
    $client = new Peer();
}

// Get history information
$history = History::where('torrent_id', $torrent->id)
    ->where('user_id', $user->id)
    ->first();

// If no History record found then create one
if ($history === null) {
    $history = new History();
    $history->user_id = $user->id;
    $history->torrent_id = $torrent->id;
    $history->info_hash = $hash;
    $history->ip = $request->ip();
}



 if ($ghost) {
    $uploaded = ($real_uploaded >= $history->client_uploaded) ? ($real_uploaded - $history->client_uploaded) : 0;
    $downloaded = ($real_downloaded >= $history->client_downloaded) ? ($real_downloaded - $history->client_downloaded) : 0;
} else {
    $uploaded = ($real_uploaded >= $client->uploaded) ? ($real_uploaded - $client->uploaded) : 0;
    $downloaded = ($real_downloaded >= $client->downloaded) ? ($real_downloaded - $client->downloaded) : 0;
}

// Ensure the client_updated_at timestamp is updated
$client_updated_at = Carbon::now();  // Set client updated timestamp

$old_update = $client->client_updated_at ? $client->client_updated_at->timestamp : Carbon::now()->timestamp;

// Check if the user_id is in user_slots table
$userSlot = DB::table('user_slots')
->where('user_id', $user->id)
->where('torrent_id', $torrent->id)
->first();
// Check if a matching user_slot exists
$userSlotExists = $userSlot !== null;

// Determine whether free or double should apply based on user_slots table
$isFree = $userSlotExists ? $userSlot->free : false;
$isDouble = $userSlotExists ? $userSlot->double : false;


// Log the config and flags
// \Log::info('Freeleech Config: ' . (config('settings.freeleech') ? 'Yes' : 'No'));
// \Log::info('Double Config: ' . (config('settings.double') ? 'Yes' : 'No'));
// \Log::info('Torrent Free: ' . $torrent->free);
// \Log::info('Torrent Double: ' . $torrent->double);
// // Log the values to verify
// \Log::info('User Slot Exists: ' . ($userSlotExists ? 'Yes' : 'No'));
// \Log::info('Is Free: ' . ($isFree ? 'Yes' : 'No'));
// \Log::info('Is Double: ' . ($isDouble ? 'Yes' : 'No'));
// \Log::info('user id: ' . $user->id);
// \Log::info('torrent id: ' . $torrent->id);



if (config('settings.freeleech') === true || $torrent->free === true || $isFree) {
    $mod_downloaded = 0;
} else {
    $mod_downloaded = $downloaded;
}

// Check if double attribute is set to 1
if (config('settings.double') === true || $torrent->double === true || $isDouble) {
    $mod_uploaded = $uploaded * 2; // Double the uploaded value if doubleup is 1
} else {
    $mod_uploaded = $uploaded; // Keep the original uploaded value otherwise
}





if ($event === 'started') {
    // Never to push stats to user on start event

    // Peer update
    $client->peer_id = $peer_id;
    $client->md5_peer_id = $md5_peer_id;
    $client->hash = $hash;
   // Ensure IP is in IPv4 format
$ip = $request->ip();
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    $client->ip = $ip; // Set the IP if it's a valid IPv4 address
} 

elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    // If valid, store the IPv6 IP
    // The model is automatically ready to save the IPv6 address
    $client->ip = $ip;
} else {
    // Set IP to a default value if it's not a valid IPv4 address
}
    $client->port = $port;
    $client->agent = $agent;
    $client->uploaded = $real_uploaded;
    $client->downloaded = $real_downloaded;
    if ($left == 0) {
    $client->seeder = 1;
    } else {
        $client->seeder = 0;
    }
    $client->left = $left;
    $client->torrent_id = $torrent->id;
    $client->user_id = $user->id;
    $client->active = true;
    $client->client_updated_at = $client_updated_at; // Set the updated timestamp
    $client->save();

    $history->agent = $agent;
    $history->active = true;
   // Set seeder only if torrent is completed
   if ($left == 0) {
    $history->seeder = 1;
}
    $history->uploaded += 0;
    $history->actual_uploaded += 0;
    $history->client_uploaded = $real_uploaded;
    $history->downloaded += 0;
    $history->actual_downloaded += 0;
    $history->client_downloaded = $real_downloaded;
    $history->ip = request()->ip();
    $history->save();

    // Clear all cache to ensure fresh data is loaded
    Cache::flush();
} elseif ($event === 'completed') {
    

    

    // Peer update
    $client->peer_id = $peer_id;
    $client->md5_peer_id = $md5_peer_id;
    $client->hash = $hash;
    $client->port = $port;
    $client->agent = $agent;
    $client->uploaded = $real_uploaded;
    $client->downloaded = $real_downloaded;
    if ($left == 0) {
        $client->seeder = 1;
        } else {
            $client->seeder = 0;
        }
    // $client->left = 0;
    $client->left = $left;
    $client->torrent_id = $torrent->id;
    $client->user_id = $user->id;
    $client->active = true;
    $client->client_updated_at = $client_updated_at; // Set the updated timestamp

    $client->save();

    $history->agent = $agent;
    $history->active = true;
    $history->seeder = true;
    $history->uploaded += $mod_uploaded;
    $history->actual_uploaded += $uploaded;
    $history->client_uploaded = $real_uploaded;
    $history->downloaded += $mod_downloaded;
    $history->actual_downloaded += $downloaded;
    $history->client_downloaded = $real_downloaded;
    $history->left = 0;
    $history->ip = request()->ip();
    $history->completed_at = Carbon::now();

    // Seedtime Allocation
    if ($left == 0) {
        $new_update = $client->client_updated_at->timestamp;
        $diff = $new_update - $old_update;
        $history->seedtime += $diff;
    }
    $history->save();

 // User Update
 $user->uploaded += $mod_uploaded;
 $user->downloaded += $mod_downloaded;
 $user->save();
 // End User Update

    // Torrent completed update
    $torrent->times_completed++;

    

    // Clear all cache to ensure fresh data is loaded
    Cache::flush();
} elseif ($event === 'stopped') {

    // Peer update
   
   
     $client->agent = $agent;
     $client->uploaded = $real_uploaded;
     $client->downloaded = $real_downloaded;
     $client->seeder = 0;
     $client->active = 0;
     $client->left = $left;
    $client->torrent_id = $torrent->id;
    $client->user_id = $user->id;
    $client->client_updated_at = $client_updated_at; // Set the updated timestamp
    $client->save();
   
    //End Peer Update

      // History Update
   // $history->agent = $agent;
    $history->active = 0;
    $history->seeder = 0;
    $history->uploaded += $mod_uploaded;
    $history->actual_uploaded += $uploaded;
    $history->client_uploaded = 0;
    $history->downloaded += $mod_downloaded;
    $history->actual_downloaded += $downloaded;
    $history->client_downloaded = 0;
    $history->ip = request()->ip();
   
    // Seedtime allocation
    if ($left == 0) {
        $new_update = $client->client_updated_at->timestamp;
        $diff = $new_update - $old_update;
        $history->seedtime += $diff;
    }
    $history->save();
    // End History Update

            // Peer Delete (Now that history is updated)
            $client->delete();
            // End Peer Delete

   

    // User update
    $user->uploaded += $mod_uploaded;
    $user->downloaded += $mod_downloaded;
    $user->save();

      // End User Update

  

    // Clear all cache to ensure fresh data is loaded
    Cache::flush();
} else {

    // Peer update
     $client->peer_id = $peer_id;
     $client->md5_peer_id = $md5_peer_id;
     $client->hash = $hash;
     $ip = $request->ip();
     if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
         $client->ip = $ip; // Set the IP if it's a valid IPv4 address
     } 
     
     elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
         // If valid, store the IPv6 IP
         // The model is automatically ready to save the IPv6 address
         $client->ip = $ip;
     } else {
         // Set IP to a default value if it's not a valid IPv4 address
        
     }
     $client->port = $port;
     $client->agent = $agent;
     $client->uploaded = $real_uploaded;
     $client->downloaded = $real_downloaded;
    if ($left == 0) {
        $client->seeder = 1;
        } else {
            $client->seeder = 0;
        }
     $client->active = true;
     $client->left = $left;
     $client->torrent_id = $torrent->id;
     $client->user_id = $user->id;
    $client->client_updated_at = $client_updated_at; // Set the updated timestamp
    $client->save();
     // End Peer Update

      // History Update
    $history->agent = $agent;
    $history->active = true;
    if ($left == 0) {
    $history->seeder = true;
} else {
    $history->seeder = false;
}
    $history->uploaded += $mod_uploaded;
    $history->actual_uploaded += $uploaded;
    $history->client_uploaded = $real_uploaded;
    $history->downloaded += $mod_downloaded;
    $history->actual_downloaded += $downloaded;
    $history->client_downloaded = $real_uploaded;
    $history->left = $left;
   
     // Seedtime allocation
    if ($left == 0) {
        $new_update = $client->client_updated_at->timestamp;
        $diff = $new_update - $old_update;
        $history->seedtime += $diff;
    }
    $history->ip = request()->ip();
    $history->save();
    // End History Update


    // User update
    $user->uploaded += $mod_uploaded;
    $user->downloaded += $mod_downloaded;
    $user->save();

}

 // Torrent Update
 $torrent->seeders = Peer::whereRaw('torrent_id = ? AND `left` = 0', [$torrent->id])->count();
 $torrent->leechers = Peer::whereRaw('torrent_id = ? AND `left` > 0', [$torrent->id])->count();
 $torrent->save();
 // End Torrent Update
 $times_completed = $torrent ? $torrent->times_completed : 0; // Default to 0 if not found
 $res = [];
 $res = [
    'interval' => 60 * 30, // 30 minutes
    'min interval' => 60 * 10, // 10 minutes
    'tracker_id' => $md5_peer_id,
    'complete' => $seeders,
    'incomplete' => $leechers,
    'downloaded' =>  $times_completed,
    'peers' => $this->givePeers($peers, $compact, $no_peer_id),
];



return response(Bencode::bencode($res))->withHeaders(['Content-Type' => 'text/plain']);

    }




private function givePeers($peers, $compact, $no_peer_id)
{
    if ($compact) {
        $pcomp = "";
        foreach ($peers as &$p) {
            // Include all peers, regardless of whether the port is valid or not
            if (isset($p['ip'])) {
                // Ensure the port defaults to 0 if it's not set, and use IP-to-long for the IP
                $port = isset($p['port']) ? (int) $p['port'] : 0;
                // Packing IP and port as binary data (N - 4 bytes for IP, n - 2 bytes for port)
                $pcomp .= pack('Nn', ip2long($p['ip']), $port);
            }
        }
        return $pcomp;
    } elseif ($no_peer_id) {
        // Include all peers that have an IP address, filter out those without IP
        return array_filter($peers, function ($p) {
            return isset($p['ip']);
        });
    } else {
        // Include all peers that have an IP address (same behavior as $no_peer_id)
        return array_filter($peers, function ($p) {
            return isset($p['ip']);
        });
    }
    
}



}
