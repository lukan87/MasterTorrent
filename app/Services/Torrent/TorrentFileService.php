<?php

namespace App\Services\Torrent;

use App\Helpers\Bencode;
use App\Helpers\TorrentTools;
use App\Models\TorrentFiles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TorrentFileService
{
    public function parseAndValidate(Request $request, ?User $user = null): array
    {
        $user ??= $request->user();
        abort_unless($user && $user->passkey, 403, 'Upload permission is restricted.');
        $file = $request->file('torrent');
        if (! $file || ! $file->isValid() || $file->getSize() > config('upload-api.torrent_max_kb') * 1024) {
            throw ValidationException::withMessages(['torrent' => 'Invalid or oversized torrent upload.']);
        }
        $raw = file_get_contents($file->getRealPath());
        $decoded = app(TorrentParser::class)->decode($raw === false ? '' : $raw);
        $announce = config('upload-api.announce_base').$user->passkey;
        $decoded['announce'] = $announce;
        unset($decoded['announce-list'], $decoded['nodes'], $decoded['azureus_properties']);
        if ($request->boolean('external')) {
            unset($decoded['info']['private']);
        } else {
            $decoded['info']['private'] = 1;
        }
        $rebuilt = Bencode::bencode($decoded);

        return ['decoded' => $decoded, 'raw' => $rebuilt, 'announce' => $announce,
            // Hash the actual info dictionary rather than searching arbitrary byte strings.
            'info_hash' => sha1(Bencode::bencode($decoded['info']))];
    }

    public function storeTorrentFile(string $raw): string
    {
        $name = 'private_'.Str::uuid().'.torrent';
        if (! Storage::disk('local')->put('torrents/'.$name, $raw)) {
            Storage::disk('local')->delete('torrents/'.$name);
            throw new \RuntimeException('Torrent storage failed.');
        }

        return $name;
    }

    /** Keep old files readable without moving or exposing new private files. */
    public function path(?string $name): string
    {
        abort_unless(is_string($name) && preg_match('/^[a-zA-Z0-9_.-]+\.torrent$/D', $name), 404);
        if (str_starts_with($name, 'private_')) {
            return Storage::disk('local')->path('torrents/'.$name);
        }

        return public_path('files/torrents/'.$name);
    }

    public function delete(?string $name): void
    {
        if (! $name) {
            return;
        }
        $path = $this->path($name);
        if (is_file($path) && ! unlink($path)) {
            throw new \RuntimeException('Torrent cleanup failed.');
        }
    }

    public function storeFileList($torrent, array $decoded): void
    {
        $rows = [];
        foreach (TorrentTools::getTorrentFiles($decoded) as $file) {
            $rows[] = ['torrent_id' => $torrent->id, 'filename' => $file['name'], 'size' => $file['size'],
                'created_at' => now(), 'updated_at' => now()];
            if (count($rows) === 500) {
                TorrentFiles::insert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            TorrentFiles::insert($rows);
        }
    }
}
