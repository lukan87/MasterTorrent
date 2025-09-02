<?php

namespace App\Http\Controllers;

use App\Models\Torrent;
use App\Models\Peer;
use App\Models\User;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\Bencode;

class AnnounceController extends Controller
{
    private function isBannedClient($userAgent)
    {
        $bannedClients = [
            'BitTorrent/5', 'BitTorrent/6', 'BitTorrent/7',
            'uTorrent/2.2', 'µTorrent/2.2',
            'uTorrent/3.0', 'µTorrent/3.0',
            'uTorrent/3.1', 'µTorrent/3.1',
            //'Transmission/2.92',
            'qBittorrent/3.3.8', 'Deluge/1.3.15'
        ];

        foreach ($bannedClients as $banned) {
            if (str_contains($userAgent, $banned)) {
                return true;
            }
        }

        return false;
    }

    public function announce(Request $request, $passkey)
    {
        $agent = $request->server('HTTP_USER_AGENT') ?: "Unknown";

        if ($this->isBannedClient($agent)) {
            return $this->failure('Your client is banned. Please use an updated client.');
        }

        if (!$passkey) {
            return $this->failure('Please Call Passkey');
        }

        if (!$request->has(['info_hash', 'peer_id', 'port', 'left', 'uploaded', 'downloaded'])) {
            return $this->failure('Bad Data from client');
        }

        $user = User::where("passkey", $passkey)->first();
        if (!$user) return $this->failure('Passkey is invalid');
        if ($user->enabled === 'no') return $this->failure('Your account has been disabled. Please contact a member of staff !');
        if ($user->downloadpos === 'no') return $this->failure('Your download privileges are Revoked');
        if ($user->hit_and_run_count > 20) return $this->failure('You cannot download any torrents as you have more than 20 hit and runs. Contact staff!!!');

        // Standard info fields
        $event = $request->get('event');
        $hash = bin2hex($request->get('info_hash'));
        $peer_id = $request->get('peer_id');
        $md5_peer_id = md5($peer_id);
        $port = (int)$request->get('port');
        $left = (float)$request->get('left');
        $uploaded = (float)$request->get('uploaded');
        $real_uploaded = $uploaded;
        $downloaded = (float)$request->get('downloaded');
        $real_downloaded = $downloaded;

        if ($uploaded < 0 || $downloaded < 0 || $left < 0) {
            return $this->failure('Data from client is a negative value');
        }

        $torrent = Torrent::select(['id', 'status', 'free', 'double', 'times_completed', 'seeders', 'leechers'])
            ->with('peers')
            ->where('info_hash', $hash)
            ->first();

        if (!$torrent) return $this->failure('Torrent not found');

        $peers = Peer::where('torrent_id', $torrent->id)
            ->where('user_id', '!=', $user->id)
            ->take(50)
            ->get()
            ->toArray();

        [$seeders, $leechers] = $this->countPeers($peers);

        
        $ip = $this->validateIP($request);

        
        $ghost = $event !== 'stopped' && ($uploaded > 0 || $downloaded > 0);
        if ($ghost && $event !== 'completed') $event = 'started';

        
        $old_client = Peer::where('torrent_id', $torrent->id)
            ->where('user_id', $user->id)
            ->where('peer_id', $peer_id)
            ->first();

        
        $prevUpdateTs = $old_client && $old_client->client_updated_at
            ? $old_client->client_updated_at->timestamp
            : Carbon::now()->timestamp;

        $wasSeeding = $old_client ? ((int)$old_client->seeder === 1) : false;

        
        $client = Peer::updateOrCreate(
            ['torrent_id' => $torrent->id, 'user_id' => $user->id, 'peer_id' => $peer_id],
            [
                'md5_peer_id' => $md5_peer_id,
                'ip' => $ip,
                'port' => $port,
                'agent' => $agent,
                'uploaded' => $real_uploaded,
                'downloaded' => $real_downloaded,
                'left' => $left,
                'seeder' => $left == 0 ? 1 : 0,
                'active' => $event !== 'stopped',
                
            ]
        );

        
        $nowTs = Carbon::now()->timestamp;
        $elapsed = max(0, $nowTs - $prevUpdateTs);

       
        $history = History::firstOrCreate(
            ['user_id' => $user->id, 'info_hash' => $hash],
            [
                'torrent_id' => $torrent->id,
                'ip' => $ip,
                'agent' => $agent,
                'active' => true,
                'seeder' => $left == 0 ? 1 : 0,
                'seedtime' => 0,
            ]
        );

        // Determine free/double
        $userSlot = DB::table('user_slots')
            ->where('user_id', $user->id)
            ->where('torrent_id', $torrent->id)
            ->first();
        $isFree = $userSlot ? (bool)$userSlot->free : false;
        $isDouble = $userSlot ? (bool)$userSlot->double : false;
        $userfree = (bool)$user->is_freeleech;

        $mod_downloaded = (config('settings.freeleech') || $torrent->free || $isFree || $userfree) ? 0 : $downloaded;
        $mod_uploaded = (config('settings.double') || $torrent->double || $isDouble) ? ($uploaded * 2) : $uploaded;

        
        $history->ip = $ip;
        $history->agent = $agent;
        $history->active = $event !== 'stopped';
        $history->seeder = ($left == 0) ? 1 : 0;

        
        if ($ghost) {
            $deltaUp = max(0, $real_uploaded  - (float)$history->client_uploaded);
            $deltaDn = max(0, $real_downloaded - (float)$history->client_downloaded);
        } else {
            // use previous client row values if present
            $previousUp = $old_client ? (float)$old_client->uploaded   : 0.0;
            $previousDn = $old_client ? (float)$old_client->downloaded : 0.0;
            $deltaUp = max(0, $real_uploaded  - $previousUp);
            $deltaDn = max(0, $real_downloaded - $previousDn);
        }

      
        $modDeltaUp = (config('settings.double') || $torrent->double || $isDouble) ? ($deltaUp * 2) : $deltaUp;
        $modDeltaDn = (config('settings.freeleech') || $torrent->free || $isFree || $userfree) ? 0 : $deltaDn;

        
        if ($wasSeeding && $elapsed > 0) {
            $history->seedtime += $elapsed;
        }

        
        $history->uploaded          = (float)$history->uploaded + $modDeltaUp;
        $history->actual_uploaded   = (float)$history->actual_uploaded + $deltaUp;
        $history->client_uploaded   = $real_uploaded;   // track last seen raw client value

        $history->downloaded        = (float)$history->downloaded + $modDeltaDn;
        $history->actual_downloaded = (float)$history->actual_downloaded + $deltaDn;
        $history->client_downloaded = $real_downloaded; // track last seen raw client value

        $history->left = $left;
        $history->save();

        
        $this->handleEvent(
            $event, $client, $history, $user, $torrent,
            $modDeltaUp, $modDeltaDn, $deltaUp, $deltaDn,
            $left
        );

       
        $client->client_updated_at = Carbon::now();
        $client->save();

        
        $torrent->seeders  = Peer::where('torrent_id', $torrent->id)->where('left', 0)->count();
        $torrent->leechers = Peer::where('torrent_id', $torrent->id)->where('left', '>', 0)->count();
        $torrent->save();

        return response(Bencode::bencode([
            'interval'      => 60 * 30,
            'min interval'  => 60 * 10,
            'tracker_id'    => $md5_peer_id,
            'complete'      => $torrent->seeders,
            'incomplete'    => $torrent->leechers,
            'downloaded'    => $torrent->times_completed,
            'peers'         => $this->givePeers($peers, $request->get('compact') == 1, $request->get('no_peer_id') == 1),
        ]))->withHeaders(['Content-Type' => 'text/plain']);
    }

