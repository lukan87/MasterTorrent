<?php

namespace App\Console\Commands;

use App\Models\History;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCorrectHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:correct_history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrects History Records Said To Be Active Even Though Really Are Not Due To Not Receiving A STOPPED Event From Client.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $current = new Carbon();
        $history = History::select(['id', 'active', 'updated_at'])->where('active', '=', 1)->where('updated_at', '<', $current->copy()->subHours(2)->toDateTimeString())->get();

        foreach ($history as $h) {
            $h->active = false;
            $h->seeder = false;
            $h->save();
        }
        $this->info('History corrected');
    }

}
