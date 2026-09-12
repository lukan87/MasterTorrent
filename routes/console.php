<?php

// use App\Jobs\SyncStatsFromRedis;
use Illuminate\Support\Facades\Schedule;

// use App\Jobs\SyncHistoryFromRedis;

// Peer and tracker synchronization
// Schedule::command('peers:remove-duplicates')->everyFiveMinutes();

Schedule::command('auto:flush_peers')->everyFiveMinutes();

// Schedule::command('auto:delete_stopped_peers')->hourly();

Schedule::command('peers:cleanup')->everyMinute();

Schedule::command('auto:sync_peers')->everyMinute()->withoutOverlapping(5);

Schedule::command('auto:seedbonus_award')->everyFifteenMinutes()->withoutOverlapping(5);

// Schedule::command('auto:correct_history')->everyFifteenMinutes();

// User account and warning maintenance
Schedule::command('users:promote-demote')->daily()->withoutOverlapping();

Schedule::command('torrents:update-imdb')->everyThirtyMinutes()->withoutOverlapping(5);

// History maintenance
// Schedule::command('history:cleanup')->everyFiveMinutes();

// Ban and backup maintenance
Schedule::command('bans:clear-expired')->everyThirtyMinutes()->withoutOverlapping();

Schedule::command('backup:run-custom')->daily()->withoutOverlapping();

// Schedule::command('auto:prewarning')->daily();
// Schedule::command('auto:warning')->daily();

// User access and status maintenance
Schedule::command('auto:deactivate_warning')->daily()->withoutOverlapping();

// VIP status maintenance
Schedule::command('users:revert-vip-status')->daily()->withoutOverlapping();

// Uploader demotion maintenance
// Schedule::command('warn:uploaders')->daily();
// Schedule::command('users:demote-inactive-uploaders')->daily();

// Invitations expire after two weeks if unused
// Schedule::command('invites:expire')->daily();

// Torrent and message cleanup
Schedule::command('torrents:unbump-old')->daily()->withoutOverlapping();

// Delete messages older than one month that have been read
Schedule::command('messages:cleanup')->daily()->withoutOverlapping(5);

// Clear expired user warnings
Schedule::command('users:clear-expired-warnings')->hourly()->withoutOverlapping();

// Delete torrents older than three years with no seeders
// Schedule::command('torrents:cleanup')->monthly();

// Invitation maintenance
Schedule::command('invites:process')->daily()->withoutOverlapping();

Schedule::command('auto:update_torrent_stats')->everyThirtyMinutes()->withoutOverlapping();

// Storage and happy-hour maintenance
Schedule::command('storage:fix')->everyTenMinutes()->withoutOverlapping();

Schedule::command('happyhour:check')->hourly()->withoutOverlapping(5);

// Redis synchronization jobs
// Schedule::job(new SyncHistoryFromRedis())->everyMinute()->withoutOverlapping();
// Schedule::job(new SyncStatsFromRedis())->everyMinute()->withoutOverlapping();

// Seeding rewards and score calculations
Schedule::command('tracker:reward-seeder-ranks')->hourly()->withoutOverlapping(5);
Schedule::command('tracker:calculate-seeder-ranks')->hourly()->withoutOverlapping(5);
Schedule::command('tracker:calculate-torrent-seeding-score')->hourly()->withoutOverlapping(5);

// Hit-and-run enforcement
Schedule::command('hitrun:reminder')->hourly()->withoutOverlapping();
Schedule::command('hitrun:enforce')->hourly()->withoutOverlapping();
Schedule::command('hitrun:recover')->everyTenMinutes()->withoutOverlapping();
Schedule::command('hitrun:download-lock')->everyTenMinutes()->withoutOverlapping();

// Peer connectivity checks
Schedule::command('tracker:check-connectable')->everyMinute()->withoutOverlapping(5);

// User statistics maintenance
//  Schedule::command('tracker:flush-user-stats')->everyMinute()->withoutOverlapping();

// Movie metadata synchronization
Schedule::command('torrent:sync-movies')->hourly()->withoutOverlapping();

//Online Movies&Series metadata backfill (ratings, genres, dates)
Schedule::command('media:backfill')->weeklyOn(0, '03:00');