    private function failure($reason)
    {
        return response(Bencode::bencode(['failure reason' => $reason]), 200, ['Content-Type' => 'text/plain']);
    }

    private function countPeers($peers)
    {
        $seeders = 0;
        $leechers = 0;
        foreach ($peers as &$p) {
            if ($p['left'] > 0) $leechers++; else $seeders++;
            unset(
                $p['id'], $p['md5_peer_id'], $p['hash'], $p['agent'],
                $p['uploaded'], $p['downloaded'], $p['left'], $p['torrent_id'],
                $p['user_id'], $p['seeder'], $p['created_at'], $p['updated_at'], $p['client_updated_at']
            );
        }
        return [$seeders, $leechers];
    }

    private function validateIP(Request $request)
    {
        $ip = $request->ip() ?: '0.0.0.0';
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = $request->server('HTTP_X_FORWARDED_FOR') ?: $request->server('REMOTE_ADDR');
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP)) $ip = '0.0.0.0';
        return $ip;
    }

    private function handleEvent(
        $event,
        Peer $client,
        History $history,
        User $user,
        Torrent $torrent,
        float $modDeltaUp,
        float $modDeltaDn,
        float $deltaUp,
        float $deltaDn,
        float $left
    ) {
        switch ($event) {
            case 'started':
                $client->active = true;
                $client->save();
                Cache::flush();
                break;

            case 'completed':
                $client->left = $left;
                $client->seeder = $left == 0 ? 1 : 0;
                $client->active = true;
                $client->save();

                $history->seeder = 1;
                $history->active = true;

               
                $history->completed_at = Carbon::now();
                $history->save();

                
                $user->uploaded  = (float)$user->uploaded  + $modDeltaUp;
                $user->downloaded= (float)$user->downloaded+ $modDeltaDn;
                $user->save();

                $torrent->times_completed++;
                Cache::flush();
                break;

            case 'stopped':
               
                $client->active = false;
                $client->save();

                $history->active = false;
                $history->seeder = false;
                
                $history->client_uploaded   = 0;
                $history->client_downloaded = 0;
                $history->save();

                
                $client->delete();

               
                $user->uploaded  = (float)$user->uploaded  + $modDeltaUp;
                $user->downloaded= (float)$user->downloaded+ $modDeltaDn;
                $user->save();

                Cache::flush();
                break;

            default:
                
                $client->active = true;
                $client->left = $left;
                $client->seeder = $left == 0 ? 1 : 0;
                $client->save();

                $history->active = true;
                $history->seeder = ($left == 0) ? 1 : 0;
                $history->save();

                
                $user->uploaded  = (float)$user->uploaded  + $modDeltaUp;
                $user->downloaded= (float)$user->downloaded+ $modDeltaDn;
                $user->save();
                break;
        }
    }

    private function givePeers($peers, $compact, $no_peer_id)
    {
        if ($compact) {
            $pcomp = "";
            foreach ($peers as $p) {
                if (!isset($p['ip'])) continue;
                $port = $p['port'] ?? 0;

               
                if (filter_var($p['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $pcomp .= pack('Nn', ip2long($p['ip']), $port);
                } elseif (filter_var($p['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $ipBinary = @inet_pton($p['ip']);
                    if ($ipBinary !== false) {
                        $pcomp .= $ipBinary . pack('n', $port);
                    }
                }
            }
            return $pcomp;
        } elseif ($no_peer_id) {
            return array_map(function ($p) {
                return [
                    'ip'   => $p['ip']   ?? '',
                    'port' => $p['port'] ?? 0
                ];
            }, array_filter($peers, fn($p) => isset($p['ip'])));
        } else {
            return array_filter($peers, fn($p) => isset($p['ip']));
        }
    }
}
