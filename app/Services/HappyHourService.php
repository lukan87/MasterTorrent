<?php

namespace App\Services;

use App\Models\HappyHour;
use App\Models\Shoutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class HappyHourService
{
    public function automaticEnabled(): bool
    {
        return (bool) DB::table('happy_hour_settings')->where('id', 1)->value('automatic_enabled');
    }

    public function theme(): array
    {
        return config('happyhour.day_themes.'.now()->dayOfWeek) ?? [
            'name' => 'Classic Happy Hour',
            'upload_multiplier' => config('happyhour.default_upload_multiplier', 3),
            'duration_hours' => config('happyhour.default_duration_hours', 2),
            'free_download' => config('happyhour.default_free_download', true),
        ];
    }

    // A singleton database row serializes manual scheduling and automatic starts.
    public function locked(callable $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            $settings = DB::table('happy_hour_settings')->where('id', 1)->lockForUpdate()->first();
            if (! $settings) {
                throw new \RuntimeException('Happy Hour settings are missing. Run the Happy Hour migration.');
            }

            return $callback();
        });
    }

    public function overlaps($start, $end): bool
    {
        return HappyHour::where('active', true)->where('start_at', '<', $end)
            ->where('end_at', '>', $start)->exists();
    }

    public function create(array $data): HappyHour
    {
        return $this->locked(function () use ($data) {
            if ($this->overlaps($data['start_at'], $data['end_at'])) {
                throw ValidationException::withMessages(['start_at' => 'This time overlaps another Happy Hour. Choose another time or stop that event first.']);
            }

            return HappyHour::create($data + ['active' => true, 'automatic' => false]);
        });
    }

    public function announce(HappyHour $event, string $action): void
    {
        try {
            $message = '🎉 '.e($event->theme).' Happy Hour '.$action.'. '
                .$event->upload_multiplier.'× upload credit'
                .($event->free_download ? ' • Freeleech' : '')
                .' • '.$event->start_at->format('M j, H:i').' – '.$event->end_at->format('M j, H:i')
                .' '.config('app.timezone').'. Seeding rules still apply.';
            Shoutbox::create(['user_id' => $event->activated_by ?? config('happyhour.announcement_user_id', 2), 'message' => $message, 'parent_id' => null]);
        } catch (\Throwable $e) {
            Log::warning('Happy Hour announcement failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
        }
    }
}
