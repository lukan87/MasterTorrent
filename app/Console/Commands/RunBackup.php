<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-custom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup process using Spatie Backup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting the backup process...');

        try {
            // Trigger the Spatie backup command
            Artisan::call('backup:run');

            $this->info('Backup completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

