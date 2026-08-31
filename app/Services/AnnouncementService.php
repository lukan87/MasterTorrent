<?php


namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AnnouncementService
{
    /*
    |--------------------------------------------------------------------------
    | Fetch active announcements
    |--------------------------------------------------------------------------
    */

public function getActive(): Collection
{
    return Cache::remember('announcements.active', 60, function () {
        return Announcement::active()
            ->orderByDesc('priority')
            ->latest()
            ->get();
    });
}

    /*
    |--------------------------------------------------------------------------
    | Get unread announcements for user
    |--------------------------------------------------------------------------
    */

    public function getUnread(User $user): Collection
    {
        return Announcement::active()
            ->whereDoesntHave('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('priority')
            ->latest()
            ->get();
    }

    public function getUnreadCount(User $user): int
    {
        return Announcement::active()
            ->whereDoesntHave('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Mark as seen
    |--------------------------------------------------------------------------
    */

    public function markAsSeen(User $user, int $announcementId): void
    {
        $user->announcements()->syncWithoutDetaching([
            $announcementId => ['seen_at' => now()]
        ]);
    }

    public function markAllAsSeen(User $user): void
    {
        $announcements = Announcement::active()->pluck('id');

        $syncData = [];

        foreach ($announcements as $id) {
            $syncData[$id] = ['seen_at' => now()];
        }

        $user->announcements()->syncWithoutDetaching($syncData);
    }

    /*
    |--------------------------------------------------------------------------
    | Create announcement (admin)
    |--------------------------------------------------------------------------
    */

public function create(array $data, User $author): Announcement
{
    $data['user_id'] = $author->id;
    $data['is_active'] = true;

    $announcement = Announcement::create($data);

    Cache::forget('announcements.active');

    return $announcement;
}
}