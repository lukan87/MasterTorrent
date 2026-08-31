<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AnnouncementServiceInterface
{
    public function getActive();
    public function getUnread(User $user);
    public function getUnreadCount(User $user): int;
    public function markAsSeen(User $user, int $announcementId): void;
}