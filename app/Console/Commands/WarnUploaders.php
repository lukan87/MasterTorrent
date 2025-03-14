<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use Carbon\Carbon;

class WarnUploaders extends Command
{
    protected $signature = 'warn:uploaders';
    protected $description = 'Warn uploaders who have not uploaded a torrent in the last 3 days';

    public function handle()
    {
        $now = Carbon::now();
        $threeDaysAgo = $now->subDays(3);
        $fiveDaysAgo = $now->subDays(5);

        // Get users in class 5 (Uploader) who haven't uploaded in the last 3 days
        $uploaders = User::where('user_class', 5)  // Only select users with user_class 5
            ->where(function ($query) use ($threeDaysAgo) {
                // Filter users who haven't uploaded in the last 3 days
                $query->where('last_upload', '<', $threeDaysAgo)
                      ->orWhereNull('last_upload');
            })
            ->get();

        $warnedUsers = [];  // Array to store the IDs of the warned users

        foreach ($uploaders as $user) {
            // Check if they received a warning already
            $alreadyWarned = Message::where('receiver_id', $user->id)
                ->where('subject', 'Warning: Upload Required')
                ->where('created_at', '>', $fiveDaysAgo)
                ->exists();

            if (!$alreadyWarned) {
                // Send warning message
                Message::create([
                    'sender_id' => 1, // System user ID or admin ID
                    'receiver_id' => $user->id,
                    'subject' => 'Warning: Upload Required',
                    'body' => 'You have 2 more days to upload a new torrent, or you will be demoted.',
                ]);
                
                // Add user ID to the warned list
                $warnedUsers[] = $user->id;
            }
        }

        // Output the info message with the IDs of warned users
        if (count($warnedUsers) > 0) {
            $this->info('Warnings sent to inactive uploaders with IDs: ' . implode(', ', $warnedUsers));
        } else {
            $this->info('No inactive uploaders found or warnings already sent.');
        }
    }
}
