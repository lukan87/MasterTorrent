<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;

class PromoteDemoteUsers extends Command
{
    protected $signature = 'users:promote-demote';
    protected $description = 'Promote or demote users based on criteria.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the current date
        $now = Carbon::now();

        // Retrieve users with id 1 and 2
        $users = User::whereIn('user_class', [1, 2])->get();  // Fetch users with ids 1 and 2

        foreach ($users as $user) {
            // Clone $now so we don't modify it
            $threeMonthsAgo = $now->copy()->subMonths(3);
            $ratio = $user->uploaded / ($user->downloaded ?: 1); // Avoid division by zero

            // Promotion logic
            if ($user->user_class == 1 &&
                $user->created_at <= $threeMonthsAgo &&
                $user->uploaded >= 536870912000 &&  // 500GB in bytes
                $user->downloaded >= 268435456000 && // 250GB in bytes
                $ratio >= 1.1) {
                // Promote to elite
                $user->user_class = 2;
                $user->save();

                // Send a message to the user
               Message::create([
                'sender_id' => 2,
                'receiver_id' => $user->id,
                'subject' => "Class Promotion",
                'body' => "Congratulations, {$user->name}! You have been promoted to Elite User. \n Keep up the good work and don't forget to seed until you bleed!!!
                \n LastFiles Team"
    ]);

                $this->info("User {$user->name} promoted to Elite.");
            }

            // Demotion logic (example: Demote if they don't meet the conditions anymore)
            if ($user->user_class == 2 && $ratio < 1.0) {
                // Demote to regular user
                $user->user_class = 1;
                $user->save();

                 // Send a message to the user
               Message::create([
                'sender_id' => 2,
                'receiver_id' => $user->id,
                'subject' => "Class Demotion",
                'body' => "Hello, {$user->name}! You have been demoted to User as your ratio dropped bellow 1 \n
                           Ensure you seed your torrents until ratio is 1 or higher! \n Thank you. \n LastFiles Team"
    ]);

                $this->info("User {$user->name} demoted to Regular.");
            }
        }
    }
}
