<?php

namespace App\Notifications;

use App\Models\UserAchievement;
use App\Services\AchievementService;
use Illuminate\Notifications\Notification;

class AchievementUnlocked extends Notification
{
    public function __construct(public UserAchievement $achievement, public array $definition) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $award = $this->achievement;

        return [
            'type' => 'achievement_unlocked',
            'title' => $this->definition['name'].' · Tier '.$award->tier,
            'message' => $award->tier === 1 && isset($this->definition['first_message'])
                ? $this->definition['first_message']
                : 'Congratulations! You reached '.app(AchievementService::class)->formatTarget($this->definition['unit'], $award->threshold).'. Keep up the good work!',
            'category' => $award->category, 'threshold' => $award->threshold,
            'bonus' => $award->bonus_awarded, 'invites' => $award->invites_awarded,
            'tokens' => $award->tokens_awarded, 'vip_months' => $award->vip_months_awarded,
            'tier' => $award->tier,
            'url' => route('profile.show', ['id' => $notifiable->id, 'name' => $notifiable->name]).'#achievement-'.$award->category.'-'.$award->threshold,
        ];
    }
}
