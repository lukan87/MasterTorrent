<?php

namespace App\Console\Commands;

use App\Models\Torrent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnbumpOldTorrents extends Command
{
    protected $signature = 'torrents:unbump-old';

    protected $description = 'Reset bump data for torrents bumped more than 30 days ago';

    public function handle()
    {
        $dateLimit = Carbon::now()->subDays(30);

        $torrents = Torrent::where('bumped', true)
            ->whereNotNull('bumped_at')
            ->where('bumped_at', '<', $dateLimit)
            ->get();

        if ($torrents->isEmpty()) {
            $this->info('No torrents found that need to be unbumped.');
            return;
        }

        foreach ($torrents as $torrent) {
            $torrent->update([
                'bumped'     => false,
                'bumped_at'  => null,
                'bumped_by'  => null,
            ]);

            $this->info("Torrent ID {$torrent->id} has been unbumped.");
        }
    }
}
