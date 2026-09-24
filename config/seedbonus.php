<?php

/*
|--------------------------------------------------------------------------
| SeedBonus Configuration
|--------------------------------------------------------------------------
|
| Central home for every seed-bonus related number. Previously these values
| were hard-coded across BonusController, AwardSeedBonus and the User model
| (which caused the shop to drift out of sync with what was actually charged).
| Tune them here without touching code.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Earning
    |--------------------------------------------------------------------------
    */

    // Points awarded per torrent the user is actively seeding, per hour.
    // Torrents owned by the user are excluded.
    'points_per_hour' => 0.15,

    // Extra points added to the multiplier while a Happy Hour with free
    // download is active.
    'happy_hour_free_download_extra' => 0.10,

    // Maximum seedbonus a single user may hold.
    'cap' => 999999.99,

    /*
    |--------------------------------------------------------------------------
    | Points from actions
    |--------------------------------------------------------------------------
    */

    'upload_torrent_points' => 10,
    'thank_points' => 0.50,
    'comment_points' => 1,

    /*
    |--------------------------------------------------------------------------
    | Bonus Shop
    |--------------------------------------------------------------------------
    |
    | All costs are in seedbonus points.
    | 'upload' maps the number of GB purchased => points charged.
    |
    */

    'shop' => [
        'upload' => [
            10 => 100,   // 10 GB
            25 => 200,   // 25 GB
            100 => 350,  // 100 GB
        ],

        'vip' => 30000,      // 1 year VIP
        'vip_slots' => 10,   // slots granted when buying VIP
        'vip_invites' => 5,  // invites granted when buying VIP

        // Buy full seedtime for a single torrent
        'seedtime' => 1000,
        'seedtime_added' => 86400, // seconds (24h)

        // Remove the H&R for a torrent the user picks
        'remove_hnr' => 1500,

        // Auto-clear the user's OLDEST H&R and restore its ratio to 1:1
        'clear_hnr' => 7500,

        // Clear the user's active H&R-triggered warning
        'reset_warning' => 5000,

        'invite' => 1500,
        'slot' => 1000,
        'surprise' => 15000,
    ],

];