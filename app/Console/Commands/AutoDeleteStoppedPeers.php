<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\DB;


class AutoDeleteStoppedPeers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:delete_stopped_peers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all stopped peers';

    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */
    final public function handle(): void
    {
        DB::transaction(static function (): void {
            DB::table('peers')
                ->where('seeder', '=', 1)
                ->where('client_updated_at', '>', now()->subHours(1))
                ->delete();
        }, 5);

        $this->comment('Automated delete stopped peers command complete');
    }
}
