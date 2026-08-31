<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\History;

class CalculateSeederRanks extends Command
{
    protected $signature = 'tracker:calculate-seeder-ranks';

    protected $description = 'Recalculate seeder reputation and rank';

    public function handle()
    {
        $this->info('Calculating seeder ranks...');

        User::where('reputation_dirty', true)
            ->chunkById(200, function ($users) {

                foreach ($users as $user) {

                    // Calculate reputation
                    $reputation = History::where('user_id', $user->id)
                        ->sum('seeding_score') ?? 0;

                    // Determine rank
                    $rank = User::calculateSeederRank($reputation);

                    /*
                    |--------------------------------------------------------------------------
                    | Update only if reputation or rank changed
                    |--------------------------------------------------------------------------
                    */

                    if (
                        round($user->seeding_reputation, 2) !== round($reputation, 2) ||
                        $user->seeder_rank !== $rank
                    ) {

                        $this->line(
                            "Updating {$user->name} → Rank {$rank} | Reputation {$reputation}"
                        );

                        $user->seeding_reputation = $reputation;
                        $user->seeder_rank = $rank;
                    }

                    // Clear dirty flag
                    $user->reputation_dirty = false;

                    // Save only if something changed
                    if ($user->isDirty()) {
                        $user->save();
                    }
                }

            });

        $this->info('Seeder ranks updated.');
    }
}