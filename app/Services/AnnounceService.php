<?php

namespace App\Services;

use App\DTO\AnnounceRequestDTO;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\User;
use App\Models\History;
use App\Models\HappyHour;
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
        $ttl       = config('settings.cache_ttl', 30);

        // --- STEP 1: Cache torrent ---
        $torrent = Cache::remember("torrent:{$hash}", $ttl, fn() =>
            Torrent::select(['id', 'status', 'free', 'double', 'times_completed', 'seeders', 'leechers'])
                ->where('info_hash', $hash)
                ->first()
        );

        if (!$torrent) {
            return ['failure reason' => 'Torrent not found'];
        }

        // --- STEP HH: Check for active Happy Hour ---
       $happyHour = HappyHour::where('active', true)
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now())
        ->latest('start_at')
        ->first();

        // --- STEP 2: Cache peers ---
        $peers = Cache::remember("torrent:{$torrent->id}:peers", $ttl, fn() =>
            Peer::where('torrent_id', $torrent->id)
                ->take(50)
                ->get()
                ->toArray()
        );

        // Remove announcing user
        $peers = array_filter($peers, fn($p) => $p['user_id'] !== $user->id);

        // --- STEP 3: Check connectable ---
        $connectable = Cache::remember("peer:connectable:{$ip}:{$dto->port}", 300, fn() =>
            $this->checkConnectable($ip, $dto->port)
        );

        $prevTs = Carbon::now();
        $wasSeeding = false;

        // --- STEP 4: Upsert Peer safely ---
        $client = $this->safeTransaction(function () use ($torrent, $user, $peerId, $md5PeerId, $ip, $dto, $agent, $connectable, &$prevTs, &$wasSeeding) {
            $oldClient = Peer::where('torrent_id', $torrent->id)
                ->where('user_id', $user->id)
                ->where('peer_id', $dto->peerId)
                ->lockForUpdate()
                ->first();

            $prevTs = $oldClient?->updated_at ?? Carbon::now();
            $wasSeeding = $oldClient ? (int)$oldClient->seeder === 1 : false;

           return Peer::updateOrCreate(
            ['torrent_id' => $torrent->id, 'user_id' => $user->id, 'peer_id' => $peerId],
            [
                'md5_peer_id'       => $md5PeerId,
                'ip'                => $ip,
                'port'              => $dto->port,
                'agent'             => $agent,
                'uploaded'          => $dto->uploaded,
                'downloaded'        => $dto->downloaded,
                'left'              => $dto->left,
                'seeder'            => $dto->left == 0 ? 1 : 0,
                'active'            => $dto->event !== 'stopped',
                'connectable'       => $connectable,
                'client_updated_at' => now(),
            ]
        );
        });

        // --- STEP 5: Update History + seedtime ---
        $history = $this->safeTransaction(function () use ($user, $hash, $torrent, $ip, $agent, $dto) {
            return History::updateOrCreate(
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
        });

        // --- STEP 6: Calculate seedtime ---
        $now = Carbon::now();
        if ($wasSeeding) {
            $delta = max(0, $now->timestamp - $prevTs->timestamp);
            $history->seedtime += $delta;
        }

        // --- STEP 7: Calculate deltas for upload/download ---
        $userSlot = DB::table('user_slots')->where('user_id', $user->id)->where('torrent_id', $torrent->id)->first();
        $isFree   = $userSlot ? (bool)$userSlot->free : false;
        $isDouble = $userSlot ? (bool)$userSlot->double : false;
        $userFree = (bool)$user->is_freeleech;

        if ($dto->event !== 'stopped' && ($realUp > 0 || $realDown > 0)) {
            $deltaUp = max(0, $realUp - (float)$history->client_uploaded);
            $deltaDn = max(0, $realDown - (float)$history->client_downloaded);
        } else {
            $deltaUp = max(0, $realUp - ($client?->uploaded ?? 0));
            $deltaDn = max(0, $realDown - ($client?->downloaded ?? 0));
        }
        //Happy Hour//
        $multiplier = 1;
        $freeDownload = false;

          if ($happyHour) {
           $multiplier = $happyHour->upload_multiplier ?? 1;
           $freeDownload = $happyHour->free_download;
                          }

        //Happy Hour

        $modUp = ($torrent->double || $isDouble || config('settings.double') || $happyHour) ? $deltaUp * $multiplier : $deltaUp;

        $modDn = ($torrent->free || $isFree || $userFree || config('settings.freeleech') || $freeDownload) ? 0 : $deltaDn;

        // --- STEP 8: Update history ---
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

if ($client) {
        $this->handleEvent($dto, $client, $history, $user, $torrent, $modUp, $modDn);
}
        // --- STEP 9: Incremental seeders/leechers safely ---
      // Recalculate torrent stats
if (in_array($dto->event, ['started', 'completed', 'stopped'])) {
    DB::afterCommit(fn() => $this->updateTorrentStats($torrent));
}


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

    switch ($event) {
        case 'started':
            $client->update([
                'active'    => true,
                'seeder'    => $left == 0 ? 1 : 0,
                'left'      => $left,
                'connectable' => $this->checkConnectable($client->ip, $client->port),
            ]);
            $history->update([
                'active' => true,
                'seeder' => $left == 0 ? 1 : 0,
                'left'   => $left,
            ]);
            break;

        case 'completed':
            $this->safeTransaction(function () use ($client, $history, $torrent, $left) {
                $client->update(['active' => true, 'seeder' => 1, 'left' => $left]);
                $history->update(['active' => true, 'seeder' => 1, 'completed_at' => now(), 'left' => $left]);
                $torrent->increment('times_completed');
            });
            break;

        case 'stopped':
            $this->safeTransaction(function () use ($client, $history, $torrent) {
                $client->delete();
                $history->update(['active' => false, 'seeder' => false]);
            });
            break;

        default:
            $client->update([
                'active' => true,
                'seeder' => $left == 0 ? 1 : 0,
                'left'   => $left,
            ]);
            $history->update([
                'active' => true,
                'seeder' => $left == 0 ? 1 : 0,
                'left'   => $left,
            ]);
            break;
    }

    // Update user stats
    $user->increment('uploaded', $modUp);
    $user->increment('downloaded', $modDn);

    // Recalculate torrent stats and clear cache immediately
    $this->updateTorrentStats($torrent);
}


