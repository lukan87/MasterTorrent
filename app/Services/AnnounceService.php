<?php

namespace App\Services;

use App\DTO\AnnounceRequestDTO;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\User;
use App\Models\History;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnounceService
{
    public function handleAnnounce(AnnounceRequestDTO $dto, User $user, string $ip, string $agent): array
    {
        $hash      = $dto->infoHash;
        $peerId    = $dto->peerId;
        $md5PeerId = md5($peerId);
        $realUp    = $dto->uploaded;
        $realDown  = $dto->downloaded;

        $ttl = config('settings.cache_ttl', 30);

        // Load torrent
        $torrent = Cache::remember("torrent:{$hash}", $ttl, function () use ($hash) {
            return Torrent::select(['id', 'status', 'free', 'double', 'times_completed', 'seeders', 'leechers'])
                ->with('peers')
                ->where('info_hash', $hash)
                ->first();
        });

        if (!$torrent) return ['failure reason' => 'Torrent not found'];

        // Load peers
        $peers = Cache::remember("torrent:{$torrent->id}:peers:{$user->id}", $ttl, function () use ($torrent, $user) {
            return Peer::where('torrent_id', $torrent->id)
                ->where('user_id', '!=', $user->id)
                ->take(50)
                ->get()
                ->toArray();
        });

        // Existing peer
        $oldClient = Peer::where('torrent_id', $torrent->id)
            ->where('user_id', $user->id)
            ->where('peer_id', $peerId)
            ->first();

        $prevTs = $oldClient?->client_updated_at?->timestamp ?? Carbon::now()->timestamp;
        $wasSeeding = $oldClient ? (int)$oldClient->seeder === 1 : false;

        $connectable = $this->checkConnectable($ip, $dto->port);

        // Update or create peer
        $client = Peer::updateOrCreate(
            ['torrent_id' => $torrent->id, 'user_id' => $user->id, 'peer_id' => $peerId],
            [
                'md5_peer_id' => $md5PeerId,
                'ip'          => $ip,
                'port'        => $dto->port,
                'agent'       => $agent,
                'uploaded'    => $realUp,
                'downloaded'  => $realDown,
                'left'        => $dto->left,
                'seeder'      => $dto->left == 0 ? 1 : 0,
                'active'      => $dto->event !== 'stopped',
                'connectable' => $connectable,
            ]
        );

        $elapsed = max(0, Carbon::now()->timestamp - $prevTs);

        // History
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

        // Free/double
        $userSlot = DB::table('user_slots')->where('user_id', $user->id)->where('torrent_id', $torrent->id)->first();
        $isFree   = $userSlot ? (bool)$userSlot->free : false;
        $isDouble = $userSlot ? (bool)$userSlot->double : false;
        $userFree = (bool)$user->is_freeleech;

        // Calculate deltas
        if ($dto->event !== 'stopped' && ($realUp > 0 || $realDown > 0)) {
            $deltaUp = max(0, $realUp - (float)$history->client_uploaded);
            $deltaDn = max(0, $realDown - (float)$history->client_downloaded);
        } else {
            $deltaUp = max(0, $realUp - ($oldClient?->uploaded ?? 0));
            $deltaDn = max(0, $realDown - ($oldClient?->downloaded ?? 0));
        }

        $modUp = ($torrent->double || $isDouble || config('settings.double')) ? $deltaUp * 2 : $deltaUp;
        $modDn = ($torrent->free || $isFree || $userFree || config('settings.freeleech')) ? 0 : $deltaDn;

        if ($wasSeeding && $elapsed > 0) {
            $history->seedtime += $elapsed;
        }

        // Update history
        $history->uploaded          += $modUp;
        $history->actual_uploaded   += $deltaUp;
        $history->client_uploaded    = $realUp;

        $history->downloaded        += $modDn;
        $history->actual_downloaded += $deltaDn;
        $history->client_downloaded  = $realDown;

        $history->left    = $dto->left;
        $history->ip      = $ip;
        $history->agent   = $agent;
        $history->active  = $dto->event !== 'stopped';
        $history->seeder  = $dto->left == 0 ? 1 : 0;
        $history->save();

        // Handle event
        $this->handleEvent($dto, $client, $history, $user, $torrent, $modUp, $modDn);

        // Update client timestamp
        $client->client_updated_at = Carbon::now();
        $client->save();

        // Update torrent stats
        $torrent->seeders  = Peer::where('torrent_id', $torrent->id)->where('left', 0)->count();
        $torrent->leechers = Peer::where('torrent_id', $torrent->id)->where('left', '>', 0)->count();
        $torrent->save();

        // Clear cache
        Cache::forget("torrent:{$torrent->info_hash}");
        Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");

        // Return final response
        return [
            'peers'      => $peers,
            'torrent'    => $torrent,
            'tracker_id' => $md5PeerId,
        ];
    }

    private function handleEvent($dto, Peer $client, History $history, User $user, Torrent $torrent, float $modUp, float $modDn)
    {
        $event = $dto->event;
        $left  = $dto->left;

        if (in_array($event, ['started', 'completed'])) {
            $client->connectable = $this->checkConnectable($client->ip, $client->port);
        }

        switch ($event) {
            case 'started':
                $client->active = true;
                $client->save();
                break;

            case 'completed':
                $client->active = true;
                $client->seeder = 1;
                $client->left   = $left;
                $client->save();

                $history->active = true;
                $history->seeder = 1;
                $history->completed_at = Carbon::now();
                $history->save();

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

                $history->active = false;
                $history->seeder = false;
                // Do not reset uploaded/downloaded
                $history->save();
                Cache::forget("torrent:{$torrent->info_hash}");
                Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");
                break;

            default:
                $client->active = true;
                $client->left   = $left;
                $client->seeder = $left == 0 ? 1 : 0;
                $client->save();

                $history->active = true;
                $history->seeder = $left == 0 ? 1 : 0;
                $history->save();
                break;
        }

        // Update user totals
        $user->uploaded   += $modUp;
        $user->downloaded += $modDn;
        $user->save();
    }

    public function givePeers(array $peers, bool $compact, bool $noPeerId): mixed
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
        }

        if ($noPeerId) {
            return array_map(fn($p) => ['ip' => $p['ip'] ?? '', 'port' => $p['port'] ?? 0], array_filter($peers, fn($p) => isset($p['ip'])));
        }

        return array_filter($peers, fn($p) => isset($p['ip']));
    }

    private function checkConnectable(string $ip, int $port): bool
    {
        $fp = @fsockopen($ip, $port, $errno, $errstr, 3);
        if ($fp) { fclose($fp); return true; }
        return false;
    }
}
