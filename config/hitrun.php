<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Hit and Run On / Off
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Seedtime
    |--------------------------------------------------------------------------
    |
    | Min Seedtime Required In Seconds
    |
    */
    'seedtime' => 43200, // 12 hours in seconds

    /* H&R timeline (stages)                                  */
    'warn_days'    => 5, // after N days from completion, send the seeding warning if not met
    'enforce_days' => 7, // after N days from completion, apply the Hit & Run if not met & not restarted

    /*
    |--------------------------------------------------------------------------
    | Max Warnings
    |--------------------------------------------------------------------------
    |
    | Max Warnings Before Ban
    |
    */
    'max_warnings' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Revoke Permissions
    |--------------------------------------------------------------------------
    |
    | Max Warnings Before Certain Permissions Are Revoked
    |
    */
    'revoke' => 2,

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Max Grace Time For User To Be Disconnected If "Seedtime" Value
    | Is Not Yet Met. "In Days"
    |
    */
    'grace' => 14,

    /*
    |--------------------------------------------------------------------------
    | Download Threshold
    |--------------------------------------------------------------------------
    |
    | Minimum percentage of the torrent that must be downloaded before
    | the hit-and-run rules apply. This prevents users from being penalized
    | for torrents they haven't downloaded much of.
    |
    */
    'download_threshold' => 25,

    /*
    |--------------------------------------------------------------------------
    | Buffer (deprecated)
    |--------------------------------------------------------------------------
    |
    | Legacy key for minimum percentage downloaded. Use 'download_threshold' instead.
    |
    */
    'buffer' => 25,

    /*
    |--------------------------------------------------------------------------
    | Warning Expire
    |--------------------------------------------------------------------------
    |
    | Max Days A Warning Lasts Before Expiring "In Days"
    |
    */
    'expire' => 14,

    /*
    |--------------------------------------------------------------------------
    | Prewarn Period
    |--------------------------------------------------------------------------
    |
    | Max Time For User To Be Disconnected If "Seedtime" Value
    | Is Not Yet Met. A Prewarning PM Will Be Sent. "In Days"
    |
    */
    'prewarn' => 10,

    /*
    |--------------------------------------------------------------------------
    | System User ID
    |--------------------------------------------------------------------------
    |
    | The ID of the system/admin user sending automated messages
    |
    */
    'system_user_id' => 2,

];
