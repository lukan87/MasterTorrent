<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;
use App\Models\Torrent;


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
      
$timeout = now()->subMinutes(60);

Torrent::chunk(100, function ($torrents) use ($timeout) {
    foreach ($torrents as $torrent) {

        $seeders = Peer::where('torrent_id', $torrent->id)
            ->where('active', 1)
            ->where('seeder', 1)
            ->where('client_updated_at', '>', $timeout)
            ->count();

        $leechers = Peer::where('torrent_id', $torrent->id)
            ->where('active', 1)
            ->where('seeder', 0)
            ->where('client_updated_at', '>', $timeout)
            ->count();

        $torrent->update([
            'seeders'  => $seeders,
            'leechers' => $leechers,
        ]);
    }
});

    }
}
   