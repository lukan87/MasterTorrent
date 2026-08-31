<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Torrent;
use Carbon\Carbon;

class DeleteDeadTorrents extends Command
{
    protected $signature = 'torrents:delete-dead';

    protected $description = 'Delete torrents with 0 seeders and 0 leechers older than 1 year';

    public function handle()
    {
        $date = Carbon::now()->subYear();

        $this->info("Searching for dead torrents older than {$date->toDateString()}...");

        $torrents = Torrent::where('seeders', 0)
            ->where('leechers', 0)
            ->where('created_at', '<', $date)
            ->get();

        $count = $torrents->count();

        if ($count === 0) {
            $this->info("No dead torrents found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$count} dead torrents. Starting deletion...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($torrents as $torrent) {

            // store deletion reason
            $torrent->deletion_reason = 'Deleted automatically by system: 0 seeders for more than 1 year';
            $torrent->deleted_by = 2;

            $torrent->save(); // ensure fields are saved

            $torrent->delete();

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("Cleanup completed. {$count} torrents deleted.");

        return Command::SUCCESS;
    }
}