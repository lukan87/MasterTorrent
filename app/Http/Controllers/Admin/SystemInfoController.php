<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
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
        $cacheStatus = Cache::get('status', 'Cache is not set');
 // Set backup time information
 $backupSchedule = 'Daily at 02:00 AM';


        return view('admin.system_info', compact('phpVersion', 'os', 'storage', 'diskTotal', 'cacheStatus', 'backupSchedule'));
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

    // Backup Database
    public function backupDatabase()
    {
        // Define the backup path
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        // Get the database credentials from the .env file
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');

        // Set the backup filename
        $filename = $dbName . '_backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
        $backupFile = $backupPath . '/' . $filename;

        // Full path to the mysqldump command
        $mysqldumpPath = '/usr/bin/mysqldump';

        // Ensure the command is executed correctly with the password
        $command = "$mysqldumpPath -h $dbHost -u $dbUser -p$dbPassword $dbName > $backupFile";

        // Use the Process class to run the command
        try {
            $process = new Process([$command]);
            // Set the proper environment variables to ensure the path is found
            $process->setEnv([
                'PATH' => '/usr/bin:/bin:/usr/sbin:/sbin',
            ]);
            $process->mustRun();
            return redirect()->route('admin.systemInfo.index')->with('success', 'Database backup completed successfully.');
        } catch (ProcessFailedException $exception) {
            Log::error('Database backup failed: ' . $exception->getMessage());
            return redirect()->route('admin.systemInfo.index')->with('error', 'Database backup failed. Please check the logs for more details.');
        }
    }





    // Backup Web Directory
    public function backupWebDirectory()
    {
        // Define the backup path
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        // Specify the source directory
        $sourceDirectory = '/var/www/html/';

        // Set the backup filename with current timestamp
        $backupFilename = 'html_backup_' . now()->format('Y_m_d_H_i_s') . '.tar.gz';
        $backupFilePath = $backupPath . '/' . $backupFilename;

        // Create a tar.gz archive of the /var/www/html directory
        $command = "tar -czf $backupFilePath $sourceDirectory";

        // Run the backup command
        $process = new Process([$command]);
        try {
            $process->mustRun();
            return redirect()->route('admin.systemInfo.index')->with('success', 'Web directory backup completed successfully.');
        } catch (ProcessFailedException $exception) {
            return redirect()->route('admin.systemInfo.index')->with('error', 'Web directory backup failed.');
        }
    }

    public function backup(Request $request)
    {
        // Run the backup command
        Artisan::call('backup:run');

        // Return back to the view with a success message
        return back()->with('info', 'Site backup was created successfully !');
    }
}
