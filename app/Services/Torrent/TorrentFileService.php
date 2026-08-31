<?php

namespace App\Services\Torrent;

use App\Models\TorrentFiles;
use App\Helpers\Bencode;
use App\Helpers\TorrentTools;
use Illuminate\Http\Request;

class TorrentFileService
{
    public function parseAndValidate(Request $request): array
    {
        $file = $request->file('torrent');

        if (!$file || !$file->isValid()) {
            abort(400, 'Invalid torrent upload');
        }

        $raw = file_get_contents($file->getRealPath());
        if ($raw === false || $raw === '') {
            abort(400, 'Empty torrent file');
        }

        $torrent = Bencode::bdecode($raw);
        if (!is_array($torrent) || !isset($torrent['info']) || !is_array($torrent['info'])) {
            abort(400, 'Invalid torrent structure');
        }

        $announce = config('tracker.announce') . '?passkey=' . auth()->user()->passkey;
        $torrent['announce'] = $announce;

        unset($torrent['announce-list']);
        unset($torrent['nodes'], $torrent['azureus_properties']);

        if ($request->has('external')) {
            unset($torrent['info']['private']); // public / DHT
        } else {
            $torrent['info']['private'] = 1;    // private
        }

        $rebuiltRaw = Bencode::bencode($torrent);

        // re-decode ONLY for internal processing (hash, files, size)
        $decoded = Bencode::bdecode($rebuiltRaw);
        if (!is_array($decoded) || !isset($decoded['info'])) {
            abort(400, 'Failed to rebuild torrent');
        }

        return [
            'decoded'  => $decoded,     // clean decoded torrent
            'raw'      => $rebuiltRaw,  // exact bytes to save
            'announce' => $announce,
        ];
    }

    public function storeTorrentFile(string $raw): string
    {
        $fileName = uniqid('', true) . '.torrent';

        file_put_contents(
            public_path("files/torrents/{$fileName}"),
            $raw
        );

        return $fileName;
    }

    public function storeFileList($torrent, array $decoded): void
    {
        foreach (TorrentTools::getTorrentFiles($decoded) as $file) {
            TorrentFiles::create([
                'torrent_id' => $torrent->id,
                'filename'   => $file['name'],
                'size'       => $file['size'],
            ]);
        }
    }
}
