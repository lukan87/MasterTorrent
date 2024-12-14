<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Models\Peer;
use App\Models\User;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Carbon\Carbon;
use App\Services\Bencode;

class AnnounceController extends Controller
{

    public function announce(Request $request, $passkey)
    {

        $agent = $request->server('HTTP_USER_AGENT') ?: "Unknown";

        // Browser safety check
        $this->BrowserCheck($request->server('HTTP_USER_AGENT'));

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
 $peer_id = bin2hex($request->get('peer_id'));
 $md5_peer_id = md5($peer_id);
 $ip = $request->ip();
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

 // If User Client Is Sending Negitive Values Return Error to Client
 if ($uploaded < 0 || $downloaded < 0 || $left < 0) {
    Log::notice('Client Attempted To Send Data With A Negitive Value');
    return response(Bencode::bencode(['failure reason' => 'Data from client is a negative value']), 200, ['Content-Type' => 'text/plain']);
}


 // Check Info Hash Agaist Torrents Table
 $torrent = Torrent::where('info_hash', '=', $hash)->first();

 if (!$torrent) {
    return response(Bencode::bencode(['failure reason' => 'Torrent not found']), 200, ['Content-Type' => 'text/plain']);
}

 $times_completed = $torrent ? $torrent->times_completed : 0; // Default to 0 if not found

 $peers = Peer::where('hash', '=', $hash)->take(100000)->get()->toArray();
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
                $p['updated_at']
            );
        }

 // Pull Count On Users Peers Per Torrent
 $limit = Peer::where('hash', '=', $hash)->where('user_id', '=', $user->id)->count();

 // Get The Current Peer

 $client = Peer::where('hash', '=', $hash)->where('md5_peer_id', '=', $md5_peer_id)->where('user_id', '=', $user->id)->first();

 // Flag is tripped if new session is created but client reports up/down > 0
 $ghost = false;

 // Creates a new client if not existing
 if (!$client && $event == 'completed') {
     return response(Bencode::bencode(['failure reason' => 'Torrent is complete but no record found.']), 200, ['Content-Type' => 'text/plain']);
 } elseif (!$client) {
     if ($uploaded > 0 || $downloaded > 0) {
         $ghost = true;
         $event = 'started';
     }
     $client = new Peer();
 }

// Get history information
$history = History::where("info_hash", "=", $hash)->where("user_id", "=", $user->id)->first();

if (!$history) {
    $history = new History([
        "user_id" => $user->id,
        "info_hash" => $hash,
        "torrent_id" => $torrent->id
        ]);
}



 if ($ghost) {
    $uploaded = ($real_uploaded >= $history->client_uploaded) ? ($real_uploaded - $history->client_uploaded) : 0;
    $downloaded = ($real_downloaded >= $history->client_downloaded) ? ($real_downloaded - $history->client_downloaded) : 0;
} else {
    $uploaded = ($real_uploaded >= $client->uploaded) ? ($real_uploaded - $client->uploaded) : 0;
    $downloaded = ($real_downloaded >= $client->downloaded) ? ($real_downloaded - $client->downloaded) : 0;
}

$old_update = $client->updated_at ? $client->updated_at->timestamp : Carbon::now()->timestamp;

// Ensure the client_updated_at timestamp is updated
$client_updated_at = Carbon::now();  // Set client updated timestamp


     //Free Torrent
    $mod_downloaded = ($torrent->free === 1) ? 0 : $downloaded;

// Check if double attribute is set to 1
     $mod_uploaded = ($torrent->double === 1) ? $uploaded * 2 : $uploaded;

  //VIP DOWNLOAD
