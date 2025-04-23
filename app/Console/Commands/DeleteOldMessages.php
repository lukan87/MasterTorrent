<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeleteOldMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete messages older than a month from the messages table where is_read is true';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $monthAgo = Carbon::now()->subMonth(); 

       
        $deletedCount = DB::table('messages')
                          ->where('is_read', true)
                          ->where('created_at', '<', $monthAgo) 
                          ->delete();

        
        $this->info("$deletedCount messages older than one month and marked as read have been deleted.");
    }
}
