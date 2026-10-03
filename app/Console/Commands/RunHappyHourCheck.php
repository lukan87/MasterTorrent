<?php

namespace App\Console\Commands;

use App\Models\HappyHour;
use App\Services\HappyHourService;
use Illuminate\Console\Command;

class RunHappyHourCheck extends Command
{
    protected $signature = 'happyhour:check';

    protected $description = 'Expire Happy Hours and check for an automatic event';

    public function handle(HappyHourService $service): int
    {
        [$ended, $started] = $service->locked(function () use ($service) {
            $now = now();
            $ended = HappyHour::where('active', true)->where('end_at', '<=', $now)->get();
            foreach ($ended as $event) {
                $event->update(['active' => false]);
            }

            if (! $service->automaticEnabled() || $now->hour < 7
                || HappyHour::where('automatic', true)->whereDate('start_at', $now->toDateString())->exists()) {
                return [$ended, null];
            }
            $theme = $service->theme();
            $end = $now->copy()->addHours($theme['duration_hours']);
            if ($service->overlaps($now, $end)
                || random_int(1, max(1, (int) config('happyhour.random_chance', 3))) !== 1) {
                return [$ended, null];
            }
            $event = HappyHour::create([
                'active' => true, 'automatic' => true, 'theme' => $theme['name'],
                'upload_multiplier' => $theme['upload_multiplier'], 'free_download' => $theme['free_download'],
                'start_at' => $now, 'end_at' => $end, 'activated_by' => null,
            ]);

            return [$ended, $event];
        });
        foreach ($ended as $event) {
            $service->announce($event, 'has ended');
        }
        if ($started) {
            $service->announce($started, 'has started');
        }
        $this->info($started ? 'Started: '.$started->theme : 'Happy Hour check complete.');

        return self::SUCCESS;
    }
}
