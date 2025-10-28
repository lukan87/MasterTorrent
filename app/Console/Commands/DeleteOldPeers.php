<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteOldPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:flush_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flushes peers not updated in the last hour and marks related history as inactive';

    /**
     * Execute the console command.
     */
    final public function handle(): void
    {
        $now = Carbon::now();
        $cutoff = $now->copy()->subHour();

        Log::info("Auto:flush_peers started at {$now}");

        // Step 1: Handle old peers
        $peers = Peer::select(['id', 'torrent_id', 'user_id', 'updated_at', 'client_updated_at'])
            ->where('client_updated_at', '<', $cutoff)
            ->get();

        Log::info('Found ' . $peers->count() . ' old peers to flush.');

        foreach ($peers as $peer) {
            // Update related history (if any)
            $history = History::where('torrent_id', $peer->torrent_id)
                ->where('user_id', $peer->user_id)
                ->first();

            if ($history) {
                $history->active = false;
                $history->seeder = false;
                $history->save();

                $this->comment("History updated for Peer ID {$peer->id} (Torrent ID: {$peer->torrent_id})");
            }

            $peer->delete();
            $this->comment("Peer ID {$peer->id} (Torrent ID: {$peer->torrent_id}) deleted. Last update: {$peer->client_updated_at}");
        }

        // Step 2: Handle old history entries without deleting peers
        $oldHistories = History::where('updated_at', '<', $cutoff)
            ->where('active', true)
            ->get();

        Log::info('Found ' . $oldHistories->count() . ' old histories to mark inactive.');

        foreach ($oldHistories as $history) {
            $history->active = false;
            $history->seeder = false;
            $history->save();

            $this->comment("Marked History ID {$history->id} as inactive (User ID: {$history->user_id}, Torrent ID: {$history->torrent_id})");
        }

        $this->comment('Auto Flush Old Peers & Histories Command Complete.');
        Log::info("Auto:flush_peers completed at {$now}");
    }
}
