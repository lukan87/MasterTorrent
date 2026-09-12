<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\SystemMessageService;

class RewardSeederRanks extends Command
{
    protected $signature = 'tracker:reward-seeder-ranks';

    protected $description = 'Give bonuses when users reach new seeder ranks';

    public function handle()
    {
        $maxSeedbonus = 999999;

        User::whereColumn('seeder_rank','>','rank_rewarded')

        ->chunkById(200,function($users) use ($maxSeedbonus){

            if ($users->isEmpty()) {
                return;
            }

            $this->info("Processing {$users->count()} users...\n");

            foreach ($users as $user) {

                $newRank = $user->seeder_rank;
                $oldRank = $user->rank_rewarded;

                $this->line("User: {$user->name} (Class {$user->user_class})");
                $this->line("Rank: {$oldRank} → {$newRank}");

                /*
                |--------------------------------------------------------------------------
                | STAFF USERS (NO BONUS)
                |--------------------------------------------------------------------------
                */

                if ($user->user_class >= 6) {

                    $this->warn("Staff class detected. No bonus applied.");

                    $this->sendSystemMessage(
                        $user->id,
                        'Seeder Rank Increased',
                        "🎉 Your Seeder Rank increased to level {$newRank}.\n\nStaff ranks do not receive seedbonus rewards."
                    );

                    $user->rank_rewarded = $newRank;
                    $user->save();

                    $this->line(str_repeat('-',50));

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CALCULATE BONUS
                |--------------------------------------------------------------------------
                */

                $totalBonus = $this->calculateBonus($user->user_class,$oldRank,$newRank);

                /*
                |--------------------------------------------------------------------------
                | APPLY BONUS
                |--------------------------------------------------------------------------
                */

                if ($totalBonus > 0) {

                    $futureSeedbonus = $user->seedbonus + $totalBonus;

                    if ($futureSeedbonus > $maxSeedbonus) {

                        $this->warn("User near max seedbonus. Reward skipped.");

                        $this->sendSystemMessage(
                            $user->id,
                            'Seeder Rank Reward Pending',
                            "🎉 Your Seeder Rank increased from level {$oldRank} to {$newRank}.\n\n".
                            "You would normally receive {$totalBonus} seedbonus.\n\n".
                            "However your current seedbonus balance is {$user->seedbonus}, which is near the maximum allowed limit of {$maxSeedbonus}.\n\n".
                            "Please spend some seedbonus in the bonus store so you can receive future rewards."
                        );

                    } else {

                        $user->seedbonus += $totalBonus;

                        $this->info("Total Bonus: {$totalBonus}");

                        $this->sendSystemMessage(
                            $user->id,
                            'Seeder Rank Increased',
                            "🎉 Congratulations!\n\n".
                            "Your Seeder Rank increased from level {$oldRank} to {$newRank}.\n\n".
                            "You received {$totalBonus} seedbonus points as a reward for supporting the tracker.\n\n".
                            "Keep seeding and supporting the community!"
                        );
                    }
                }

                $user->rank_rewarded = $newRank;
                $user->save();

                $this->line(str_repeat('-',50));
            }

        });

        $this->info("\nSeeder rank rewards processed successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | BONUS CALCULATION
    |--------------------------------------------------------------------------
    */

    private function calculateBonus($class,$oldRank,$newRank)
    {
        $multiplier = 0;

        if (in_array($class,[1,2])) {
            $multiplier = 500;
        } elseif (in_array($class,[3,4])) {
            $multiplier = 1000;
        } elseif ($class == 5) {
            $multiplier = 2000;
        }

        if ($multiplier == 0) {
            return 0;
        }

        $bonus = 0;

        for ($rank = $oldRank + 1; $rank <= $newRank; $rank++) {
            $bonus += $rank * $multiplier;
        }

        return $bonus;
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message via conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = 2;

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}