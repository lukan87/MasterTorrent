<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Torrent;
use App\Helpers\Bencode;

class AutoUploadService
{
   public function upload(array $data, string $torrentContent, string $torrentName): array
{
    // 1️⃣ Ensure temp directory exists
    $tempDir = storage_path('app/temp_uploads');
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    // 2️⃣ Proper torrent filename
    $safeName = Str::slug($torrentName) ?: 'torrent';
    $fileName = $safeName . '.torrent';
    $tempPath = $tempDir . '/' . $fileName;

    file_put_contents($tempPath, $torrentContent);

    $uploadedFile = new UploadedFile(
        $tempPath,
        $fileName,
        'application/x-bittorrent',
        null,
        true
    );

    // 3️⃣ Create fake request
    $request = \Illuminate\Http\Request::create('/auto-upload', 'POST', $data);
    $request->files->set('torrent', $uploadedFile);
    $request->setUserResolver(fn () => Auth::user());

    // 4️⃣ Call TorrentUploadService DIRECTLY
    $uploadService = app(\App\Services\Torrent\TorrentUploadService::class);

    $result = $uploadService->handle($request, Auth::user());

    // 5️⃣ Cleanup temp file
    @unlink($tempPath);

    return $result; // ← return array with ['torrent', 'created']
}


    protected function computeInfoHash(string $torrentContent): string
    {
        $decoded = Bencode::bdecode($torrentContent);

        if (!isset($decoded['info'])) {
            throw new \RuntimeException('Invalid torrent: missing info dictionary');
        }

        $infoBencoded = Bencode::bencode($decoded['info']);
        return sha1($infoBencoded);
    }
}
