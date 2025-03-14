<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Warning;
use App\Models\Message;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoDeactivateWarning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:deactivate_warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically deactivates user warnings if expired or based on other conditions';

    /**
     * Execute the console command.
     *
     * @throws Exception|Throwable If there is an error during the execution of the command.
     */
    final public function handle(): void
    {
        $current = Carbon::now();

        try {
            // Deactivate warnings that are expired or based on history conditions
            Warning::query()
                ->where('active', '=', true)
                ->where(
                    fn ($query) => $query
                        ->where('expires_on', '<=', $current)
                        ->orWhereHas(
                            'torrenttitle.history',
                            fn ($query) => $query
                                ->whereColumn('history.user_id', '=', 'warnings.user_id')
                                ->where('history.seedtime', '>=', config('hitrun.seedtime'))
                        )
                )
                ->chunkById(100, function ($warnings): void {
                    foreach ($warnings as $warning) {
                        // Deactivate the warning
                        $warning->update(['active' => false]);

                         
                           // Prepare user and torrent details
                          $user = $warning->warneduser;
                          $torrent = $warning->torrenttitle;

                           // If user's last warning is expired, remove the warned flag
                    $hasActiveWarnings = Warning::where('user_id', $user->id)->where('active', true)->exists();
                    if (!$hasActiveWarnings && $user->warned_until && $user->warned_until <= now()) {
                        $user->update(['warned' => 0, 'warned_until' => null]);
                    }


                          $torrentLink = route('torrents.show', ['id' => $warning->torrenttitle->id, 'slug' => $warning->torrenttitle->slug ?? '']);
                          $body = "Your warning regarding the torrent <a href=\"$torrentLink\">{$warning->torrenttitle->name}</a> has expired!\n\n";
                          $body .= "Keep seeding until you reach the requested time, so you will not get any more warnings!  ";

                           // Send a message to the user
                         Message::create([
                             'receiver_id' => $warning->warneduser->id,
                             'subject' => 'Warning Expired',
                             'sender_id' => config('hitrun.system_user_id', 2),
                             'body' => $body,
                             'is_read' => false,
                         ]);

                         // Output information about the deactivated warning in the console
                        $this->comment("Warning deactivated for User: {$user->name} (ID: {$user->id}) on Torrent: {$torrent->name} (ID: {$torrent->id})");


                    }
                });

            // Calculate User Warning Count and Enable Download Privileges If Needed
            Warning::with('warneduser')
                ->select(DB::raw('user_id, SUM(active = 1) as value'))
                ->groupBy('user_id')
                ->having('value', '<', config('hitrun.max_warnings'))
                ->whereRelation('warneduser', 'downloadpos', '=', 'no')
                ->chunkById(100, function ($warnings): void {
                    foreach ($warnings as $warning) {
                        // Enable download privileges
                        $warning->warneduser->update(['downloadpos' => 'yes']);

                        // Forget the cache for the user to reflect the changes
                        cache()->forget('user:'.$warning->warneduser->passkey);



                    }
                }, 'user_id');

            $this->comment('Automated warning deactivation command complete');
        } catch (Throwable $e) {
            // Log any errors
            Log::error('Error during auto warning deactivation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('An error occurred during the execution of the command. Check the logs for details.');
        }
    }
}
