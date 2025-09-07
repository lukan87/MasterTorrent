<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\History;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

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
        if (!config('hitrun.enabled', false)) {
            return;
        }

        try {
            // Fetch download threshold from config
            $downloadThreshold = config('hitrun.download_threshold', 25); // default 25%

            // Process History records in chunks
            History::with(['user', 'torrent'])
                ->where('created_at', '>', '2025-02-01 00:00:00')
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
                          ->where('user_class', '<', 3)
                          ->where('donor', 'no');
                })
                ->whereHas('torrent', function ($query) use ($downloadThreshold) {
                    $query->whereRaw(
                        'history.actual_downloaded >= torrents.size * ?',
                        [$downloadThreshold / 100]
                    )
                    ->where('seeders', '>', 0);
                })
                ->whereRaw('(history.uploaded / NULLIF(history.actual_downloaded, 0)) < 1.0')
                ->whereDoesntHave('user.warnings', fn($query) => 
                    $query->withTrashed()
                          ->whereColumn('warnings.torrent', '=', 'history.torrent_id')
                )
                ->chunkById(100, function ($prewarns) use ($downloadThreshold): void {
                    foreach ($prewarns as $pre) {

                        // Calculate the ratio
                        $uploaded = $pre->uploaded ?? 0;
                        $downloaded = $pre->actual_downloaded ?? 0;
                        $ratio = $downloaded > 0 ? $uploaded / $downloaded : 0;

                        // Skip if the ratio is 1.00 or higher
                        if ($ratio >= 1.00) {
                            continue;
                        }

                        // Update prewarned_at timestamp
                        History::query()
                            ->where('torrent_id', $pre->torrent_id)
                            ->where('user_id', $pre->user_id)
                            ->update(['prewarned_at' => now()]);

                        // Construct the torrent link
                        $torrentLink = route('torrents.show', [
                            'id' => $pre->torrent_id,
                            'slug' => $pre->torrent->slug
                        ]);

                        // Calculate downloaded percentage
                        $downloadedPercent = $pre->torrent->size > 0
                            ? ($downloaded / $pre->torrent->size) * 100
                            : 0;

                        // Build the message body
                        $body = "This is a pre-warning regarding your recent torrent activity. "
                              . "Please be aware of the hit-and-run policy.\n\n"
                              . "Torrent: <a href=\"$torrentLink\">{$pre->torrent->name}</a>\n"
                              . "You have downloaded about " . number_format($downloadedPercent, 2) . "% of this torrent.\n"
                              . "Your ratio for this torrent is: " . number_format($ratio, 2) . ".\n\n"
                              . "⚠️ Note: Warnings are only applied if you have downloaded at least {$downloadThreshold}% of a torrent.\n";

                        // Create a new message for the user
                        Message::create([
                            'receiver_id' => $pre->user_id,
                            'subject' => 'Pre-Warning: Hit and Run',
                            'sender_id' => config('hitrun.system_user_id', 2),
                            'body' => $body,
                            'is_read' => false,
                        ]);

                        // Log or output
                        $this->comment("Pre-warning sent to User ID: {$pre->user_id} for Torrent: {$pre->torrent->name}");
                    }
                });

            $this->comment('Automated User Pre-Warning Command Complete');
        } catch (Throwable $e) {
            Log::error('Error sending pre-warnings: ' . $e->getMessage());
            $this->error('An error occurred while processing the pre-warnings.');
        }
    }
}
