<?php

namespace App\Jobs;

use App\Models\Peer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPeerConnectable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 5;

    public function __construct(
        public int $peerId,
        public string $ip,
        public int $port
    ) {}

   public function handle(): void
{
    if (!filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return;
    }

    $fp = @stream_socket_client(
        "tcp://{$this->ip}:{$this->port}",
        $errno,
        $errstr,
        1,
        STREAM_CLIENT_CONNECT
    );

    $connectable = $fp !== false;

    if ($fp) {
        fclose($fp);
    }

    $peer = Peer::find($this->peerId);

    if ($peer && $peer->connectable !== $connectable) {
        $peer->update(['connectable' => $connectable]);
    }
}
}
