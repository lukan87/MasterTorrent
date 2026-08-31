<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;
use App\Models\History;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeleteStalePeers extends Command
{
    protected $signature = 'peers:cleanup';

    protected $description = 'Delete peers inactive for 60 minutes and update history';

    public function handle()
    {
        $cutoff = Carbon::now()->subMinutes(60);

        $totalDeleted = 0;

        Peer::where('updated_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(500, function ($peers) use (&$totalDeleted) {

                DB::transaction(function () use ($peers, &$totalDeleted) {

                    $peerIds = $peers->pluck('id');

                    /*
                    |--------------------------------------------------------------------------
                    | Update history (only active sessions)
                    |--------------------------------------------------------------------------
                    */

                    foreach ($peers as $peer) {
                        History::where('user_id', $peer->user_id)
                            ->where('torrent_id', $peer->torrent_id)
                            ->where('active', true)
                            ->update([
                                'active'     => false,
                                'seeder'     => false,
                                'updated_at' => now(),
                            ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Delete peers
                    |--------------------------------------------------------------------------
                    */

                    $deleted = Peer::whereIn('id', $peerIds)->delete();

                    $totalDeleted += $deleted;
                });
            });

        $this->info("Deleted {$totalDeleted} stale peers and updated history.");
    }
}