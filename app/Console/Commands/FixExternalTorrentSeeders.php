<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Torrent;

class FixExternalTorrentSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:external-seeders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure external torrents always have at least 1 seeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
{
    $totalExternal = Torrent::where('external', true)->count();
    $needFixing    = Torrent::where('external', true)
        ->where('seeders', '<', 1)
        ->count();

    if ($needFixing === 0) {
        $this->info("✔ No external torrents need fixing. ({$totalExternal} external torrents checked)");
         $this->line("ℹ Total external torrents: {$totalExternal}");
        return self::SUCCESS;
    }

    $affected = Torrent::where('external', true)
        ->where('seeders', '<', 1)
        ->update(['seeders' => 1]);

    $this->info("✔ Fixed {$affected} / {$needFixing} external torrents with 0 seeders.");
    $this->line("ℹ Total external torrents: {$totalExternal}");

    return self::SUCCESS;
}

}
