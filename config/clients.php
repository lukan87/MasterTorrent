<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Banned BitTorrent Clients
    |--------------------------------------------------------------------------
    |
    | List of client identifiers (substrings of User-Agent headers) that
    | should be rejected by the tracker. Add or remove entries as needed.
    |
    */

    'banned' => [
        'BitTorrent/5',
        'BitTorrent/6',
        'BitTorrent/7',
        'uTorrent/2.2',
        'µTorrent/2.2',
        'uTorrent/3.0',
        'µTorrent/3.0',
        'uTorrent/3.1',
        'µTorrent/3.1',
        'qBittorrent/3.3.8',
        'Deluge/1.3.15',
    ],

];
