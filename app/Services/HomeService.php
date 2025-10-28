<?php

namespace App\Services;

use App\Models\News;
use App\Models\User;
use App\Models\Torrent;
use App\Models\Poll;
use App\Models\Topic;
use App\Models\HappyHour;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeService
{
    private int $cacheDuration;

    public function __construct()
    {
        $this->cacheDuration = config('cache.dashboard_duration', 10); // default 10 minutes
    }

    private function cacheQuery(string $key, \Closure $callback)
    {
        return Cache::remember($key, $this->cacheDuration, $callback);
    }

   public function getOnlineUsers()
{
    return $this->cacheQuery('online_users', function () {
        $users = User::where('updated_at', '>=', now()->subMinutes(1))
                     ->orderBy('user_class', 'desc')
                     ->get()
                     ->map(function ($user) {
                         // Add a timezone-adjusted attribute for display
                         $user->last_active_local = $user->updated_at->timezone($user->timezone ?? 'Europe/London');
                         return $user;
                     });

        return [
            'users' => $users,
            'count' => $users->count(),
        ];
    });
}


    public function getTopTorrents($period = null, $type = null, $limit = 6)
    {
        $query = Torrent::query()->whereNotIn('category_id', [27, 34]);


        if ($period) {
            $query->where('created_at', '>=', now()->sub($period));
        }

        if ($type) {
            $query->where('tmdb_type', $type);
        }

        return $query->orderByDesc('seeders')->limit($limit)->get();
    }

    public function getCurrentHappyHour(): ?HappyHour
{
    return HappyHour::where('active', true)
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now())
                    ->latest('start_at')
                    ->first();
}

    public function getDashboardData(): array
    {
        $onlineUsersData = $this->getOnlineUsers();

        return [
            'onlineUsers'       => $onlineUsersData['users'],
            'onlineUserCount'   => $onlineUsersData['count'],

            'topLastDay'        => $this->cacheQuery('top_last_day', fn() => $this->getTopTorrents('1 day')),
            'topLastWeek'       => $this->cacheQuery('top_last_week', fn() => $this->getTopTorrents('1 week')),
            'topLastMonth'      => $this->cacheQuery('top_last_month', fn() => $this->getTopTorrents('1 month')),
            'topMovies'         => $this->cacheQuery('top_movies', fn() => $this->getTopTorrents(null, 'movie')),
            'topSeries'         => $this->cacheQuery('top_series', fn() => $this->getTopTorrents(null, 'tv')),

            'topUploaders'      => User::orderBy('uploaded', 'desc')->take(10)->get(),
            'topDownloaders'    => User::orderBy('downloaded', 'desc')->take(10)->get(),

            'latestNews'        => $this->cacheQuery('latest_news', fn() => News::latest()->take(1)->get()),
            'polls'             => $this->cacheQuery('polls', fn() => Poll::with('options.votes')->latest()->take(1)->get()),

            'torrentCount'      => $this->cacheQuery('torrent_count', fn() => Torrent::count()),
            'torrentActive'      => $this->cacheQuery('torrent_active_count', fn() => Torrent::where('seeders', '>', 0)->count()),
            'userCount'         => $this->cacheQuery('user_count', fn() => User::count()),
            'forumTopicCount'   => $this->cacheQuery('forum_topic_count', fn() => Topic::count()),

            'uniqueSeeders'     => $this->cacheQuery('unique_seeders', fn() => DB::table('peers')->where('seeder', 1)->where('active', true)->count()),
            'uniqueLeechers'    => $this->cacheQuery('unique_leechers', fn() => DB::table('peers')->where('seeder', 0)->where('active', true)->count()),

            'currentHappyHour'  => $this->getCurrentHappyHour(),
        ];
    }
}
