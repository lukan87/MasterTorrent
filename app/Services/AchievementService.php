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
        return config('achievements.enabled', true) && ($this->schemaReady ??= Schema::hasTable('user_achievements') && Schema::hasColumn('user_achievements', 'tokens_awarded') && Schema::hasColumn('user_achievements', 'vip_months_awarded') && Schema::hasColumn('users', 'login_streak_days'));
    }

    public function recordLoginDay(int $userId): void
    {
        if (! $this->available()) {
            return;
        }
        $changed = DB::transaction(function () use ($userId) {
            $user = User::whereKey($userId)->lockForUpdate()->first();
            $today = now()->toDateString();
            if (! $user || $user->enabled === 'no' || $user->isBanned() || $user->last_login_streak_date === $today) {
                return false;
            }
            $user->login_streak_days = $user->last_login_streak_date === now()->subDay()->toDateString()
                ? (int) $user->login_streak_days + 1 : 1;
            $user->last_login_streak_date = $today;
            $user->saveQuietly();

            return true;
        }, 3);
        if ($changed) {
            ProfileService::invalidate($userId);
            \App\Jobs\CheckUserAchievements::dispatch($userId);
        }
    }

    public function metrics(User $user): array
    {
        $history = History::where('user_id', $user->id)->selectRaw('SUM(actual_uploaded) AS upload_total, SUM(actual_downloaded) AS download_total, COUNT(DISTINCT CASE WHEN completed_at IS NOT NULL THEN torrent_id END) AS completions')->first();

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
            'torrent_seedtime' => (int) History::where('user_id', $user->id)
                ->whereHas('torrent', fn ($q) => $q->where('owner', '!=', $user->id))->max('seedtime'),
            'login_streak' => $user->last_login_streak_date && $user->last_login_streak_date >= now()->subDay()->toDateString()
                ? (int) $user->login_streak_days : 0,
        ];
    }

    public function reached(User $user, string $category, int $threshold, array $metrics): bool
    {
        if ($category === 'account_age') {
            return $user->created_at && now()->greaterThanOrEqualTo($user->created_at->copy()->addMonthsNoOverflow($threshold));
        }

        return ($metrics[$category] ?? 0) >= $threshold;
    }

    public function reward(float $balance, int $tier, ?string $category = null): float
    {
        $requested = $this->tierReward($tier, $category);
        $room = max(0, round(config('seedbonus.cap', 999999.99) - $balance, 2));

        return min($requested, $room);
    }

    public function tierReward(int $tier, ?string $category = null): float
    {
        return round(max(0, (float) config('achievements.categories.'.$category.'.points.'.$tier, config('achievements.tier_rewards.'.$tier, 0))), 2);
    }

    public function tierTokens(int $tier, ?string $category = null): int
    {
        return max(0, (int) config('achievements.categories.'.$category.'.tokens.'.$tier, config('achievements.tier_tokens.'.$tier, 0)));
    }

    public function award(int $userId): int
    {
        if (! config('achievements.awarding_enabled', false) || ! $this->available()) {
            return 0;
        }

        // Serializes competing workers; award, balances, invites, and notification commit together.
        $awarded = DB::transaction(function () use ($userId) {
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
                    $bonus = $this->reward((float) $user->seedbonus, $tier, $category);
                    $invites = $definition['invites'][$tier] ?? 0;
                    $tokens = $this->tierTokens($tier, $category);
                    $vipMonths = (int) ($definition['vip_months'][$tier] ?? 0);
                    $achievement = UserAchievement::create([
                        'user_id' => $userId, 'category' => $category, 'threshold' => $threshold,
                        'tier' => $tier, 'balance_before' => $user->seedbonus,
                        'bonus_awarded' => $bonus, 'invites_awarded' => $invites,
                        'tokens_awarded' => $tokens, 'vip_months_awarded' => $vipMonths, 'earned_at' => now(),
                    ]);
                    $user->seedbonus = round((float) $user->seedbonus + $bonus, 2);
                    $user->invites = (int) $user->invites + $invites;
                    $user->slots = (int) $user->slots + $tokens;
                    if ($vipMonths > 0) {
                        $base = $user->vip_until && $user->vip_until->isFuture() ? $user->vip_until->copy() : now();
                        $user->vip_until = $base->addMonthsNoOverflow($vipMonths);
                        if ((int) $user->user_class <= \App\Models\UserClass::VIP) {
                            $user->user_class = \App\Models\UserClass::VIP;
                        }
                    }
                    $user->saveQuietly();
                    $user->notify(new AchievementUnlocked($achievement, $definition));
                    $count++;
                }
            }

            return $count;
        }, 3);
        if ($awarded > 0) {
            ProfileService::invalidate($userId);
        }

        return $awarded;
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
                    'tier' => $index + 1, 'target' => $definition['labels'][$index] ?? ($key === 'seeding' ? number_format($threshold).' at Once' : $this->formatTarget($definition['unit'], $threshold)),
                    'award' => $award, 'qualified' => $reached,
                    'percent' => $award ? 100 : min(100, round($current / $total * 100, 1)),
                    'remaining' => $award ? 'Unlocked' : ($reached ? 'Reached · reward pending' : $remaining),
                    'reward_points' => $this->tierReward($index + 1, $key),
                    'reward_tokens' => $this->tierTokens($index + 1, $key),
                    'invites' => $definition['invites'][$index + 1] ?? 0,
                    'vip_months' => $definition['vip_months'][$index + 1] ?? 0,
                ];
            }
            $categories[] = $definition + ['key' => $key, 'tiers' => $tiers, 'earned_count' => collect($tiers)->whereNotNull('award')->count()];
        }

        return $categories;
    }
}
