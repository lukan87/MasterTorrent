<?php

namespace App\Services\HitRun;

use App\Models\History;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Support\Facades\DB;

class HitRunAmnestyService
{
    /**
     * Preview how many hit & runs would be affected and how much upload would
     * be credited, without writing anything.
     */
    public function preview(?int $userId = null): array
    {
        return $this->process(true, $userId);
    }

    /**
     * Wipe every hit & run. For each affected torrent the user is credited the
     * shortfall so the torrent reaches a 1:1 ratio, the H&R flag is cleared,
     * and the user's hit & run counter / related restrictions are reset.
     *
     * @return array{records:int, users:int, total_upload_credited:int, downloads_restored:int, warnings_cleared:int}
     */
    public function apply(?int $userId = null, bool $notify = true): array
    {
        return $this->process(false, $userId, $notify);
    }

    /**
     * Clear a single user's OLDEST hit & run, giving 1:1 on that torrent and
     * decreasing their hit & run counter.
     *
     * @return array{cleared:bool, history:?\App\Models\History, shortfall:int}
     */
    public function clearOldestForUser(User $user): array
    {
        $history = History::where('user_id', $user->id)
            ->where('hitrun', 1)
            ->orderBy('created_at')
            ->first();

        if (!$history) {
            return ['cleared' => false, 'history' => null, 'shortfall' => 0];
        }

        $shortfall = max(0, (int) $history->downloaded - (int) $history->uploaded);

        if ($shortfall > 0) {
            $user->increment('uploaded', $shortfall);
        }

        $history->update([
            'uploaded' => max((int) $history->uploaded, (int) $history->downloaded),
            'actual_uploaded' => max((int) $history->actual_uploaded, (int) $history->actual_downloaded),
            'hitrun' => 0,
            'hitrun_removed_at' => now(),
        ]);

        if ((int) $user->hit_and_run_count > 0) {
            $user->decrement('hit_and_run_count');
        }

        return ['cleared' => true, 'history' => $history, 'shortfall' => $shortfall];
    }

    /**
     * Core routine shared by preview/apply.
     *
     * @return array{records:int, users:int, total_upload_credited:int, downloads_restored:int, warnings_cleared:int, affected_users:array}
     */
    protected function process(bool $dryRun, ?int $userId = null, bool $notify = false): array
    {
        $query = History::with('user', 'torrent')
            ->where('hitrun', 1)
            ->whereHas('torrent');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $stats = [
            'records' => 0,
            'users' => 0,
            'total_upload_credited' => 0,
            'downloads_restored' => 0,
            'warnings_cleared' => 0,
            'affected_users' => [],
        ];

        // uid => ['shortfall' => int]
        $affected = [];

        $callback = function ($records) use ($dryRun, &$stats, &$affected) {
            foreach ($records as $history) {
                $user = $history->user;

                if (!$user) {
                    continue;
                }

                $shortfall = max(0, (int) $history->downloaded - (int) $history->uploaded);

                $stats['records']++;
                $stats['total_upload_credited'] += $shortfall;
                $affected[$user->id]['shortfall'] = ($affected[$user->id]['shortfall'] ?? 0) + $shortfall;

                if ($dryRun) {
                    $stats['affected_users'][$user->id]['user'] = $user->name;
                    $stats['affected_users'][$user->id]['torrents'][] = $history->torrent->name;
                    continue;
                }

                if ($shortfall > 0) {
                    $user->increment('uploaded', $shortfall);
                }

                $requiredSeedtime = (int) config('hitrun.min_seedtime', 12 * 3600);

                $history->update([
                    'uploaded' => max((int) $history->uploaded, (int) $history->downloaded),
                    'actual_uploaded' => max((int) $history->actual_uploaded, (int) $history->actual_downloaded),
                    'seedtime'         => max((int) $history->seedtime, $requiredSeedtime),
                    'hitrun' => 0,
                    'hitrun_removed_at' => now(),
                    'hitrun_warned_at' => null,
                    'prewarned_at'     => null,
                ]);
            }
        };

        if ($dryRun) {
            $query->chunkById(200, $callback);
        } else {
            DB::transaction(function () use ($query, $callback) {
                $query->chunkById(200, $callback);
            });
        }

        $stats['users'] = count($affected);

        if (!$dryRun && count($affected)) {
            foreach ($affected as $uid => $meta) {
                $user = User::find($uid);

                if (!$user) {
                    continue;
                }

                // Reset the per-user hit & run counter in the users table.
                if ((int) $user->hit_and_run_count !== 0) {
                    $user->update(['hit_and_run_count' => 0]);
                }

                // Restore download privilege when it had been locked by H&R.
                if ($user->downloadpos === 'no') {
                    $user->update(['downloadpos' => 'yes']);
                    $stats['downloads_restored']++;
                }

                // Only clear warnings that are clearly H&R related.
                $reason = (string) $user->warned_reason;
                if ((int) $user->warned === 1 && stripos($reason, 'hit') !== false) {
                    $user->update([
                        'warned' => 0,
                        'warned_until' => null,
                        'warned_reason' => null,
                    ]);
                    $stats['warnings_cleared']++;
                }

                if ($notify) {
                    $this->notifyUser($user->id, $meta['shortfall'] ?? 0);
                }
            }
        }

        return $stats;
    }

    /**
     * Send a friendly summary to a user informing them about the amnesty.
     */
    protected function notifyUser(int $userId, int $uploadCredited): void
    {
        $systemId = (int) config('hitrun.system_user_id', 2);

        $body = "\n[color=teal][b]HIT & RUN AMNESTY[/b][/color]\n\n"
            . 'All of your Hit & Runs have been cleared by the administration.' . "\n";

        if ($uploadCredited > 0) {
            $body .= 'Your upload was topped up by ' . $this->formatBytes($uploadCredited)
                . " so each affected torrent now counts as a 1:1 ratio.\n";
        }

        $body .= "\nThank you!\n";

        SystemMessageService::send(
            $systemId,
            $userId,
            'Hit & Run Amnesty — Everything Cleared',
            $body
        );
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
        }
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
