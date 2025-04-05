<?php

namespace App\Console\Commands;

use App\Models\Torrent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnbumpOldTorrents extends Command
{
    // The name and signature of the console command.
    protected $signature = 'torrents:unbump-old';

    // The console command description.
    protected $description = 'Unbump torrents where bumped is true and created_at is older than 10 days';

    // Execute the console command.
    public function handle()
    {
        // Get the current date and subtract 30 days
        $dateLimit = Carbon::now()->subDays(30);

        // Query to find torrents with bumped true and created_at older than 10 days
        $torrents = Torrent::where('bumped', true)
            ->where('created_at', '<', $dateLimit)
            ->get();

        // If there are torrents to unbump
        if ($torrents->isNotEmpty()) {
            // Loop through the torrents and set bumped to false
            foreach ($torrents as $torrent) {
                $torrent->update([
                    'bumped' => false,
                ]);
                $this->info("Torrent ID {$torrent->id} has been unbumped.");
            }
        } else {
            $this->info('No torrents found that need to be unbumped.');
        }
    }
}

