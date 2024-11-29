<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;
use App\Models\History;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        $carbon = new Carbon();

        $peers = Peer::select(['id', 'hash', 'user_id', 'updated_at', 'client_updated_at'])->where('client_updated_at', '<', $carbon->copy()->subHours(1)->toDateTimeString())->get();

        foreach ($peers as $peer) {
            $history = History::where('info_hash', '=', $peer->hash)->where('user_id', '=', $peer->user_id)->first();
            if ($history) {
                $history->active = false;
                $history->save();
            }
            $peer->delete();
        }

        $this->comment('Automated Flush Old Peers Command Complete');
    }
}
