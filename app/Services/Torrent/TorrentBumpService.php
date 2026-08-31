<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TorrentBumpService
{
    const DAILY_LIMIT = 10;

    public function bump(User $user, Torrent $torrent): void
    {
        DB::transaction(function () use ($user, $torrent) {

            if (!$user->can_bump_unlimited) {
                $todayCount = Torrent::where('bumped_by', $user->id)
                    ->whereDate('created_at', today())
                    ->lockForUpdate()
                    ->count();

                if ($todayCount >= self::DAILY_LIMIT) {
                    throw ValidationException::withMessages([
                        'bump' => 'You have reached the daily bump limit (10).'
                    ]);
                }
            }

            $torrent->update([
                'created_at' => now(),
                'bumped_at' => now(),
                'bumped_by' => $user->id,
                'bumped' => true,
            ]);
        });
    }
}