$mod_downloaded = ($user->user_class === 3) ? 0 : $downloaded;

        switch ($event) {
            case 'started':
                $history->agent = $agent;
                $history->active = true;
                $history->seeder = ($left == 0) ? true : false;
                $history->uploaded += 0;
                $history->actual_uploaded += 0;
                $history->client_uploaded = $real_uploaded;
                $history->downloaded += 0;
                $history->actual_downloaded += 0;
                $history->client_downloaded = $real_downloaded;
                $history->left = $left;
                $history->save();

                // Never to push stats to user on start event

                //Peer update
                $client->peer_id = $peer_id;
                $client->md5_peer_id = $md5_peer_id;
                $client->hash = $hash;
                $client->ip = $request->ip();
                $client->port = $port;
                $client->agent = $agent;
                $client->uploaded = $real_uploaded;
                $client->downloaded = $real_downloaded;
                $client->seeder = ($left == 0) ? true : false;
                $client->left = $left;
                $client->torrent_id = $torrent->id;
                $client->user_id = $user->id;
                $client->active = true;
                // Update client_updated_at each time
                $client->client_updated_at = $client_updated_at;  // Set the updated timestamp

                //End Peer update

                $client->save();

                // Clear all cache to ensure fresh data is loaded
                Cache::flush();
                break;
            case 'completed':
                $history->agent = $agent;
                $history->active = true;
                // $history->seeder = ($left == 0) ? true : false;
                $history->seeder = true;
                $history->uploaded += $mod_uploaded;
                $history->actual_uploaded += $uploaded;
                $history->client_uploaded = $real_uploaded;
                $history->downloaded += $mod_downloaded;
                $history->actual_downloaded += $downloaded;
                $history->client_downloaded = $real_downloaded;
                $history->left = 0;
                $history->completed_at = Carbon::now();
                $history->save();

                // user update
                $user->uploaded += $mod_uploaded;
                $user->downloaded += $mod_downloaded;
                $user->save();
                // End User update

                //Peer update
                $client->peer_id = $peer_id;
                $client->md5_peer_id = $md5_peer_id;
                $client->hash = $hash;
                $client->ip = $request->ip();
                $client->port = $port;
                $client->agent = $agent;
                $client->uploaded = $real_uploaded;
                $client->downloaded = $real_downloaded;
                $client->seeder = true;
                $client->left = 0;
                $client->torrent_id = $torrent->id;
                $client->user_id = $user->id;
                $client->active = true;
                // Update client_updated_at each time
                $client->client_updated_at = $client_updated_at;  // Set the updated timestamp



                $client->save();
                //End Peer update

                // Torrent completed update
                $torrent->times_completed++;

                // Seedtime allocation
                $new_update = $client->updated_at->timestamp;
                $diff = $new_update - $old_update;
                $history->seedtime += $diff;
                $history->save();

                // Clear all cache to ensure fresh data is loaded
                Cache::flush();
                break;
            case 'stopped':
                $history->agent = $agent;
                $history->active = false;
                $history->seeder = false;
                $history->uploaded += $mod_uploaded;
                $history->actual_uploaded += $uploaded;
                $history->client_uploaded = 0;
                $history->downloaded += $mod_downloaded;
                $history->actual_downloaded += $downloaded;
                $history->client_downloaded = 0;
                $history->left = $left;
                $history->save();

                // user update
                $user->uploaded += $mod_uploaded;
                $user->downloaded += $mod_downloaded;
                $user->save();
                // End User update

                //Peer update
                $client->peer_id = $peer_id;
                $client->md5_peer_id = $md5_peer_id;
                $client->hash = $hash;
                $client->ip = $request->ip();
                $client->port = $port;
                $client->agent = $agent;
                $client->uploaded = $real_uploaded;
                $client->downloaded = $real_downloaded;
                $client->seeder = false;
                $client->left = $left;
                $client->torrent_id = $torrent->id;
                $client->user_id = $user->id;
                // Update client_updated_at each time
                $client->client_updated_at = $client_updated_at;  // Set the updated timestamp


                //End Peer update

                $client->save();

                // Seedtime allocation
                if ($left == 0) {
                    $new_update = $client->updated_at->timestamp;
                    $diff = $new_update - $old_update;
                    $history->seedtime += $diff;
                    $history->save();
                }

                $client->delete();

                // Clear all cache to ensure fresh data is loaded
                Cache::flush();
                break;
            default:
                $history->agent = $agent;
                $history->active = true;
                $history->seeder = ($left == 0) ? true : false;
                $history->uploaded += $mod_uploaded;
                $history->actual_uploaded += $uploaded;
                $history->client_uploaded = $real_uploaded;
                $history->downloaded += $mod_downloaded;
                $history->actual_downloaded += $downloaded;
                $history->client_downloaded = $real_uploaded;
                $history->save();

                // user update
                $user->uploaded += $mod_uploaded;
                $user->downloaded += $mod_downloaded;
                $user->save();
                // End User update

                //Peer update
                $client->peer_id = $peer_id;
                $client->md5_peer_id = $md5_peer_id;
                $client->hash = $hash;
                $client->ip = $request->ip();
                $client->port = $port;
                $client->agent = $agent;
                $client->uploaded = $real_uploaded;
                $client->downloaded = $real_downloaded;
                $client->seeder = ($left == 0) ? true : false;
                $client->left = $left;
                $client->torrent_id = $torrent->id;
                $client->user_id = $user->id;
                // Update client_updated_at timestamp
                $client->client_updated_at = $client_updated_at; // Set the updated timestamp
                //End Peer update

                $client->save();

                // Seedtime allocation
                if ($left == 0) {
                    $new_update = $client->updated_at->timestamp;
                    $diff = $new_update - $old_update;
                    $history->seedtime += $diff;
                    $history->save();
                }
                break;
        }

 $torrent->seeders = Peer::whereRaw('torrent_id = ? AND `left` = 0', [$torrent->id])->count();
 $torrent->leechers = Peer::whereRaw('torrent_id = ? AND `left` > 0', [$torrent->id])->count();
 $torrent->save();

 $res = [];
