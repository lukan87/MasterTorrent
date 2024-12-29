<?php



use Illuminate\Support\Facades\Schedule;




Schedule::command('auto:flush_peers')->everyThirtyMinutes();
//Schedule::command('auto:delete_stopped_peers')->hourly();
Schedule::command('auto:sync_peers')->everyThirtyMinutes();

Schedule::command('auto:seedbonus_award')->hourly();

Schedule::command('auto:correct_history')->hourly();

Schedule::command('users:promote-demote')->daily();

Schedule::command('torrents:update-imdb')->everyThirtyMinutes();

//Clear Banned Expired
Schedule::command('bans:clear-expired')->everyThirtyMinutes();

Schedule::command('backup:run-custom')->monthlyOn(1, '02:00');

Schedule::command('auto:prewarning')->daily();
Schedule::command('auto:warning')->daily();
Schedule::command('auto:deactivate_warning')->daily();

// VIP until demote
Schedule::command('users:revert-vip-status')->daily();



