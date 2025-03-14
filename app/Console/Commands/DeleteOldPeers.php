<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Add Log Facade

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
    protected $description = 'Flushes peers that have not been updated in the last hour';

    /**
     * Execute the console command.
     */
    final public function handle(): void
    {
        // Log when the command is executed
        Log::info('Auto:flush_peers command started at ' . Carbon::now());

        $carbon = new Carbon();

        // Get the peers that need to be deleted
        $peers = Peer::select(['id', 'hash', 'user_id', 'updated_at', 'client_updated_at'])
                     ->where('client_updated_at', '<', $carbon->copy()->subHours(1)->toDateTimeString())
                    //  ->where('seeder', '=', 0)
                     ->get();

        // Log the number of peers found
        Log::info('Found ' . $peers->count() . ' peers to be flushed.');

        // Process each peer
        foreach ($peers as $peer) {
            $history = History::where('info_hash', '=', $peer->hash)
                              ->where('user_id', '=', $peer->user_id)
                              ->first();
            if ($history) {
                $history->active = false;
                $history->seeder = false;
                $history->save();
                $this->comment("History updated for peer with ID: {$peer->id} and Hash: {$peer->hash}");
                //Log::info("History updated for peer with ID: {$peer->id} and Hash: {$peer->hash}");
            }

            $peer->delete();
            $this->comment("Peer with ID: {$peer->id} and Hash: {$peer->hash} has been deleted. Last updated at {$peer->client_updated_at}");
           // Log::info("Peer with ID: {$peer->id} and Hash: {$peer->hash} has been deleted.");
        }

        // Log completion of the command
        $this->comment('Automated Flush Old Peers Command Complete');
        Log::info('Auto:flush_peers command completed at ' . Carbon::now());
    }
}
