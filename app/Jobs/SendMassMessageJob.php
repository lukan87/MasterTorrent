<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        $activeUsers = User::where('updated_at', '>=', now()->subMonths(6))
            ->pluck('id')
            ->toArray();

        // Split into chunks
        $chunks = array_chunk($activeUsers, 500);

        foreach ($chunks as $chunk) {
            // Dispatch a sub-job for each chunk
            SendMassMessageChunkJob::dispatch($this->message, $chunk);
        }
    }
}
