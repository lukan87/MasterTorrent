<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Torrent;
use App\Models\User;

class AdminController extends Controller
{
    // Show the admin dashboard
    public function index()
    {
        $totalUsers = User::count();
        $totalMovies = Movie::count();
        $totalSeries = Series::count();
        $totalTorrents = Torrent::count();

        return view('admin.index', compact('totalUsers', 'totalMovies', 'totalSeries', 'totalTorrents'));
    }
}
