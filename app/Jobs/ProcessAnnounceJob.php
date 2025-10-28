<?php

namespace App\Jobs;

use App\Services\AnnounceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAnnounceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $announceData;
    public int $userId;
    public string $ip;
    public string $agent;

    public function __construct(array $announceData, int $userId, string $ip, string $agent)
    {
        // Encode binary fields as base64 for queue safety
        $announceData['info_hash'] = base64_encode($announceData['info_hash']);
        $announceData['peer_id']   = base64_encode($announceData['peer_id']);

        $this->announceData = $announceData;
        $this->userId       = $userId;
        $this->ip           = $ip;
        $this->agent        = $agent;
    }

    public function handle(AnnounceService $announceService)
    {
        // Decode back to binary
        $this->announceData['info_hash'] = base64_decode($this->announceData['info_hash']);
        $this->announceData['peer_id']   = base64_decode($this->announceData['peer_id']);

        $announceService->handleAnnounceAsync(
            $this->announceData,
            $this->userId,
            $this->ip,
            $this->agent
        );
    }
}