$res['interval'] = 60 * 30; // Interval de 30 minute
$res['min interval'] = 60 * 10; // Interval minim de 10 minute

 $res['tracker_id'] = $md5_peer_id; // A string that the client should send back on its next announcements.
 $res['complete'] = $seeders;
 $res['incomplete'] = $leechers;
 $res['downloaded'] = $times_completed; // Add times completed here
 $res['peers'] = $this->givePeers($peers, $compact, $no_peer_id);
 $res['peers6'] = $this->givePeers6($peers, $compact, $no_peer_id);

return response(Bencode::bencode($res), 200, ['Content-Type' => 'text/plain']);

    }


private function BrowserCheck($user_agent)
{
    // if (preg_match("/^Mozilla|^Opera|^Links|^Lynx/i", $user_agent)) {
    //     abort(500, "This application failed to load");
    //     die();
    // }
}


private function givePeers($peers, $compact, $no_peer_id)
{
    if ($compact) {
        $pcomp = "";
        foreach ($peers as &$p) {
            // Include toți peers, indiferent dacă portul este valid sau nu
            if (isset($p['ip'])) {
                $pcomp .= pack('Nn', ip2long($p['ip']), (int)($p['port'] ?? 0)); // Port implicit 0 dacă nu este setat
            }
        }
        return $pcomp;
    } elseif ($no_peer_id) {
        // Include toți peers care au o adresă IP
        return array_filter($peers, function ($p) {
            return isset($p['ip']); // Nu mai verifică portul
        });
    } else {
        // Include toți peers care au o adresă IP
        return array_filter($peers, function ($p) {
            return isset($p['ip']); // Nu mai verifică portul
        });
    }
}

private function givePeers6($peers, $compact, $no_peer_id)
{
    if ($compact) {
        $pcomp = '';
        foreach ($peers as &$p) {
            // Process peers with valid IPv6 addresses
            if (isset($p['ip']) && filter_var($p['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $pcomp .= inet_pton($p['ip']); // Convert IPv6 to binary
                $pcomp .= pack('n', (int)($p['port'] ?? 0)); // Pack port, default to 0 if not set
            }
        }
        return $pcomp;
    } elseif ($no_peer_id) {
        // Include all peers with valid IPv6 addresses, exclude 'peer_id'
        return array_filter(array_map(function ($p) {
            if (isset($p['ip']) && filter_var($p['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                unset($p['peer_id']);
                return $p;
            }
            return null;
        }, $peers), fn($p) => $p !== null);
    } else {
        // Include all peers with valid IPv6 addresses
        return array_filter($peers, function ($p) {
            return isset($p['ip']) && filter_var($p['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        });
    }
}



}
