<?php

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\User;
use App\Models\UserClass;

final class ForumAccess
{
    public static function allows(?User $user, string $permission): bool
    {
        return $user !== null && UserClass::userHasPermission($user->user_class, $permission);
    }

    public static function canView(?User $user, ForumCategory $category): bool
    {
        return ! $category->is_private || self::allows($user, 'manage_topics');
    }

    public static function canParticipate(?User $user): bool
    {
        return $user !== null && ! $user->forumblock;
    }

    public static function authorizeView(ForumCategory $category): void
    {
        abort_unless(self::canView(auth()->user(), $category), 403);
    }

    public static function authorize(string $permission): void
    {
        abort_unless(self::allows(auth()->user(), $permission), 403, 'You do not have permission for this forum action.');
    }

    public static function authorizeParticipation(): void
    {
        abort_unless(self::canParticipate(auth()->user()), 403, 'You are not allowed to participate in the forum.');
    }
}
