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

        
        $topUploaders = User::orderBy('uploaded', 'desc')->take(10)->get();
        $topDownloaders = User::orderBy('downloaded', 'desc')->take(10)->get();

        // Cache online users
        $onlineUsersData = Cache::remember('online_users', $cacheDuration, function () {
            return $this->getOnlineUsers();
        });

        $onlineUsers = $onlineUsersData['users'];
        $onlineUserCount = $onlineUsersData['count'];

       
$now = now();


$topLastDay = Cache::remember('top_last_day', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subDay())
                  ->orderByDesc('seeders') 
                  ->limit(5)
                  ->get();
});


$topLastWeek = Cache::remember('top_last_week', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subWeek())
                  ->orderByDesc('seeders') 
                  ->limit(5)
                  ->get();
});


$topLastMonth = Cache::remember('top_last_month', $cacheDuration, function () use ($now) {
    return Torrent::where('created_at', '>=', $now->copy()->subMonth())
                  ->orderByDesc('seeders')  
                  ->limit(5)
                  ->get();
});


$topMovies = Cache::remember('top_movies', $cacheDuration, function () {
    return Torrent::where('tmdb_type', 'movie')
                  ->orderByDesc('seeders')
                  ->limit(5)
                  ->get();
});


$topSeries = Cache::remember('top_series', $cacheDuration, function () {
    return Torrent::where('tmdb_type', 'tv')
                  ->orderByDesc('seeders')
                  ->limit(5)
                  ->get();
});


        
        $latestNews = Cache::remember('latest_news', $cacheDuration, function () {
            return News::latest()->take(1)->get();
        });

       
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
                     ->where('seeder', 1)  
                     ->where('active', true)  
                     ->count();  
        });
        

        $uniqueLeechers = Cache::remember('unique_leechers', $cacheDuration, function () {
            return DB::table('history')
            ->where('seeder', 0)  
            ->where('active', true)  
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
            'uniqueLeechers',
            'topMovies',
            'topSeries',

        ));
    }

    public function getOnlineUsers()
    {
       
        $onlineUsers = User::where('updated_at', '>=', now()->subMinutes(3))
            ->orderBy('user_class', 'desc') 
            ->get();

        $onlineUserCount = $onlineUsers->count();

        return ['users' => $onlineUsers, 'count' => $onlineUserCount];
    }

    public function getRecommendedTorrents()
    {
        $cacheDuration = 3600; 
    
       
        $recommendedTorrents = Cache::remember('recommended_torrents', $cacheDuration, function () {
            return Torrent::with('genres') 
                          ->where('recommended', true)
                          ->where('category_id', '!=', 27)
                          ->latest('created_at')
                          ->limit(20)
                          ->get();
        });
    
        return $recommendedTorrents;
    }
    

}
