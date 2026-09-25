<?php

namespace App\Services;

use App\Models\News;
use App\Models\User;
use App\Models\Torrent;
use App\Models\Poll;
use App\Models\HappyHour;
use App\Models\Shoutbox;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeService
{
    private int $cacheDuration;

    public function __construct()
    {
        $this->cacheDuration = config('cache.dashboard_duration', 10);
    }

    private function cacheQuery(string $key, \Closure $callback)
    {
        return Cache::remember($key, $this->cacheDuration, $callback);
    }


    public function getLatestUsers(int $limit = 1)
{
    return $this->cacheQuery('latest_users', function () use ($limit) {
        return User::latest('created_at')
            ->take($limit)
            ->get()
            ->map(function ($user) {
                $user->registered_ago = $user->created_at->diffForHumans();
                return $user;
            });
    });
}

    public function getActiveUsers24h()
{
    return $this->cacheQuery('active_users_24h', function () {
        $users = User::where('last_activity', '>=', now()->subDay())
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($user) {
                $user->last_active_local = $user->last_activity->timezone($user->timezone ?? 'Europe/London');
                return $user;
            });

        return [
            'users' => $users,
            'count' => $users->count(),
        ];
    });
}

    private function getShoutboxMessages(int $limit = 30)
    {
        return Shoutbox::with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('sticky', 'desc')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getOnlineUsers()
    {
        return $this->cacheQuery('online_users', function () {
            $users = User::where('updated_at', '>=', now()->subMinutes(1))
                ->orderBy('user_class', 'desc')
                ->get()
                ->map(function ($user) {
                    $user->last_active_local = $user->updated_at->timezone($user->timezone ?? 'Europe/London');
                    return $user;
                });

            return [
                'users' => $users,
                'count' => $users->count(),
            ];
        });
    }

    public function getCurrentHappyHour(): ?HappyHour
    {
        return HappyHour::where('active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->latest('start_at')
            ->first();
    }

    public function getTrendingTorrents(int $limit = 12)
    {
        $days = rand(2, 5);

        return Torrent::query()
            ->whereNotIn('category_id', [27, 34])
            ->where('seeders', '>', 0)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('seeders')
            ->orderByDesc('leechers')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    private function getTopDownloaders24h(int $limit = 7)
    {
        return DB::table('history')
            ->join('users', 'users.id', '=', 'history.user_id')
            ->select('users.id', 'users.name', DB::raw('SUM(history.actual_downloaded) as downloaded_24h'))
            ->where('history.created_at', '>=', now()->subDay())
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('downloaded_24h')
            ->limit($limit)
            ->get();
    }

    private function getTopUploaders24h(int $limit = 7)
    {
        return DB::table('history')
            ->join('users', 'users.id', '=', 'history.user_id')
            ->select('users.id', 'users.name', DB::raw('SUM(history.uploaded) as uploaded_24h'))
            ->where('history.created_at', '>=', now()->subDay())
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('uploaded_24h')
            ->limit($limit)
            ->get();
    }

    private function getTopSeeders24h(int $limit = 7)
    {
        return DB::table('peers')
            ->join('users', 'users.id', '=', 'peers.user_id')
            ->select('users.id', 'users.name', DB::raw('COUNT(peers.id) as seed_count'))
            ->where('peers.seeder', 1)
            ->where('peers.active', true)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('seed_count')
            ->limit($limit)
            ->get();
    }

/**
     * Returns up to $limit random titles from the movies or series table,
     * mapped to the poster/URL format the view needs.
     */
    private function getRandomOnlineTitles(string $type, int $limit = 10): \Illuminate\Support\Collection
    {
        $model = $type === 'series' ? new Series : new Movie;

        return $model->inRandomOrder()
            ->take($limit)
            ->get()
            ->map(function ($item) use ($type) {
                $routeName = $type === 'series' ? 'series.show' : 'movies.show';

                return [
                    'title'  => $item->name,
                    'poster' => $item->poster_url,
                    'year'   => $item->year,
                    'rating' => number_format($item->vote_average ?? 0, 1),
                    'url'    => route($routeName, [$item->id, $item->slug]),
                ];
            });
    }

    public function getDashboardData(): array
    {
        $onlineUsersData = $this->getOnlineUsers();
        $activeUsers24hData = $this->getActiveUsers24h();
        $userId = auth()->id();

        $userUploadRank = null;
        $userDownloadRank = null;
        $userSeederRank = null;

        $uploadMovement = null;
        $downloadMovement = null;
        $seederMovement = null;

        if ($userId) {

// TOTAL USERS TODAY (download)
$totalDownloadUsers = DB::table('history')
    ->where('created_at', '>=', now()->subDay())
    ->distinct('user_id')
    ->count('user_id');

// STREAK (download)
$downloadStreak = 0;
for ($i = 0; $i < 7; $i++) {
    $has = DB::table('history')
        ->where('user_id', $userId)
        ->whereBetween('created_at', [
            now()->subDays($i + 1),
            now()->subDays($i)
        ])
        ->sum('actual_downloaded');

    if ($has > 0) $downloadStreak++;
    else break;
}

// PERCENTILE
$downloadPercentile = null;
if ($userDownloadRank) {
    $downloadPercentile = round(($userDownloadRank['rank'] / $totalDownloadUsers) * 100);
}

// MOVEMENT CLASS
$downloadMovementClass = null;
if ($downloadMovement !== null) {
    if ($downloadMovement >= 10) $downloadMovementClass = 'move-big-up';
    elseif ($downloadMovement >= 3) $downloadMovementClass = 'move-up';
    elseif ($downloadMovement > 0) $downloadMovementClass = 'move-small-up';
    elseif ($downloadMovement <= -10) $downloadMovementClass = 'move-big-down';
    elseif ($downloadMovement < 0) $downloadMovementClass = 'move-down';
}


// TOTAL SEEDERS
$totalSeedUsers = DB::table('peers')
    ->where('seeder', 1)
    ->where('active', true)
    ->distinct('user_id')
    ->count('user_id');

// STREAK (seeders = days active)
$seederStreak = 0;
for ($i = 0; $i < 7; $i++) {
    $has = DB::table('peers')
        ->where('user_id', $userId)
        ->where('seeder', 1)
        ->where('active', true)
        ->exists();

    if ($has) $seederStreak++;
    else break;
}

// PERCENTILE
$seederPercentile = null;
if ($userSeederRank) {
    $seederPercentile = round(($userSeederRank['rank'] / $totalSeedUsers) * 100);
}

// MOVEMENT CLASS (optional - no real history so reuse null)
$seederMovementClass = null;

        // TOTAL USERS TODAY (for percentile)
$totalUsersToday = DB::table('history')
    ->where('created_at', '>=', now()->subDay())
    ->distinct('user_id')
    ->count('user_id');

// STREAK (last 7 days example)
$streak = 0;
for ($i = 0; $i < 7; $i++) {
    $hasUpload = DB::table('history')
        ->where('user_id', $userId)
        ->whereBetween('created_at', [
            now()->subDays($i + 1),
            now()->subDays($i)
        ])
        ->sum('uploaded');

    if ($hasUpload > 0) {
        $streak++;
    } else {
        break;
    }
}

// PERCENTILE
$percentile = null;
if ($userUploadRank) {
    $percentile = round(($userUploadRank['rank'] / $totalUsersToday) * 100);
}

// MOVEMENT INTENSITY CLASS
$movementClass = null;
if ($uploadMovement !== null) {
    if ($uploadMovement >= 10) $movementClass = 'move-big-up';
    elseif ($uploadMovement >= 3) $movementClass = 'move-up';
    elseif ($uploadMovement > 0) $movementClass = 'move-small-up';
    elseif ($uploadMovement <= -10) $movementClass = 'move-big-down';
    elseif ($uploadMovement < 0) $movementClass = 'move-down';
}

// attach to return

            // ---------------- UPLOAD ----------------
            $uploadTotal = DB::table('history')
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDay())
                ->sum('uploaded');

            if ($uploadTotal > 0) {

                $currentRank = DB::table('history')
                    ->select('user_id', DB::raw('SUM(uploaded) as total'))
                    ->where('created_at', '>=', now()->subDay())
                    ->groupBy('user_id')
                    ->having('total', '>', $uploadTotal)
                    ->count() + 1;

                $userUploadRank = [
                    'rank' => $currentRank,
                    'value' => $uploadTotal
                ];

                // Yesterday
                $yesterdayTotal = DB::table('history')
                    ->where('user_id', $userId)
                    ->whereBetween('created_at', [now()->subDays(2), now()->subDay()])
                    ->sum('uploaded');

                if ($yesterdayTotal > 0) {
                    $yesterdayRank = DB::table('history')
                        ->select('user_id', DB::raw('SUM(uploaded) as total'))
                        ->whereBetween('created_at', [now()->subDays(2), now()->subDay()])
                        ->groupBy('user_id')
                        ->having('total', '>', $yesterdayTotal)
                        ->count() + 1;

                    $uploadMovement = $yesterdayRank - $currentRank;
                }


                
            }

            // ---------------- DOWNLOAD ----------------
            $downloadTotal = DB::table('history')
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->subDay())
                ->sum('actual_downloaded');

            if ($downloadTotal > 0) {

                $currentRank = DB::table('history')
                    ->select('user_id', DB::raw('SUM(actual_downloaded) as total'))
                    ->where('created_at', '>=', now()->subDay())
                    ->groupBy('user_id')
                    ->having('total', '>', $downloadTotal)
                    ->count() + 1;

                $userDownloadRank = [
                    'rank' => $currentRank,
                    'value' => $downloadTotal
                ];

                $yesterdayTotal = DB::table('history')
                    ->where('user_id', $userId)
                    ->whereBetween('created_at', [now()->subDays(2), now()->subDay()])
                    ->sum('actual_downloaded');

                if ($yesterdayTotal > 0) {
                    $yesterdayRank = DB::table('history')
                        ->select('user_id', DB::raw('SUM(actual_downloaded) as total'))
                        ->whereBetween('created_at', [now()->subDays(2), now()->subDay()])
                        ->groupBy('user_id')
                        ->having('total', '>', $yesterdayTotal)
                        ->count() + 1;

                    $downloadMovement = $yesterdayRank - $currentRank;
                }
            }

            // ---------------- SEEDERS ----------------
            $seedCount = DB::table('peers')
                ->where('user_id', $userId)
                ->where('seeder', 1)
                ->where('active', true)
                ->where('left', 0)
                ->count();

            if ($seedCount > 0) {

                $currentRank = DB::table('peers')
                    ->select('user_id', DB::raw('COUNT(*) as total'))
                    ->where('seeder', 1)
                    ->where('active', true)
                    ->groupBy('user_id')
                    ->having('total', '>', $seedCount)
                    ->count() + 1;

                $userSeederRank = [
                    'rank' => $currentRank,
                    'value' => $seedCount
                ];
            }
        }

        return [
            'onlineUsers' => $onlineUsersData['users'],
            'onlineUserCount' => $onlineUsersData['count'],

            'latestUsers' => $this->getLatestUsers(),
            

            'topUploaders' => User::orderBy('uploaded', 'desc')->take(10)->get(),
            'topDownloaders' => User::orderBy('downloaded', 'desc')->take(10)->get(),

            'latestNews' => $this->cacheQuery('latest_news', fn() => News::latest()->take(1)->get()),
            'polls' => $this->cacheQuery('polls', fn() => Poll::with('options.votes')->latest()->take(1)->get()),

            'torrentCount' => $this->cacheQuery('torrent_count', fn() => Torrent::count()),
            'torrentActive' => $this->cacheQuery('torrent_active_count', fn() => Torrent::where('seeders', '>', 0)->count()),
            'userCount' => $this->cacheQuery('user_count', fn() => User::count()),

            'activeUsers24h' => $activeUsers24hData['users'],
            'activeUsers24hCount' => $activeUsers24hData['count'],

            'uniqueSeeders' => $this->cacheQuery('unique_seeders', fn() => DB::table('peers')->where('seeder', 1)->where('active', true)->count()),
           'uniqueLeechers' => DB::table('peers')
    ->where('seeder', false)
    ->where('active', true)
    ->where('client_updated_at', '>', now()->subMinutes(30))
    ->select(DB::raw('COUNT(DISTINCT CONCAT(user_id, "-", torrent_id)) as total'))
    ->value('total'),

            'currentHappyHour' => $this->getCurrentHappyHour(),
            'trendingTorrents' => $this->cacheQuery('trending_torrents', fn() => $this->getTrendingTorrents(12)),
            'randomOnlineMovies' => $this->getRandomOnlineTitles('movie'),
            'randomOnlineSeries' => $this->getRandomOnlineTitles('series'),
            'messages' => $this->cacheQuery('home_shoutbox_messages', fn() => $this->getShoutboxMessages(30)),

            'topUploaders24h' => $this->cacheQuery('top_uploaders_24h', fn() => $this->getTopUploaders24h()),
            'topDownloaders24h' => $this->cacheQuery('top_downloaders_24h', fn() => $this->getTopDownloaders24h()),
            'topSeeders24h' => $this->cacheQuery('top_seeders_24h', fn() => $this->getTopSeeders24h()),

            'userUploadRank24h' => $userUploadRank,
            'userDownloadRank24h' => $userDownloadRank,
            'userSeederRank' => $userSeederRank,

            'uploadMovement' => $uploadMovement,
            'downloadMovement' => $downloadMovement,
            'seederMovement' => $seederMovement,

            'uploadMovementClass' => $movementClass,
            'uploadStreak' => $streak,
            'uploadPercentile' => $percentile,

            'downloadMovementClass' => $downloadMovementClass,
            'downloadStreak' => $downloadStreak,
            'downloadPercentile' => $downloadPercentile,
            
            'seederStreak' => $seederStreak,
            'seederPercentile' => $seederPercentile,
        ];
    }
}