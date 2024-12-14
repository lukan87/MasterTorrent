<?php



use Illuminate\Support\Facades\Schedule;




Schedule::command('auto:flush_peers')->everyThirtyMinutes();
//Schedule::command('auto:delete_stopped_peers')->hourly();
Schedule::command('auto:sync_peers')->everyThirtyMinutes();

Schedule::command('auto:seedbonus_award')->hourly();

//Schedule::command('auto:correct_history')->everyTwoHours();

Schedule::command('users:promote-demote')->daily();

Schedule::command('torrents:update-imdb')->everyThirtyMinutes();

Schedule::command('backup:run-custom')->dailyAt('02:00');

// VIP until demote
Schedule::command('users:revert-vip-status')->daily();



