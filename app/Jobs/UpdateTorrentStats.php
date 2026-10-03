<?php

namespace App\Jobs;

use App\Models\Peer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdateTorrentStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before the job is marked as failed.
     */
    public int $tries = 3;

    /**
     * Maximum execution time.
     */
    public int $timeout = 30;

    public function __construct(
        public int $torrentId
    ) {
    }

    public function handle(): void
    {
        $activeSince = now()->subMinutes(60);

        /*
        |--------------------------------------------------------------------------
        | Calculate swarm
        |--------------------------------------------------------------------------
        |
        | Calculate seeders and leechers in ONE query.
        |
        | Only peers that:
        | - belong to this torrent
        | - are active
        | - announced within the last 60 minutes
        |
        | are counted.
        |
        */

        $counts = Peer::query()
            ->where('torrent_id', $this->torrentId)
            ->where('active', true)
            ->where('client_updated_at', '>', $activeSince)
            ->selectRaw('
                COALESCE(
                    SUM(
                        CASE
                            WHEN seeder = 1 THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS seeders,

                COALESCE(
                    SUM(
                        CASE
                            WHEN seeder = 0 THEN 1
                            ELSE 0
                        END
                    ),
                    0
                ) AS leechers
            ')
            ->first();

        $seeders = (int) ($counts->seeders ?? 0);
        $leechers = (int) ($counts->leechers ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Synchronise torrent counters
        |--------------------------------------------------------------------------
        |
        | Query builder is intentional here.
        |
        | We do not need to load the full Torrent model and we don't need
        | Torrent model events/cache-clearing logic for this reconciliation.
        |
        */

        DB::table('torrents')
            ->where('id', $this->torrentId)
            ->update([
                'seeders' => $seeders,
                'leechers' => $leechers,
            ]);
    }
}