<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DemoteInactiveUploaders extends Command
{
    protected $signature = 'users:demote-inactive-uploaders';
    protected $description = 'Demote users with user_class 5 who have not uploaded in the last 5 days';

    public function handle()
    {
        // Get the current date and time
        $currentDate = Carbon::now();

        // Get users with user_class 5 and check if their last_upload was 5 days ago
        $users = User::where('user_class', 5)
                     ->whereNotNull('last_upload')
                     ->get();

        $demotedUsers = [];

        foreach ($users as $user) {
            $lastUploadDate = Carbon::parse($user->last_upload);

            // Check if it's been more than 5 days since their last upload
            if ($lastUploadDate->diffInDays($currentDate) >= 5) {
                // Demote the user to user_class 1
                $user->user_class = 1;
                $user->save();

                // Add to timeline
        UserTimeline::create([
            'user_id'  => $user->id,
            'staff_id' => 2, // or auth()->id() if a staff runs it manually
            'comment'  => "User was demoted from class {$oldClass} to 1 due to inactivity (no uploads for 5+ days).",
        ]);


                // Log the demotion action
                $demotedUsers[] = [
                    'user_id' => $user->id,
                    'old_class' => 5,
                    'new_class' => 1,
                    'demoted_at' => $currentDate,
                ];
            }
        }

        // Print out demoted users
        if (count($demotedUsers) > 0) {
            foreach ($demotedUsers as $demotedUser) {
                $this->info("User ID: {$demotedUser['user_id']} demoted from user_class {$demotedUser['old_class']} to {$demotedUser['new_class']} at {$demotedUser['demoted_at']}");
            }
        } else {
            $this->info("No users were demoted.");
        }
    }
}
