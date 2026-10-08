<?php

namespace App\Jobs;

use App\Models\MassMessage;
use App\Models\MassMessageDelivery;
use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    protected $classes;

    protected $senderId;

    protected $massMessageId = null;

    public $tries = 3;

    public $timeout = 60;

    public function __construct($message, $classes, $senderId, $massMessageId = null)
    {
        $this->message = $message;
        $this->classes = $classes;
        $this->senderId = $senderId;
        $this->massMessageId = $massMessageId;
    }

    public function handle()
    {
        if ($this->massMessageId !== null) {
            $this->sendTrackedBroadcast();

            return;
        }

        // Compatibility for jobs queued before broadcast history was introduced.
        User::whereIn('user_class', $this->classes)
            ->whereNull('deleted_at')
            ->chunk(500, function ($users) {
                foreach ($users as $user) {
                    // Centralized find-or-create + message + cache invalidation
                    SystemMessageService::send(
                        $this->senderId,
                        $user->id,
                        'Mass Message',
                        $this->message
                    );
                }
            });
    }

    private function sendTrackedBroadcast(): void
    {
        $broadcast = DB::transaction(function () {
            $broadcast = MassMessage::whereKey($this->massMessageId)->lockForUpdate()->first();
            // An explicitly retried failed queue job resumes only the remaining recipients.
            if ($broadcast?->status === 'failed') {
                $broadcast->update(['status' => 'queued']);
            }

            return $broadcast;
        });
        if (! $broadcast || ! in_array($broadcast->status, ['queued', 'sending'], true)) {
            return;
        }
        if (! User::find($broadcast->sender_id)) {
            throw new \RuntimeException('The broadcast sender is unavailable.');
        }

        // Small continuations keep a large audience within the queue worker timeout.
        $deliveries = $broadcast->deliveries()->where('status', 'pending')->orderBy('id')->limit(200)->get();
        foreach ($deliveries as $delivery) {
            DB::transaction(function () use ($delivery) {
                $broadcast = MassMessage::whereKey($this->massMessageId)->lockForUpdate()->first();
                if (! $broadcast || ! in_array($broadcast->status, ['queued', 'sending'], true)) {
                    return;
                }
                $delivery = MassMessageDelivery::whereKey($delivery->id)->lockForUpdate()->first();
                if (! $delivery || $delivery->status !== 'pending') {
                    return;
                }
                if (! User::find($delivery->receiver_id)) {
                    $delivery->update(['status' => 'skipped']);

                    return;
                }
                $broadcast->update(['status' => 'sending']);
                $message = SystemMessageService::send(
                    $broadcast->sender_id, $delivery->receiver_id, $broadcast->subject, $broadcast->body
                );
                $delivery->update(['message_id' => $message->id, 'status' => 'sent', 'delivered_at' => $message->created_at]);
            }, 3);
        }

        $continue = DB::transaction(function () {
            $broadcast = MassMessage::whereKey($this->massMessageId)->lockForUpdate()->first();
            if (! $broadcast || ! in_array($broadcast->status, ['queued', 'sending'], true)) {
                return false;
            }
            if ($broadcast->deliveries()->where('status', 'pending')->exists()) {
                return true;
            }
            $broadcast->update(['status' => 'sent', 'completed_at' => now()]);

            return false;
        });
        if ($continue) {
            self::dispatch($this->message, $this->classes, $this->senderId, $this->massMessageId);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($this->massMessageId !== null) {
            MassMessage::whereKey($this->massMessageId)->whereIn('status', ['queued', 'sending'])
                ->update(['status' => 'failed']);
        }
    }
}
