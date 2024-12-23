<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Message;

class RevertVipStatus extends Command
{
    protected $signature = 'users:revert-vip-status';
    protected $description = 'Revert VIP users to regular users once their VIP time expires.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Get the current date
        $now = Carbon::now();

        // Retrieve users who have a VIP expiration date set
        $users = User::whereNotNull('vip_until')->get();  // Fetch users with a vip_until date

        foreach ($users as $user) {
            // Check if the VIP status has expired
            if ($user->vip_until <= $now) {
                // Revert the user class back to regular (1)
                $user->user_class = 1;
                $user->is_immune = 0;
                $user->is_freeleech = 0;
                $user->vip_until = null;  // Clear VIP expiration date
                $user->save();

                // Send a message to the user about the demotion
                Message::create([
                    'sender_id' => 2,
                    'receiver_id' => $user->id,
                    'subject' => "VIP Expiration",
                    'body' => "Dear {$user->name}, your VIP status has expired. You have been reverted to regular user status. \n Thank you for your support. \n LastFiles Team"
                ]);

                $this->info("User {$user->name} reverted to regular user as VIP expired.");
            }
        }
    }
}
