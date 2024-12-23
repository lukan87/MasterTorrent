<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\History;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
use Illuminate\Support\Facades\DB;

class AutoPreWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:prewarning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Sends Pre Warning Notifications To Users';

    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */
    final public function handle(): void
    {
        // Check if the pre-warning feature is enabled in the config
        if (config('hitrun.enabled') !== true) {
            return;
        }

        try {
            // Process History records in chunks
            History::with(['user', 'torrent'])
                ->where('created_at', '>', '2024-12-15 00:00:00')
                ->whereNull('prewarned_at')
                ->where('hitrun', '=', 0)
                ->where('immune', '=', 0)
                ->where('actual_downloaded', '>', 0)
                ->where('active', '=', 0)
                ->where('seedtime', '<=', config('hitrun.seedtime'))
                ->where('updated_at', '<', now()->subDays(config('hitrun.prewarn')))
                ->has('torrent')
                ->whereHas('user', function ($query) {
                    $query->where('is_immune', false)
                          ->where('donor', 'no');
                })
                ->whereHas('torrent', function ($query) {
                    $query->whereRaw('history.actual_downloaded > torrents.size * ?', [config('hitrun.buffer') / 100])
                          ->where('seeders', '>', 0);  // Ensure there are seeders greater than 0
                })
                ->whereDoesntHave('user.warnings', fn ($query) => $query->withTrashed()->whereColumn('warnings.torrent', '=', 'history.torrent_id'))
                ->chunkById(100, function ($prewarns): void {
                    foreach ($prewarns as $pre) {
                        // Update prewarned_at timestamp for each user meeting the pre-warning conditions
                        History::query()
                            ->where('torrent_id', '=', $pre->torrent_id)
                            ->where('user_id', '=', $pre->user_id)
                            ->update([
                                'prewarned_at' => now(),
                            ]);

                        // Construct the link to the torrent page using the slug if available
                        $torrentLink = route('torrents.show', ['id' => $pre->torrent_id, 'slug' => $pre->torrent->slug]);

                        // Build the message body with the torrent link
                        $body = "This is a pre-warning regarding your recent torrent activity. Please be aware of the hit-and-run policy.\n\n";
                        $body .= "Torrent: <a href=\"$torrentLink\">{$pre->torrent->name}</a>";

                        // Create a new message for the user
                        Message::create([
                            'receiver_id' => $pre->user_id,  // The user receiving the message
                            'subject' => 'Pre-Warning: Hit and Run',
                            'sender_id' => 2,  // The system or admin user ID (assuming 2 for this example)
                            'body' => $body,
                            'is_read' => false,  // Mark as unread initially
                        ]);

                        // Log or output a comment with the user and torrent name
                        $this->comment("Pre-warning sent to User ID: {$pre->user_id} for Torrent: {$pre->torrent->name}");
                    }
                });

            // Output a success message
            $this->comment('Automated User Pre-Warning Command Complete');
        } catch (Throwable $e) {
            // Log the error and output a message
            Log::error('Error sending pre-warnings: ' . $e->getMessage());
            $this->error('An error occurred while processing the pre-warnings.');
        }
    }
}
