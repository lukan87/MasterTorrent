<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Monicahq\Cloudflare\LaravelCloudflare;
use Monicahq\Cloudflare\Facades\CloudflareProxies;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Ticket;

use App\Services\AnnouncementService;
use App\Services\Contracts\AnnouncementServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
        // Add your custom middleware globally
        app('router')->pushMiddlewareToGroup('web', \App\Http\Middleware\CheckUserEnabled::class);
        app('router')->pushMiddlewareToGroup('web', \App\Http\Middleware\CheckUserBanned::class);

        View::composer('layouts.app', function ($view) {

            $user = Auth::user();

            /*
            |--------------------------------------------------------------------------
            | Default values (important for guests like login/register pages)
            |--------------------------------------------------------------------------
            */

            $seedingCount = 0;
            $leechingCount = 0;

            $messages = collect();
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
                | Messages
                |--------------------------------------------------------------------------
                */

                $messages = Message::where('receiver_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get();

                $unreadMessagesCount = Message::where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                /*
                |--------------------------------------------------------------------------
                | Conversations
                |--------------------------------------------------------------------------
                */

                $conversations = Conversation::where(function ($q) use ($user) {
                        $q->where('user_one', $user->id)
                          ->orWhere('user_two', $user->id);
                    })
                    ->with(['lastMessage','userOne','userTwo'])
                    ->orderByDesc('last_message_at')
                    ->take(5)
                    ->get();

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

                    $newTickets = Ticket::where('status','Open')->count();

                    $waitingStaffTickets = Ticket::where('status','Waiting Staff')->count();

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
                'messages',
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