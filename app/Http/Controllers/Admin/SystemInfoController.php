<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Http\Request;

class SystemInfoController extends Controller
{
    // Display system information
    public function index()
    {
        // Example system info
        $phpVersion = phpversion();
        $os = php_uname();
        $storage = disk_free_space("/");
        $diskTotal = disk_total_space("/");
        $cacheStatus = Cache::get('status', 'Cache is not set');

        return view('admin.system_info', compact('phpVersion', 'os', 'storage', 'diskTotal', 'cacheStatus'));
    }

    // Clear Cache
    public function clearCache()
    {
        Artisan::call('cache:clear');
        return redirect()->route('admin.systemInfo.index')->with('success', 'Cache cleared successfully.');
    }

    // Clear Views
    public function clearViews()
    {
        Artisan::call('view:clear');
        return redirect()->route('admin.systemInfo.index')->with('success', 'Views cleared successfully.');
    }

    // Clear Routes
    public function clearRoutes()
    {
        Artisan::call('route:clear');
        return redirect()->route('admin.systemInfo.index')->with('success', 'Routes cleared successfully.');
    }

    // Display the list of available routes
    public function showRoutes()
    {
        $routes = Route::getRoutes();
        return view('admin.routes', compact('routes'));
    }
}
