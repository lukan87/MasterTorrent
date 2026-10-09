<?php

namespace App\Providers;

use App\Http\Middleware\CheckUserBanned;
use App\Http\Middleware\CheckUserEnabled;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Ticket;
use App\Models\UserClass;
use App\Services\AnnouncementService;
use App\Services\Contracts\AnnouncementServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Monicahq\Cloudflare\Facades\CloudflareProxies;
use Monicahq\Cloudflare\LaravelCloudflare;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\Torrent\MetadataHttpCache::class);
        //

        $this->app->bind(
            AnnouncementServiceInterface::class,
            AnnouncementService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([\App\Models\User::class, \App\Models\Torrent::class, \App\Models\Comment::class, \App\Models\CommentReaction::class, \App\Models\ForumPost::class, \App\Models\ForumPostLike::class, \App\Models\TorrentReaction::class] as $activityModel) {
            $activityModel::observe(\App\Observers\AchievementActivityObserver::class);
        }

        foreach ([\App\Models\User::class, \App\Models\ForumCategory::class, \App\Models\ForumTopic::class, \App\Models\ForumPost::class, \App\Models\ForumPostLike::class] as $forumModel) {
            $forumModel::observe(\App\Observers\ForumCacheObserver::class);
        }

        foreach ([\App\Models\User::class, \App\Models\UserAchievement::class, \App\Models\Comment::class, \App\Models\CommentReaction::class, \App\Models\ForumPost::class, \App\Models\ForumPostLike::class, \App\Models\TorrentThank::class, \App\Models\TorrentReaction::class] as $profileModel) {
            $profileModel::observe(\App\Observers\ProfileCacheObserver::class);
        }

        Gate::define('manage-admin-system', fn ($user) => (int) $user->user_class === UserClass::WEB_DEVELOPER);

        // Add your custom middleware globally
        app('router')->pushMiddlewareToGroup('web', CheckUserEnabled::class);
        app('router')->pushMiddlewareToGroup('web', CheckUserBanned::class);

        View::composer('layouts.app', function ($view) {

            $user = Auth::user();

            /*
            |--------------------------------------------------------------------------
            | Default values (important for guests like login/register pages)
            |--------------------------------------------------------------------------
            */

            $seedingCount = 0;
            $leechingCount = 0;

            $conversations = collect();

            $unreadMessagesCount = 0;

            $globalPoll = null;
            $hasVotedPoll = false;

            $waitingStaffTickets = 0;
            $newTickets = 0;
            $unassignedTickets = 0;

            /*
            |--------------------------------------------------------------------------
            | Only run queries if user is logged in
            |--------------------------------------------------------------------------
            */

            if ($user) {

                /*
                |--------------------------------------------------------------------------
                | Seeder / Leecher counters
                |--------------------------------------------------------------------------
                */

                $seedingCount = $user->seedingCount();
                $leechingCount = $user->leechingCount();

                /*
                |--------------------------------------------------------------------------
                | Messages (cached to avoid 3 queries on every request)
                |--------------------------------------------------------------------------
                */

                $unreadMessagesCount = Cache::remember("user_unread_count_{$user->id}", 30, function () use ($user) {
                    return Message::where('receiver_id', $user->id)
                        ->where('is_read', false)
                        ->count();
                });

                /*
                |--------------------------------------------------------------------------
                | Conversations
                |--------------------------------------------------------------------------
                */

                $conversations = Cache::remember("user_conversations_{$user->id}", 30, function () use ($user) {
                    return Conversation::where(function ($q) use ($user) {
                        $q->where('user_one', $user->id)
                            ->orWhere('user_two', $user->id);
                    })
                        ->whereHas('messages')
                        ->with(['lastMessage', 'userOne', 'userTwo'])
                        ->orderByDesc('last_message_at')
                        ->take(5)
                        ->get();
                });

                /*
                |--------------------------------------------------------------------------
                | Active Poll (cached)
                |--------------------------------------------------------------------------
                */

                $globalPoll = Cache::remember('active_poll', 60, function () {
                    return Poll::withCount('votes')
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->latest()
                        ->first();
                });

                /*
                |--------------------------------------------------------------------------
                | Check if user voted in poll
                |--------------------------------------------------------------------------
                */

                if ($globalPoll) {
                    $hasVotedPoll = PollVote::where('poll_id', $globalPoll->id)
                        ->where('user_id', $user->id)
                        ->exists();
                }

                /*
                |--------------------------------------------------------------------------
                | Staff ticket counters
                |--------------------------------------------------------------------------
                */

                if ($user->user_class > 5) {

                    $newTickets = Ticket::where('status', 'Open')->count();

                    $waitingStaffTickets = Ticket::where('status', 'Waiting Staff')->count();

                    $unassignedTickets = Ticket::whereNull('claimed_by')->count();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Send data to layout
            |--------------------------------------------------------------------------
            */

            $view->with(compact(
                'seedingCount',
                'leechingCount',
                'conversations',
                'unreadMessagesCount',
                'globalPoll',
                'hasVotedPoll',
                'waitingStaffTickets',
                'newTickets',
                'unassignedTickets'
            ));
        });

        LaravelCloudflare::getProxiesUsing(fn () => CloudflareProxies::load());
    }
}
