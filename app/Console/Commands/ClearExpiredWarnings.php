<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserTimeline;
use App\Models\Message;
use Carbon\Carbon;

class ClearExpiredWarnings extends Command
{
    protected $signature = 'users:clear-expired-warnings';
    protected $description = 'Clears expired user warnings and sends a notification';

    public function handle()
    {
        $now = Carbon::now();

        $expiredWarnings = User::where('warned', true)
            ->whereNotNull('warned_until')
            ->where('warned_until', '<', $now)
            ->get();

        $count = $expiredWarnings->count();

        foreach ($expiredWarnings as $user) {
            $user->warned = false;
            $user->warned_until = null;
            $user->save();

           
            Message::create([
                'sender_id'   => 2, 
                'receiver_id' => $user->id,
                'subject'     => 'Your Warning Has Expired!',
                'body'        => "Your warning has expired. Keep up the good behaviour from now on. Best of luck.\n\n— LastFiles Team!",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // UserTimeline::create([
            //     'user_id' => $user->id,
            //     'staff_id'  => '2',
            //     'comment' => 'User warning expired and status was reset automatically.',
            // ]);
            



        }

        $this->info("Cleared warnings and sent messages to {$count} user(s).");

        return 0;
    }
}
