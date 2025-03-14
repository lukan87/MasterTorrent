<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\UserClass;
use App\Models\Torrent;
use App\Models\Poll;
use App\Models\Topic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cacheDuration = 3600; // Cache duration in seconds (1 hour)

        $recommendedTorrents = $this->getRecommendedTorrents();

        // Get top uploaders and top downloaders
        $topUploaders = User::orderBy('uploaded', 'desc')->take(10)->get();
        $topDownloaders = User::orderBy('downloaded', 'desc')->take(10)->get();

        // Cache online users
        $onlineUsersData = Cache::remember('online_users', $cacheDuration, function () {
            return $this->getOnlineUsers();
        });

        $onlineUsers = $onlineUsersData['users'];
        $onlineUserCount = $onlineUsersData['count'];

       // Define time intervals for filtering
$now = now();

// Cache torrents from the last day
$topLastDay = Cache::remember('top_last_day', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subDay())
                  ->orderByDesc('seeders')  // Order by seeders (descending)
                  ->limit(5)
                  ->get();
});

// Cache torrents from the last week
$topLastWeek = Cache::remember('top_last_week', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subWeek())
                  ->orderByDesc('seeders')  // Order by seeders (descending)
                  ->limit(5)
                  ->get();
});

// Cache torrents from the last month
$topLastMonth = Cache::remember('top_last_month', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subMonth())
                  ->orderByDesc('seeders')  // Order by seeders (descending)
                  ->limit(5)
                  ->get();
});


        // Cache latest news
        $latestNews = Cache::remember('latest_news', $cacheDuration, function () {
            return News::latest()->take(1)->get();
        });

        // Cache polls with options and votes
        $polls = Cache::remember('polls', $cacheDuration, function () {
            return Poll::with('options.votes')
                       ->orderBy('created_at', 'desc')
                       ->limit(1)
                       ->get();
        });

        // Cache counts
        $torrentCount = Cache::remember('torrent_count', $cacheDuration, function () {
            return Torrent::count();
        });

        $torrentActive = Cache::remember('torrent_active_count', $cacheDuration, function () {
            return Torrent::where('seeders', '>', 0)->count();
        });

        $userCount = Cache::remember('user_count', $cacheDuration, function () {
            return User::count();
        });

        $forumTopicCount = Cache::remember('forum_topic_count', $cacheDuration, function () {
            return Topic::count();
        });

        $uniqueSeeders = Cache::remember('unique_seeders', $cacheDuration, function () {
            return DB::table('history')
                     ->where('seeder', 1)  // Filters for peers who are seeding (seeder = 1)
                     ->where('active', true)  // Filters for active peers
                     ->count();  // Counts all matching records, not distinct users
        });
        

        $uniqueLeechers = Cache::remember('unique_leechers', $cacheDuration, function () {
            return DB::table('history')
            ->where('seeder', 0)  // Checks for users who are seeding (seeder = 1)
            ->where('active', true)  // Checks for active peers
            ->count();
});

        return view('home', compact(
            'onlineUsers',
            'latestNews',
            'topLastDay',
            'topLastWeek',
            'topLastMonth',
            'polls',
            'torrentCount',
            'userCount',
            'forumTopicCount',
           'recommendedTorrents',
            'topUploaders',
            'topDownloaders',
            'torrentActive',
            'onlineUserCount',
            'uniqueSeeders',
            'uniqueLeechers'

        ));
    }

    public function getOnlineUsers()
    {
        // Query online users and order by user class (highest to lowest)
        $onlineUsers = User::where('updated_at', '>=', now()->subMinutes(3))
            ->orderBy('user_class', 'desc') // 'user_class' is the integer column representing class
            ->get();

        $onlineUserCount = $onlineUsers->count();

        return ['users' => $onlineUsers, 'count' => $onlineUserCount];
    }

    public function getRecommendedTorrents()
    {
        $cacheDuration = 3600; // Cache duration in seconds (1 hour)
    
        // Cache the latest 10 recommended torrents
        $recommendedTorrents = Cache::remember('recommended_torrents', $cacheDuration, function () {
            return Torrent::with('genres') // Eager load genres if needed for display
                          ->where('recommended', true)
                          ->where('category_id', '!=', 27)
                          ->latest('created_at')
                          ->limit(20)
                          ->get();
        });
    
        return $recommendedTorrents;
    }
    

}
