<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class AutoUploadService
{
    /**
     * Auto-upload a rebuilt torrent to internal tracker.
     * Returns the created Torrent model.
     */
    public function upload(array $data, string $torrentContent, string $torrentName)
    {
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp_uploads');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        // Save torrent temporarily
        $tempPath = $tempDir . '/' . $torrentName;
        file_put_contents($tempPath, $torrentContent);

        // Convert to UploadedFile
        $uploadedFile = new UploadedFile(
            $tempPath,
            $torrentName,
            'application/x-bittorrent',
            null,
            true // test mode
        );
        $data['torrent'] = $uploadedFile;

        // Call controller store method
        $controller = app(\App\Http\Controllers\TorrentController::class);
        $request = \Illuminate\Http\Request::create('/torrents/store', 'POST', $data);
        $request->files->set('torrent', $uploadedFile);
        $request->setUserResolver(fn() => Auth::user());

        $torrentModel = $controller->store($request);

        // Cleanup temp file
        @unlink($tempPath);

        return $torrentModel; // return the Torrent model
    }
}
