<?php

namespace App\Services\Torrent;

use App\Models\Torrent;
use App\Models\User;
use App\Models\TorrentLog;
use App\Helpers\Bencode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;

class ExternalTorrentUploadService
{
    public function __construct(
        protected TorrentFileService $fileService,
        protected TorrentMetadataService $metadataService,
        protected TorrentGenreService $genreService,
        protected TorrentImageService $imageService,
        protected TorrentSteamService $steamService
    ) {}

    public function handle(Request $request, User $user): array
    {
        $file = $request->file('torrent');
        $rawTorrent = file_get_contents($file->getRealPath());
        $decoded = Bencode::bdecode($rawTorrent);

        // 1. Set our announce URL
        $announceUrl = 'https://tracker.fileiplay.org/announce/' . $user->passkey;
        $decoded['announce'] = $announceUrl;
        
        // Ensure no other trackers are present if we want to force private
        unset($decoded['announce-list']);

        // Remove private flag to make it DHT/public-friendly
        if (isset($decoded['info']['private'])) {
            unset($decoded['info']['private']);
        }

        // 2. Re-bencode
        $modifiedRawTorrent = Bencode::bencode($decoded);

        // 3. Get new infohash
        $infoHash = Bencode::get_infohash_raw($modifiedRawTorrent);

        // 4. Duplicate Check
        if (Torrent::where('info_hash', $infoHash)->exists()) {
             $torrent = Torrent::where('info_hash', $infoHash)->first();
             return ['torrent' => $torrent, 'created' => false];
        }

        $name = $this->cleanName($request->name);
        $slug = $this->uniqueSlug($name);
        
        $meta = Bencode::get_meta($decoded);

        $metadata = $this->metadataService->resolve($request);

        $torrent = Torrent::create([
            'info_hash'   => $infoHash,
            'file_signature' => $this->buildFileSignature($decoded),
            'name'        => $name,
            'slug'        => $slug,
            'file_name'   => $this->fileService->storeTorrentFile($modifiedRawTorrent),
            'description' => $request->description,
            'poster'      => $request->poster,
            'category_id' => $request->category_id,
            'owner'       => $user->id,
            'size'        => $meta['size'],
            'num_files'   => $meta['count'],
            'announce'    => $announceUrl,
            'external'    => true,
            'seeders'     => 1, // External torrents have seeders
        ]);

        $this->imageService->upload($torrent, $request);

        TorrentLog::create([
            'user_id'    => $user->id,
            'torrent_id' => $torrent->id,
            'action'     => 'uploaded',
            'description' => 'Uploaded external torrent "' . $torrent->name . '" (ID: ' . $torrent->id . ')',
        ]);

        return ['torrent' => $torrent, 'created' => true];
    }

    private function cleanName(string $name): string
    {
        $name = str_replace(['{', '}'], '.', $name);
        $name = preg_replace('/[^A-Za-z0-9\.\-\s]/', '.', $name);
        $name = preg_replace('/[\.]{2,}/', '.', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $i = 1;
        while (Torrent::where('slug', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $i++;
        }
        return $slug;
    }

    private function buildFileSignature(array $decoded): string
    {
        $files = [];
        if (isset($decoded['info']['files'])) {
            foreach ($decoded['info']['files'] as $file) {
                $path = implode('/', $file['path']);
                $files[] = strtolower($path) . ':' . $file['length'];
            }
        } else {
            $files[] = strtolower($decoded['info']['name']) . ':' . $decoded['info']['length'];
        }
        sort($files);
        return sha1(implode('|', $files));
    }
}
