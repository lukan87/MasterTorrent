<?php

return [
    // Enable only after provider and worker checks; API/browser uploads do not require it.
    'seedbox_publishing_enabled' => env('UPLOAD_API_SEEDBOX_PUBLISHING_ENABLED', false),
    'torrent_max_kb' => 10240,
    'request_max_kb' => 122880,
    'max_files' => 100000,
    'max_depth' => 64,
    'uploads_per_minute' => 6,
    'reads_per_minute' => 60,
    'announce_base' => 'https://tracker.fileiplay.org/announce/',
];
