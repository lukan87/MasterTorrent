<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Warning;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\SystemMessageService;

class AutoDeactivateWarning extends Command
{
    protected $signature = 'auto:deactivate_warning';

    protected $description = 'Automatically deactivates user warnings if expired or based on other conditions';

    final public function handle(): void
    {
        $current = Carbon::now();

        try {

            Warning::query()
                ->where('active', true)
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

                        /*
                        |--------------------------------------------------------------------------
                        | Deactivate warning
                        |--------------------------------------------------------------------------
                        */

                        $warning->update(['active' => false]);

                        $user = $warning->warneduser;
                        $torrent = $warning->torrenttitle;

                        /*
                        |--------------------------------------------------------------------------
                        | Remove warned flag if no active warnings
                        |--------------------------------------------------------------------------
                        */

                        $hasActiveWarnings = Warning::where('user_id', $user->id)
                            ->where('active', true)
                            ->exists();

                        if (!$hasActiveWarnings && $user->warned_until && $user->warned_until <= now()) {

                            $user->update([
                                'warned' => 0,
                                'warned_until' => null
                            ]);

                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Prepare message
                        |--------------------------------------------------------------------------
                        */

                        $torrentLink = route('torrents.show', [
                            'id' => $torrent->id,
                            'slug' => $torrent->slug ?? ''
                        ]);

                        $body = "
Your warning regarding the torrent:

[url={$torrentLink}]{$torrent->name}[/url]

has expired.

Please continue seeding torrents to avoid future warnings.
";

                        /*
                        |--------------------------------------------------------------------------
                        | Send system message
                        |--------------------------------------------------------------------------
                        */

                        $this->sendSystemMessage(
                            $user->id,
                            'Warning Expired',
                            $body
                        );

                        $this->comment(
                            "Warning deactivated → User: {$user->name} (ID {$user->id}) | Torrent: {$torrent->name}"
                        );

                    }

                });

            /*
            |--------------------------------------------------------------------------
            | Restore download privileges if warnings below limit
            |--------------------------------------------------------------------------
            */

            Warning::with('warneduser')
                ->select(DB::raw('user_id, SUM(active = 1) as value'))
                ->groupBy('user_id')
                ->having('value', '<', config('hitrun.max_warnings'))
                ->whereRelation('warneduser', 'downloadpos', 'no')
                ->chunkById(100, function ($warnings): void {

                    foreach ($warnings as $warning) {

                        $warning->warneduser->update([
                            'downloadpos' => 'yes'
                        ]);

                        cache()->forget('user:'.$warning->warneduser->passkey);

                    }

                }, 'user_id');

            $this->comment('Automated warning deactivation complete');

        } catch (Throwable $e) {

            Log::error('Error during auto warning deactivation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('Error occurred. Check logs.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send system message using conversation
    |--------------------------------------------------------------------------
    */

    private function sendSystemMessage($userId, $subject, $body)
    {
        $systemId = config('hitrun.system_user_id', 2);

        SystemMessageService::send($systemId, $userId, $subject, $body);
    }
}