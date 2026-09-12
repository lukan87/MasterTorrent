<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;
use App\Models\UserTimeline;
use App\Services\SystemMessageService;

class PromoteDemoteUsers extends Command
{
    protected $signature = 'users:promote-demote';
    protected $description = 'Promote or demote users based on configurable criteria.';

    public function handle()
    {
        $this->info("Starting promotion/demotion scan...\n");

        $now = Carbon::now();
        $rules = config('promotionsreq.elite_user');

        $promotedUsers = [];
        $demotedUsers = [];

        User::whereIn('user_class', [1,2])->chunkById(200, function ($users) use ($now, $rules, &$promotedUsers, &$demotedUsers) {

            foreach ($users as $user) {

                $requiredAge = $now->copy()->subMonths($rules['account_age_months']);

                $uploaded = $user->uploaded;
                $downloaded = $user->downloaded;

                $ratio = $downloaded > 0 ? $uploaded / $downloaded : INF;

                /*
                |--------------------------------------------------------------------------
                | Requirements
                |--------------------------------------------------------------------------
                */

                $eligibleAge = $user->created_at <= $requiredAge;
                $eligibleUploaded = $uploaded >= $rules['uploaded'];
                $eligibleDownloaded = $downloaded >= $rules['downloaded'];
                $eligibleRatio = $ratio >= $rules['ratio'];

                $eligiblePosts = ($rules['forum_posts'] ?? 0) == 0 || $user->forum_posts >= $rules['forum_posts'];
                $eligibleComments = ($rules['comments'] ?? 0) == 0 || $user->comments >= $rules['comments'];
                $eligibleThanks = ($rules['thanks'] ?? 0) == 0 || $user->thanks >= $rules['thanks'];
                $eligibleHitRuns = $user->hit_and_run_count == 0;

                /*
                |--------------------------------------------------------------------------
                | Promotion
                |--------------------------------------------------------------------------
                */

                if (
                    $user->user_class == 1 &&
                    $eligibleAge &&
                    $eligibleUploaded &&
                    $eligibleDownloaded &&
                    $eligibleRatio &&
                    $eligiblePosts &&
                    $eligibleComments &&
                    $eligibleThanks &&
                    $eligibleHitRuns
                ) {

                    $user->update([
                        'user_class' => 2
                    ]);

                    $body = "
Congratulations {$user->name}!

You have been promoted to [b]Elite User[/b].

Keep up the good work and don't forget to seed until you bleed!

LastFiles Team
";

                    $this->sendSystemMessage($user->id, "Class Promotion", $body);

                    UserTimeline::create([
                        'user_id' => $user->id,
                        'staff_id' => 2,
                        'comment' => "⬆️ Auto promoted to Elite User"
                    ]);

                    $promotedUsers[] = $user->name;

                    $this->info("PROMOTED: {$user->name} → Elite User");
                }

                /*
                |--------------------------------------------------------------------------
                | Demotion
                |--------------------------------------------------------------------------
                */

                if (
                    $user->user_class == 2 &&
                    $ratio < $rules['ratio']
                ) {

                    $user->update([
                        'user_class' => 1
                    ]);

                    $body = "
Hello {$user->name}!

You have been demoted to [b]User[/b] because your ratio dropped below {$rules['ratio']}.

Please reseed your torrents to restore your ratio.

LastFiles Team
";

                    $this->sendSystemMessage($user->id, "Class Demotion", $body);

                    UserTimeline::create([
                        'user_id' => $user->id,
                        'staff_id' => 2,
                        'comment' => "⬇️ Auto demoted to User due to low ratio"
                    ]);

                    $demotedUsers[] = $user->name;

                    $this->info("DEMOTED: {$user->name} → User");
                }

            }

        });

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $this->info("\n==============================");
        $this->info("Promotion/Demotion Summary");
        $this->info("==============================");

        $this->info("Promoted users: " . count($promotedUsers));
        if (!empty($promotedUsers)) {
            $this->line(" - " . implode("\n - ", $promotedUsers));
        }

        $this->info("\nDemoted users: " . count($demotedUsers));
        if (!empty($demotedUsers)) {
            $this->line(" - " . implode("\n - ", $demotedUsers));
        }

        if (empty($promotedUsers) && empty($demotedUsers)) {
            $this->comment("\nNo users were promoted or demoted.");
        }

        $this->info("\nScan completed.");
    }

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = 2;

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}