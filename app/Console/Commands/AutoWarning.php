<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\History;
use App\Models\User;
use App\Models\Warning;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;

class AutoWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Gives Warning To Users And Records In Warnings Table';

    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */
    final public function handle(): void
    {
        if (config('hitrun.enabled') !== true) {
            $this->info('Hit and Run warning feature is disabled in the configuration.');
            return;
        }

        try {
            History::with(['user', 'torrent'])
                ->where('created_at', '>', '2024-12-15 00:00:00')
                ->where('prewarned_at', '<=', now()->subDays(config('hitrun.prewarn')))
                ->where('hitrun', '=', 0)
                ->where('immune', '=', 0)
                ->where('actual_downloaded', '>', 0)
                ->where('active', '=', 0)
                ->where('seedtime', '<=', config('hitrun.seedtime'))
                ->where('updated_at', '<', now()->subDays(config('hitrun.grace')))
                ->whereHas('user', fn ($query) => $query->where('is_immune', false)->where('donor', 'no'))
                ->whereHas('torrent', fn ($query) => $query->whereRaw('history.actual_downloaded > torrents.size * ?', [config('hitrun.buffer') / 100])
                                                          ->where('seeders', '>', 0))
                ->whereDoesntHave('user.warnings', fn ($query) => $query->withTrashed()->whereColumn('warnings.torrent', '=', 'history.torrent_id'))
                ->chunkById(100, function ($hitrun) {
                    foreach ($hitrun as $hr) {
                        // Skip if relationships are null
                        if (!$hr->user || !$hr->torrent) {
                            $this->warn("Skipping history record with missing user or torrent: History ID {$hr->id}");
                            continue;
                        }

                        // Create a warning
                        Warning::create([
                            'user_id' => $hr->user->id,
                            'warned_by' => config('hitrun.system_user_id', 2),
                            'torrent' => $hr->torrent->id,
                            'reason' => sprintf('Hit and Run Warning For Torrent %s', $hr->torrent->name),
                            'expires_on' => now()->addDays(config('hitrun.expire')),
                            'active' => true,
                        ]);

                        // Mark history as hit-and-run
                        History::query()
                            ->where('torrent_id', '=', $hr->torrent_id)
                            ->where('user_id', '=', $hr->user_id)
                            ->update(['hitrun' => true]);

                        // Increment user's hit-and-run count
                        $hr->user->increment('hit_and_run_count');

                        // Send a message to the user
                        $torrentLink = route('torrents.show', ['id' => $hr->torrent_id, 'slug' => $hr->torrent->slug ?? '']);
                        $body = "This is a warning regarding your recent torrent activity. You have received a hit-and-run for the torrent below.\n\n";
                        $body .= "Torrent: <a href=\"$torrentLink\">{$hr->torrent->name}</a>";

                        Message::create([
                            'receiver_id' => $hr->user_id,
                            'subject' => 'Hit and Run - Warning Added',
                            'sender_id' => config('hitrun.system_user_id', 2),
                            'body' => $body,
                            'is_read' => false,
                        ]);

                        // Log or output a comment
                        $this->comment("Warning sent to User ID: {$hr->user_id} for Torrent: {$hr->torrent->name}");
                    }
                });

            // Check for users exceeding max warnings and disable download privileges

            $maxWarnings = config('hitrun.max_warnings');

            $users = Warning::query()
    ->select('user_id')
    ->where('active', 1)
    ->groupBy('user_id')
    ->havingRaw('COUNT(*) >= ?', [$maxWarnings])
    ->pluck('user_id');

foreach ($users as $userId) {
    $this->comment("User ID: $userId has exceeded the maximum warnings.");
}


            $this->comment('Automated User Warning Command Complete');
        } catch (Throwable $e) {
            Log::error('Error sending warnings:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('An error occurred while processing the warnings. Check the logs for details.');
        }

    }
}
