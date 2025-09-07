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
use App\DTO\AnnounceRequestDTO;
use App\Services\Bencode;

class AnnounceController extends Controller
{
    // Check banned clients from config
    private function isBannedClient(string $userAgent): bool
    {
        $bannedClients = config('clients.banned', []);
        foreach ($bannedClients as $client) {
            if (str_contains($userAgent, $client)) return true;
        }
        return false;
    }

    public function announce(Request $request, $passkey)
    {
        $agent = $request->server('HTTP_USER_AGENT') ?: "Unknown";
        $dto   = new AnnounceRequestDTO($request);

        if ($this->isBannedClient($agent)) {
            return $this->failure('Your client is banned. Please use an updated client.');
        }

        if (!$passkey) {
            return $this->failure('Please Call Passkey');
        }

        if (!$dto->infoHash || !$dto->peerId || !$dto->port || $dto->left < 0) {
            return $this->failure('Bad Data from client');
        }

        $user = User::where("passkey", $passkey)->first();
        if (!$user) return $this->failure('Passkey is invalid');
        if ($user->enabled === 'no') return $this->failure('Your account has been disabled. Please contact staff!');
        if ($user->downloadpos === 'no') return $this->failure('Your download privileges are revoked');
        if ($user->hit_and_run_count > 20) return $this->failure('You cannot download any torrents due to more than 20 hit and runs.');

        // Standard info fields
        $hash          = $dto->infoHash;
        $peerId        = $dto->peerId;
        $md5_peer_id   = md5($peerId);
        $uploaded      = $dto->uploaded;
        $downloaded    = $dto->downloaded;
        $real_uploaded = $uploaded;
        $real_downloaded = $downloaded;

        if ($uploaded < 0 || $downloaded < 0 || $dto->left < 0) {
            return $this->failure('Data from client is negative');
        }

        // ✅ Cache torrent object
        $ttl     = config('settings.cache_ttl', 30);
        $torrent = Cache::remember("torrent:{$hash}", $ttl, function () use ($hash) {
            return Torrent::select(['id', 'status', 'free', 'double', 'times_completed', 'seeders', 'leechers'])
                ->with('peers')
                ->where('info_hash', $hash)
                ->first();
        });

        if (!$torrent) return $this->failure('Torrent not found');

        // ✅ Cache peers (excluding current user)
        $peers = Cache::remember("torrent:{$torrent->id}:peers:{$user->id}", $ttl, function () use ($torrent, $user) {
            return Peer::where('torrent_id', $torrent->id)
                ->where('user_id', '!=', $user->id)
                ->take(50)
                ->get()
                ->toArray();
        });

        [$seeders, $leechers] = $this->countPeers($peers);
        $ip = $this->validateIP($request);

        $ghost = $dto->event !== 'stopped' && ($uploaded > 0 || $downloaded > 0);
        if ($ghost && $dto->event !== 'completed') $dto->event = 'started';

        $old_client = Peer::where('torrent_id', $torrent->id)
            ->where('user_id', $user->id)
            ->where('peer_id', $peerId)
            ->first();

        $prevUpdateTs = $old_client && $old_client->client_updated_at
            ? $old_client->client_updated_at->timestamp
            : Carbon::now()->timestamp;

        $wasSeeding = $old_client ? ((int)$old_client->seeder === 1) : false;

        $connectable = $this->checkConnectable($ip, $dto->port);

        $client = Peer::updateOrCreate(
            ['torrent_id' => $torrent->id, 'user_id' => $user->id, 'peer_id' => $peerId],
            [
                'md5_peer_id' => $md5_peer_id,
                'ip'          => $ip,
                'port'        => $dto->port,
                'agent'       => $agent,
                'uploaded'    => $real_uploaded,
                'downloaded'  => $real_downloaded,
                'left'        => $dto->left,
                'seeder'      => $dto->left == 0 ? 1 : 0,
                'active'      => $dto->event !== 'stopped',
                'connectable' => $connectable, 
            ]
        );

        $elapsed = max(0, Carbon::now()->timestamp - $prevUpdateTs);

        $history = History::firstOrCreate(
            ['user_id' => $user->id, 'info_hash' => $hash],
            [
                'torrent_id' => $torrent->id,
                'ip'         => $ip,
                'agent'      => $agent,
                'active'     => true,
                'seeder'     => $dto->left == 0 ? 1 : 0,
                'seedtime'   => 0,
            ]
        );

        // Determine free/double
        $userSlot  = DB::table('user_slots')->where('user_id', $user->id)->where('torrent_id', $torrent->id)->first();
        $isFree    = $userSlot ? (bool)$userSlot->free : false;
        $isDouble  = $userSlot ? (bool)$userSlot->double : false;
        $userfree  = (bool)$user->is_freeleech;

        $history->ip     = $ip;
        $history->agent  = $agent;
        $history->active = $dto->event !== 'stopped';
        $history->seeder = ($dto->left == 0) ? 1 : 0;

        if ($ghost) {
            $deltaUp = max(0, $real_uploaded - (float)$history->client_uploaded);
            $deltaDn = max(0, $real_downloaded - (float)$history->client_downloaded);
        } else {
            $previousUp = $old_client ? (float)$old_client->uploaded : 0.0;
            $previousDn = $old_client ? (float)$old_client->downloaded : 0.0;
            $deltaUp    = max(0, $real_uploaded - $previousUp);
            $deltaDn    = max(0, $real_downloaded - $previousDn);
        }

        $modDeltaUp = (config('settings.double') || $torrent->double || $isDouble) ? ($deltaUp * 2) : $deltaUp;
        $modDeltaDn = (config('settings.freeleech') || $torrent->free || $isFree || $userfree) ? 0 : $deltaDn;

        if ($wasSeeding && $elapsed > 0) {
            $history->seedtime += $elapsed;
        }

        $history->uploaded          += $modDeltaUp;
        $history->actual_uploaded   += $deltaUp;
        $history->client_uploaded    = $real_uploaded;

        $history->downloaded        += $modDeltaDn;
        $history->actual_downloaded += $deltaDn;
        $history->client_downloaded  = $real_downloaded;

        $history->left = $dto->left;
        $history->save();

        $this->handleEvent($dto, $client, $history, $user, $torrent, $modDeltaUp, $modDeltaDn);

        $client->client_updated_at = Carbon::now();
        $client->save();

        // Update torrent stats in DB (but cache may serve slightly stale values)
        $torrent->seeders  = Peer::where('torrent_id', $torrent->id)->where('left', 0)->count();
        $torrent->leechers = Peer::where('torrent_id', $torrent->id)->where('left', '>', 0)->count();
        $torrent->save();

        // Invalidate caches for this torrent after update
        Cache::forget("torrent:{$torrent->info_hash}");
        Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");

        return response(Bencode::bencode([
            'interval'     => config('settings.announce_interval'),
            'min interval' => config('settings.min_interval'),
            'tracker_id'   => $md5_peer_id,
            'complete'     => $torrent->seeders,
            'incomplete'   => $torrent->leechers,
            'downloaded'   => $torrent->times_completed,
            'peers'        => $this->givePeers($peers, $dto->compact, $dto->noPeerId),
        ]))->withHeaders(['Content-Type' => 'text/plain']);
    }

