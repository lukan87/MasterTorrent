<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peer;

class CheckConnectablePeers extends Command
{
    protected $signature = 'tracker:check-connectable';
    protected $description = 'Check peer connectability in batches';

    public function handle()
    {
        $peers = Peer::where(function ($q) {
            $q->whereNull('connectable_checked_at')
              ->orWhere('connectable_checked_at', '<', now()->subMinutes(30));
        })
        ->limit(100)
        ->get();

        foreach ($peers as $peer) {

            // Skip private/local IPs
            if (!filter_var($peer->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                continue;
            }

            $fp = @stream_socket_client(
                "tcp://{$peer->ip}:{$peer->port}",
                $errno,
                $errstr,
                1
            );

            $connectable = $fp !== false;

            if ($fp) {
                fclose($fp);
            }

            $peer->update([
                'connectable' => $connectable,
                'connectable_checked_at' => now(),
            ]);
        }

        $this->info("Checked {$peers->count()} peers.");
    }
}