<?php

namespace App\Support;

class AdminWhitelist
{
    // DOAR aceste ID-uri pot promova useri (schimba user_class)
    public const PROMOTE_ALLOWED_IDS = [
        893,
        1,
        1067,
        1069421,
        1076853,
        1083357,
    ];

    public static function canPromote(?int $userId): bool
    {
        if (!$userId) return false;
        return in_array($userId, self::PROMOTE_ALLOWED_IDS, true);
    }
}