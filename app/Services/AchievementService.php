<?php

namespace App\Services;

use App\Helpers\FormatHelper;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\ForumPost;
use App\Models\ForumPostLike;
use App\Models\History;
use App\Models\Peer;
use App\Models\Torrent;
use App\Models\TorrentReaction;
use App\Models\User;
use App\Models\UserAchievement;
use App\Notifications\AchievementUnlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AchievementService
{
    private ?bool $schemaReady = null;

    public function available(): bool
    {
        return config('achievements.enabled', true) && ($this->schemaReady ??= Schema::hasTable('user_achievements'));
    }

    public function metrics(User $user): array
    {
        $history = History::where('user_id', $user->id)->selectRaw('SUM(actual_uploaded) AS upload_total, SUM(actual_downloaded) AS download_total, SUM(seedtime) AS seed_total, COUNT(DISTINCT CASE WHEN completed_at IS NOT NULL THEN torrent_id END) AS completions')->first();

        return [
            'invited_users' => User::withTrashed()->where('invited_by', $user->id)->where('id', '!=', $user->id)->count(),
            'torrents' => Torrent::where('owner', $user->id)->count(),
            'comments' => Comment::where('user_id', $user->id)->count(),
            'comment_reactions' => CommentReaction::where('user_id', $user->id)->whereHas('comment', fn ($q) => $q->where('user_id', '!=', $user->id))->count(),
            'forum_posts' => ForumPost::where('user_id', $user->id)->count(),
            'forum_reactions' => ForumPostLike::where('user_id', $user->id)->whereHas('post', fn ($q) => $q->where('user_id', '!=', $user->id))->count(),
            'torrent_reactions' => TorrentReaction::where('user_id', $user->id)->whereHas('torrent', fn ($q) => $q->where('owner', '!=', $user->id))->count(),
            'seeding' => Peer::where('user_id', $user->id)->where('active', true)->where('seeder', true)->distinct()->count('torrent_id'),
            'snatches' => (int) $history->completions,
            'uploaded' => (int) $history->upload_total,
            'downloaded' => (int) $history->download_total,
            'seedtime' => (int) $history->seed_total,
        ];
    }

    public function reached(User $user, string $category, int $threshold, array $metrics): bool
    {
        if ($category === 'account_age') {
            return $user->created_at && now()->greaterThanOrEqualTo($user->created_at->copy()->addMonthsNoOverflow($threshold));
        }

        return ($metrics[$category] ?? 0) >= $threshold;
    }

    public function reward(float $balance, int $tier): float
    {
        $requested = round(max(0, $balance) * (config('achievements.reward_percent', 25) / 100) * $tier, 2);
        $room = max(0, round(config('seedbonus.cap', 999999.99) - $balance, 2));

        return min($requested, $room);
    }

    public function award(int $userId): int
    {
        if (! config('achievements.awarding_enabled', false) || ! $this->available()) {
            return 0;
        }

        // Serializes competing workers; award, balance, invites, and notification commit together.
        return DB::transaction(function () use ($userId) {
            $user = User::whereKey($userId)->lockForUpdate()->first();
            if (! $user || $user->enabled === 'no' || $user->banned_until !== null) {
                return 0;
            }

            $metrics = $this->metrics($user);
            $earned = UserAchievement::where('user_id', $userId)->get()->keyBy(fn ($a) => $a->category.':'.$a->threshold);
            $count = 0;
            foreach (config('achievements.categories', []) as $category => $definition) {
                foreach ($definition['thresholds'] as $index => $threshold) {
                    if ($earned->has($category.':'.$threshold) || ! $this->reached($user, $category, $threshold, $metrics)) {
                        continue;
                    }
                    $tier = $index + 1;
                    $bonus = $this->reward((float) $user->seedbonus, $tier);
                    $invites = $definition['invites'][$tier] ?? 0;
                    $achievement = UserAchievement::create([
                        'user_id' => $userId, 'category' => $category, 'threshold' => $threshold,
                        'tier' => $tier, 'balance_before' => $user->seedbonus,
                        'bonus_awarded' => $bonus, 'invites_awarded' => $invites, 'earned_at' => now(),
                    ]);
                    $user->seedbonus = round((float) $user->seedbonus + $bonus, 2);
                    $user->invites = (int) $user->invites + $invites;
                    $user->saveQuietly();
                    $user->notify(new AchievementUnlocked($achievement, $definition));
                    $count++;
                }
            }

            return $count;
        }, 3);
    }

    public function formatTarget(string $unit, int $value): string
    {
        return match ($unit) {
            'bytes' => FormatHelper::formatSize($value),
            'seconds' => number_format($value / 86400, 1).' seed days',
            'months' => $value >= 12 && $value % 12 === 0 ? ($value / 12).' years' : $value.' months',
            default => number_format($value).' '.$unit,
        };
    }

    public function progress(User $user): array
    {
        if (! $this->available()) {
            return [];
        }
        $metrics = $this->metrics($user);
        $earned = UserAchievement::where('user_id', $user->id)->get()->keyBy(fn ($a) => $a->category.':'.$a->threshold);
        $categories = [];
        foreach (config('achievements.categories', []) as $key => $definition) {
            $tiers = [];
            foreach ($definition['thresholds'] as $index => $threshold) {
                $award = $earned->get($key.':'.$threshold);
                $reached = $this->reached($user, $key, $threshold, $metrics);
                if ($key === 'account_age') {
                    $due = $user->created_at?->copy()->addMonthsNoOverflow($threshold);
                    $total = $due ? max(1, $user->created_at->diffInSeconds($due)) : 1;
                    $current = $user->created_at ? max(0, $user->created_at->diffInSeconds(now())) : 0;
                    $remaining = $due ? $due->diffForHumans(now(), true).' remaining' : 'Join date unavailable';
                } else {
                    $total = $threshold;
                    $current = $metrics[$key] ?? 0;
                    $remaining = $this->formatTarget($definition['unit'], max(0, $threshold - $current)).' to go';
                }
                $tiers[] = [
                    'tier' => $index + 1, 'target' => $this->formatTarget($definition['unit'], $threshold),
                    'award' => $award, 'qualified' => $reached,
                    'percent' => $award ? 100 : min(100, round($current / $total * 100, 1)),
                    'remaining' => $award ? 'Unlocked' : ($reached ? 'Reached · reward pending' : $remaining),
                    'reward_percent' => config('achievements.reward_percent', 25) * ($index + 1),
                    'invites' => $definition['invites'][$index + 1] ?? 0,
                ];
            }
            $categories[] = $definition + ['key' => $key, 'tiers' => $tiers, 'earned_count' => collect($tiers)->whereNotNull('award')->count()];
        }

        return $categories;
    }
}
