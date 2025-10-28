<?php



use Illuminate\Support\Facades\Schedule;



Schedule::command('peers:remove-duplicates')->everyFiveMinutes();
Schedule::command('auto:flush_peers')->everyFiveMinutes();
//Schedule::command('auto:delete_stopped_peers')->hourly();
Schedule::command('auto:sync_peers')->everyFiveMinutes();

Schedule::command('auto:seedbonus_award')->everyFifteenMinutes();


//Schedule::command('auto:correct_history')->everyFifteenMinutes();

Schedule::command('users:promote-demote')->daily();

Schedule::command('torrents:update-imdb')->everyThirtyMinutes();

//Clean duplicate history entries
//Schedule::command('history:cleanup')->everyFiveMinutes();


//Clear Banned Expired
Schedule::command('bans:clear-expired')->everyThirtyMinutes();

Schedule::command('backup:run-custom')->daily();

Schedule::command('auto:prewarning')->daily();
Schedule::command('auto:warning')->daily();
Schedule::command('auto:deactivate_warning')->daily();

// VIP until demote
Schedule::command('users:revert-vip-status')->daily();

//Uploaders demotion
Schedule::command('warn:uploaders')->daily();
Schedule::command('users:demote-inactive-uploaders')->daily();

//Invites expire after two weeks of creation and not being used
//Schedule::command('invites:expire')->daily();

//Change bumped torrents to not bumped after 30 days
Schedule::command('torrents:unbump-old')->daily();

//Sterge mesaje mai vechi de o luna si care sunt citite
Schedule::command('messages:delete-old')->daily();

//Sterge waned_until daca e expirat
Schedule::command('users:clear-expired-warnings')->hourly();

//Sterge torrente mai vechi de 3 ani si care nu au seeders
Schedule::command('torrents:cleanup')->monthly();

//Sterge invitatiile nefolosite 
Schedule::command('invites:process')->daily();

Schedule::command('auto:update_torrent_stats')->everyThirtyMinutes();

Schedule::command('storage:fix')->everyTenMinutes();

Schedule::command('happyhour:check')->hourly();