private function updateTorrentStats(Torrent $torrent): void
{
    $counts = Peer::where('torrent_id', $torrent->id)
        ->selectRaw('
            SUM(CASE WHEN seeder = 1 THEN 1 ELSE 0 END) as seeders,
            SUM(CASE WHEN seeder = 0 THEN 1 ELSE 0 END) as leechers
        ')
        ->first();

    if ($counts) {
        $torrent->updateQuietly([
            'seeders'  => (int) $counts->seeders,
            'leechers' => (int) $counts->leechers,
            'updated_at' => now(),
        ]);

      // Invalidate caches
        Cache::forget("torrent:{$torrent->info_hash}");
        Cache::forget("torrent:{$torrent->id}:peers");

        // Optional: refresh torrent cache immediately
        Cache::put("torrent:{$torrent->info_hash}", $torrent, config('settings.cache_ttl', 30));
    }
}



    private function safeTransaction(callable $callback, int $maxAttempts = 3)
    {
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction($callback);
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'Deadlock')) {
                    $attempts++;
                    usleep(100000 * $attempts);
                    continue;
                }
                throw $e;
            }
        }
        \Log::warning("Deadlock persisted after {$maxAttempts} retries in AnnounceService::safeTransaction");
        return null;
    }

    public function givePeers(array $peers, bool $compact, bool $noPeerId): mixed
    {
        if ($compact) {
            $pcomp = '';
            foreach ($peers as $p) {
                $ip = $p['ip'] ?? null;
                $port = $p['port'] ?? 0;
                if (!$ip) continue;
                $ipBinary = @inet_pton($ip);
                if ($ipBinary === false) continue;
                $pcomp .= $ipBinary . pack('n', $port);
            }
            return $pcomp;
        }

        $result = [];
        foreach ($peers as $p) {
            if (!isset($p['ip'])) continue;
            $peerData = ['ip' => $p['ip'] ?? '', 'port' => $p['port'] ?? 0];
            if (!$noPeerId && isset($p['peer_id'])) {
                $peerData['peer_id'] = $p['peer_id'];
            }
            $result[] = $peerData;
        }

        return $result;
    }

    private function checkConnectable(string $ip, int $port): bool
    {
        $fp = @fsockopen($ip, $port, $errno, $errstr, 3);
        if ($fp) { fclose($fp); return true; }
        return false;
    }
}