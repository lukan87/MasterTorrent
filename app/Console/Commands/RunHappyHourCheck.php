<?php

namespace App\Console\Commands;

use App\Models\HappyHour;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class RunHappyHourCheck extends Command
{
    protected $signature = 'happyhour:check';
    protected $description = 'Start or stop automatic Happy Hours, announce to shoutbox';

    public function handle()
    {
        $now = now();
        $active = HappyHour::where('active', true)->first();

        // Stop expired Happy Hour
        if ($active && $now->greaterThanOrEqualTo($active->end_at)) {
            $active->update(['active' => false]);
            $this->info("Happy Hour ended: {$active->theme}");
            Log::info("Happy Hour ended automatically: {$active->theme}");
            $this->announceHappyHourEnd($active);
            return 0;
        }

        // Check if automatic is enabled
        $latest = HappyHour::latest()->first();
        if (!$latest || !$latest->automatic) {
            $this->info('Automatic Happy Hour is disabled.');
            return 0;
        }

        // Only start if no active Happy Hour
        if (!$active) {
            $hour = $now->hour;

            // Start only after 7 AM
            if ($hour < 7) {
                $this->info('Too early to start automatic Happy Hour. Waiting until 7 AM.');
                return 0;
            }

            // Start once per day
            if ($latest->start_at->isToday()) {
                $this->info('Automatic Happy Hour already started today.');
                return 0;
            }

            $chance = Config::get('happyhour.random_chance', 3);
            if (rand(1, $chance) === 1) {
                $day = $now->dayOfWeek;
                $theme = Config::get("happyhour.day_themes.{$day}") ?? [
                    'name' => 'Classic Happy Hour',
                    'upload_multiplier' => Config::get('happyhour.default_upload_multiplier', 3),
                    'duration_hours' => Config::get('happyhour.default_duration_hours', 2),
                    'free_download' => Config::get('happyhour.default_free_download', true),
                ];

                $start = $now;
                $end = $now->copy()->addHours($theme['duration_hours']);

                // Deactivate any previous active Happy Hours just in case
                HappyHour::where('active', true)->update(['active' => false]);

                $happyHour = HappyHour::create([
                    'active' => true,
                    'automatic' => true,
                    'theme' => $theme['name'],
                    'free_download' => $theme['free_download'],
                    'upload_multiplier' => $theme['upload_multiplier'],
                    'start_at' => $start,
                    'end_at' => $end,
                    'activated_by' => null,
                ]);

                $this->info("🎉 Happy Hour started: {$theme['name']} ({$theme['upload_multiplier']}x for {$theme['duration_hours']}h)");
                Log::info("Happy Hour started automatically: {$theme['name']}");
                $this->announceHappyHour($happyHour);
            } else {
                $this->info('No Happy Hour today.');
            }
        }
    }

    protected function announceHappyHour(HappyHour $happyHour)
    {
        try {
            $message = "🎉 <strong>{$happyHour->theme}</strong> Happy Hour has started! "
                     . "{$happyHour->upload_multiplier}x Uploads for "
                     . "{$happyHour->end_at->diffInHours($happyHour->start_at)}h";

            if ($happyHour->free_download) {
                $message .= " | Free Downloads enabled!";
            }

            \App\Models\Shoutbox::create([
                'user_id' => 2,
                'message' => $message,
                'parent_id' => null,
            ]);

            $this->info('🎉 Happy Hour announcement posted to Shoutbox!');
            Log::info('Happy Hour announcement posted: ' . $message);

        } catch (\Throwable $e) {
            Log::error('Failed to announce Happy Hour start: ' . $e->getMessage());
        }
    }

    protected function announceHappyHourEnd(HappyHour $happyHour)
    {
        try {
            $message = "⏰ <strong>{$happyHour->theme}</strong> Happy Hour has ended!";
            \App\Models\Shoutbox::create([
                'user_id' => 2,
                'message' => $message,
                'parent_id' => null,
            ]);
            $this->info('🎉 Happy Hour end announcement posted!');
            Log::info('Happy Hour end announcement posted: ' . $message);
        } catch (\Throwable $e) {
            Log::error('Failed to announce Happy Hour end: ' . $e->getMessage());
        }
    }
}
