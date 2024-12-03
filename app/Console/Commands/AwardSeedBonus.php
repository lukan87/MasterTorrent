<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer; // Adjust the model if needed
use App\Models\User; // Assuming you have a User model
use Exception;


class AwardSeedBonus extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:seedbonus_award';
/**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award 0.5 seedbonus points for each torrent being seeded every hour';
    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */

     final public function handle()
     {
         $current_time = now();

         // Fetch all peers that are currently seeding
         $peers = Peer::where('seeder', true)
             ->where(function($query) use ($current_time) {
                 $query->whereNull('last_awarded')
                       ->orWhere('last_awarded', '<=', $current_time->subHour());
             })
             ->get();

         // Group peers by user ID and torrent ID to award points
         $userPoints = [];

         foreach ($peers as $peer) {
             $userId = $peer->user_id; // Use user_id from the Peer model
             $torrentId = $peer->torrent_id; // Use torrent_id from the Peer model

             // Create a unique key based on user ID and torrent ID
             $key = "{$userId}-{$torrentId}";

             if (!isset($userPoints[$key])) {
                 // Initialize points for this user-torrent pair if not already set
                 $userPoints[$key] = [
                     'user_id' => $userId,
                     'points' => 0.15 // Points to award for seeding
                 ];
             }

             // Update the last_awarded timestamp for this peer
             $peer->last_awarded = $current_time;
         }

         // Save all peers with the updated last_awarded timestamp
         foreach ($peers as $peer) {
             $peer->save();
         }

         // Batch update user seedbonus points
         foreach ($userPoints as $data) {
             User::where('id', $data['user_id'])->increment('seedbonus', $data['points']);
         }

         $this->info('Seedbonus points awarded successfully.');
     }

}
