<?php

namespace App\Services;

use App\Helpers\Bencode;

class TorrentRebuildService
{
    protected string $announceUrl;

    public function __construct(string $announceUrl)
    {
        $this->announceUrl = $announceUrl; 
    }

    /**
     * Rebuild the torrent file with your announce URL and metadata
     */
    public function rebuildTorrent(string $rawTorrent): string
    {
        $decoded = Bencode::bdecode($rawTorrent);

        if (!$decoded || !isset($decoded['info'])) {
            throw new \Exception('Invalid torrent file');
        }

        // Replace announce URLs
        $decoded['announce'] = $this->announceUrl;
        $decoded['announce-list'] = [[$this->announceUrl]];
        $decoded['created by'] = config('app.name') . ' Torrent Rebuilder';
        $decoded['creation date'] = time();
        $decoded['comment'] = 'Rebuilt for ' . config('app.name') . ' tracker';

        // Add source inside info dictionary (safe)
        if (isset($decoded['info']) && is_array($decoded['info'])) {
            $decoded['info']['source'] = '' . config('app.name') . ' ';
        }

        // Re-sort dictionaries to ensure valid bencode
       // $decoded = $this->sortDictionaries($decoded);

        return Bencode::bencode($decoded);
    }

    /**
     * Recursively sort dictionaries for valid bencoding
     */
    private function sortDictionaries($data)
    {
        if (is_array($data)) {
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);
            if ($isAssoc) {
                ksort($data, SORT_STRING);
            }
            foreach ($data as $k => $v) {
                $data[$k] = $this->sortDictionaries($v);
            }
        }
        return $data;
    }
}
