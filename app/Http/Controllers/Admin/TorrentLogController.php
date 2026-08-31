<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TorrentLog;
use App\Models\User;
use App\Models\UserClass;
use Carbon\Carbon;

class TorrentLogController extends Controller
{


public function index()
{
    $query = TorrentLog::with(['user', 'torrent']);

    /*
    |--------------------------------------------------------------------------
    | 🔒 Valid uploaders logic (your custom rule)
    |--------------------------------------------------------------------------
    */
    $query->whereHas('user', function ($q) {
        $q->where(function ($q) {
            $q->where('user_class', '>=', UserClass::UPLOADER)
              ->orWhere(function ($q) {
                  $q->where('user_class', '<', UserClass::UPLOADER)
                    ->where('uploadpos', 'yes');
              });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 📅 Date filter
    |--------------------------------------------------------------------------
    */
    if (request('range')) {
        $days = match(request('range')) {
            'today' => 1,
            '7' => 7,
            '30' => 30,
            default => null,
        };

        if ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 Other filters
    |--------------------------------------------------------------------------
    */
    if (request()->filled('action')) {
        $query->where('action', request('action'));
    }

    if (request()->filled('user_id')) {
        $query->where('user_id', request('user_id'));
    }

    if (request()->filled('user_class')) {
        $query->whereHas('user', function ($q) {
            $q->where('user_class', request('user_class'));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 Users dropdown (same logic)
    |--------------------------------------------------------------------------
    */
    $usersQuery = User::where(function ($q) {
        $q->where('user_class', '>=', UserClass::UPLOADER)
          ->orWhere(function ($q) {
              $q->where('user_class', '<', UserClass::UPLOADER)
                ->where('uploadpos', 'yes');
          });
    });

    if (request()->filled('user_class')) {
        $usersQuery->where('user_class', request('user_class'));
    }

    $users = $usersQuery->orderBy('name')->get();

    /*
    |--------------------------------------------------------------------------
    | 📊 COUNTS (IMPORTANT)
    |--------------------------------------------------------------------------
    */
    $baseCountQuery = TorrentLog::whereHas('user', function ($q) {
        $q->where(function ($q) {
            $q->where('user_class', '>=', UserClass::UPLOADER)
              ->orWhere(function ($q) {
                  $q->where('user_class', '<', UserClass::UPLOADER)
                    ->where('uploadpos', 'yes');
              });
        });
    });

    // Apply SAME filters (except action)
    if (request()->filled('user_id')) {
        $baseCountQuery->where('user_id', request('user_id'));
    }

    if (request()->filled('user_class')) {
        $baseCountQuery->whereHas('user', function ($q) {
            $q->where('user_class', request('user_class'));
        });
    }

    if (request('range')) {
        $days = match(request('range')) {
            'today' => 1,
            '7' => 7,
            '30' => 30,
            default => null,
        };

        if ($days) {
            $baseCountQuery->where('created_at', '>=', now()->subDays($days));
        }
    }

    $counts = [
        'all' => $baseCountQuery->count(),
        'uploaded' => (clone $baseCountQuery)->where('action', 'uploaded')->count(),
        'edited' => (clone $baseCountQuery)->where('action', 'edited')->count(),
        'deleted' => (clone $baseCountQuery)->where('action', 'deleted')->count(),
        'restored' => (clone $baseCountQuery)->where('action', 'restored')->count(),
        'force_deleted' => (clone $baseCountQuery)->where('action', 'force_deleted')->count(),
    ];

    /*
    |--------------------------------------------------------------------------
    | 📄 Logs
    |--------------------------------------------------------------------------
    */
    $logs = $query->latest()->paginate(50)->withQueryString();

    $classes = UserClass::getClasses();

    return view('admin.torrent_logs.index', compact('logs', 'classes', 'users', 'counts'));
}

    public function show($id)
    {
        $log = TorrentLog::with(['user', 'torrent'])->findOrFail($id);

        return view('admin.torrent_logs.show', compact('log'));
    }
}