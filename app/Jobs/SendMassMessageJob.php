<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SystemMessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $classes;
    protected $senderId;

    public function __construct($message, $classes, $senderId)
    {
        $this->message = $message;
        $this->classes = $classes;
        $this->senderId = $senderId;
    }

    public function handle()
    {
        $systemId = 2;

        User::whereIn('user_class', $this->classes)
            ->whereNull('deleted_at')
            ->chunk(500, function ($users) use ($systemId) {
                foreach ($users as $user) {
                    // Centralized find-or-create + message + cache invalidation
                    SystemMessageService::send(
                        $systemId,
                        $user->id,
                        'Mass Message',
                        $this->message
                    );
                }
            });
    }
}