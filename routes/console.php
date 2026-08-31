<?php

//use App\Jobs\SyncStatsFromRedis;
use Illuminate\Support\Facades\Schedule;
//use App\Jobs\SyncHistoryFromRedis;

//Schedule::command('peers:remove-duplicates')->everyFiveMinutes();

Schedule::command('auto:flush_peers')->everyFiveMinutes();

//Schedule::command('auto:delete_stopped_peers')->hourly();

Schedule::command('peers:cleanup')->everyMinute();

Schedule::command('auto:sync_peers')->everyMinute()->withoutOverlapping(5);

Schedule::command('auto:seedbonus_award')->everyFifteenMinutes()->withoutOverlapping(5);

//Schedule::command('auto:correct_history')->everyFifteenMinutes();

Schedule::command('users:promote-demote')->daily()->withoutOverlapping();

Schedule::command('torrents:update-imdb')->everyThirtyMinutes()->withoutOverlapping(5);

//Clean duplicate history entries
//Schedule::command('history:cleanup')->everyFiveMinutes();

//Clear Banned Expired
Schedule::command('bans:clear-expired')->everyThirtyMinutes()->withoutOverlapping();

Schedule::command('backup:run-custom')->daily()->withoutOverlapping();

//Schedule::command('auto:prewarning')->daily();
//Schedule::command('auto:warning')->daily();

Schedule::command('auto:deactivate_warning')->daily()->withoutOverlapping();

// VIP until demote
Schedule::command('users:revert-vip-status')->daily()->withoutOverlapping();

//Uploaders demotion
//Schedule::command('warn:uploaders')->daily();
//Schedule::command('users:demote-inactive-uploaders')->daily();

//Invites expire after two weeks of creation and not being used
//Schedule::command('invites:expire')->daily();

//Change bumped torrents to not bumped after 30 days
Schedule::command('torrents:unbump-old')->daily()->withoutOverlapping();

//Delete messages older than 1 month and read
Schedule::command('messages:cleanup')->daily()->withoutOverlapping(5);

//Clear expired warnings
Schedule::command('users:clear-expired-warnings')->hourly()->withoutOverlapping();

//Delete torrents older than 3 years with no seeders
//Schedule::command('torrents:cleanup')->monthly();

//Delete unused invites
Schedule::command('invites:process')->daily()->withoutOverlapping();

Schedule::command('auto:update_torrent_stats')->everyThirtyMinutes()->withoutOverlapping();

Schedule::command('storage:fix')->everyTenMinutes()->withoutOverlapping();

Schedule::command('happyhour:check')->hourly()->withoutOverlapping(5);

// Redis sync jobs
// Schedule::job(new SyncHistoryFromRedis())->everyMinute()->withoutOverlapping();
// Schedule::job(new SyncStatsFromRedis())->everyMinute()->withoutOverlapping();

// Rank rewards and seeding score calculations
Schedule::command('tracker:reward-seeder-ranks')->hourly()->withoutOverlapping(5);
Schedule::command('tracker:calculate-seeder-ranks')->hourly()->withoutOverlapping(5);
Schedule::command('tracker:calculate-torrent-seeding-score')->hourly()->withoutOverlapping(5);

// HNR
Schedule::command('hitrun:reminder')->hourly()->withoutOverlapping();
Schedule::command('hitrun:enforce')->hourly()->withoutOverlapping();
Schedule::command('hitrun:recover')->everyTenMinutes()->withoutOverlapping();
Schedule::command('hitrun:download-lock')->everyTenMinutes()->withoutOverlapping();

// PEERS CHECK
Schedule::command('tracker:check-connectable')->everyMinute()->withoutOverlapping(5);

// Flush user stats
//  Schedule::command('tracker:flush-user-stats')->everyMinute()->withoutOverlapping();

Schedule::command('torrent:sync-movies')->hourly()->withoutOverlapping();