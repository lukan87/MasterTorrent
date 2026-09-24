<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Services\SystemMessageService;

trait WarnsForHitRun
{
    /**
     * Issue (or extend) the user's warning and send the enriched Hit & Run message.
     *
     * Applies a 14-day warning on every H&R, or +3 days if the user is already
     * actively warned. Returns a short human description of what was done.
     *
     * @param  Model  $row  A History model (must have its torrent relationship loaded).
     */
    protected function applyHitRunWarning($user, $row, int $requiredSeedTime, float $ratio): string
    {
        $currentWarnedUntil = $user->warned_until;
        $alreadyWarned      = $user->warned && $currentWarnedUntil && $currentWarnedUntil->gt(now());

        if ($alreadyWarned) {
            $newWarnedUntil = $currentWarnedUntil->copy()->addDays(3);
            $warningNote    = "You have been warned before. Your active warning has been [b]increased by 3 days[/b] (now expires on {$newWarnedUntil->format('Y-m-d')}).";
            $result         = 'warning increased by 3 days';
        } else {
            $newWarnedUntil = now()->addDays(14);
            $warningNote    = "You have been [b]warned for 14 days[/b] (until {$newWarnedUntil->format('Y-m-d')}).";
            $result         = 'warned for 14 days';
        }

        $user->update([
            'warned'        => 1,
            'warned_until'  => $newWarnedUntil,
            'warned_reason' => trim($user->warned_reason ? $user->warned_reason.' | ' : '').'Hit & Run issued',
        ]);

        $torrentLink = route('torrents.show', [
            'id'   => $row->torrent->id,
            'slug' => $row->torrent->slug,
        ]);

        $effectiveDownload  = $row->effectiveDownload();
        $seedtimeHuman      = $this->formatDuration((int) $row->seedtime);
        $remainingSeedtime  = max(0, $requiredSeedTime - (int) $row->seedtime);
        $remainingSeedHuman = $this->formatDuration($remainingSeedtime);
        $neededUpload       = max(0, $effectiveDownload - $row->uploaded);

        // When bytes were actually downloaded but nothing was credited (e.g. freeleech,
        // credited download = 0), we fall back to actual_downloaded so the 1:1 ratio is
        // computed fairly and the user cannot "get away with it".
        $statsText = $effectiveDownload > 0
            ? "• Ratio: ".number_format($ratio, 2)."\n• Upload still needed for 1:1: {$this->formatBytes($neededUpload)}"
            : "• No download recorded\n• Please reach the [b]12 hour[/b] seedtime requirement";

        $body = "
[b][color=red]HIT & RUN ISSUED[/color][/b]

You received a Hit & Run for torrent: [url={$torrentLink}]{$row->torrent->name}[/url]

Your seeding is below the minimum requirement.

[b]Required:[/b]
• Seed for at least 12 hours
• OR reach a ratio of 1.00

[b]Your stats:[/b]
• Seedtime: {$seedtimeHuman}
{$statsText}
• Seedtime still needed: {$remainingSeedHuman}

{$warningNote}

Please continue seeding this torrent until you reach the 1:1 ratio or 12 hours of seedtime to have this Hit & Run removed.
";

        SystemMessageService::send(
            config('hitrun.system_user_id', 2),
            $user->id,
            'Hit & Run Issued',
            $body
        );

        return $result;
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        $parts = [];

        if ($h > 0) { $parts[] = $h.'h'; }
        if ($m > 0) { $parts[] = $m.'m'; }
        if ($s > 0 || empty($parts)) { $parts[] = $s.'s'; }

        return implode(' ', $parts);
    }

    private function formatBytes(float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $i     = (int) floor(log($bytes, 1024));
        $i     = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 2).' '.$units[$i];
    }
}