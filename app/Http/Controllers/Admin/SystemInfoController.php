<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Cache::put('status', 'Cache is working::Using Redis Server', now()->addMinutes(10));
        $cacheStatus = Cache::get('status', 'Cache is not set');
        // Set backup time information
        $backupSchedule = 'Daily at 02:00 AM';

        // Get CPU load (1, 5, 15 minute averages)
        $cpuLoad = sys_getloadavg();  // Returns an array: [1 minute, 5 minute, 15 minute load]

        // Get RAM usage (Linux-based command using shell_exec)
        $ramUsage = $this->getRAMUsage();

        // Get System Uptime
        $uptime = $this->getUptime();

        return view('admin.system_info', compact('phpVersion', 'os', 'storage', 'diskTotal', 'cacheStatus', 'backupSchedule', 'cpuLoad', 'ramUsage', 'uptime'));
    }

     // Method to get system uptime
     private function getUptime()
     {
         // Execute the `uptime` command to get system uptime
         $uptimeCommandOutput = shell_exec('uptime -p');

         // The command outputs in a format like: "up 10 days, 3 hours, 12 minutes"
         // We'll return the raw output
         return $uptimeCommandOutput;
     }
    // Method to get RAM usage on Linux systems
    private function getRAMUsage()
    {
        // Execute the `free` command to get memory usage details
        $freeCommandOutput = shell_exec('free -m');
        $lines = explode("\n", $freeCommandOutput);

        // The second line of the `free` output contains memory info
        $memoryLine = isset($lines[1]) ? $lines[1] : '';
        $memoryData = preg_split('/\s+/', $memoryLine);

        // $memoryData contains values like: total, used, free, shared, buff/cache, available
        $totalMemory = isset($memoryData[1]) ? $memoryData[1] : 0;
        $usedMemory = isset($memoryData[2]) ? $memoryData[2] : 0;
        $freeMemory = isset($memoryData[3]) ? $memoryData[3] : 0;

        // You can return a formatted response or raw data as needed
        return [
            'total' => $totalMemory,
            'used' => $usedMemory,
            'free' => $freeMemory
        ];
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

    // Clear Views
    public function clearConfig()
    {
        Artisan::call('config:clear');
        return redirect()->route('admin.systemInfo.index')->with('success', 'Config cleared successfully.');
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

    // Backup Database


    public function backup(Request $request)
    {
        // Run the backup command
        Artisan::call('backup:run');

        // Return back to the view with a success message
        return back()->with('info', 'Site backup was created successfully !');
    }
}
