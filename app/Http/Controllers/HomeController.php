<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Torrent;
use App\Models\Poll;
use App\Models\Topic;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cacheDuration = 3600; // Cache duration in seconds (1 hour)

        // Cache online users
        $onlineUsers = Cache::remember('online_users', $cacheDuration, function () {
            return $this->getOnlineUsers();
        });

        // Define time intervals for filtering
        $now = now();

        // Cache torrents from the last day
        $topLastDay = Cache::remember('top_last_day', $cacheDuration, function () use ($now) {
            return Torrent::where('created_at', '>=', $now->copy()->subDay())
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get();
        });

        // Cache torrents from the last week
        $topLastWeek = Cache::remember('top_last_week', $cacheDuration, function () use ($now) {
            return Torrent::where('created_at', '>=', $now->copy()->subWeek())
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get();
        });

        // Cache torrents from the last month
        $topLastMonth = Cache::remember('top_last_month', $cacheDuration, function () use ($now) {
            return Torrent::where('created_at', '>=', $now->copy()->subMonth())
                          ->orderBy('created_at', 'desc')
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

        $userCount = Cache::remember('user_count', $cacheDuration, function () {
            return User::count();
        });

        $forumTopicCount = Cache::remember('forum_topic_count', $cacheDuration, function () {
            return Topic::count();
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
            'forumTopicCount'
        ));
    }

    public function getOnlineUsers()
    {
        // Assuming you store online users in the database with a 'last_activity' column
        return User::where('last_activity', '>=', now()->subMinutes(5))->get();
    }
}
