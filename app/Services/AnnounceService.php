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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AnnounceService
{
    /**
     * Handle tracker announce.
     */
    public function handleAnnounce(
        AnnounceRequestDTO $dto,
        User $user,
        string $ip,
        string $agent
    ): array {
        $hash = $dto->infoHash;
        $peerId = $dto->peerId;

        $realUp = max(0, (int) $dto->uploaded);
        $realDown = max(0, (int) $dto->downloaded);
        $left = max(0, (int) $dto->left);

        $event = $dto->event ?: 'update';

        $isStopped = $event === 'stopped';
        $isSeeder = $left === 0;

        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Torrent
        |--------------------------------------------------------------------------
        */

        $ttl = (int) config(
            'settings.cache_ttl',
            120
        );

        $torrent = Cache::remember(
            "torrent_hash:{$hash}",
            $ttl,
            fn () => Torrent::query()
                ->select([
                    'id',
                    'info_hash',
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

        if (! $torrent) {
            return [
                'failure reason' => 'Torrent not found',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Happy Hour
        |--------------------------------------------------------------------------
        */

        $happyHour = $this->getActiveHappyHour();

        /*
        |--------------------------------------------------------------------------
        | Peers returned to torrent client
        |--------------------------------------------------------------------------
        */

        $peers = Peer::query()
            ->select([
                'user_id',
                'peer_id',
                'ip',
                'port',
            ])
            ->where(
                'torrent_id',
                $torrent->id
            )
            ->where(
                'active',
                true
            )
            ->where(
                'client_updated_at',
                '>',
                $now->copy()->subMinutes(60)
            )
            ->where(
                'user_id',
                '!=',
                $user->id
            )
            ->orderByDesc(
                'client_updated_at'
            )
            ->limit(50)
            ->get()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Previous peer state
        |--------------------------------------------------------------------------
        |
        | A peer is identified by:
        |
        | torrent_id + user_id + peer_id
        |
        */

        $oldClient = Peer::query()
            ->select([
                'id',
                'torrent_id',
                'user_id',
                'peer_id',
                'uploaded',
                'downloaded',
                'seeder',
                'active',
            ])
            ->where(
                'torrent_id',
                $torrent->id
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'peer_id',
                $peerId
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Previous state
        |--------------------------------------------------------------------------
        */

        $peerPreviouslyExisted =
            $oldClient !== null;

        $peerPreviouslyActive =
            $oldClient
                ? (bool) $oldClient->active
                : false;

        $previousSeeder =
            $oldClient
                ? (bool) $oldClient->seeder
                : null;

        /*
        |--------------------------------------------------------------------------
        | Per-peer accounting
        |--------------------------------------------------------------------------
        |
        | Torrent clients report cumulative upload/download counters.
        |
        | Calculate the difference against this exact peer's previous
        | counters.
        |
        */

        if ($oldClient) {
            $previousUploaded = max(
                0,
                (int) $oldClient->uploaded
            );

            $previousDownloaded = max(
                0,
                (int) $oldClient->downloaded
            );
        } else {
            /*
             * New peer.
             *
             * Current counters become its baseline.
             */

            $previousUploaded = $realUp;
            $previousDownloaded = $realDown;
        }

        $deltaUp = max(
            0,
            $realUp - $previousUploaded
        );

        $deltaDn = max(
            0,
            $realDown - $previousDownloaded
        );

        /*
        |--------------------------------------------------------------------------
        | Create/update peer
        |--------------------------------------------------------------------------
        */

        $client = $this->safeTransaction(
            function () use (
                $torrent,
                $user,
                $dto,
                $peerId,
                $ip,
                $agent,
                $realUp,
                $realDown,
                $left,
                $isSeeder,
                $isStopped,
                $now
            ) {
                return Peer::updateOrCreate(
                    [
                        'torrent_id' =>
                            $torrent->id,

                        'user_id' =>
                            $user->id,

                        'peer_id' =>
                            $peerId,
                    ],
                    [
                        'md5_peer_id' =>
                            md5($peerId),

                        'ip' =>
                            $ip,

                        'port' =>
                            $dto->port,

                        'agent' =>
                            $agent,

                        'uploaded' =>
                            $realUp,

                        'downloaded' =>
                            $realDown,

                        'left' =>
                            $left,

                        'seeder' =>
                            $isSeeder,

                        'active' =>
                            ! $isStopped,

                        'client_updated_at' =>
                            $now,
                    ]
                );
            }
        );

        if (! $client) {
            return [
                'failure reason' =>
                    'Unable to update peer',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | History
        |--------------------------------------------------------------------------
        */

        $history = History::firstOrNew([
            'user_id' =>
                $user->id,

            'info_hash' =>
                $hash,
        ]);

        if (! $history->exists) {
            $history->torrent_id =
                $torrent->id;

            $history->uploaded = 0;
            $history->downloaded = 0;

            $history->actual_uploaded = 0;
            $history->actual_downloaded = 0;

            $history->client_uploaded = 0;
            $history->client_downloaded = 0;

            $history->seedtime = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Seed time
        |--------------------------------------------------------------------------
        */

        $previousEventAt =
            $history->last_event_at;

        $historyWasSeeding =
            (bool) $history->seeder;

        if (
            $previousEventAt &&
            $historyWasSeeding
        ) {
            $previousTimestamp =
                $previousEventAt
                    instanceof \DateTimeInterface

                    ? $previousEventAt
                        ->getTimestamp()

                    : strtotime(
                        (string) $previousEventAt
                    );

            if ($previousTimestamp !== false) {
                $seedDelta = max(
                    0,
                    $now->timestamp
                        - $previousTimestamp
                );

                /*
                 * Maximum seed time credited from
                 * one announce interval = 30 minutes.
                 */

                $seedDelta = min(
                    $seedDelta,
                    1800
                );

                if ($seedDelta > 0) {
                    $history->seedtime =
                        (int) (
                            $history->seedtime
                            ?? 0
                        )
                        + $seedDelta;

                    User::query()
                        ->whereKey(
                            $user->id
                        )
                        ->update([
                            'reputation_dirty' =>
                                true,
                        ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | User slot
        |--------------------------------------------------------------------------
        */

        $slotKey =
            "user_slot:{$user->id}:{$torrent->id}";

        $cachedSlot =
            Redis::get($slotKey);

        if ($cachedSlot !== null) {
            $userSlot =
                json_decode($cachedSlot);
        } else {
            $userSlot =
                DB::table('user_slots')
                    ->select([
                        'free',
                        'double',
                    ])
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'torrent_id',
                        $torrent->id
                    )
                    ->first();

            /*
             * json_encode(null) produces "null",
             * giving us negative caching too.
             */

            Redis::setex(
                $slotKey,
                300,
                json_encode($userSlot)
            );
        }

        $slotFree =
            $userSlot
                ? (bool) $userSlot->free
                : false;

        $slotDouble =
            $userSlot
                ? (bool) $userSlot->double
                : false;

        /*
        |--------------------------------------------------------------------------
        | Happy Hour modifiers
        |--------------------------------------------------------------------------
        */

        $happyHourMultiplier = max(
            1.0,
            (float) (
                $happyHour[
                    'upload_multiplier'
                ]
                ?? 1
            )
        );

        $happyHourFreeDownload =
            (bool) (
                $happyHour[
                    'free_download'
                ]
                ?? false
            );

        /*
        |--------------------------------------------------------------------------
        | Upload/download modifiers
        |--------------------------------------------------------------------------
        */

        if ($torrent->external) {
            /*
             * External torrents:
             *
             * Upload = 5%
             * Download = free
             */

            $modUp = (int) floor(
                $deltaUp * 0.05
            );

            $modDn = 0;

            $actualDownloadDelta = 0;
        } else {
            /*
             * Normal upload multiplier.
             */

            $uploadMultiplier = (
                (bool) $torrent->double
                ||
                $slotDouble
                ||
                (bool) config(
                    'settings.double'
                )
            ) ? 2 : 1;

            /*
             * Apply Happy Hour multiplier.
             */

            $modUp = (int) floor(
                $deltaUp
                * $uploadMultiplier
                * $happyHourMultiplier
            );

            /*
             * Freeleech conditions.
             */

            $freeDownload =
                (bool) $torrent->free
                ||
                $slotFree
                ||
                (bool) $user->is_freeleech
                ||
                (bool) config(
                    'settings.freeleech'
                )
                ||
                $happyHourFreeDownload;

            $modDn =
                $freeDownload
                    ? 0
                    : $deltaDn;

            /*
             * actual_downloaded records real
             * traffic even during freeleech.
             */

            $actualDownloadDelta =
                $deltaDn;
        }

        /*
        |--------------------------------------------------------------------------
        | Completion
        |--------------------------------------------------------------------------
        |
        | Count completion only when a previously known leecher
        | transitions to seeder.
        |
        */

        $completedNow =
            $event === 'completed'
            &&
            $isSeeder
            &&
            $previousSeeder === false;

        /*
        |--------------------------------------------------------------------------
        | Update history
        |--------------------------------------------------------------------------
        */

        if (! $torrent->external) {
            $history->downloaded =
                (int) (
                    $history->downloaded
                    ?? 0
                )
                + $modDn;

            $history->actual_downloaded =
                (int) (
                    $history->actual_downloaded
                    ?? 0
                )
                + $actualDownloadDelta;
        }

        $history->uploaded =
            (int) (
                $history->uploaded
                ?? 0
            )
            + $modUp;

        $history->actual_uploaded =
            (int) (
                $history->actual_uploaded
                ?? 0
            )
            + $deltaUp;

        /*
         * Keep cumulative client counters
         * for compatibility.
         */

        $history->client_uploaded =
            $realUp;

        $history->client_downloaded =
            $realDown;

        $history->left =
            $left;

        $history->ip =
            $ip;

        $history->agent =
            $agent;

        $history->active =
            ! $isStopped;

        $history->seeder =
            $isStopped
                ? false
                : $isSeeder;

        $history->last_event_at =
            $now;

        $history->last_event =
            $event;

        if ($completedNow) {
            $history->completed_at =
                $now;
        }

        /*
         * No stopped_at.
         *
         * The history table does not have
         * that column.
         */

        $history->save();

        /*
        |--------------------------------------------------------------------------
        | Update user's global totals
        |--------------------------------------------------------------------------
        */

        if (
            $modUp > 0 ||
            $modDn > 0
        ) {
            User::query()
                ->whereKey(
                    $user->id
                )
                ->update([
                    'uploaded' =>
                        DB::raw(
                            'uploaded + '
                            .(int) $modUp
                        ),

                    'downloaded' =>
                        DB::raw(
                            'downloaded + '
                            .(int) $modDn
                        ),
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Torrent swarm counters
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT count the entire swarm here.
        |
        | We only adjust the counter affected by THIS peer.
        |
        | This keeps announce processing O(1) instead of counting every
        | peer belonging to the torrent.
        |
        */

        $counterChanged = false;

        /*
         * NEW ACTIVE PEER
         *
         * The peer did not exist before, or existed but was inactive.
         */

        if (
            ! $isStopped
            &&
            (
                ! $peerPreviouslyExisted
                ||
                ! $peerPreviouslyActive
            )
        ) {
            if ($isSeeder) {
                $this->incrementSeeder(
                    $torrent->id
                );

                $torrent->seeders =
                    (int) $torrent->seeders + 1;
            } else {
                $this->incrementLeecher(
                    $torrent->id
                );

                $torrent->leechers =
                    (int) $torrent->leechers + 1;
            }

            $counterChanged = true;
        }

        /*
         * EXISTING ACTIVE PEER CHANGED STATE
         *
         * Leecher -> Seeder
         */

        elseif (
            ! $isStopped
            &&
            $peerPreviouslyActive
            &&
            $previousSeeder === false
            &&
            $isSeeder === true
        ) {
            $this->leecherToSeeder(
                $torrent->id
            );

            $torrent->leechers = max(
                0,
                (int) $torrent->leechers - 1
            );

            $torrent->seeders =
                (int) $torrent->seeders + 1;

            $counterChanged = true;
        }

        /*
         * Seeder -> Leecher
         *
         * Rare, but possible if the client
         * rechecks/re-downloads data.
         */

        elseif (
            ! $isStopped
            &&
            $peerPreviouslyActive
            &&
            $previousSeeder === true
            &&
            $isSeeder === false
        ) {
            $this->seederToLeecher(
                $torrent->id
            );

            $torrent->seeders = max(
                0,
                (int) $torrent->seeders - 1
            );

            $torrent->leechers =
                (int) $torrent->leechers + 1;

            $counterChanged = true;
        }

        /*
         * STOPPED PEER
         *
         * Only decrement if this peer was previously
         * active. This prevents duplicate stopped
         * announces from decrementing twice.
         */

        elseif (
            $isStopped
            &&
            $peerPreviouslyActive
        ) {
            if ($previousSeeder === true) {
                $this->decrementSeeder(
                    $torrent->id
                );

                $torrent->seeders = max(
                    0,
                    (int) $torrent->seeders - 1
                );
            } else {
                $this->decrementLeecher(
                    $torrent->id
                );

                $torrent->leechers = max(
                    0,
                    (int) $torrent->leechers - 1
                );
            }

            $counterChanged = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Completion counter
        |--------------------------------------------------------------------------
        */

        if ($completedNow) {
            DB::table('torrents')
                ->where(
                    'id',
                    $torrent->id
                )
                ->increment(
                    'times_completed'
                );

            $torrent->times_completed =
                (int) $torrent->times_completed
                + 1;

            $counterChanged = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove stopped peer
        |--------------------------------------------------------------------------
        |
        | Counter has already been adjusted using the previous state.
        |
        */

        if ($isStopped) {
            Peer::query()
                ->whereKey(
                    $client->id
                )
                ->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | Invalidate torrent cache
        |--------------------------------------------------------------------------
        |
        | Only necessary when a torrent counter changed.
        |
        */

        if ($counterChanged) {
            Cache::forget(
                "torrent_hash:{$hash}"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Background reconciliation
        |--------------------------------------------------------------------------
        |
        | Increment/decrement operations are fast, but counters can eventually
        | drift if:
        |
        | - a client disappears without sending stopped
        | - a peer expires
        | - a request crashes halfway through
        | - a client behaves incorrectly
        |
        | Occasionally ask the background worker to rebuild the authoritative
        | values from the peers table.
        |
        | This is NOT performed inside the announce request.
        |
        */

        if (random_int(1, 100) === 1) {
            UpdateTorrentStats::dispatch(
                $torrent->id
            )->onQueue(
                'announce-low'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Peer cache
        |--------------------------------------------------------------------------
        */

        Redis::del(
            "torrent:{$torrent->id}:peers"
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'peers' =>
                $peers,

            'torrent' =>
                $torrent,

            'tracker_id' =>
                md5($peerId),
        ];
    }

    /**
     * Increment torrent seeder count.
     */
    private function incrementSeeder(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->increment(
                'seeders'
            );
    }

    /**
     * Increment torrent leecher count.
     */
    private function incrementLeecher(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->increment(
                'leechers'
            );
    }

    /**
     * Safely decrement torrent seeder count.
     *
     * GREATEST prevents the counter becoming negative.
     */
    private function decrementSeeder(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->update([
                'seeders' => DB::raw(
                    'GREATEST(seeders - 1, 0)'
                ),
            ]);
    }

    /**
     * Safely decrement torrent leecher count.
     */
    private function decrementLeecher(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->update([
                'leechers' => DB::raw(
                    'GREATEST(leechers - 1, 0)'
                ),
            ]);
    }

    /**
     * Peer transitioned:
     *
     * Leecher -> Seeder
     *
     * Both counters are changed with ONE SQL update.
     */
    private function leecherToSeeder(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->update([
                'leechers' => DB::raw(
                    'GREATEST(leechers - 1, 0)'
                ),

                'seeders' => DB::raw(
                    'seeders + 1'
                ),
            ]);
    }

    /**
     * Peer transitioned:
     *
     * Seeder -> Leecher
     */
    private function seederToLeecher(
        int $torrentId
    ): void {
        DB::table('torrents')
            ->where(
                'id',
                $torrentId
            )
            ->update([
                'seeders' => DB::raw(
                    'GREATEST(seeders - 1, 0)'
                ),

                'leechers' => DB::raw(
                    'leechers + 1'
                ),
            ]);
    }

    /**
     * Get current Happy Hour.
     */
    private function getActiveHappyHour(): array
    {
        $cacheKey =
            'tracker:happy_hour:state';

        $state = Cache::remember(
            $cacheKey,
            5,
            function (): array {
                $now = now();

                $happyHour =
                    HappyHour::query()
                        ->select([
                            'id',
                            'upload_multiplier',
                            'free_download',
                            'start_at',
                            'end_at',
                        ])
                        ->where(
                            'active',
                            true
                        )
                        ->where(
                            'start_at',
                            '<=',
                            $now
                        )
                        ->where(
                            'end_at',
                            '>',
                            $now
                        )
                        ->latest(
                            'start_at'
                        )
                        ->orderByDesc('id')
                        ->first();

                /*
                 * Explicit inactive state.
                 *
                 * Never return null because null would
                 * effectively behave like a cache miss.
                 */

                if (! $happyHour) {
                    return $this
                        ->inactiveHappyHour();
                }

                return [
                    'active' =>
                        true,

                    'id' =>
                        $happyHour->id,

                    'upload_multiplier' =>
                        max(
                            1.0,
                            (float) (
                                $happyHour
                                    ->upload_multiplier
                                ?? 1
                            )
                        ),

                    'free_download' =>
                        (bool) $happyHour
                            ->free_download,

                    'start_at' =>
                        $happyHour->start_at
                            ? (string)
                                $happyHour
                                    ->start_at
                            : null,

                    'end_at' =>
                        $happyHour->end_at
                            ? (string)
                                $happyHour
                                    ->end_at
                            : null,
                ];
            }
        );

        /*
         * Inactive cached state.
         */

        if (
            empty($state['active'])
            ||
            empty($state['start_at'])
            ||
            empty($state['end_at'])
        ) {
            return $this
                ->inactiveHappyHour();
        }

        /*
         * Safety validation.
         *
         * Never allow an expired cached Happy Hour
         * to continue giving bonuses.
         */

        try {
            $startsAt =
                \Carbon\Carbon::parse(
                    $state['start_at']
                );

            $endsAt =
                \Carbon\Carbon::parse(
                    $state['end_at']
                );
        } catch (\Throwable $e) {
            Cache::forget(
                $cacheKey
            );

            return $this
                ->inactiveHappyHour();
        }

        $now = now();

        if (
            $now->lt($startsAt)
            ||
            $now->gte($endsAt)
        ) {
            Cache::forget(
                $cacheKey
            );

            return $this
                ->inactiveHappyHour();
        }

        return [
            'active' =>
                true,

            'id' =>
                $state['id']
                ?? null,

            'upload_multiplier' =>
                max(
                    1.0,
                    (float) (
                        $state[
                            'upload_multiplier'
                        ]
                        ?? 1
                    )
                ),

            'free_download' =>
                (bool) (
                    $state[
                        'free_download'
                    ]
                    ?? false
                ),

            'start_at' =>
                $state['start_at'],

            'end_at' =>
                $state['end_at'],
        ];
    }

    /**
     * Default inactive Happy Hour.
     */
    private function inactiveHappyHour(): array
    {
        return [
            'active' =>
                false,

            'id' =>
                null,

            'upload_multiplier' =>
                1.0,

            'free_download' =>
                false,

            'start_at' =>
                null,

            'end_at' =>
                null,
        ];
    }

    /**
     * Build peer response.
     */
    public function givePeers(
        array $peers,
        bool $compact,
        bool $noPeerId
    ): mixed {
        if ($compact) {
            $ipv4Peers = '';

            foreach ($peers as $peer) {
                $ip =
                    $peer['ip']
                    ?? null;

                $port =
                    (int) (
                        $peer['port']
                        ?? 0
                    );

                if (
                    ! $ip
                    ||
                    $port < 1
                    ||
                    $port > 65535
                ) {
                    continue;
                }

                $ipBinary =
                    @inet_pton($ip);

                if (
                    $ipBinary === false
                ) {
                    continue;
                }

                /*
                 * Standard compact IPv4:
                 *
                 * 4 bytes IP
                 * +
                 * 2 bytes port
                 */

                if (
                    strlen($ipBinary)
                    !== 4
                ) {
                    continue;
                }

                $ipv4Peers .=
                    $ipBinary
                    .pack(
                        'n',
                        $port
                    );
            }

            return $ipv4Peers;
        }

        $result = [];

        foreach ($peers as $peer) {
            $ip =
                $peer['ip']
                ?? null;

            $port =
                (int) (
                    $peer['port']
                    ?? 0
                );

            if (
                ! $ip
                ||
                $port < 1
                ||
                $port > 65535
            ) {
                continue;
            }

            $peerData = [
                'ip' =>
                    $ip,

                'port' =>
                    $port,
            ];

            if (
                ! $noPeerId
                &&
                isset(
                    $peer['peer_id']
                )
            ) {
                $peerData[
                    'peer_id'
                ] =
                    $peer['peer_id'];
            }

            $result[] =
                $peerData;
        }

        return $result;
    }

    /**
     * Execute a short DB transaction and
     * retry when MySQL reports a deadlock.
     */
    private function safeTransaction(
        callable $callback,
        int $maxAttempts = 3
    ): mixed {
        $attempt = 0;

        while (
            $attempt < $maxAttempts
        ) {
            try {
                return DB::transaction(
                    $callback
                );
            } catch (
                QueryException $e
            ) {
                if (
                    ! $this->isDeadlock($e)
                ) {
                    throw $e;
                }

                $attempt++;

                if (
                    $attempt >=
                    $maxAttempts
                ) {
                    Log::warning(
                        'Deadlock persisted in AnnounceService.',
                        [
                            'attempts' =>
                                $attempt,

                            'error' =>
                                $e
                                    ->getMessage(),
                        ]
                    );

                    throw $e;
                }

                usleep(
                    100000
                    * $attempt
                );
            }
        }

        return null;
    }

    /**
     * Determine whether the exception
     * represents a database deadlock.
     */
    private function isDeadlock(
        QueryException $e
    ): bool {
        $message =
            strtolower(
                $e->getMessage()
            );

        return
            str_contains(
                $message,
                'deadlock'
            )
            ||
            str_contains(
                $message,
                '1213'
            )
            ||
            str_contains(
                $message,
                '40001'
            );
    }
}