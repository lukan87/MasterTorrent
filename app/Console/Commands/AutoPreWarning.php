<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\History;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
use App\Services\SystemMessageService;

class AutoPreWarning extends Command
{
    protected $signature = 'auto:prewarning';

    protected $description = 'Automatically Sends Pre Warning Notifications To Users';

    final public function handle(): void
    {
        if (!config('hitrun.enabled', false)) {
            return;
        }

        try {

            $downloadThreshold = config('hitrun.download_threshold', 25);

            History::with(['user', 'torrent'])

                ->where('created_at', '>', '2025-02-01 00:00:00')
                ->whereNull('prewarned_at')
                ->where('hitrun', 0)
                ->where('immune', 0)
                ->where('actual_downloaded', '>', 0)
                ->where('active', 0)
                ->where('seedtime', '<=', config('hitrun.seedtime'))
                ->where('updated_at', '<', now()->subDays(config('hitrun.prewarn')))

                ->whereHas('torrent', function ($query) use ($downloadThreshold) {

                    $query->whereNull('deleted_at')
                          ->where('external', false)
                          ->whereRaw(
                              'history.actual_downloaded >= torrents.size * ?',
                              [$downloadThreshold / 100]
                          )
                          ->where('seeders', '>', 0);

                })

                ->whereHas('user', function ($query) {

                    $query->where('is_immune', false)
                          ->where('user_class', '<', 3)
                          ->where('donor', 'no');

                })

                ->whereRaw('(history.uploaded / NULLIF(history.actual_downloaded, 0)) < 1.0')

                ->whereDoesntHave('user.warnings', fn($query) =>
                    $query->withTrashed()
                          ->whereColumn('warnings.torrent', '=', 'history.torrent_id')
                )

                ->chunkById(100, function ($prewarns) {

                    foreach ($prewarns as $pre) {

                        $uploaded = $pre->uploaded ?? 0;
                        $downloaded = $pre->actual_downloaded ?? 0;

                        $ratio = $downloaded > 0
                            ? $uploaded / $downloaded
                            : 0;

                        if ($ratio >= 1.00) {
                            continue;
                        }

                        History::where('torrent_id', $pre->torrent_id)
                            ->where('user_id', $pre->user_id)
                            ->update(['prewarned_at' => now()]);

                        $torrentLink = route('torrents.show', [
                            'id' => $pre->torrent_id,
                            'slug' => $pre->torrent->slug
                        ]);

                        $downloadedPercent = $pre->torrent->size > 0
                            ? ($downloaded / $pre->torrent->size) * 100
                            : 0;

$body = "
[b][color=red]FINAL WARNING: Hit & Run Imminent[/color][/b]

Torrent:
[url={$torrentLink}]{$pre->torrent->name}[/url]

Downloaded:
".number_format($downloadedPercent,2)."%

Current Ratio:
".number_format($ratio,2)."

Your ratio remains below the required minimum of 1.00 and your grace period is about to expire.

If you do NOT:
• Resume seeding
• Improve your ratio to at least 1.00

A Hit & Run warning will be automatically applied to your account.
";

                        $this->sendSystemMessage(
                            $pre->user_id,
                            'Pre-Warning: Hit and Run',
                            $body
                        );

                        $this->comment(
                            "Pre-warning sent → User {$pre->user_id} | Torrent {$pre->torrent->name}"
                        );
                    }

                });

            $this->comment('Automated User Pre-Warning Command Complete');

        } catch (Throwable $e) {

            Log::error('Error sending pre-warnings: '.$e->getMessage());

            $this->error('Error while processing pre-warnings.');

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message inside conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = config('hitrun.system_user_id', 2);

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}