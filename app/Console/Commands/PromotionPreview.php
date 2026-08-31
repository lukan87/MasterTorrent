<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class PromotionPreview extends Command
{
    protected $signature = 'users:promotion-preview';
    protected $description = 'Preview which users will be promoted or demoted';

    public function handle()
    {
        $now = Carbon::now();

        $rules = config('promotionsreq.elite_user');

        $requiredAge = $now->copy()->subMonths($rules['account_age_months']);

        $this->info("Promotion Preview");
        $this->info("------------------------------------");

        User::whereIn('user_class', [1,2])->chunkById(200, function ($users) use ($rules, $requiredAge) {

            foreach ($users as $user) {

                $uploaded = $user->uploaded;
                $downloaded = $user->downloaded;

                $ratio = $downloaded > 0 ? $uploaded / $downloaded : INF;

                $eligibleAge = $user->created_at <= $requiredAge;
                $eligibleUploaded = $uploaded >= $rules['uploaded'];
                $eligibleDownloaded = $downloaded >= $rules['downloaded'];
                $eligibleRatio = $ratio >= $rules['ratio'];

                $eligiblePosts = ($rules['forum_posts'] ?? 0) == 0 || $user->forum_posts >= $rules['forum_posts'];
                $eligibleComments = ($rules['comments'] ?? 0) == 0 || $user->comments >= $rules['comments'];
                $eligibleThanks = ($rules['thanks'] ?? 0) == 0 || $user->thanks >= $rules['thanks'];

                /*
                |--------------------------------------------------------------------------
                | Promotion Preview
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
                    $eligibleThanks
                ) {

                    $this->line("[PROMOTE] {$user->name} | Ratio: " . number_format($ratio,2));
                }

                /*
                |--------------------------------------------------------------------------
                | Demotion Preview
                |--------------------------------------------------------------------------
                */

                if (
                    $user->user_class == 2 &&
                    $ratio < $rules['ratio']
                ) {

                    $this->line("[DEMOTE] {$user->name} | Ratio: " . number_format($ratio,2));
                }

            }

        });

        $this->info("------------------------------------");
        $this->info("Preview complete.");
    }
}