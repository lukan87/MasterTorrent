<?php

namespace App\Services;

use App\DTO\AnnounceRequestDTO;
use App\Jobs\UpdateTorrentStats;
use App\Models\HappyHour;
use App\Models\History;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AnnounceService
{
    public function handleAnnounce(AnnounceRequestDTO $dto, User $user, string $ip, string $agent): array
    {

        $hash = $dto->infoHash;
        $peerId = $dto->peerId;
        $md5PeerId = md5($peerId);
        $realUp = $dto->uploaded;
        $realDown = $dto->downloaded;
        $ttl = config('settings.cache_ttl', 120);

        $torrent = Cache::remember("torrent_hash:{$hash}", $ttl, fn () => Torrent::select([
            'id',
            'free',
            'double',
            'external',
            'times_completed',
            'seeders',
            'leechers',
        ])
            ->where('info_hash', $hash)
            ->whereNull('deleted_at')
            ->first()
        );

        if ($torrent && method_exists($torrent, 'trashed') && $torrent->trashed()) {
            return ['failure reason' => 'Torrent is deleted'];
        }

        if (! $torrent) {
            return ['failure reason' => 'Torrent not found'];
        }

        $happyHour = HappyHour::where('active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->latest('start_at')
            ->first();

        $redisKey = "torrent:{$torrent->id}:peers";

        $peers = Peer::where('torrent_id', $torrent->id)
            ->where('client_updated_at', '>', now()->subMinutes(60))
            ->orderByDesc('client_updated_at')
            ->limit(50)
            ->get()
            ->toArray();

        $peers = array_filter($peers, fn ($p) => $p['user_id'] !== $user->id);

        $previousSeeder = null;

        $client = $this->safeTransaction(function () use (
            $torrent,
            $user,
            $dto,
            $ip,
            $agent,
            &$previousSeeder
        ) {

            $oldClient = Peer::where('torrent_id', $torrent->id)
                ->where('user_id', $user->id)
                ->where('peer_id', $dto->peerId)
                ->first();

            $previousSeeder = $oldClient ? (int) $oldClient->seeder : null;

            $client = Peer::updateOrCreate(
                [
                    'torrent_id' => $torrent->id,
                    'user_id' => $user->id,
                ],
                [
                    'peer_id' => $dto->peerId,
                    'md5_peer_id' => md5($dto->peerId),
                    'ip' => $ip,
                    'port' => $dto->port,
                    'agent' => $agent,
                    'uploaded' => $dto->uploaded,
                    'downloaded' => $dto->downloaded,
                    'left' => $dto->left,
                    'seeder' => $dto->left <= 0 ? 1 : 0,
                    'active' => $dto->event !== 'stopped',
                    'client_updated_at' => now(),
                ]
            );

            return $client;
        });

        /*
        |--------------------------------------------------------------------------
        | ✅ FIXED HISTORY + SEEDTIME LOGIC
        |--------------------------------------------------------------------------
        */

        $history = History::firstOrNew([
            'user_id' => $user->id,
            'info_hash' => $hash,
        ]);

        if (! $history->exists) {
            $history->torrent_id = $torrent->id;

            // Optional but recommended defaults
            $history->uploaded = 0;
            $history->downloaded = 0;
            $history->actual_uploaded = 0;
            $history->actual_downloaded = 0;
            $history->client_uploaded = 0;
            $history->client_downloaded = 0;
            $history->seedtime = 0;
        }

        $prevTs = $history->last_event_at;
        $wasSeeding = (bool) $history->seeder;

        $now = now();

        // ✅ Always count previous seeding time (even if client glitches)
        if ($prevTs && $wasSeeding) {
            $delta = max(0, $now->timestamp - strtotime($prevTs));

            // anti-cheat cap (optional)
            $delta = min($delta, 1800);

            $history->seedtime += $delta;

            User::where('id', $user->id)->update([
                'reputation_dirty' => true,
            ]);
        }

        // ✅ Extra safety: stopped event final delta
        if ($dto->event === 'stopped' && $prevTs && $wasSeeding) {
            $delta = max(0, $now->timestamp - $prevTs->timestamp);
            $history->seedtime += $delta;
        }

        /*
        |--------------------------------------------------------------------------
        | ORIGINAL LOGIC CONTINUES (UNCHANGED)
        |--------------------------------------------------------------------------
        */

        $slotKey = "user_slot:{$user->id}:{$torrent->id}";

        $userSlot = Redis::get($slotKey);

        if ($userSlot) {
            $userSlot = json_decode($userSlot);
        } else {
            $userSlot = DB::table('user_slots')
                ->where('user_id', $user->id)
                ->where('torrent_id', $torrent->id)
                ->first();

            Redis::setex($slotKey, 300, json_encode($userSlot));
        }

        $isFree = $userSlot ? (bool) $userSlot->free : false;
        $isDouble = $userSlot ? (bool) $userSlot->double : false;
        $userFree = (bool) $user->is_freeleech;

        if ($dto->event !== 'stopped' && ($realUp > 0 || $realDown > 0)) {
            $deltaUp = max(0, $realUp - (float) $history->client_uploaded);
            $deltaDn = max(0, $realDown - (float) $history->client_downloaded);
        } else {
            $deltaUp = max(0, $realUp - ($client?->uploaded ?? 0));
            $deltaDn = max(0, $realDown - ($client?->downloaded ?? 0));
        }

        $multiplier = 1;
        $freeDownload = false;

        if ($happyHour) {
            $multiplier = $happyHour->upload_multiplier ?? 1;
            $freeDownload = $happyHour->free_download;
        }

        if ($torrent->external) {
            $modDn = 0;
            $modUp = (int) floor($deltaUp * 0.05);
            $deltaDn = 0;
        } else {
            $modUp = $deltaUp * (
                ($torrent->double || $isDouble || config('settings.double') ? 2 : 1)
                * ($happyHour ? ($happyHour->upload_multiplier ?? 1) : 1)
            );

            $modDn = ($torrent->free || $isFree || $userFree || config('settings.freeleech') || $freeDownload)
                ? 0
                : $deltaDn;
        }

        if (! $torrent->external) {
            $history->downloaded += $modDn;
            $history->actual_downloaded += $deltaDn;
        }

        $history->uploaded += $modUp;
        $history->actual_uploaded += $deltaUp;
        $history->client_uploaded = $realUp;
        $history->client_downloaded = $realDown;
        $history->left = $dto->left;
        $history->ip = $ip;
        $history->agent = $agent;
        $history->active = $dto->event !== 'stopped';
        $history->seeder = $dto->left == 0 ? 1 : 0;

        $history->last_event_at = $now;
        $history->last_event = $dto->event ?? 'update';

        $history->save();

        if ($modUp > 0 || $modDn > 0) {
            User::where('id', $user->id)->update([
                'uploaded' => DB::raw('uploaded + '.(int) $modUp),
                'downloaded' => DB::raw('downloaded + '.(int) $modDn),
            ]);
        }

        if ($client) {
            $this->handleEvent(
                $dto,
                $client,
                $history,
                $user,
                $torrent,
                $modUp,
                $modDn,
                $previousSeeder
            );
        }

        if (rand(1, 15) === 1) {
            UpdateTorrentStats::dispatch($torrent->id)
                ->onQueue('announce-low');
        }

        Redis::del("torrent:{$torrent->id}:peers");

        return [
            'peers' => $peers,
            'torrent' => $torrent,
            'tracker_id' => $md5PeerId,
        ];
    }

    // 🔽 EVERYTHING BELOW REMAINS EXACTLY AS YOU HAD IT (UNCHANGED)

    private function handleEvent(
        $dto,
        Peer $client,
        History $history,
        User $user,
        Torrent $torrent,
        float $modUp,
        float $modDn,
        ?int $previousSeeder
    ) {
        $event = $dto->event;
        $left = $dto->left;

        $isSeeder = $left == 0 ? 1 : 0;
        $wasSeeder = $previousSeeder === 1;

        // 🔴 Redis swarm keys
        $seedersKey = "torrent:{$torrent->id}:seeders";
        $leechersKey = "torrent:{$torrent->id}:leechers";
        $peerKey = "{$user->id}:{$client->peer_id}";

        switch ($event) {

            case 'started':

                $client->update([
                    'active' => true,
                    'seeder' => $isSeeder,
                    'left' => $left,
                ]);

                $history->update([
                    'active' => true,
                    'seeder' => $isSeeder,
                    'left' => $left,
                ]);

                break;

            case 'completed':

                $this->safeTransaction(function () use ($client, $history, $torrent, $left) {
                    $client->update([
                        'active' => true,
                        'seeder' => 1,
                        'left' => $left,
                    ]);

                    $history->update([
                        'active' => true,
                        'seeder' => 1,
                        'completed_at' => now(),
                        'left' => $left,
                    ]);

                    $torrent->increment('times_completed');
                });

                break;

            case 'stopped':

                $this->safeTransaction(function () use ($client, $history) {

                    /*
                    |--------------------------------------------------------------------------
                    | Update peer BEFORE delete (important for consistency)
                    |--------------------------------------------------------------------------
                    */

                    $client->update([
                        'active' => false,
                        'seeder' => false,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Update history
                    |--------------------------------------------------------------------------
                    */

                    $history->update([
                        'active' => false,
                        'seeder' => false,
                        'stopped_at' => now(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Delete peer (final step)
                    |--------------------------------------------------------------------------
                    */

                    $client->delete();
                });

                break;

            default:

                $client->update([
                    'active' => true,
                    'seeder' => $isSeeder,
                    'left' => $left,
                ]);

                $history->update([
                    'active' => true,
                    'seeder' => $isSeeder,
                    'left' => $left,
                ]);

                break;
        }

        // 🧹 Cache cleanup
        \Cache::forget("torrent:{$torrent->info_hash}");
    }

    private function safeTransaction(callable $callback, int $maxAttempts = 3)
    {
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction($callback);
            } catch (QueryException $e) {
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
                if (! $ip) {
                    continue;
                }
                $ipBinary = @inet_pton($ip);
                if ($ipBinary === false) {
                    continue;
                }
                $pcomp .= $ipBinary.pack('n', $port);
            }

            return $pcomp;
        }

        $result = [];
        foreach ($peers as $p) {
            if (! isset($p['ip'])) {
                continue;
            }
            $peerData = ['ip' => $p['ip'] ?? '', 'port' => $p['port'] ?? 0];
            if (! $noPeerId && isset($p['peer_id'])) {
                $peerData['peer_id'] = $p['peer_id'];
            }
            $result[] = $peerData;
        }

        return $result;
    }

    private function safeIncrement(Torrent $torrent, string $field): void
    {
        $torrent->newQuery()->where('id', $torrent->id)->increment($field);
    }

    private function safeDecrement(Torrent $torrent, string $field): void
    {
        $torrent->newQuery()
            ->where('id', $torrent->id)
            ->where($field, '>', 0)
            ->decrement($field);
    }
}
