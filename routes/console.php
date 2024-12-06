<?php



use Illuminate\Support\Facades\Schedule;




Schedule::command('auto:flush_peers')->everyThirtyMinutes(); // Adjust frequency as needed
//Schedule::command('auto:delete_stopped_peers')->hourly(); // Adjust frequency as needed
Schedule::command('auto:sync_peers')->everyThirtyMinutes(); // Adjust frequency as needed
Schedule::command('auto:seedbonus_award')->hourly();
//Schedule::command('auto:correct_history')->everyTwoHours();
Schedule::command('users:promote-demote')->daily();

Schedule::command('backup:run-custom')->dailyAt('02:00');



//Peers Clean If internet was cut off or client was shutdown without announcing
