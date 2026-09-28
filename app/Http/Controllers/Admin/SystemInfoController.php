<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class SystemInfoController extends Controller
{
    // Display system information
    public function index()
    {
        // Example system info
        $phpVersion = phpversion();
        $os = php_uname();
        $storage = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $cacheStatus = Cache::remember('admin:cache-health', now()->addMinutes(10), function () {
            return 'Cache is working: '.config('cache.default');
        });
        // Set backup time information
        $backupSchedule = 'Daily (application timezone)';

        // Get CPU load (1, 5, 15 minute averages)
        $cpuLoad = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $cpuLoad = $cpuLoad ?: ['N/A', 'N/A', 'N/A'];  // Returns an array: [1 minute, 5 minute, 15 minute load]

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
        $uptimeCommandOutput = (function_exists('shell_exec') ? shell_exec('uptime -p') : null) ?? 'Unavailable';

        // The command outputs in a format like: "up 10 days, 3 hours, 12 minutes"
        // We'll return the raw output
        return $uptimeCommandOutput;
    }

    // Method to get RAM usage on Linux systems
    private function getRAMUsage()
    {
        // Execute the `free` command to get memory usage details
        $freeCommandOutput = (function_exists('shell_exec') ? shell_exec('free -m') : null) ?? '';
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
            'free' => $freeMemory,
        ];
    }

    // Clear Cache
    public function clearCache()
    {
        return $this->runCommand('cache:clear', 'Cache cleared successfully.');
    }

    // Clear Views
    public function clearViews()
    {
        return $this->runCommand('view:clear', 'Views cleared successfully.');
    }

    // Clear Views
    public function clearConfig()
    {
        return $this->runCommand('config:clear', 'Config cleared successfully.');
    }

    // Clear Routes
    public function clearRoutes()
    {
        return $this->runCommand('route:clear', 'Routes cleared successfully.');
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
        return $this->runCommand('backup:run', 'Site backup created successfully.');

        // Return back to the view with a success message

    }

    private function runCommand(string $command, string $success)
    {
        try {
            $exitCode = Artisan::call($command);
            if ($exitCode !== 0) {
                Log::error('Admin command failed', ['command' => $command, 'exit_code' => $exitCode]);

                return back()->with('error', 'The operation failed. Check the application logs for details.');
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'The operation failed. Check the application logs for details.');
        }

        return redirect()->route('admin.systemInfo.index')->with('success', $success);
    }
}
