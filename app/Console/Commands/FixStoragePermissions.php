<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixStoragePermissions extends Command
{
    protected $signature = 'storage:fix';
    protected $description = 'Fix storage & bootstrap/cache permissions, ensure laravel.log exists, and truncate if too big';

    // Max log size in bytes (1 GB)
    protected $maxLogSize = 1 * 1024 * 1024 * 1024;

    public function handle()
    {
        $paths = [storage_path(), base_path('bootstrap/cache')];

        foreach ($paths as $path) {
            // Fix directories recursively
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @chmod($item->getPathname(), 0775);
                } else {
                    @chmod($item->getPathname(), 0664);
                }
            }

            // Change ownership recursively (Linux only, adjust www-data if needed)
            exec("sudo chown -R www-data:www-data " . escapeshellarg($path));
        }

        // Ensure laravel.log exists
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
            @chmod($logFile, 0664);
            exec("sudo chown www-data:www-data " . escapeshellarg($logFile));
        }

        // Truncate log if bigger than max size (1 GB)
        if (file_exists($logFile) && filesize($logFile) > $this->maxLogSize) {
            $resetMessage = "[Log truncated automatically on " . now()->toDateTimeString() . " due to exceeding 1 GB]\n";
            file_put_contents($logFile, $resetMessage);
            $this->info("laravel.log exceeded 1 GB and has been truncated with a reset note.");
        }

        $this->info('Storage permissions fixed and laravel.log ensured.');
    }
}