    private function failure($reason)
    {
        return response(Bencode::bencode(['failure reason' => $reason]), 200, ['Content-Type' => 'text/plain']);
    }

    private function countPeers($peers)
    {
        $seeders = $leechers = 0;
        foreach ($peers as &$p) {
            if ($p['left'] > 0) $leechers++; else $seeders++;
            unset($p['id'], $p['md5_peer_id'], $p['hash'], $p['agent'], $p['uploaded'], $p['downloaded'], $p['left'], $p['torrent_id'], $p['user_id'], $p['seeder'], $p['created_at'], $p['updated_at'], $p['client_updated_at']);
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
        AnnounceRequestDTO $dto,
        Peer $client,
        History $history,
        User $user,
        Torrent $torrent,
        float $modDeltaUp,
        float $modDeltaDn
    ) {
        $left  = $dto->left;
        $event = $dto->event;

        // Update connectable status for started or completed events
    if (in_array($event, ['started', 'completed'])) {
        $client->connectable = $this->checkConnectable($client->ip, $client->port);
    }

        switch ($event) {
            case 'started':
                $client->active = true;
                $client->save();
                Cache::forget("torrent:{$torrent->info_hash}");
                break;

            case 'completed':
                $client->left   = $left;
                $client->seeder = $left == 0 ? 1 : 0;
                $client->active = true;
                $client->save();

                $history->seeder       = 1;
                $history->active       = true;
                $history->completed_at = Carbon::now();
                $history->save();

                $user->uploaded   += $modDeltaUp;
                $user->downloaded += $modDeltaDn;
                $user->save();

                $torrent->times_completed++;
                $torrent->save();

                Cache::forget("torrent:{$torrent->info_hash}");
                Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");
                break;

               case 'stopped':
                $client->active  = false;
                $client->seeder  = 0;  
                $client->left    = $left;
                $client->save();
                $client->delete(); 
              
                $history->active            = false;
                $history->seeder            = false;
                $history->client_uploaded   = 0;
                $history->client_downloaded = 0;
                $history->save();

                 $user->uploaded   += $modDeltaUp;
                 $user->downloaded += $modDeltaDn;
                 $user->save();

                  Cache::forget("torrent:{$torrent->info_hash}");
                  Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");
                  break;

            default:
                $client->active = true;
                $client->left   = $left;
                $client->seeder = $left == 0 ? 1 : 0;
                $client->save();

                $history->active = true;
                $history->seeder = ($left == 0) ? 1 : 0;
                $history->save();

                $user->uploaded   += $modDeltaUp;
                $user->downloaded += $modDeltaDn;
                $user->save();

                Cache::forget("torrent:{$torrent->info_hash}");
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
                    if ($ipBinary !== false) $pcomp .= $ipBinary . pack('n', $port);
                }
            }
            return $pcomp;
        } elseif ($no_peer_id) {
            return array_map(fn($p) => ['ip' => $p['ip'] ?? '', 'port' => $p['port'] ?? 0], array_filter($peers, fn($p) => isset($p['ip'])));
        } else {
            return array_filter($peers, fn($p) => isset($p['ip']));
        }
    }

    private function checkConnectable(string $ip, int $port): bool
{
    $timeout = 3; // seconds
    $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

}
